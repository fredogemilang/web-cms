<?php
namespace App\Controllers\Backend\Events;

use App\Controllers\BaseController;
use App\Models\EventsModel;
use App\Models\EventAccessModel;
use App\Models\FeedbackQuestionModel;
use App\Models\FeedbackOptionModel;

class EventFeedbackController extends BaseController
{
    protected $eventsModel;
    protected $eventAccessModel;
    protected $feedbackQuestionModel;
    protected $feedbackOptionModel;

    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->eventAccessModel = new EventAccessModel();
        $this->feedbackQuestionModel = new FeedbackQuestionModel();
        $this->feedbackOptionModel = new FeedbackOptionModel();
    }

    public function edit_feedback($id)
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

        // Check if any questions exist before calling the method that includes conditional logic
        $questionsExist = $this->feedbackQuestionModel->where('event_id', $id)->countAllResults() > 0;
        
        if ($questionsExist) {
            // Use the new method that includes conditional logic information
            $questions = $this->feedbackQuestionModel->getQuestionsWithDependencies($id);

            foreach ($questions as &$question) {
                $question['options'] = $this->feedbackOptionModel->where('question_id', $question['id'])->findAll();

                 // If the question has a parent question, get the parent question's type
                if (!empty($question['parent_question_id'])) {
                    $parentQuestion = $this->feedbackQuestionModel->find($question['parent_question_id']);
                    $question['parent_question_type'] = $parentQuestion ? $parentQuestion['type'] : '';
                } else {
                    $question['parent_question_type'] = '';
                }
            }
             unset($question);

            // Get a list of all questions that can be used as parent questions
            // We'll exclude rating and digit types as they're not good for conditional logic
            $parentQuestions = $this->feedbackQuestionModel
                ->where('event_id', $id)
                ->whereNotIn('type', ['rating', 'digits'])
                ->findAll();
        } else {
            // If no questions exist, initialize empty arrays
            $questions = [];
            $parentQuestions = [];
        }
        
        $data = [
            'title' => 'feedback',
            'event' => $event,
            'questions' => $questions,
            'parentQuestions' => $parentQuestions,
        ];

        return view('events/feedback', $data);
    }

    public function updateFeedbackOrder()
    {
        $data = $this->request->getJSON(true);

        if (isset($data['step'], $data['order']) && is_array($data['order'])) {
            foreach ($data['order'] as $item) {
                $this->feedbackQuestionModel->update($item['id'], [
                    'step' => $data['step'],
                    'sort_order' => $item['sort_order'],
                ]);
            }
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);
    }

    public function saveFeedbackQuestion()
    {
        $postData = $this->request->getPost();

        // Process conditional logic settings
        $isConditional = isset($postData['is_conditional']) ? 1 : 0;
        $parentQuestionId = $isConditional ? $postData['parent_question_id'] : null;
        $conditionOperator = $isConditional ? $postData['condition_operator'] : null;
        $conditionValue = $isConditional ? $postData['condition_value'] : null;

        // For multi-select conditions, we need to store them as JSON
        if ($isConditional && isset($postData['condition_values']) && is_array($postData['condition_values'])) {
            $conditionValue = json_encode($postData['condition_values']);
        }

        // Prepare the question data including conditional logic
        $questionData = [
            'event_id' => $postData['event_id'],
            'step' => $postData['step'],
            'question' => $postData['question'],
            'short_label' => $postData['short_label'],
            'type' => $postData['type'],
            'is_conditional' => $isConditional,
            'parent_question_id' => $parentQuestionId,
            'condition_operator' => $conditionOperator,
            'condition_value' => $conditionValue
        ];

        if (isset($postData['id']) && !empty($postData['id'])) {
            // Updating an existing question
            $questionId = $postData['id'];

            // Update the question data
            $this->feedbackQuestionModel->update($questionId, $questionData);
            
            // Delete existing options for select-type questions
            $this->feedbackOptionModel->where('question_id', $questionId)->delete();
            
            // Handle options for single_select and multi_select types
            if (in_array($postData['type'], ['single_select', 'multi_select']) && isset($postData['options'])) {
                // Get leads flags array if available
                $leadsFlags = $postData['leads_flags'] ?? [];
                
                // Insert updated options
                foreach ($postData['options'] as $index => $option) {
                    // Check if this option should be marked as leads (only for single select)
                    $isLeadsFlag = 0;
                    if ($postData['type'] === 'single_select' && isset($leadsFlags[$index])) {
                        $isLeadsFlag = 1;
                    }
                    
                    $this->feedbackOptionModel->insert([
                        'question_id' => $questionId, 
                        'option_label' => $option,
                        'is_leads_flag' => $isLeadsFlag
                    ]);
                }
            }
        } else {
            // Creating a new question

            // Get the maximum sort order for the selected step
            $maxSortOrder = $this->feedbackQuestionModel
                ->where('event_id', $postData['event_id'])
                ->where('step', $postData['step'])
                ->selectMax('sort_order')
                ->get()
                ->getRowArray();

            $nextSortOrder = isset($maxSortOrder['sort_order']) ? $maxSortOrder['sort_order'] + 1 : 1;

            // Add the calculated sort order to the data
            $questionData['sort_order'] = $nextSortOrder;

            // Save the new question
            $this->feedbackQuestionModel->save($questionData);
            $questionId = $this->feedbackQuestionModel->insertID();

            // Handle options for single_select and multi_select types
            if (in_array($postData['type'], ['single_select', 'multi_select']) && isset($postData['options'])) {
                // Get leads flags array if available
                $leadsFlags = $postData['leads_flags'] ?? [];
                
                foreach ($postData['options'] as $index => $option) {
                    // Check if this option should be marked as leads (only for single select)
                    $isLeadsFlag = 0;
                    if ($postData['type'] === 'single_select' && isset($leadsFlags[$index])) {
                        $isLeadsFlag = 1;
                    }
                    
                    $this->feedbackOptionModel->insert([
                        'question_id' => $questionId, 
                        'option_label' => $option,
                        'is_leads_flag' => $isLeadsFlag
                    ]);
                }
            }
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteFeedback($id)
    {
        $this->feedbackOptionModel->where('question_id', $id)->delete();
        if ($this->feedbackQuestionModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success']);
        }
        return $this->response->setJSON(['status' => 'error'], 500);
    }

    public function getQuestionOptions($questionId)
    {
        $options = $this->feedbackOptionModel->where('question_id', $questionId)->findAll();
        
        return $this->response->setJSON([
            'questionId' => $questionId,
            'options' => $options
        ]);
    }

    public function updateFeedbackSettings($eventId)
    {
        $data = $this->request->getPost();

        $newName = "";
        $newName2 = "";
        $uploadPath = 'uploads/events/';
        
        // Fetch existing settings
        $settings = $this->eventsModel->find($eventId);

        if ($this->request->getFile('feedback_background')) {
            $file = $this->request->getFile('feedback_background');

            if ($file->isValid() && !$file->hasMoved()) {
                // Validate file size (max 500KB)
                if ($file->getSize() > 500 * 1024) { // 500KB
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File size must be 500KB or less.']);
                }

                // Validate image dimensions (max width 1920px)
                list($width, $height) = getimagesize($file->getTempName());
                if ($width > 740) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Image width must be 740px or less.']);
                }

                // Delete old banner if it exists
                if ($settings && $settings['feedback_background'] !== "no-image.jpg") {
                    $oldFile = $uploadPath . $settings['feedback_background'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // Delete old file
                    }
                }

                // Move new file if valid
                $newName = $file->getRandomName();
                if (!$file->move($uploadPath, $newName)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File upload failed: ' . $file->getErrorString()]);
                }

                $data['feedback_background'] = $newName;
            }
        }

        if ($this->request->getFile('feedback_foreground')) {
            $file = $this->request->getFile('feedback_foreground');

            if ($file->isValid() && !$file->hasMoved()) {
                // Validate file size (max 500KB)
                if ($file->getSize() > 500 * 1024) { // 500KB
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File size must be 500KB or less.']);
                }

                // Validate image dimensions (max width 1920px)
                list($width, $height) = getimagesize($file->getTempName());
                if ($width > 500) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Image width must be 500px or less.']);
                }

                // Delete old banner if it exists
                if ($settings && $settings['feedback_foreground'] !== "no-image.jpg") {
                    $oldFile = $uploadPath . $settings['feedback_foreground'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // Delete old file
                    }
                }

                // Move new file if valid
                $newName2 = $file->getRandomName();
                if (!$file->move($uploadPath, $newName2)) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'File upload failed: ' . $file->getErrorString()]);
                }

                $data['feedback_foreground'] = $newName2;
            }
        }

        // Set the new or existing banner name
        $feedback_background = $newName ?: ($settings['feedback_background'] ?? "");
        $feedback_foreground = $newName2 ?: ($settings['feedback_foreground'] ?? "");

        $this->eventsModel->update($eventId, $data);
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Settings updated successfully', 
            'feedback_background' => $feedback_background,
            'feedback_foreground' => $feedback_foreground
        ]);
    }
}