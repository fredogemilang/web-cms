<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\SubsidiariesModel;
use App\Models\SubsidiariesLimitModel;
use App\Models\EventAccessModel;
use App\Models\RegistrantModel;
use App\Models\ApprovalTypeModel;
use App\Models\PairingModel;
use App\Models\PairingAdminModel;

use Pusher\Pusher;
use Config\Pusher as PusherConfig;

use App\Libraries\LogActivity;
use App\Libraries\Sendinblue;

use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\ResponseInterface;

class EventGuestController extends BaseController
{
    protected $eventsModel;
    protected $subsidiariesModel;
    protected $subsidiariesLimitModel;
    protected $eventAccessModel;
    protected $registrantModel;
    protected $approvalTypeModel;
    protected $pairingModel;
    protected $pairingAdminModel;
    protected $logActivity;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->subsidiariesModel = new SubsidiariesModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->registrantModel = new RegistrantModel();
        $this->approvalTypeModel = new ApprovalTypeModel();
        $this->pairingModel = new PairingModel();
        $this->pairingAdminModel = new PairingAdminModel();
        $this->logActivity = new LogActivity();
        $this->subsidiariesLimitModel = new SubsidiariesLimitModel();
    }

    public function edit_guests($id)
    {
        $event = $this->eventsModel->find($id);
        
        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
        }

        $approved_reason = $this->approvalTypeModel->where('event_id', $id)->where('cat', "approved")->findAll();
        $reject_reason = $this->approvalTypeModel->where('event_id', $id)->where('cat', "rejected")->findAll();
        $currentUser = auth()->user();
        if($currentUser->inGroup('admin', 'superadmin')){
            $data['is_admin'] = true;
            $data['subs_limit'] = $this->subsidiariesLimitModel->where('event_id', $id)->findAll();
            foreach ($data['subs_limit'] as &$record) {
                $subsidiaryId = $record['subs_id'];

                $subsidiary = $this->subsidiariesModel->find($subsidiaryId);

                if ($subsidiary) {
                    $record['subs_name'] = $subsidiary['short_name']; 
                } else {
                    $record['subs_name'] = 'Unknown'; 
                }
            }
            unset($record);
        }else{
            $data['is_admin'] = false;
            // Fetch the pairing admin data
            $pairingAdmin = $this->pairingAdminModel->where('admin_id', $currentUser->id)->first();
            $subs_id = $pairingAdmin['subs_id'];

            // Build the query based on the subs_id condition
            $query = $this->subsidiariesLimitModel->where('event_id', $id);
            if ($subs_id != 0) {
                $query->where('subs_id', $subs_id);
            }
            $data['subs_limit'] = $query->findAll();

            // Add subsidiary names to the results
            foreach ($data['subs_limit'] as &$record) {
                $subsidiaryId = $record['subs_id'];
                $subsidiary = $this->subsidiariesModel->find($subsidiaryId);
                $record['subs_name'] = $subsidiary ? $subsidiary['short_name'] : 'Unknown';
            }
            unset($record); // Unset the reference
        }

        $pusherConfig = new PusherConfig();
        $data['pusher'] = $pusherConfig->settings;

        $data['title'] = 'guests';
        $data['event'] = $event;
        $data['cti_subs'] = $this->subsidiariesModel->findAll();
        $data['approved_reason'] = $approved_reason;
        $data['reject_reason'] = $reject_reason;
        //hanya untuk pko
        foreach ($data['cti_subs'] as $sub) {
            $is_limit = $this->subsidiariesLimitModel->where('event_id', 318)->where('subs_id', $sub['id'])->first();

            if ($is_limit) {
                $countBPVIP = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 121)->where('reg_type', 'BP')->countAllResults();
                $countBPVIPAwardee = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 123)->where('reg_type', 'BP')->countAllResults();
                $countBPVIP = $countBPVIP + $countBPVIPAwardee;

                $countBPRegular = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 119)->where('reg_type', 'BP')->countAllResults();

                $countPrincipalVIP = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 120)->where('reg_type', 'Principal')->countAllResults();
                $countPrincipalVIPAwardee = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 122)->where('reg_type', 'Principal')->countAllResults();
                $countPrincipalVIP = $countPrincipalVIP + $countPrincipalVIPAwardee;

                $countPrincipalRegular = $this->registrantModel->where('event_id', 318)->where('from_subs', $sub['id'])->where('verified_type', 119)->where('reg_type', 'Principal')->countAllResults();

                $limitBP = $this->subsidiariesLimitModel->where('event_id', 318)->where('subs_id', $sub['id'])->where('type', 'BP')->first();
                $limitPrincipal = $this->subsidiariesLimitModel->where('event_id', 318)->where('subs_id', $sub['id'])->where('type', 'Principal')->first();

                $slotBPRegular = $limitBP['regular'] - $countBPRegular;
                $slotBPVIP = $limitBP['vip'] - $countBPVIP;

                $slotPrincipalRegular = $limitPrincipal['regular'] - $countPrincipalRegular;
                $slotPrincipalVIP = $limitPrincipal['vip'] - $countPrincipalVIP;
                
                //update subsidiaries limit 
                $this->subsidiariesLimitModel
                    ->where('event_id', 318)->where('subs_id', $sub['id'])->where('type', 'BP')
                    ->set(['curr_regular' => $slotBPRegular, 'curr_vip' => $slotBPVIP])
                    ->update();
                
                $this->subsidiariesLimitModel
                    ->where('event_id', 318)->where('subs_id', $sub['id'])->where('type', 'Principal')
                    ->set(['curr_regular' => $slotPrincipalRegular, 'curr_vip' => $slotPrincipalVIP])
                    ->update();
            }
            
        };

        return view('events/guests', $data);
    }

    public function getGuests($eventID, $status)
    {
        $currentUser = auth()->user();
        $event = $this->eventsModel->find($eventID);
        
        if($currentUser->inGroup('admin', 'superadmin')){
            if($status == 'all'){
                $guests = $this->registrantModel->where('event_id', $eventID)->findAll();
            }else{
                $guests = $this->registrantModel->where('event_id', $eventID)->where('status', $status)->findAll();
            }
        }else{
            $pairingAdmin = $this->pairingAdminModel->where('admin_id', $currentUser->id)->first(); 
            $subs_id = $pairingAdmin['subs_id'];
            if($subs_id == 0){
                if($status == 'all'){
                    $guests = $this->registrantModel->where('event_id', $eventID)->findAll();
                }else{
                    $guests = $this->registrantModel->where('event_id', $eventID)->where('status', $status)->findAll();
                }
            }else{
                if($status == 'all'){
                    $guests = $this->registrantModel->getPairingRegistrant($eventID, $subs_id);
                }
                else if($status=="approved"){
                    $guests = $this->registrantModel->where('event_id', $eventID)->where('status', $status)->where('from_subs', $subs_id)->findAll();
                }
                else if($status=="rejected"){
                    $guests = $this->registrantModel->where('event_id', $eventID)->where('status', $status)->where('from_subs', $subs_id)->findAll();
                }
                else{
                    $guests = $this->registrantModel->getPairingRegistrant($eventID, $subs_id, $status);
                }
            }
            
        }

        foreach ($guests as &$reg) {
            
            $reg['humanTime'] = Time::parse($reg['created_on'])->humanize();

            $approvalType = $this->approvalTypeModel->where('id', $reg['verified_type'])->first();
            $reg['approval_name'] = $approvalType ? $approvalType['type_name'] : '-';

            $type = $this->pairingModel->where('email', $reg['email'])->where('event_id', $eventID)->first();
            $reg['type'] = $type ? $type['pair_type'] : '';
            $reg['subs_id'] = $type ? $type['lead_of'] : '0';

            $sid = $reg['from_subs'] !== '0' ? $reg['from_subs'] : $reg['subs_id'];

            if ($sid !== '0') {
                $subs_data = $this->subsidiariesModel->find($sid);
                $reg['subs_name'] = $subs_data['short_name'] ?? '-';
            } else {
                $reg['subs_name'] = '-';
            }
        }
        
        unset($reg);
        return $this->response->setJSON($guests);
    }

    public function getGuestCounts($eventID)
    {
        $currentUser = auth()->user();
        $event = $this->eventsModel->find($eventID);

        if (!$event) {
            return $this->response->setJSON(['error' => 'Event not found'])->setStatusCode(404);
        }

        if ($currentUser->inGroup('admin', 'superadmin')) {
            $pending = $this->registrantModel->where('event_id', $eventID)->where('status', 'pending')->countAllResults();
            $approved = $this->registrantModel->where('event_id', $eventID)->where('status', 'approved')->countAllResults();
            $rejected = $this->registrantModel->where('event_id', $eventID)->where('status', 'rejected')->countAllResults();
        } else {
            $pairingAdmin = $this->pairingAdminModel->where('admin_id', $currentUser->id)->first();
            $subs_id = $pairingAdmin['subs_id'] ?? 0;

            if ($subs_id == 0) {
                $pending = $this->registrantModel->where('event_id', $eventID)->where('status', 'pending')->countAllResults();
                $approved = $this->registrantModel->where('event_id', $eventID)->where('status', 'approved')->countAllResults();
                $rejected = $this->registrantModel->where('event_id', $eventID)->where('status', 'rejected')->countAllResults();
            } else {
                $pending_result  = $this->registrantModel->getPairingRegistrant($eventID, $subs_id, 'pending');

                $pending = count($pending_result);
                $approved = $this->registrantModel->where('event_id', $eventID)->where('status', 'approved')->where('from_subs', $subs_id)->countAllResults();
                $rejected = $this->registrantModel->where('event_id', $eventID)->where('status', 'rejected')->where('from_subs', $subs_id)->countAllResults();
            }
        }
        $data = [
            'pending'  => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ];

        return $this->response->setJSON($data);
    }

    public function pairing_guests()
    {
        $request = service('request');
        $json = $request->getJSON();

        if (empty($json->guestId) || empty($json->guestSubsId)|| empty($json->pair_type)|| empty($json->eventId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid input data.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $registrant = $this->registrantModel->find($json->guestId);
        if (!$registrant) { 
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Registrant not found.'
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $registrantIsPaired = $this->pairingModel->where('email', $registrant['email'])->where('event_id', $json->eventId)->first();
        if ($registrantIsPaired) {
            //update pairing
            $this->pairingModel->update($registrantIsPaired['id'], [
                'lead_of' => $json->guestSubsId,
                'pair_type' => $json->pair_type,
            ]);
        } else {
            //insert pairing
            $this->pairingModel->insert([
                'event_id' => $json->eventId,
                'name' => $registrant['full_name'],
                'email' => $registrant['email'],
                'company' => $registrant['company_name'],
                'title' => $registrant['job_title'],
                'level' => $registrant['job_level'],
                'pair_type' => $json->pair_type,
                'lead_of' => $json->guestSubsId
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pairing updated successfully.'
        ])->setStatusCode(ResponseInterface::HTTP_OK); 
    }

    public function updateCounter()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['id']) || !in_array($data['action'], ['tambah', 'kurang'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak valid']);
        }

        $id = $data['id'];
        $action = $data['action'];

        $parts = explode('_', $id);
        if (count($parts) === 3) {
            $guestType = $parts[0];    // e.g., "BP"
            $sendReason = $parts[1];   // e.g., "regular"
            $guestSubsId = $parts[2];  // e.g., "4"

            $guestSubsId = (int)$guestSubsId;
            $counter_data = $this->subsidiariesLimitModel->where('type', $guestType)->where('subs_id', $guestSubsId)->first();
            $subs_data = $this->subsidiariesModel->find($guestSubsId);
            $subs_name = $subs_data['short_name'];
            $ind_curr = 'curr_' . $sendReason;
            $counters = $counter_data[$ind_curr];
            $counters = (int)$counters;

            // Update counter berdasarkan instruksi
            if ($action === 'tambah') {
                $counters++;
            } elseif ($action === 'kurang') {
                if ($counters > 0) {
                    $counters--;
                }else{
                    return $this->response->setJSON(['status' => 'error', 'message' => $subs_name.' have reached '.$sendReason.' '.$guestType.' approval limit: 0/'.$counter_data[$sendReason].'.']);
                }
                
            }

            // Simpan kembali ke session (atau database)
            $this->subsidiariesLimitModel->update($counter_data['id'], [$ind_curr => $counters]);

            // Load konfigurasi Pusher
            $pusherConfig = new PusherConfig();
            $settings = $pusherConfig->settings;

            // Inisialisasi Pusher
            $pusher = new Pusher(
                $settings['key'],
                $settings['secret'],
                $settings['app_id'],
                ['cluster' => $settings['cluster'], 'useTLS' => $settings['useTLS']]
            );

            // Kirim event ke channel 'counter-updates'
            $pusher->trigger('counter-updates', 'update-counter', [
                'id' => $id,
                'value' => $counters,
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Counter berhasil diperbarui']);
        } else {
            // Handle invalid input
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid input format. Expected format: guestType_sendReason_guestSubsId',
            ])->setStatusCode(400);
        }
    }

    public function approve()
    {
        $input = $this->request->getJSON(true);

        if (!isset($input['guestId']) || !isset($input['reasonId'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid input.'])->setStatusCode(400);
        }

        // Example logic for approval
        $guestId = $input['guestId'];
        $reasonId = $input['reasonId'];
        $subsId = $input['subsId'];
        $type = $input['type'];
        $user = auth()->user();

        $updateData = [
            'status'    => 'approved',
            'verified_type' => $reasonId,
            'from_subs' => $subsId,
            'verified_by' => $user->id,
            'verified_date' => date('Y-m-d H:i:s'), 
            'reg_type' => $type
        ];

        $success = $this->registrantModel->update($guestId, $updateData);

        if ($success) {
            $reason = $this->approvalTypeModel->find($reasonId);
            $registrant = $this->registrantModel->find($guestId);
            if($type == NULL){
                $this->logActivity->log('Approve Guest ' . $registrant['email'].' as '.$reason['type_name']);
            }else{
                $this->logActivity->log('Approve Guest ' . $registrant['email'].' as '.$reason['type_name'].' ('.$type.')');
            }
            
            $this->send_notif($registrant, $reasonId);

            return $this->response->setJSON(['success' => true, 'message' => 'Guest approved successfully.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to approve guest.'])->setStatusCode(500);
    }

    public function reject()
    {
        $input = $this->request->getJSON(true);

        if (!isset($input['guestId']) || !isset($input['reasonId'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid input.'])->setStatusCode(400);
        }

        // Example logic for approval
        $guestId = $input['guestId'];
        $reasonId = $input['reasonId'];
        $type = $input['type'];
        $user = auth()->user();

        $updateData = [
            'status'    => 'rejected',
            'verified_type' => $reasonId,
            'verified_by' => $user->id,
            'verified_date' => date('Y-m-d H:i:s'),
            'reg_type' => $type 
        ];

        $success = $this->registrantModel->update($guestId, $updateData);

        if ($success) {
            $reason = $this->approvalTypeModel->find($reasonId);
            $registrant = $this->registrantModel->find($guestId);
            if($type == NULL){
                $this->logActivity->log('Reject Guest ' . $registrant['email'].' as '.$reason['type_name']);
            }else{
                $this->logActivity->log('Reject Guest ' . $registrant['email'].' as '.$reason['type_name'].' ('.$type.')');
            }

            $this->send_notif($registrant, $reasonId);
            return $this->response->setJSON(['success' => true, 'message' => 'Guest rejected successfully.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to reject guest.'])->setStatusCode(500);
    }

    private function send_notif($data, $reasonId)
    {
        $pending = $this->approvalTypeModel->where([
            'id' => $reasonId,
            'event_id'      => $data['event_id']
        ])->first();

        $event = $this->eventsModel->where([
            'id'      => $data['event_id']
        ])->first();

        $company = $this->subsidiariesModel->where([
            'id'      => $event['subs_id']
        ])->first();

        $dataEmail = [
            'title'     => $pending['email_subject'],
            'preheader' => 'Thank You for Registering to Our Event '.date('Y-m-d H:i:s'),
            'image_url' => base_url('uploads/events/'.$pending['email_banner']),  // Or null if no image
            'name'      => $data['full_name'],
            'company'   => $company['name'],
        ];
        $htmlContent = Services::parser()->setData($dataEmail)->render('emails/allmail');

        $htmlContent = str_replace(
            ['{content}'],
            [$pending['email_body']],
            $htmlContent
        );

        $urlqr = base_url('qr/'.$event['slug'].'/'.$data['uuid']);

        $htmlContent = str_replace(
            ['{url_qr}'],
            [$urlqr],
            $htmlContent
        );

        $sendinblue = new Sendinblue();

        $subject = $pending['email_subject'];
        $html = $htmlContent;
        $from_name = $event['sender_name'];
        $from_email = $event['sender_email'];
        $to = [['email' => $data['email'], 'name' => $data['full_name'] ]];

        $result = $sendinblue->sendinblue_email($subject, $html, $from_name, $from_email, $to);
    }
}