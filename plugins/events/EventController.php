<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\SubsidiariesModel;
use App\Models\SubsidiariesLimitModel;
use App\Models\EventAccessModel;
use App\Models\ApprovalTypeModel;
use App\Libraries\LogActivity;

use CodeIgniter\Config\Services;
use DateTime;

class EventController extends BaseController
{
    protected $eventsModel;
    protected $subsidiariesModel;
    protected $subsidiarysLimitModel;
    protected $eventAccessModel;
    protected $approvalTypeModel;
    protected $logActivity;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->subsidiariesModel = new SubsidiariesModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->approvalTypeModel = new ApprovalTypeModel();
        $this->logActivity = new LogActivity();
        $this->subsidiarysLimitModel = new SubsidiariesLimitModel();
    }

    public function index()
    {
        $currentUser = auth()->user();
        $users_model = auth()->getProvider();
        $events = $this->eventsModel->getEventsByUserId($currentUser->id);

        foreach ($events as &$event) {
            $event['canEdit'] = $this->eventAccessModel->hasAccess($currentUser->id, $event['id'], 'edit');
            $event['guests'] = $this->registrantModel->countRegistrantsByEventId($event['id']);
        }

        $admins = $users_model->findAll();
        
        $data = [
            'title' => 'Manage Events',
            'events' => $events,
            'admins' => $admins,
            'currentUser' => $currentUser,
        ];

        return view('events/index', $data);
    }

    // Share access to an event
    public function shareAccess($eventId)
    {
        $user = service('auth')->user();
        $event = $this->eventsModel->find($eventId);

        // Ensure the user has access to share this event
        if (!$event || (!$user->inGroup('admin', 'superadmin') && $event['created_by'] !== $user->id)) {
            return redirect()->back()->with('error', 'You do not have permission to share this event.');
        }

        $admins = $this->userModel->getAllAdmins();

        return view('events/share_access', [
            'event' => $event,
            'admins' => $admins,
            'sharedAccess' => $this->eventAccessModel->getAccessByEventId($eventId),
        ]);
    }

    // Save shared access
    public function saveSharedAccess($eventId)
    {
        $user = service('auth')->user();
        $event = $this->eventsModel->find($eventId);

        // Ensure the user has access to modify sharing
        if (!$event || (!$user->inGroup('admin', 'superadmin') && $event['created_by'] !== $user->id)) {
            return redirect()->back()->with('error', 'You do not have permission to modify access for this event.');
        }

        $sharedAccess = $this->request->getPost('shared_access'); // array of admin IDs and access types
        $this->eventAccessModel->updateEventAccess($eventId, $sharedAccess);

        return redirect()->to('/adminpanel/all-events')->with('message', 'Access shared successfully.');
    }

    private function convertToYMD($date)
    {
        return \DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d H:i');
    }

    public function add()
    {
        $data = [
            'title' => 'Add Event',
            'subsidiaries' => $this->subsidiariesModel->getAllSubsidiaries(),
        ];

        return view('events/wizard', $data);
    }

    public function draft($eventId = NULL)
    {
        if ($eventId == NULL) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
        }else{
            $event = $this->eventsModel->find($eventId);

            if (!$event) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
            }
            else{
                $tgl_event = new DateTime($event['tgl_event']);
                $event['tgl_event'] = $tgl_event->format('d/m/Y H:i');
                $tgl_event_end = new DateTime($event['tgl_event_end']);
                $event['tgl_event_end'] = $tgl_event_end->format('d/m/Y H:i');
                $tgl_start = new DateTime($event['tgl_start']);
                $event['tgl_start'] = $tgl_start->format('d/m/Y H:i');
                $tgl_end = new DateTime($event['tgl_end']);
                $event['tgl_end'] = $tgl_end->format('d/m/Y H:i');

                $data = [
                    'title' => 'Add Event',
                    'event' => $event,
                    'subsidiaries' => $this->subsidiariesModel->getAllSubsidiaries(),
                ];

                return view('events/wizard', $data);   
            }
        }
    }

    public function generateUniqueSlug($eventName)
    {
        $length = 8;

        do {
            // Generate a random string containing only alphabets (lowercase and uppercase)
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= chr(mt_rand(0, 1) ? mt_rand(65, 90) : mt_rand(97, 122));
            }

            // Check if the slug already exists
            $slugExists = $this->eventsModel->getEventBySlug($randomString);
        } while ($slugExists);

        return $randomString;
    }

    public function ajax_submit_form($step = null)
    {
        $eventId = $this->request->getPost('event_id');
        if ($eventId) {

            $event = $this->eventsModel->find($eventId);
            if (!$event) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
            }

            $rules = [];
            switch ($step) {

                case 'eventDetailForm':
                    $rules = [
                        'event_name' => ['label' => 'Event Name', 'rules' => 'required|max_length[255]'],
                        'subs' => ['label' => 'Subsidiary', 'rules' => 'required|integer'],
                        'seo_desc' => ['label' => 'SEO Description', 'rules' => 'required|max_length[255]'],
                        'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                    ];

                    $subsID = $this->request->getPost('subs');
                    $subsdata = $this->subsidiariesModel->find($subsID);
                    $favicon = $subsdata['logo'];

                    $updateData = [
                        'name' => $this->request->getPost('event_name'),
                        'subs_id' => $subsID,
                        'logo' => $favicon,
                        'description' => $this->request->getPost('event_desc'),
                        'step' => 1,
                    ];
                break;

                case 'eventDateForm':
                    $rules = [
                        'start_date' => ['label' => 'Registration Date Start', 'rules' => 'required'],
                        'end_date' => ['label' => 'Registration Date End', 'rules' => 'required'],
                        'event_date_start' => ['label' => 'Event Date Start', 'rules' => 'required'],
                        'event_date_end' => ['label' => 'Event Date End', 'rules' => 'required'],
                        'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                    ];
                    $updateData = [
                        'tgl_event' => $this->convertToYMD($this->request->getPost('event_date_start')),
                        'tgl_event_end' => $this->convertToYMD($this->request->getPost('event_date_end')),
                        'tgl_start' => $this->convertToYMD($this->request->getPost('start_date')),
                        'tgl_end' => $this->convertToYMD($this->request->getPost('end_date')),
                        'step' => 2,
                    ];
                break;

                case 'eventPropertiesForm':
                    $rules = [
                        'event_logo' => 'permit_empty|is_image[event_logo]|max_size[event_logo,300]|ext_in[event_logo,jpg,jpeg,png]',
                        'event_banner' => 'permit_empty|is_image[event_banner]|max_size[event_banner,500]|ext_in[event_banner,jpg,jpeg,png]',
                        'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                    ];

                    $ccEmails = $this->request->getPost('cc_to_email');
                    $ccEmailsArray = json_decode($ccEmails[0], true);
                    if($ccEmailsArray !== NULL){
                        $ccEmailsString = implode(', ', array_column($ccEmailsArray, 'value'));
                    }else{
                        $ccEmailsString = "";
                    }
                    

                    $quota = $this->request->getPost('quota') === "" ? NULL : $this->request->getPost('quota');

                    $logoName = $event['logo'];
                    if ($this->request->getFile('event_logo')->isValid()) {
                        $logo = $this->request->getFile('event_logo');
                        $logoName = $logo->getRandomName();
                        $logo->move('uploads/events', $logoName);
                    }

                    $bannerName = $event['banner'];
                    if ($this->request->getFile('event_banner')->isValid()) {
                        $banner = $this->request->getFile('event_banner');
                        $bannerName = $banner->getRandomName();
                        $banner->move('uploads/events', $bannerName);
                    }

                    

                    $updateData = [
                        'corpo_email' => $this->request->getPost('corpo_email') ? 'yes' : 'no',
                        'one_company' => $this->request->getPost('one_company') ? 1 : 0,
                        'is_approval' => $this->request->getPost('is_approval') ? 1 : 0,
                        'quota' => $quota,
                        'default_company' => $this->request->getPost('default_company'),
                        'logo' => $logoName,
                        'banner' => $bannerName,
                        'sender_email' => $this->request->getPost('sender_email'),
                        'sender_name' => $this->request->getPost('sender_name'),
                        'to_email' => $this->request->getPost('to_email'),
                        'to_name' => $this->request->getPost('to_name'),
                        'cc_to_email' => $ccEmailsString,
                        'step' => 3,
                    ];

                    $parser = \Config\Services::parser();

                    $dataDefault= [
                            'event' => $event['name'],
                            'email' => $event['sender_email']
                        ];
                    $htmlDefault = $parser->setData($dataDefault)->render('emails/default');

                    $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'default',
                        'type_name' => 'Default no approval',
                        'email_subject' => 'Registration Notification - ' . $event['name'],
                        'email_banner' => $bannerName,
                        'email_body' => $htmlDefault,
                    ]);

                    $dataPending= [
                            'event' => $event['name'],
                            'email' => $event['sender_email']
                        ];
                    $htmlPending = $parser->setData($dataPending)->render('emails/pending');

                    $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'pending',
                        'type_name' => 'Pending',
                        'email_subject' => 'Pending Notification - ' . $event['name'],
                        'email_banner' => $bannerName,
                        'email_body' => $htmlPending,
                    ]);

                    $dataApproved= [
                            'event' => $event['name'],
                            'email' => $event['sender_email']
                        ];
                    $htmlApproved = $parser->setData($dataApproved)->render('emails/approve');

                    $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'approved',
                        'type_name' => 'Regular',
                        'email_subject' => 'Approve Notification - ' . $event['name'],
                        'email_banner' => $bannerName,
                        'email_body' => $htmlApproved,
                    ]);

                    $dataRejected= [
                            'event' => $event['name'],
                            'email' => $event['sender_email']
                        ];
                    $htmlReject = $parser->setData($dataRejected)->render('emails/reject');

                    $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'rejected',
                        'type_name' => 'Not Eligible',
                        'email_subject' => 'Reject Notification - ' . $event['name'],
                        'email_banner' => $bannerName,
                        'email_body' => $htmlReject,
                    ]);

                    break;

                case 'eventSuccessForm':
                    
                    $success_link = $this->request->getPost('success_link_type') === 'custom'
                    ? $this->request->getPost('custom_success_link')
                    : base_url($event["slug"]);

                    $rules = [
                        'success_title' => ['label' => 'Success Title', 'rules' => 'required'],
                        'success_desc' => ['label' => 'Success Description', 'rules' => 'required'],
                        'success_button' => ['label' => 'Success Button', 'rules' => 'required'],
                        'success_link_type' => ['label' => 'Success Link Type', 'rules' => 'required|in_list[event,custom]'],
                        'custom_success_link' => ['label' => 'Custom Success Link', 'rules' => 'permit_empty|valid_url'],
                        'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                    ];
                    $updateData = [
                        'success_title' => $this->request->getPost('success_title'),
                        'success_desc' => $this->request->getPost('success_desc'),
                        'success_button' => $this->request->getPost('success_button'),
                        'success_link_type' => $this->request->getPost('success_link_type'),
                        'success_link' => $success_link,
                        'status' => 'on',
                        'step' => 4,
                    ];

                    break;

                default:
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'Invalid step provided.',
                    ]);
            }
            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                ]);

            }else{
                $this->eventsModel->update($eventId, $updateData);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Event updated successfully.',
                    'event_id' => $eventId,
                ]);
            }

        } else {
            
            $rules = [
                'event_name' => ['label' => 'Event Name', 'rules' => 'required|max_length[255]'],
                'seo_desc' => ['label' => 'SEO Description', 'rules' => 'required|max_length[255]'],
                'subs' => ['label' => 'Subsidiary', 'rules' => 'required|integer'],
            ];

            if (!$this->validate($rules)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                ]);
            }else{
                $subsID = $this->request->getPost('subs');
                $eventName = $this->request->getPost('event_name');
                $slug = $this->generateUniqueSlug($eventName);
                $currentUser = auth()->user();
                $subsdata = $this->subsidiariesModel->find($subsID);
                $favicon = $subsdata['logo'];
                
                $eventId = $this->eventsModel->insert([
                    'name' => $this->request->getPost('event_name'),
                    'slug' => $slug,
                    'subs_id' => $subsID,
                    'logo' => $favicon,
                    'description' => $this->request->getPost('event_desc'),
                    'seo_description' => $this->request->getPost('seo_desc'),
                    'step' => 0,
                    'created_by' => $currentUser->id,
                ], true);

                $this->eventAccessModel->insert([
                    'event_id' => $eventId,
                    'admin_id' => $currentUser->id,
                    'access_type' => 'edit'
                ], true);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Event created successfully.',
                    'event_id' => $eventId,
                ]);
            }
            
        }
    }

    public function ajax_edit_event($formType = null)
    {
        $eventId = $this->request->getPost('event_id');
        if (!$eventId) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Event ID is required.',
            ]);
        }

        $event = $this->eventsModel->find($eventId);
        if (!$event) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Event not found.',
            ]);
        }

        $rules = [];
        $updateData = [];

        switch ($formType) {
            case 'eventForm':
                $rules = [
                    'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                    'event_name' => ['label' => 'Event Name', 'rules' => 'required|max_length[255]'],
                    'subs' => ['label' => 'Subsidiary', 'rules' => 'required|integer'],
                    'seo_desc' => ['label' => 'SEO Description', 'rules' => 'required|max_length[255]'],
                    'start_date' => ['label' => 'Registration Date Start', 'rules' => 'required'],
                    'end_date' => ['label' => 'Registration Date End', 'rules' => 'required'],
                    'event_date_start' => ['label' => 'Event Date Start', 'rules' => 'required'],
                    'event_date_end' => ['label' => 'Event Date End', 'rules' => 'required'],
                    'quota' => ['label' => 'Capacity', 'rules' => 'permit_empty|integer'],
                    'event_logo' => 'permit_empty|is_image[event_logo]|max_size[event_logo,1024]|ext_in[event_logo,jpg,jpeg,png]',
                    'event_banner' => 'permit_empty|is_image[event_banner]|max_size[event_banner,2048]|ext_in[event_banner,jpg,jpeg,png]',
                ];

                // Prepare data for updating
                $subsID = $this->request->getPost('subs');
                $subsdata = $this->subsidiariesModel->find($subsID);
                $favicon = $subsdata['logo'];

                $logoName = $event['logo'];
                if ($this->request->getFile('event_logo')->isValid()) {
                    $logo = $this->request->getFile('event_logo');
                    $logoName = $logo->getRandomName();
                    $logo->move('uploads/events', $logoName);
                }

                $bannerName = $event['banner'];
                if ($this->request->getFile('event_banner')->isValid()) {
                    $banner = $this->request->getFile('event_banner');
                    $bannerName = $banner->getRandomName();
                    $banner->move('uploads/events', $bannerName);
                }

                if($this->request->getPost('quota') == ""){
                    $quota = NULL;
                }else{
                    $quota = $this->request->getPost('quota');
                }

                $updateData = [
                    'name' => $this->request->getPost('event_name'),
                    'subs_id' => $subsID,
                    'logo' => $logoName,
                    'banner' => $bannerName,
                    'description' => $this->request->getPost('event_desc'),
                    'seo_description' => $this->request->getPost('seo_desc'),
                    'tgl_start' => $this->convertToYMD($this->request->getPost('start_date')),
                    'tgl_end' => $this->convertToYMD($this->request->getPost('end_date')),
                    'tgl_event' => $this->convertToYMD($this->request->getPost('event_date_start')),
                    'tgl_event_end' => $this->convertToYMD($this->request->getPost('event_date_end')),
                    'corpo_email' => $this->request->getPost('corpo_email') ? 'yes' : 'no',
                    'one_company' => $this->request->getPost('one_company') ? 1 : 0,
                    'is_approval' => $this->request->getPost('is_approval') ? 1 : 0,
                    'quota' => $quota,
                    'default_company' => $this->request->getPost('default_company')
                ];
            break;

            case 'editEmailForm':
                $rules = [
                    'sender_email' => ['label' => 'Sender Email', 'rules' => 'required'],
                    'sender_name' => ['label' => 'Sender Name', 'rules' => 'required'],
                    'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                ];
                
                $updateData = [
                    'sender_email' => $this->request->getPost('sender_email'),
                    'sender_name' => $this->request->getPost('sender_name'),
                ];
            break;

            case 'editRegPageForm':
                $rules = [
                    'reg_page' => ['label' => 'Page Link', 'rules' => 'required'],
                    'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                ];
                
                $updateData = [
                    'reg_page' => $this->request->getPost('reg_page'),
                ];
            break;

            case 'editNotificationForm':
                $rules = [
                    'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                ];

                $ccEmails = $this->request->getPost('cc_to_email');
                $ccEmailsArray = json_decode($ccEmails[0], true);
                if($ccEmailsArray !== NULL){
                    $ccEmailsString = implode(', ', array_column($ccEmailsArray, 'value'));
                }else{
                    $ccEmailsString = "";
                }

                
                $updateData = [
                    'to_email' => $this->request->getPost('to_email'),
                    'to_name' => $this->request->getPost('to_name'),
                    'cc_to_email' => $ccEmailsString,
                ];
            break;

            case 'successPageForm':
                $success_link = $this->request->getPost('success_link_type') === 'custom'
                ? $this->request->getPost('custom_success_link')
                : base_url('event/' . $event["slug"]);

                $rules = [
                    'success_title' => ['label' => 'Success Title', 'rules' => 'required'],
                    'success_desc' => ['label' => 'Success Description', 'rules' => 'required'],
                    'success_button' => ['label' => 'Success Button', 'rules' => 'required'],
                    'success_link_type' => ['label' => 'Success Link Type', 'rules' => 'required|in_list[event,custom]'],
                    'custom_success_link' => ['label' => 'Custom Success Link', 'rules' => 'permit_empty|valid_url'],
                    'event_id' => ['label' => 'Event ID', 'rules' => 'required|integer'],
                ];
                $updateData = [
                    'success_title' => $this->request->getPost('success_title'),
                    'success_desc' => $this->request->getPost('success_desc'),
                    'success_button' => $this->request->getPost('success_button'),
                    'success_link_type' => $this->request->getPost('success_link_type'),
                    'success_link' => $success_link,
                ];
            break;

            default:
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid form type provided.',
                ]);
        }

        // Validate the input
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        // Update the event data
        $this->eventsModel->update($eventId, $updateData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Event Updated successfully.',
            'event_id' => $eventId,
        ]);
    }

    public function edit($id)
    {
        $event = $this->eventsModel->find($id);
        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
        }
        $currentUser = auth()->user();
        $event['canEdit'] = $this->eventAccessModel->hasAccess($currentUser->id, $id, 'edit');
        $tgl_event = new DateTime($event['tgl_event']);
        $event['tgl_event'] = $tgl_event->format('d/m/Y H:i');
        $tgl_event_end = new DateTime($event['tgl_event_end']);
        $event['tgl_event_end'] = $tgl_event_end->format('d/m/Y H:i');
        $tgl_start = new DateTime($event['tgl_start']);
        $event['tgl_start'] = $tgl_start->format('d/m/Y H:i');
        $tgl_end = new DateTime($event['tgl_end']);
        $event['tgl_end'] = $tgl_end->format('d/m/Y H:i');
        $subs_name = $this->subsidiariesModel->find($event['subs_id']);
        $event['subs'] = $subs_name['name']." (".$subs_name['short_name'].")";

        $data = [
            'title' => 'overview',
            'event' => $event,
            'subsidiaries' => $this->subsidiariesModel->getAllSubsidiaries(),
        ];

        return view('events/form', $data);
    }

    public function delete($id)
    {
        $event = $this->eventsModel->find($id);

        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Event not found');
        }

        $this->eventsModel->delete($id);

        return redirect()->to('/adminpanel/all-events')->with('success', 'Event deleted successfully.');
    }
}