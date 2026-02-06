<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\EventAccessModel;
use App\Models\ApprovalTypeModel;
use App\Models\SubsidiariesModel;

use CodeIgniter\Config\Services;

class EventSettingsController extends BaseController
{
    protected $eventsModel;
    protected $eventAccessModel;
    protected $approvalTypeModel;
    protected $subsidiariesModel;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->approvalTypeModel = new ApprovalTypeModel();
        $this->subsidiariesModel = new SubsidiariesModel();
    }

    public function edit_settings($id)
    {
        $event = $this->eventsModel->find($id);
        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
        }
        $currentUser = auth()->user();
        $canEdit = $this->eventAccessModel->hasAccess($currentUser->id, $id, 'edit');
        if (!$canEdit) {
            return redirect()->to('/adminpanel/all-events')->with('error', "you didn't have enough credentials to edit this event!");
        }

        $approvalEmail = $this->approvalTypeModel->where('event_id', $id)->where('cat', 'approved')->findAll();
        $pendingEmail = $this->approvalTypeModel->where('event_id', $id)->where('cat', 'pending')->first();
        $rejectEmail = $this->approvalTypeModel->where('event_id', $id)->where('cat', 'rejected')->findAll();
        $defaultEmail = $this->approvalTypeModel->where('event_id', $id)->where('cat', 'default')->first();
        $event_access = $this->eventAccessModel->where('event_id', $id)->findAll();
        
        $users = auth()->getProvider();
        foreach ($event_access as $key => $access) {
            $user = $users->findById($access['admin_id']);
            if($access['access_type'] == "edit"){
                $akses = "Approval - Edit Event - Delete Event";
            }else{
                $akses = "Approval";
            }
            $event_access[$key] = [
                'id' => $access['id'],
                'email' => $user->getEmail(),
                'akses' => $akses,
                'access_type' => $access['access_type']
            ];
        }

        $admins = $users->findAll();

        foreach ($admins as $key => $admin) {

            $admins[$key] = [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->getEmail(),
                'active' => $admin->active,
            ];
        }

        $data = [
            'title' => 'settings',
            'event' => $event,
            'approvalEmails' => $approvalEmail,
            'pendingEmail' => $pendingEmail,
            'rejectEmails' => $rejectEmail,
            'defaultEmail' => $defaultEmail,
            'event_access' => $event_access,
            'available_users' => $admins,
        ];

        return view('events/settings', $data);
    }

    public function getAvailableUser($eventId)
    {
        // Check if eventId is provided
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing event_id']);
            return;
        }

        // Get the authentication provider (Shield User Provider)
        $users = auth()->getProvider();

        // Get all event access records for the given event_id
        $eventAccess = $this->eventAccessModel->where('event_id', $eventId)->findAll();

        // Extract admin IDs that already have access to the current event
        $excludedUsers = array_map(fn($access) => $access['admin_id'], $eventAccess);

        // Fetch all users
        $allUsers = $users->findAll();

        // Filter out users that are in the current event
        $availableUsers = array_filter($allUsers, function ($user) use ($excludedUsers) {
            return !in_array($user->id, $excludedUsers);
        });

        // Format user list for Tagify
        $userList = array_values(array_map(function ($user) {
            return [
                'value'  => $user->id,
                'name'   => $user->username,
                'email'  => $user->getEmail(),
                'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($user->username) . "&background=random&size=32",
            ];
        }, $availableUsers));
        
        return $this->response->setJSON($userList);
    }

    public function updateEventSettings($eventId = NULL)
    {
        if ($this->request->isAJAX()) {
            $useCase = $this->request->getPost('useCase');
            $response = ['success' => false, 'message' => 'Invalid use case'];

            switch ($useCase) {

                case 'approved':
                    $reason = $this->request->getPost('reason');
                    $event = $this->eventsModel->find($eventId);

                    $parser = \Config\Services::parser();

                    $dataHTML = [
                        'event' => $event['name'],
                        'email' => $event['sender_email']
                    ];
                    $htmlTemplate = $parser->setData($dataHTML)->render('emails/approve');

                    $insertedId = $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'approved',
                        'type_name' => $reason,
                        'email_subject' => 'Approved Notification - ' . $event['name'],
                        'email_banner' => $event['banner'],
                        'email_body' => $htmlTemplate,
                    ], true);

                    $response = [
                        'success' => true,
                        'message' => 'Approval reason updated successfully.',
                        'newCardHTML' => "
                            <div class=\"col-4 mb-3\">
                                <div class=\"card shadow-none border mb-0\">
                                    <div class=\"card-body p-3\">
                                        <div class=\"d-flex align-items-center\">
                                            <div class=\"flex-shrink-0\">
                                                <div class=\"avtar avtar-s bg-light-secondary\"><i class=\"ph-duotone ph-envelope-simple f-20\"></i></div>
                                            </div>
                                            <div class=\"flex-grow-1 ms-3\">
                                                <div class=\"d-flex align-items-center justify-content-center gap-2 text-success\">
                                                    <p class=\"mb-0\">$reason</p>
                                                </div>
                                            </div>
                                            <div class=\"flex-shrink-0\">
                                                <button data-typeid=".$insertedId." type=\"button\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-original-title=\"Edit Approve Reason & Email\" class=\"btn btn-icon btn-light-warning btnEditApproval me-2\">
                                                    <i data-typeid=".$insertedId." class=\"ti ti-edit f-20 btnEditApproval\"></i>
                                                </button>
                                                <button data-typeid=".$insertedId." type=\"button\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-original-title=\"Delete Approve Reason & Email\" class=\"btn btn-icon btn-light-danger btnDeleteApproval\">
                                                    <i data-typeid=".$insertedId." class=\"ti ti-trash f-20 btnDeleteApproval\"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>"
                    ];

                    return $this->response->setJSON($response);

                    break;

                case 'rejected':
                    $reason = $this->request->getPost('reason');
                    $event = $this->eventsModel->find($eventId);

                    $parser = \Config\Services::parser();

                    $dataHTML = [
                        'event' => $event['name'],
                        'email' => $event['sender_email']
                    ];
                    $htmlTemplate = $parser->setData($dataHTML)->render('emails/approve');

                    $insertedId = $this->approvalTypeModel->insert([
                        'event_id' => $eventId,
                        'cat' => 'rejected',
                        'type_name' => $reason,
                        'email_subject' => 'Reject Notification - ' . $event['name'],
                        'email_banner' => $event['banner'],
                        'email_body' => $htmlTemplate,
                    ], true);

                    $response = [
                        'success' => true,
                        'message' => 'Reject reason updated successfully.',
                        'newCardHTML' => "
                            <div class=\"col-4 mb-3\">
                                <div class=\"card shadow-none border mb-0\">
                                    <div class=\"card-body p-3\">
                                        <div class=\"d-flex align-items-center\">
                                            <div class=\"flex-shrink-0\">
                                                <div class=\"avtar avtar-s bg-light-secondary\"><i class=\"ph-duotone ph-envelope-simple f-20\"></i></div>
                                            </div>
                                            <div class=\"flex-grow-1 ms-3\">
                                                <div class=\"d-flex align-items-center justify-content-center gap-2 text-success\">
                                                    <p class=\"mb-0\">$reason</p>
                                                </div>
                                            </div>
                                            <div class=\"flex-shrink-0\">
                                                <button data-typeid=".$insertedId." type=\"button\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-original-title=\"Edit Reject Reason & Email\" class=\"btn btn-icon btn-light-warning me-2 btnEditApproval\">
                                                    <i data-typeid=".$insertedId." class=\"ti ti-edit f-20 btnEditApproval\"></i>
                                                </button>
                                                <button data-typeid=".$insertedId." type=\"button\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-original-title=\"Delete Reject Reason & Email\" class=\"btn btn-icon btn-light-danger btnDeleteApproval\">
                                                    <i data-typeid=".$insertedId." class=\"ti ti-trash f-20 btnDeleteApproval\"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>"
                    ];

                    return $this->response->setJSON($response);

                    break;

                case 'registration':
                    $status = $this->request->getPost('status') === 'on' ? 'on' : 'off';
                    $this->eventsModel->update($eventId, ['status' => $status]);
                    $response = [
                        'success' => true,
                        'status' => $status,
                        'bgClass' => $status === 'on' ? 'bg-light-success' : 'bg-light-danger',
                        'message' => 'Registration status updated successfully.',
                    ];
                    break;

                case 'approval':
                    $isApproval = $this->request->getPost('is_approval') === 'on' ? 1 : 0;
                    $this->eventsModel->update($eventId, ['is_approval' => $isApproval]);
                    $response = [
                        'success' => true,
                        'isApproval' => $isApproval,
                        'message' => 'Approval setting updated successfully.',
                    ];
                    break;

                case 'capacity':
                    $quota = $this->request->getPost('quota');
                    $this->eventsModel->update($eventId, ['quota' => $quota]);
                    $response = [
                        'success' => true,
                        'quota' => $quota ?: 'Unlimited',
                        'message' => 'Capacity updated successfully.',
                    ];
                    break;

                case 'email':
                    $corpoEmail = $this->request->getPost('corpo_email') === 'on' ? 1 : 0;
                    $this->eventsModel->update($eventId, ['corpo_email' => $corpoEmail]);
                    $response = [
                        'success' => true,
                        'corpoEmail' => $corpoEmail,
                        'message' => 'Guests Email updated successfully.',
                    ];
                    break;

                case 'onecompany':
                    $isOneCompany = $this->request->getPost('one_company') === 'on' ? 1 : 0;
                    $defaultCompany = $this->request->getPost('default_company');

                    if ($isOneCompany && !$defaultCompany) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Default Company is required when "One Company" is enabled.',
                        ]);
                    }

                    $data = ['one_company' => $isOneCompany];
                    if ($isOneCompany) {
                        $data['default_company'] = $defaultCompany;
                    }

                    $this->eventsModel->update($eventId, $data);
                    $response = [
                        'success' => true,
                        'isOneCompany' => $isOneCompany,
                        'defaultCompany' => $isOneCompany ? $defaultCompany : null,
                        'message' => 'One Company setting updated successfully.',
                    ];
                    break;
                case 'shareaccess':
                    $userJson = $this->request->getPost('selectedUser');
                    $users = json_decode($userJson, true);

                    $event_id = $this->request->getPost('event_id');
                    $access_level = $this->request->getPost('access_level');
                    $newCard = "";
                    foreach($users as $user){
                        $this->eventAccessModel->insert([
                            'event_id' => $event_id,
                            'admin_id' => $user['value'],
                            'access_type' => $access_level
                        ], true);

                        $alvl = "Approval - Edit Event - Delete Event";

                        if($access_level == "view"){
                            $alvl = "Approval";
                        }
                        $newCard .="
                            <div class=\"col-md-6 mb-3\">
                                <div class=\"card shadow-none border mb-0\">
                                    <div class=\"card-body p-3\">
                                        <div class=\"d-flex align-items-center\">
                                            <div class=\"flex-shrink-0\">
                                                <div class=\"avtar avtar-s bg-light-secondary\">
                                                    <i class=\"ph-duotone ph-user f-20\"></i>
                                                </div>
                                            </div>
                                            <div class=\"flex-grow-1 ms-3\">
                                                <div class=\"gap-2 text-success\">
                                                    <p class=\"mb-0\">".$user['email']."</p>
                                                    <small class=\"text-muted mb-0 aksesteks\">".$alvl."</small>
                                                </div>
                                            </div>
                                            <div class=\"flex-shrink-0\">
                                            <button type=\"button\" 
                                                data-bs-toggle=\"tooltip\" 
                                                data-typeid=".$user['value']."
                                                data-email=".$user['email']."
                                                data-bs-placement=\"top\" 
                                                data-bs-original-title=\"Edit Access\" 
                                                class=\"btn btn-icon btn-light-warning btnEditAccess\">
                                                <i data-typeid=".$user['value']." data-email=".$user['email']." class=\"ti ti-edit f-20 btnEditAccess\"></i>
                                            </button>

                                            <button type=\"button\" 
                                                    data-bs-toggle=\"tooltip\" 
                                                    data-typeid=".$user['value']." 
                                                    data-bs-placement=\"top\" 
                                                    data-bs-original-title=\"Delete Access\" 
                                                    class=\"btn btn-icon btn-light-danger btnDeleteAccess\">
                                                <i data-typeid=".$user['value']." class=\"ti ti-trash f-20 btnDeleteAccess\"></i>
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                    }
                    $response = [
                        'success' => true,
                        'message' => 'Access Shared successfully.',
                        'newCardHTML' => $newCard
                    ];
                    
                    break;

                case 'editaccess':
                    $accessId = $this->request->getPost('accessId');
                    $access_level = $this->request->getPost('access_level');

                    $data = [
                        'access_type' => $access_level,
                    ];

                    if ($this->eventAccessModel->update($accessId, $data)) {
                        $response = [
                            'success' => true,
                            'message' => 'Access level updated successfully!',
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Failed to update access level.'
                        ];
                    }
                    break;
                
                default:
                break;
            }

            return $this->response->setJSON($response);
        }
    }

    public function deleteApproval($id)
    {
        if (!$id) {
            return $this->respond(['success' => false, 'message' => 'Invalid ID provided'], 400);
        }

        if ($this->approvalTypeModel->find($id)) {
            $this->approvalTypeModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'approval_type' => "Approval deleted successfully"
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'approval_type' => "Failed to delete Approval"
            ]);
        }
    }

    public function deleteAccess($id)
    {
        if (!$id) {
            return $this->respond(['success' => false, 'message' => 'Invalid ID provided'], 400);
        }

        if ($this->eventAccessModel->find($id)) {
            $this->eventAccessModel->delete($id);
            return $this->response->setJSON([
                'success' => true,
                'approval_type' => "Access deleted successfully"
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'approval_type' => "Failed to delete Access"
            ]);
        }
    }

    public function getApprovalType($id)
    {
        $approvalType = $this->approvalTypeModel->find($id);

        if ($approvalType) {
            return $this->response->setJSON([
                'success' => true,
                'approval_type' => $approvalType
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Approval type not found.'
            ]);
        }
    }

    public function getAccessType($id)
    {
        $accessType = $this->eventAccessModel->find($id);

        if ($accessType) {
            return $this->response->setJSON([
                'success' => true,
                'accessType' => $accessType
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Approval type not found.'
            ]);
        }
    }

    public function updateApprovalType()
    {
        // Get form data
        $approvalTypeId = $this->request->getPost('approval_type_id');
        $typeName = $this->request->getPost('type_name');
        $emailSubject = $this->request->getPost('email_subject');
        $emailBody = $this->request->getPost('email_body');
        
        $data = [
            'type_name' => $typeName,
            'email_subject' => $emailSubject,
            'email_body' => $emailBody,
        ];

        // Handle file upload for the banner
        $banner = $this->request->getFile('event_banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
            // Validate file size and dimensions
            if ($banner->getSize() > 500000) { // 500KB limit
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'The uploaded file exceeds the maximum size of 500KB.'
                ]);
            }

            // Optional: Validate dimensions
            $imageSize = getimagesize($banner->getTempName());
            if ($imageSize[0] > 800) { // 800px width limit
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'The uploaded file exceeds the maximum width of 800px.'
                ]);
            }

            // Move file to the uploads directory
            $newName = $banner->getRandomName();
            $banner->move('uploads/events', $newName);

            // Update banner filename in the data array
            $data['email_banner'] = $newName;
        }

        // Update the database record
        if ($this->approvalTypeModel->update($approvalTypeId, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Approval type updated successfully!',
                'approval_type' => array_merge(['id' => $approvalTypeId], $data)
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update approval type.'
            ]);
        }
    }
}