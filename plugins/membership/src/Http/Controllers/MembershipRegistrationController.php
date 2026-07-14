<?php

namespace Plugins\Membership\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Plugins\Membership\Models\Membership;

class MembershipRegistrationController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cleaned = preg_replace('/[^\d]/', '', $value);
                    if (strlen($cleaned) < 9 || strlen($cleaned) > 13) {
                        $fail('The phone number must be between 9 and 13 digits.');
                    }
                },
            ],
            'job_level' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'domicile' => 'required|string|max:100',
            'domicile_other' => 'required_if:domicile,Other|nullable|string|max:100',
            'institution' => 'required|string|max:255',
            'industry' => 'required|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'linkedin' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/)?(www\.)?linkedin\.com\/.*$/i'],
        ], [
            'linkedin.regex' => 'LinkedIn account must be a valid LinkedIn URL.',
        ]);

        if ($validated['domicile'] === 'Other' && ! empty($validated['domicile_other'])) {
            $validated['domicile'] = $validated['domicile_other'];
        }
        unset($validated['domicile_other']);

        // Check if email already registered as member
        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            $existingMembership = Membership::withTrashed()->where('user_id', $existingUser->id)->first();
            if ($existingMembership && ! $existingMembership->trashed()) {
                return back()->withErrors(['email' => 'Email ini sudah terdaftar sebagai member.'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Create or get user
            if (! $existingUser) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make(Str::random(16)), // Random password, user can reset later
                ]);
            } else {
                $user = $existingUser;
            }

            // Create membership with metadata
            $membershipData = [
                'user_id' => $user->id,
                'status' => 'pending',
                'metadata' => [
                    'phone' => $validated['phone'] ?? null,
                    'job_level' => $validated['job_level'] ?? null,
                    'job_title' => $validated['job_title'] ?? null,
                    'domicile' => $validated['domicile'] ?? null,
                    'institution' => $validated['institution'] ?? null,
                    'industry' => $validated['industry'] ?? null,
                    'education_level' => $validated['education_level'] ?? null,
                    'linkedin' => $validated['linkedin'] ?? null,
                ],
            ];

            $existingMembership = Membership::withTrashed()->where('user_id', $user->id)->first();

            if ($existingMembership) {
                $existingMembership->restore();
                $existingMembership->update($membershipData);
            } else {
                Membership::create($membershipData);
            }

            DB::commit();

            // Check if there's a custom redirect (e.g., from homepage form)
            if ($request->has('redirect_to') && $request->redirect_to) {
                return redirect($request->redirect_to)->with('success', 'Pendaftaran berhasil! Tim kami akan meninjau aplikasi Anda.');
            }

            return redirect()->route('membership.success')->with('success', 'Pendaftaran berhasil! Tim kami akan meninjau aplikasi Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Membership Registration Error: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withErrors(['error' => 'Terjadi kesalahan. Silakan coba lagi.'])->withInput();
        }
    }
}
