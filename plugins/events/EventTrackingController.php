<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\EventAccessModel;
use App\Models\TrackingCodeModel;

class EventTrackingController extends BaseController
{
    protected $eventsModel;
    protected $eventAccessModel;
    protected $trackingCodeModel;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->trackingCodeModel = new TrackingCodeModel();
    }

    public function trackingCode($eventId)
    {
        $data['event_id'] = $eventId;
        $data['title'] = "tracking";
        $data['event'] = $this->eventsModel->find($eventId);
        return view('events/tracking_code', $data);
    }

    // Get all tracking codes for an event
    public function getTrackingCodes($eventId)
    {
        $trackingCodes = $this->trackingCodeModel->getTrackingCodesByEvent($eventId);
        $event = $this->eventsModel->find($eventId);
        $url_page = base_url($event['slug']);

        if (strpos($event['reg_page'], 'http://') === 0 || strpos($event['reg_page'], 'https://') === 0) {
            $url_page = $event['reg_page'];
        } 
        
        foreach ($trackingCodes as &$code) {
            $code['link'] = $url_page . "?src=" . $code['tracking_code'];
        }
        return $this->response->setJSON(['data' => $trackingCodes]);
    }

    public function getTrackingCode($id)
    {
        $trackingCode = $this->trackingCodeModel->find($id);

        if ($trackingCode) {
            return $this->response->setJSON($trackingCode);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Tracking code not found.'
        ], 404);
    }

    // Add a new tracking code
    public function addTrackingCode()
    {
        $eventId = $this->request->getPost('event_id');
        $source = $this->request->getPost('source');
        $trackingCode = bin2hex(random_bytes(6)); // Generate random tracking code

        $data = [
            'event_id' => $eventId,
            'source' => $source,
            'tracking_code' => $trackingCode,
        ];

        $this->trackingCodeModel->insert($data);
        $event = $this->eventsModel->find($eventId);
        if($event['reg_page'] !== NULL || $event['reg_page'] !== '' ){
            $url_page = $event['reg_page'];
        }else{
            $url_page = base_url($event['slug']);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tracking code added successfully.',
            'link' => $url_page . "?src=$trackingCode",
        ]);
    }

    public function uploadCSV(){
        $file = $this->request->getFile('csv_file');
        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid file format. Please upload a valid CSV file.'
            ]);
        }
        $csvData = array_map('str_getcsv', file($file->getTempName()));

        if (empty($csvData)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Empty CSV file.'
            ]);
        }
        $eventId = $this->request->getPost('event_id');
        $inserted = 0;
        foreach ($csvData as $row) {
            if (!empty($row[0])) {
                $trackingCode = bin2hex(random_bytes(6));
                $this->trackingCodeModel->insert([
                    'event_id' => $eventId,
                    'tracking_code' => $trackingCode,
                    'source' => trim($row[0])
                ]);
                $inserted++;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Successfully imported $inserted tracking sources."
        ]);
        
    }

    // Edit an existing tracking code (only source name can be edited)
    public function editTrackingCode($id)
    {
        $source = $this->request->getPost('source');
        $this->trackingCodeModel->update($id, ['source' => $source]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Source name updated successfully.',
        ]);
    }

    // Delete a tracking code
    public function deleteTrackingCode($id)
    {
        $this->trackingCodeModel->delete($id);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Tracking code deleted successfully.',
        ]);
    }
}