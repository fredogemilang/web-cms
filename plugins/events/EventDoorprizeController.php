<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\EventAccessModel;
use App\Models\RegistrantModel;
use App\Models\DoorprizeRoundModel;
use App\Models\DoorprizePrizeModel;
use App\Models\DoorprizeWinnerModel;
use App\Models\DoorprizeSettingsModel;

class EventDoorprizeController extends BaseController
{
    protected $eventsModel;
    protected $eventAccessModel;
    protected $registrantModel;
    protected $doorprizeRoundModel;
    protected $doorprizePrizeModel;
    protected $doorprizeWinnerModel;
    protected $doorprizeSettingsModel;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->registrantModel = new RegistrantModel();
        $this->doorprizeRoundModel = new DoorprizeRoundModel();
        $this->doorprizePrizeModel = new DoorprizePrizeModel();
        $this->doorprizeWinnerModel = new DoorprizeWinnerModel();
        $this->doorprizeSettingsModel = new DoorprizeSettingsModel();
    }

    public function doorprize($eventId)
    {
        $data['event_id'] = $eventId;
        $data['title'] = "doorprize";
        $data['event'] = $this->eventsModel->find($eventId);
        $data['rounds'] = $this->doorprizeRoundModel->where('event_id', $eventId)->findAll();
        $data['settings'] = $this->doorprizeSettingsModel->where('event_id', $eventId)->first();

        foreach ($data['rounds'] as &$round) { // Use a reference to modify the array in place
            $round['prizes'] = $this->doorprizePrizeModel->where('round_id', $round['id'])->findAll();
        }

        return view('events/doorprize', $data);
    }

    public function addRound()
    {
        $data = [
            'event_id' => $this->request->getPost('event_id'),
            'round_name' => $this->request->getPost('round_name'),
        ];
        $roundId = $this->doorprizeRoundModel->insert($data, true);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Round added successfully.',
            'round_id' => $roundId,
        ]);
    }

    public function editRound($roundId)
    {
        // Find the round by ID
        $round = $this->doorprizeRoundModel->find($roundId);

        if (!$round) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Round not found.'
            ]);
        }

        // Get the new data from the request
        $roundName = $this->request->getPost('round_name');

        // Update the round in the database
        $updateData = [
            'round_name' => $roundName,
        ];

        $this->doorprizeRoundModel->update($roundId, $updateData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Round updated successfully.',
        ]);
    }

    public function deleteRound($roundId)
    {
        // Find the round
        $round = $this->doorprizeRoundModel->find($roundId);

        if (!$round) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Round not found.'
            ]);
        }

        // Find all associated prizes and delete them using removePrize()
        $prizes = $this->doorprizePrizeModel->where('round_id', $roundId)->findAll();
        foreach ($prizes as $prize) {
            $this->removePrizeUnit($prize['id']);
        }

        // Delete the round from the database
        $this->doorprizeRoundModel->delete($roundId);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Round and its associated prizes deleted successfully.'
        ]);
    }

    public function addPrize()
    {
        $roundId = $this->request->getPost('round_id');
        $prizeName = $this->request->getPost('prize_name');
        $slot = $this->request->getPost('total_winner');
        $prizeImage = null;

        // Count the number of existing prizes for this round
        $prizeCount = $this->doorprizePrizeModel->where('round_id', $roundId)->countAllResults();

        if ($prizeCount >= 6) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Maximum 6 prizes can be added for each round.'
            ]);
        }

        // Handle image upload
        if ($file = $this->request->getFile('prize_image')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $file->move('uploads/prizes', $file->getRandomName());
                $prizeImage = $file->getName();
            }
        }

        // Insert prize into the database
        $data = [
            'round_id' => $roundId,
            'prize_name' => $prizeName,
            'slot' => $slot,
            'prize_image' => $prizeImage,
        ];
        $prizeId = $this->doorprizePrizeModel->insert($data, true);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Prize added successfully.',
            'prize_id' => $prizeId,
            'prize_image' => $prizeImage,
        ]);
    }

    private function removePrizeUnit($prizeId)
    {
        $prize = $this->doorprizePrizeModel->find($prizeId);

        if (!$prize) {
            return false; // Return false if prize not found
        }

        // Delete the image file if exists
        if (!empty($prize['prize_image'])) {
            $imagePath = 'uploads/prizes/' . $prize['prize_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete the prize from the database
        $this->doorprizePrizeModel->delete($prizeId);

        return true; // Return true if deletion successful
    }

    public function deletePrize($prizeId)
    {
        if ($this->removePrizeUnit($prizeId)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Prize deleted successfully.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Prize not found.'
        ]);
    }

    public function setWinner()
    {
        $prizeId = $this->request->getPost('prize_id');
        $registrantId = $this->request->getPost('registrant_id');

        $this->doorprizeWinnerModel->save([
            'prize_id' => $prizeId,
            'registrant_id' => $registrantId,
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Winner set successfully.']);
    }

    public function editPrize($prizeId)
    {
        $prize = $this->doorprizePrizeModel->find($prizeId);

        if (!$prize) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Prize not found.'
            ]);
        }

        $prizeName = $this->request->getPost('prize_name');
        $total_winner = $this->request->getPost('total_winner');
        $newPrizeImage = null;

        // Check if a new image is uploaded
        if ($file = $this->request->getFile('prize_image')) {
            if ($file->isValid() && !$file->hasMoved()) {
                // Delete old image if exists
                if (!empty($prize['prize_image'])) {
                    $oldImagePath = 'uploads/prizes/' . $prize['prize_image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Save new image
                $newPrizeImage = $file->getRandomName();
                $file->move('uploads/prizes', $newPrizeImage);
            }
        } else {
            // Keep existing image if no new image is uploaded
            $newPrizeImage = $prize['prize_image'];
        }

        // Update prize in the database
        $updateData = [
            'prize_name' => $prizeName,
            'slot' => $total_winner,
            'prize_image' => $newPrizeImage,
        ];

        $this->doorprizePrizeModel->update($prizeId, $updateData);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Prize updated successfully.',
            'prize_image' => $newPrizeImage, // Return new image name if updated
        ]);
    }

    public function getDoorprizeGuests($eventID)
    { 
        try {
            $settings = $this->doorprizeSettingsModel->where('event_id', $eventID)->first();

            $query = $this->registrantModel
                    ->select('registrant.*, doorprize_prizes.prize_name as hadiah') // Adjust the columns as needed
                    ->join('doorprize_prizes', 'doorprize_prizes.id = registrant.prize_id', 'left') // Join the related table
                    ->where('registrant.event_id', $eventID);
            
            $event = $this->eventsModel->find($eventID);
            if($event['is_approval']){
                $query->where('registrant.status', 'approved');
            }
            
            foreach (['checkin' => 'check_in', 'feedback' => 'feedback', 'mission' => 'mission'] as $key => $column) {
                if (isset($settings[$key]) && $settings[$key] == 1) {
                    $query->where($column, 1);
                }
            }

            $guests = $query->findAll();

            if (empty($guests)) {
                return $this->response->setJSON(['data' => []]); // Return empty if no guests
            }

            return $this->response->setJSON(['data' => $guests]); // DataTables needs { "data": [...] }

        } catch (\Exception $e) {
            log_message('error', 'DataTables Error: ' . $e->getMessage()); // Log error
            return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function getPrizes($eventId)
    {
        $prizes = [];

        // Fetch all rounds associated with the event
        $data_rounds = $this->doorprizeRoundModel->where('event_id', $eventId)->findAll();

        // Loop through each round and fetch its prizes
        foreach ($data_rounds as $round) { 
            $round_prizes = $this->doorprizePrizeModel->where('round_id', $round['id'])->findAll();
            
            // Add round and its prizes to the response array
            $prizes[] = [
                'round_id'   => $round['id'],
                'round_name' => $round['round_name'],
                'prizes'     => $round_prizes
            ];
        }

        return $this->response->setJSON($prizes);
    }

    public function setGuestPrize()
    {
        $guestId = $this->request->getPost('guest_id');
        $prizeId = $this->request->getPost('prize_id');
        
        if (!$guestId || !$prizeId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);
        }

        $update = $this->registrantModel->update($guestId, ['prize_id' => $prizeId]);

        if ($update) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Prize assigned successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to assign prize']);
    }

    public function removePrize()
    {
        try {
            $guestId = $this->request->getJSON()->guest_id;

            if (!$guestId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid guest ID'])->setStatusCode(400);
            }

            $updated = $this->registrantModel
                ->where('id', $guestId)
                ->set(['prize_id' => 0])
                ->update();

            if ($updated) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Prize removed successfully']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to remove prize']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Remove Prize Error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function updateDoorprizeSettings($eventId)
    {
        $data = $this->request->getPost();

        // Explicitly set checkbox values
        $data['checkin'] = $this->request->getPost('checkin') ? 1 : 0;
        $data['feedback'] = $this->request->getPost('feedback') ? 1 : 0;
        $data['mission'] = $this->request->getPost('mission') ? 1 : 0;
        
        $newName = "";
        $uploadPath = 'uploads/events/';
        
        // Fetch existing settings
        $settings = $this->doorprizeSettingsModel->where('event_id', $eventId)->first();

        // Handle file upload for doorprize_background
        if ($this->request->getFile('doorprize_banner')) {
            $file = $this->request->getFile('doorprize_banner');

            if ($file->isValid() && !$file->hasMoved()) {
                // Validate file size (max 500KB)
                if ($file->getSize() > 500 * 1024) { // 500KB
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File size must be 500KB or less.']);
                }

                // Validate image dimensions (max width 1920px)
                list($width, $height) = getimagesize($file->getTempName());
                if ($width > 1920) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Image width must be 1920px or less.']);
                }

                // Delete old banner if it exists
                if ($settings && !empty($settings['doorprize_background'])) {
                    $oldFile = $uploadPath . $settings['doorprize_background'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // Delete old file
                    }
                }

                // Move new file if valid
                $newName = $file->getRandomName();
                if (!$file->move($uploadPath, $newName)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File upload failed: ' . $file->getErrorString()]);
                }

                $data['doorprize_background'] = $newName;
            }
        }

        // Set the new or existing banner name
        $doorprize_background = $newName ?: ($settings['doorprize_background'] ?? "");

        // Update or insert settings
        if ($settings) {
            $this->doorprizeSettingsModel->update($settings['id'], $data);
        } else {
            $data['event_id'] = $eventId;
            $this->doorprizeSettingsModel->insert($data);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Settings updated successfully',
            'doorprize_background' => $doorprize_background
        ]);
    }
}