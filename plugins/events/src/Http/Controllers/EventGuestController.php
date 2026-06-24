<?php

namespace Plugins\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Plugins\Events\Models\ApprovalType;
use Plugins\Events\Models\Event;
use Plugins\Events\Models\EventRegistration;
use Plugins\Events\Services\EventGuestService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventGuestController extends Controller
{
    public function __construct(
        protected EventGuestService $guestService
    ) {}

    /**
     * Display the guest list page for an event.
     */
    public function index(Event $event): \Illuminate\View\View
    {
        $approvalTypes = ApprovalType::where('event_id', $event->id)
            ->orWhere('event_id', 0)
            ->get()
            ->groupBy('cat');

        return view('events::admin.guests.index', [
            'event' => $event,
            'approvalTypes' => $approvalTypes,
            'remainingSlots' => $this->guestService->getRemainingSlots($event),
        ]);
    }

    /**
     * Approve a single guest registration.
     */
    public function approve(Request $request, Event $event, EventRegistration $registration): JsonResponse
    {
        $request->validate([
            'approval_type_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        $approvalType = ApprovalType::findOrFail($request->approval_type_id);

        // Only allow approving pending registrations
        if ($registration->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending registrations can be approved.',
            ], 422);
        }

        try {
            $this->guestService->approve(
                $event,
                $registration,
                $approvalType,
                $request->input('note')
            );

            return response()->json([
                'success' => true,
                'message' => 'Guest approved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject a guest registration.
     */
    public function reject(Request $request, Event $event, EventRegistration $registration): JsonResponse
    {
        $request->validate([
            'approval_type_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        $approvalType = ApprovalType::findOrFail($request->approval_type_id);

        // Only allow rejecting pending or approved registrations
        if (!in_array($registration->status, ['pending', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'This registration cannot be rejected.',
            ], 422);
        }

        try {
            $this->guestService->reject(
                $event,
                $registration,
                $approvalType,
                $request->input('note')
            );

            return response()->json([
                'success' => true,
                'message' => 'Guest rejected successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk approve all pending registrations for an event.
     */
    public function bulkApprove(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'approval_type_id' => 'required|integer',
        ]);

        $approvalType = ApprovalType::findOrFail($request->approval_type_id);

        $pendingRegs = EventRegistration::where('event_id', $event->id)
            ->pending()
            ->get();

        $count = $this->guestService->bulkApprove($event, $pendingRegs, $approvalType);

        return response()->json([
            'success' => true,
            'message' => "{$count} guest(s) approved successfully.",
            'count' => $count,
        ]);
    }

    /**
     * Bulk reject all pending registrations for an event.
     */
    public function bulkReject(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'approval_type_id' => 'required|integer',
        ]);

        $approvalType = ApprovalType::findOrFail($request->approval_type_id);

        $pendingRegs = EventRegistration::where('event_id', $event->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        $count = $this->guestService->bulkReject($event, $pendingRegs, $approvalType);

        return response()->json([
            'success' => true,
            'message' => "{$count} guest(s) rejected.",
            'count' => $count,
        ]);
    }

    /**
     * Inline update a registration field.
     */
    public function inlineUpdate(Request $request, Event $event, EventRegistration $registration): JsonResponse
    {
        $allowedFields = ['full_name', 'name', 'email', 'phone', 'mobile_phone', 'organization', 'company_name', 'job_title', 'notes'];

        $request->validate([
            'field' => 'required|string|in:' . implode(',', $allowedFields),
            'value' => 'nullable|string|max:255',
        ]);

        $field = $request->input('field');
        $value = $request->input('value');

        // Validate email format if updating email
        if ($field === 'email' && $value) {
            $request->validate(['value' => 'email']);
        }

        // Prevent updating registrations from other events
        if ($registration->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Invalid registration.'], 422);
        }

        $registration->update([$field => $value]);

        return response()->json([
            'success' => true,
            'message' => 'Field updated.',
            'value' => $value,
        ]);
    }

    /**
     * Check-in a guest.
     */
    public function checkin(Request $request, Event $event, EventRegistration $registration): JsonResponse
    {
        if ($registration->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Invalid registration.'], 422);
        }

        $registration->checkIn();

        return response()->json([
            'success' => true,
            'message' => 'Guest checked in successfully.',
        ]);
    }

    /**
     * Export guest list as Excel.
     */
    public function export(Event $event): StreamedResponse
    {
        $registrations = EventRegistration::with(['event', 'user', 'verifiedBy'])
            ->where('event_id', $event->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'ID', 'UUID', 'Salutation', 'Full Name', 'Email', 'Phone', 'Company',
            'Company Type', 'Job Title', 'Status', 'Walk-in', 'Checked In',
            'Registered At', 'Approved At', 'Verified By', 'Verified At',
            'Verified Type', 'Verified Note', 'Referral Source',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $rowNumber = 2;
        foreach ($registrations as $reg) {
            $sheet->fromArray([
                $reg->id,
                $reg->uuid,
                $reg->salutation ?? '',
                $reg->full_name ?? $reg->name,
                $reg->email,
                $reg->mobile_phone ?? $reg->phone ?? '',
                $reg->company_name ?? $reg->organization ?? '',
                $reg->company_type ?? '',
                $reg->job_title ?? '',
                ucfirst($reg->status),
                $reg->walk_in ? 'Yes' : 'No',
                $reg->check_in ? 'Yes' : 'No',
                $reg->created_at->format('Y-m-d H:i:s'),
                $reg->approved_at?->format('Y-m-d H:i:s') ?? '',
                $reg->verifiedBy?->name ?? '',
                $reg->verified_at?->format('Y-m-d H:i:s') ?? '',
                $reg->verified_type ?? '',
                $reg->verified_note ?? '',
                $reg->referral_source ?? '',
            ], null, 'A' . $rowNumber);
            $rowNumber++;
        }

        $filename = Str::slug($event->title) . '-guests-' . date('Ymd') . '.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}