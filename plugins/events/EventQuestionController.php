<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\EventAccessModel;
use App\Models\CustomQuestionModel;
use App\Models\QuestionOptionModel;

class EventQuestionController extends BaseController
{
    protected $eventsModel;
    protected $eventAccessModel;
    protected $customQuestionModel;
    protected $questionOptionModel;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->customQuestionModel = new CustomQuestionModel();
        $this->questionOptionModel = new QuestionOptionModel();
    }

    public function edit_cquestions($id)
    {
        $event = $this->eventsModel->find($id);
        
        if (!$event) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Event not found");
        }
        
        $currentUser = auth()->user();
        $canEdit = $this->eventAccessModel->hasAccess($currentUser->id, $id, 'edit');
        if (!$canEdit) {
            return redirect()->to('/adminpanel/all-events')->with('error', "You don't have enough credentials to edit this event!");
        }

        $questions = $this->customQuestionModel->where('event_id', $id)->orderBy('order', 'asc')->findAll();

        // Fix: Assign options properly using array keys
        foreach ($questions as $key => $question) {
            $options = $this->questionOptionModel->getOptionsByQuestion($question['id']);
        
            // Extract only `option_text` values from the options array
            $questions[$key]['options'] = array_column($options, 'option_text');
        }

        $data = [
            'title' => 'customquestions',
            'event' => $event,
            'questions' => $questions,
        ];

        return view('events/custom_questions', $data);
    }

    // Save or update a question
    public function saveQuestion()
    {
        if ($this->request->getPost('id')) {
            $questionId = $this->request->getPost('id');

            $data = [
                'type'        => $this->request->getPost('type'),
                'question'    => $this->request->getPost('question'),
                'question_description'    => $this->request->getPost('questionDesc'),
                'short_label' => $this->request->getPost('short_label'),
                // 'order'       => $nextSortOrder
            ];

            $this->customQuestionModel->update($this->request->getPost('id'), $data);

            $this->questionOptionModel->where('question_id', $questionId)->delete();

            $response = [
                'status' => 'success',
                'message' => 'Custom Question updated successfully.',
            ];
        } else {
            $maxSortOrder = $this->customQuestionModel
            ->where('event_id', $this->request->getPost('event_id'))
            ->selectMax('order')
            ->get()
            ->getRowArray();

            $nextSortOrder = isset($maxSortOrder['order']) ? $maxSortOrder['order'] + 1 : 1;
            
            $data = [
                'event_id'    => $this->request->getPost('event_id'),
                'type'        => $this->request->getPost('type'),
                'question'    => $this->request->getPost('question'),
                'question_description'    => $this->request->getPost('questionDesc'),
                'short_label' => $this->request->getPost('short_label'),
                'order'       => $nextSortOrder
            ];
            $questionId = $this->customQuestionModel->insert($data);

            $response = [
                'status'  => 'success',
                'id'      => $questionId,
                'message' => 'Custom Question inserted successfully.',
            ];
        }

        // Handle options if the type is "Single Select" or "Multi Select"
        if (in_array($data['type'], ['single_select', 'multi_select'])) {
            $options = $this->request->getPost('options');
            foreach ($options as $option) {
                $this->questionOptionModel->insert([
                    'question_id' => $questionId,
                    'option_text' => $option,
                ]);
            }
        }
        
        return $this->response->setJSON($response);
    }

    // Delete a question
    public function deleteQuestion($id)
    {
        $this->questionOptionModel->where('question_id', $id)->delete();
        if ($this->customQuestionModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success']);
        }
        return $this->response->setJSON(['status' => 'error'], 500);
    }

    // Update question order
    public function updateQuestionOrder()
    {
        if ($this->request->isAJAX()) {
            $questions = $this->request->getJSON(); // Expecting an array of question IDs in new order

            foreach ($questions as $order => $id) {
                $this->customQuestionModel->update($id, ['order' => $order]);
            }

            return $this->response->setJSON(['status' => 'success']);
        }

        throw new \CodeIgniter\Exceptions\PageNotFoundException('Invalid Request');
    }

    public function getQuestionOptions($questionId)
    {
        $options = $this->questionOptionModel->getOptionsByQuestion($questionId);
        
        return $this->response->setJSON([
            'questionId' => $questionId,
            'options' => $options
        ]);
    }
}