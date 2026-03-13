<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;

class EventsubmissionsController extends AppController {

    public $paginate = ['limit' => 50];
    var $components = array('RequestHandler', 'PImage', 'PImageTest');
	
	public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Users");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Events");
		$this->loadModel("Books");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Judgeevaluations");
    }
	
	public function viewlist() {

        $this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
        $this->set("title_for_layout", "View/Edit Event Submissions" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_eventsubmission','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Eventsubmissions.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// if teacher is logged in, then only show documents uploaded by teacher
		if($user_type == "Teacher_Parent")
		{
			$condition[] = "(Eventsubmissions.uploaded_by_user_id = '".$user_id."')";
		}
		
		$eventsubmissions = $this->Eventsubmissions->find()->where($condition)->contain(['Events','Students'])->order(['Eventsubmissions.id' => 'DESC'])->all();
		$this->set('eventsubmissions',$eventsubmissions);
		//$this->prx($condition);
    }
	
	public function submitnewevent() {
		
		$this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Submit New Event " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_eventsubmission','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to get the list of event ids chosen in this convention for this season
			$arrConvSeasonEvents = array();
			$arrConvSeasonEvents[] = 0;
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $conventionRegD->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convsevent)
			{
				$arrConvSeasonEvents[] = $convsevent->event_id;
			}
			$arrConvSeasonEventsImplode = implode(",",$arrConvSeasonEvents);
			
			// now create event dropdown with event name and number_format
			$eventNameIDDD = array();
			$condEvents = array();
			$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
			$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
			foreach($eventsList as $eventrec)
			{
				$eventNameIDDD[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
			}
			$this->set('eventNameIDDD', $eventNameIDDD);
			
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $eventsubmissions = $this->Eventsubmissions->newEntity();
        if ($this->request->is('post')) {
            $data = $this->Eventsubmissions->patchEntity($eventsubmissions, $this->request->data);
            if (count($data->errors()) == 0) {
				
				$book_ids = $this->request->data['Eventsubmissions']['book_ids'];
				//$this->prx($book_ids);
				
				if(isset($book_ids) && count((array)$book_ids))
				{
					$data->book_ids = implode(",",(array)$book_ids);
				}
				else
				{
					$data->book_ids = '';
				}
				
				$eventD = $this->Events->find()->where(["Events.id" => $data->event_id])->first();
				
				if(!empty($this->request->data['Eventsubmissions']['event_document']['name']))
				{
					$data->mediafile_original_file_name =  $this->request->data['Eventsubmissions']['event_document']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['event_document']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['event_document']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['event_document'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->mediafile_file_system_name =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['report']['name']))
				{
					$data->report =  $this->request->data['Eventsubmissions']['report']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['report']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['report']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['report'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->report =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['score_sheet']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['score_sheet']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['score_sheet']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['score_sheet'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->score_sheet =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['additional_documents']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['additional_documents']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['additional_documents']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['additional_documents'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->additional_documents =  $returnedUploadImageArray[0];
				}
				
                $data->slug = 'event-submission-'.$sess_selected_convention_registration_id.'-'.time().'-'.rand(100,1000000);
				
				$data->conventionregistration_id	= $sess_selected_convention_registration_id;
				$data->conventionseason_id			= $conventionRegD->conventionseason_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->event_id_number 				= $eventD->event_id_number;
				
				
				if($eventD->group_event_yes_no == 1)
				{
					$data->student_id 			= 0;
				}
				else
				{
					$data->group_name 			= NULL;
				}
				
				$data->uploaded_by_user_id 			= $user_id;
				
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
				
				//$this->prx($data);
				
                if ($this->Eventsubmissions->save($data)) {
					
					$this->Flash->success('Events submission completed successfully.');
                    $this->redirect(['controller' => 'eventsubmissions', 'action' => 'viewlist']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('eventsubmissions', $eventsubmissions);
    }
	
	public function removesubmission($eventsubmission_slug = null) {
		
		$this->schoolAdminLoginCheck();
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to check if slug exists
			$checkExists = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $eventsubmission_slug,'Eventsubmissions.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkExists)
			{
				// to remove document as well
				if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->mediafile_file_system_name) && !empty($checkExists->mediafile_file_system_name))
				{
					@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->mediafile_file_system_name);
				}
				
				if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->report) && !empty($checkExists->report))
				{
					@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->report);
				}
				
				if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->score_sheet) && !empty($checkExists->score_sheet))
				{
					@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->score_sheet);
				}
				
				if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->additional_documents) && !empty($checkExists->additional_documents))
				{
					@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->additional_documents);
				}
				
				$this->Flash->success('Events submission removed successfully.');
				$this->Eventsubmissions->deleteAll(["slug" => $eventsubmission_slug]);
			}
			else
			{
				$this->Flash->error('Invalid supervisor details.');
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->redirect(['controller' => 'eventsubmissions', 'action' => 'viewlist']);
    }
	
	public function submitstudentevent($conv_reg_student_slug=NULL,$event_slug=NULL) {
		
		//$this->schoolAdminLoginCheck();
		
		$this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Submit Student Event " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_packageregistration','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($conv_reg_student_slug)
		{
			$convRegStudentD = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $conv_reg_student_slug])->contain(['Students'])->first();
			$this->set('convRegStudentD', $convRegStudentD);
		}
		
		if($event_slug)
		{
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			
			// to get list of books associated with this event
			if(!empty($eventD->book_ids))
			{
				$evBookIDS = $eventD->book_ids;
				
				// to get list of books from name
				$condEventBooks = array();
				$condEventBooks[] = "(Books.id IN ($evBookIDS) )";
				$booksDD = $this->Books->find()->where($condEventBooks)->combine('id', 'book_name')->toArray();
				$this->set('booksDD', $booksDD);
			}
		}
		
		if($convRegStudentD->id>0 && $eventD->id>0)
		{
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $convRegStudentD->conventionregistration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
		}
		else
		{
			$this->Flash->error('Invaid information.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'packageregistration']);
		}
		
		
        $eventsubmissions = $this->Eventsubmissions->newEntity();
        if ($this->request->is('post')) {
            $data = $this->Eventsubmissions->patchEntity($eventsubmissions, $this->request->data);
            if (count($data->errors()) == 0) {
				
				$book_ids = $this->request->data['Eventsubmissions']['book_ids'];
				//$this->prx($book_ids);
				
				if(isset($book_ids) && count((array)$book_ids))
				{
					$data->book_ids = implode(",",(array)$book_ids);
				}
				else
				{
					$data->book_ids = '';
				}
				
				if(!empty($this->request->data['Eventsubmissions']['event_document']['name']))
				{
					$data->mediafile_original_file_name =  $this->request->data['Eventsubmissions']['event_document']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['event_document']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['event_document']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['event_document'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->mediafile_file_system_name =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['report']['name']))
				{
					$data->report =  $this->request->data['Eventsubmissions']['report']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['report']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['report']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['report'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->report =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['score_sheet']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['score_sheet']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['score_sheet']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['score_sheet'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->score_sheet =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['additional_documents']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['additional_documents']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['additional_documents']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['additional_documents'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->additional_documents =  $returnedUploadImageArray[0];
				}
				
                $data->slug = 'event-submission-'.$conventionRegD->id.'-'.time().'-'.rand(100,1000000);
				
				$data->conventionregistration_id	= $conventionRegD->id;
				$data->conventionseason_id			= $conventionRegD->conventionseason_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->event_id 					= $eventD->id;
				$data->event_id_number 				= $eventD->event_id_number;
				$data->student_id 					= $convRegStudentD->student_id;
				
				$data->uploaded_by_user_id 			= $user_id;
				
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
				
				//$this->prx($data);
				
                if ($this->Eventsubmissions->save($data)) {
					
					$this->Flash->success('Events submission completed successfully.');
                    $this->redirect(['controller' => 'conventionregistrations', 'action' => 'packageregistration']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('eventsubmissions', $eventsubmissions);
    }
	
	public function submitgroupevent($conv_reg_student_slug=NULL,$event_slug=NULL,$crstudentevents_slug=NULL) {
		
		//$this->schoolAdminLoginCheck();
		
		$this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Submit Group Event " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_packageregistration','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($conv_reg_student_slug)
		{
			$convRegStudentD = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $conv_reg_student_slug])->contain(['Students'])->first();
			$this->set('convRegStudentD', $convRegStudentD);
		}
		
		if($crstudentevents_slug)
		{
			$crstudentEventsD = $this->Crstudentevents->find()->where(['Crstudentevents.slug' => $crstudentevents_slug])->first();
			$this->set('crstudentEventsD', $crstudentEventsD);
		}
		
		if($event_slug)
		{
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			
			// to get list of books associated with this event
			if(!empty($eventD->book_ids))
			{
				$evBookIDS = $eventD->book_ids;
				
				// to get list of books from name
				$condEventBooks = array();
				$condEventBooks[] = "(Books.id IN ($evBookIDS) )";
				$booksDD = $this->Books->find()->where($condEventBooks)->combine('id', 'book_name')->toArray();
				$this->set('booksDD', $booksDD);
			}
		}
		
		if($crstudentEventsD->id>0 && !empty($crstudentEventsD->group_name) && $crstudentEventsD->group_name != NULL && $eventD->id>0)
		{
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $crstudentEventsD->conventionregistration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
		}
		else
		{
			$this->Flash->error('Invaid information.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'packageregistration']);
		}
		
		// to check submission already done for this group or not
		$condEVSubmission 		= array();
		$condEVSubmission[] 	= "(Eventsubmissions.conventionregistration_id = '".$conventionRegD->id."')";
		$condEVSubmission[] 	= "(Eventsubmissions.event_id = '".$eventD->id."')";
		$condEVSubmission[] 	= "(Eventsubmissions.group_name = '".$crstudentEventsD->group_name."')";
		
		$checkEVSubmission		= $this->Eventsubmissions->find()->where($condEVSubmission)->count();
		if($checkEVSubmission>0)
		{
			$this->Flash->error('Submission alreadydone for this group/event.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'packageregistration']);
		}
		
		// to get students for this group
		$groupStudentListArr 	= array();
		$condGrpStudents 		= array();
		$condGrpStudents[] 		= "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."' AND Crstudentevents.convention_id = '".$conventionRegD->convention_id."' AND Crstudentevents.season_id = '".$conventionRegD->season_id."' AND Crstudentevents.season_year = '".$conventionRegD->season_year."')";
		$condGrpStudents[] = "(Crstudentevents.event_id = '".$eventD->id."')";
		$condGrpStudents[] = "(Crstudentevents.group_name = '".$crstudentEventsD->group_name."')";
		$groupstudentlist = $this->Crstudentevents->find()->where($condGrpStudents)->all();
		foreach($groupstudentlist as $grpstudent)
		{
			// to get student details
			$studentD = $this->Users->find()->where(['Users.id' => $grpstudent->student_id])->first();
			$groupStudentListArr[] = $studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name;
			$this->set('groupStudentList', implode(", ",$groupStudentListArr));
		}
		
		
        $eventsubmissions = $this->Eventsubmissions->newEntity();
        if ($this->request->is('post')) {
            $data = $this->Eventsubmissions->patchEntity($eventsubmissions, $this->request->data);
            if (count($data->errors()) == 0) {
				
				$book_ids = $this->request->data['Eventsubmissions']['book_ids'];
				//$this->prx($this->request->data);
				
				if(isset($book_ids) && count((array)$book_ids))
				{
					$data->book_ids = implode(",",(array)$book_ids);
				}
				else
				{
					$data->book_ids = '';
				}
				
				if(!empty($this->request->data['Eventsubmissions']['event_document']['name']))
				{
					$data->mediafile_original_file_name =  $this->request->data['Eventsubmissions']['event_document']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['event_document']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['event_document']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['event_document'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->mediafile_file_system_name =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['report']['name']))
				{
					$data->report =  $this->request->data['Eventsubmissions']['report']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['report']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['report']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['report'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->report =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['score_sheet']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['score_sheet']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['score_sheet']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['score_sheet'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->score_sheet =  $returnedUploadImageArray[0];
				}
				
				if(!empty($this->request->data['Eventsubmissions']['additional_documents']['name']))
				{
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->data['Eventsubmissions']['additional_documents']['name'] = str_replace($specialCharacters, $toReplace, $this->request->data['Eventsubmissions']['additional_documents']['name']);
					$imageArray = $this->request->data['Eventsubmissions']['additional_documents'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH); 
					 
					$data->additional_documents =  $returnedUploadImageArray[0];
				}
				
                $data->slug = 'event-submission-'.$conventionRegD->id.'-'.time().'-'.rand(100,1000000);
				
				$data->conventionregistration_id	= $conventionRegD->id;
				$data->conventionseason_id			= $conventionRegD->conventionseason_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->event_id 					= $eventD->id;
				$data->event_id_number 				= $eventD->event_id_number;
				
				$data->group_name 					= $crstudentEventsD->group_name;
				$data->student_id 					= 0;
				
				$data->uploaded_by_user_id 			= $user_id;
				
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
				
				//$this->prx($data);
				
                if ($this->Eventsubmissions->save($data)) {
					
					$this->Flash->success('Events submission completed successfully for group.');
                    $this->redirect(['controller' => 'conventionregistrations', 'action' => 'packageregistration']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('eventsubmissions', $eventsubmissions);
    }
	
	public function timeseventsentries($conv_reg_slug=null,$event_slug=null) {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Times Event Entries" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
		
		$user_id 	= $this->request->session()->read("user_id");
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
		}
		else
		{
			$this->set('active_convention_registrations','active');
		}
		
		$this->set('conv_reg_slug',$conv_reg_slug);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions'])->first();
		//$this->prx($conventionRegD);
		$this->set('conventionRegD', $conventionRegD);
		if($conventionRegD->status == 2)
		{
			$this->Flash->error('Sorry, admin has not approved these events yet. You will receive an email and entries will be visible once approved.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		else
		if($conventionRegD->status == 0)
		{
			$this->Flash->error('Sorry, admin declined your registration. Please contact events team.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		
		
		$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
		$this->set('eventD', $eventD);

        $condition = array();
		$condition[] = "(Eventsubmissions.convention_id = '".$conventionRegD->convention_id."')";
		$condition[] = "(Eventsubmissions.season_id = '".$conventionRegD->season_id."')";
		$condition[] = "(Eventsubmissions.season_year = '".$conventionRegD->season_year."')";
		$condition[] = "(Eventsubmissions.event_id = '".$eventD->id."')";
		
		if($eventD->group_event_yes_no == 1)
		{
			$condition[] = "(Eventsubmissions.student_id = '0')";
		}
		else
		{
			$condition[] = "(Eventsubmissions.student_id >0)";
		}
		
		$eventsubmissions = $this->Eventsubmissions->find()->where($condition)->contain(["Students","Users"])->order(['Eventsubmissions.id' => 'DESC'])->all();
		$this->set('eventsubmissions',$eventsubmissions);
		
		
		if ($this->request->is(['post']))
		{
			//$this->prx($this->request->data);
			
			$total_records = $this->request->data['total_records'];
			
			for($cntrE=1;$cntrE<=$total_records;$cntrE++)
			{
				$time_score			= $this->request->data['time_score_'.$cntrE];
				$submission_id		= $this->request->data['submission_id_'.$cntrE];
				if(isset($this->request->data['withdrawn_'.$cntrE]))
				{
					$withdraw_yes_no = 1;
				}
				else
				{
					$withdraw_yes_no = 0;
				}
				
				if(empty($time_score))
				{
					$time_score = NULL;
				}	
				
				$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.id' => $submission_id])->contain(["Users","Students"])->first();
				
				// now check if this judge submitted time score for this event submission
				$condCheckJS = array();
				$condCheckJS[] = "(Judgeevaluations.eventsubmission_id = '".$submission_id."')";
				$condCheckJS[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
				
				$checkJS = $this->Judgeevaluations->find()->where($condCheckJS)->first();
				if($checkJS)
				{
					// update record
					$this->Judgeevaluations->updateAll(
					[
						'time_score' 		=> $time_score,
						'withdraw_yes_no' 	=> $withdraw_yes_no,
					], 
					[
						"id" => $checkJS->id]
					);
				}
				else
				{
					// insert new record
					$judgeevaluations = $this->Judgeevaluations->newEntity();
					$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, array());
					
					$dataJ->slug 							= "judge-times-event-evaluation-".$eventsubmissionD->id.'-'.time();
					$dataJ->eventsubmission_id				= $eventsubmissionD->id;
					$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
					$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
					$dataJ->convention_id					= $eventsubmissionD->convention_id;
					$dataJ->user_id							= $eventsubmissionD->user_id;
					$dataJ->season_id						= $eventsubmissionD->season_id;
					$dataJ->season_year						= $eventsubmissionD->season_year;
					$dataJ->event_id						= $eventsubmissionD->event_id;
					$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
					$dataJ->group_name						= $eventsubmissionD->group_name;
					$dataJ->student_id						= $eventsubmissionD->student_id;
					$dataJ->uploaded_by_user_id				= $user_id;
					$dataJ->time_score						= $time_score;
					$dataJ->withdraw_yes_no					= $withdraw_yes_no;
					$dataJ->created 						= date('Y-m-d H:i:s');

					$resultJ = $this->Judgeevaluations->save($dataJ);
					
				}
			}
			
			$this->Flash->success('Times score submitted successfully.');
			$this->redirect(['controller' => 'eventsubmissions', 'action' => 'timeseventsentries',$conv_reg_slug,$eventD->slug]);
			
		}
    }
	
	public function distanceseventsentries($conv_reg_slug=null,$event_slug=null) {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Distance Event Entries" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
		
		$user_id 	= $this->request->session()->read("user_id");
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
		}
		else
		{
			$this->set('active_convention_registrations','active');
		}
		
		$this->set('conv_reg_slug',$conv_reg_slug);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions'])->first();
		//$this->prx($conventionRegD);
		$this->set('conventionRegD', $conventionRegD);
		if($conventionRegD->status == 2)
		{
			$this->Flash->error('Sorry, admin has not approved these events yet. You will receive an email and entries will be visible once approved.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		else
		if($conventionRegD->status == 0)
		{
			$this->Flash->error('Sorry, admin declined your registration. Please contact events team.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		
		
		$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
		$this->set('eventD', $eventD);

        $condition = array();
		$condition[] = "(Eventsubmissions.convention_id = '".$conventionRegD->convention_id."')";
		$condition[] = "(Eventsubmissions.season_id = '".$conventionRegD->season_id."')";
		$condition[] = "(Eventsubmissions.season_year = '".$conventionRegD->season_year."')";
		$condition[] = "(Eventsubmissions.event_id = '".$eventD->id."')";
		
		if($eventD->group_event_yes_no == 1)
		{
			$condition[] = "(Eventsubmissions.student_id = '0')";
		}
		else
		{
			$condition[] = "(Eventsubmissions.student_id >0)";
		}
		
		$eventsubmissions = $this->Eventsubmissions->find()->where($condition)->contain(["Students","Users"])->order(['Eventsubmissions.id' => 'DESC'])->all();
		$this->set('eventsubmissions',$eventsubmissions);
		
		
		if ($this->request->is(['post']))
		{
			//$this->prx($this->request->data);
			
			$total_records = $this->request->data['total_records'];
			
			for($cntrE=1;$cntrE<=$total_records;$cntrE++)
			{
				$arrBestD = array();
				
				$distance_attempt_1			= $this->request->data['distance_attempt_1_'.$cntrE];
				$distance_attempt_2			= $this->request->data['distance_attempt_2_'.$cntrE];
				$distance_attempt_3			= $this->request->data['distance_attempt_3_'.$cntrE];
				
				$submission_id			= $this->request->data['submission_id_'.$cntrE];
				if(isset($this->request->data['withdrawn_'.$cntrE]))
				{
					$withdraw_yes_no = 1;
				}
				else
				{
					$withdraw_yes_no = 0;
				}
				
				if(empty($distance_attempt_1))
				{
					$distance_attempt_1 = NULL;
				}
				else
				{
					$arrBestD[] = $distance_attempt_1;
				}
				
				if(empty($distance_attempt_2))
				{
					$distance_attempt_2 = NULL;
				}
				else
				{
					$arrBestD[] = $distance_attempt_2;
				}
				
				if(empty($distance_attempt_3))
				{
					$distance_attempt_3 = NULL;
				}
				else
				{
					$arrBestD[] = $distance_attempt_3;
				}
				
				$bestScore = max($arrBestD);
				
				$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.id' => $submission_id])->contain(["Users","Students"])->first();
				
				// now check if this judge submitted time score for this event submission
				$condCheckJS = array();
				$condCheckJS[] = "(Judgeevaluations.eventsubmission_id = '".$submission_id."')";
				$condCheckJS[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
				
				$checkJS = $this->Judgeevaluations->find()->where($condCheckJS)->first();
				if($checkJS)
				{
					// update record
					$this->Judgeevaluations->updateAll(
					[
						'distance_score' 			=> $bestScore,
						'distance_attempt_1' 		=> $distance_attempt_1,
						'distance_attempt_2' 		=> $distance_attempt_2,
						'distance_attempt_3' 		=> $distance_attempt_3,
						'withdraw_yes_no' 			=> $withdraw_yes_no,
					], 
					[
						"id" => $checkJS->id]
					);
				}
				else
				{
					// insert new record
					$judgeevaluations = $this->Judgeevaluations->newEntity();
					$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, array());
					
					$dataJ->slug 							= "judge-times-event-evaluation-".$eventsubmissionD->id.'-'.time();
					$dataJ->eventsubmission_id				= $eventsubmissionD->id;
					$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
					$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
					$dataJ->convention_id					= $eventsubmissionD->convention_id;
					$dataJ->user_id							= $eventsubmissionD->user_id;
					$dataJ->season_id						= $eventsubmissionD->season_id;
					$dataJ->season_year						= $eventsubmissionD->season_year;
					$dataJ->event_id						= $eventsubmissionD->event_id;
					$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
					$dataJ->group_name						= $eventsubmissionD->group_name;
					$dataJ->student_id						= $eventsubmissionD->student_id;
					$dataJ->uploaded_by_user_id				= $user_id;
					
					$dataJ->distance_attempt_1				= $distance_attempt_1;
					$dataJ->distance_attempt_2				= $distance_attempt_2;
					$dataJ->distance_attempt_3				= $distance_attempt_3;
					
					$dataJ->withdraw_yes_no					= $withdraw_yes_no;
					$dataJ->created 						= date('Y-m-d H:i:s');

					$resultJ = $this->Judgeevaluations->save($dataJ);
					
				}
			}
			
			$this->Flash->success('Distance score submitted successfully.');
			$this->redirect(['controller' => 'eventsubmissions', 'action' => 'distanceseventsentries',$conv_reg_slug,$eventD->slug]);
			
		}
    }
	
	public function scoreseventsentries($conv_reg_slug=null,$event_slug=null) {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Scores Event Entries" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
		
		$user_id 	= $this->request->session()->read("user_id");
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
		}
		else
		{
			$this->set('active_convention_registrations','active');
		}
		
		$this->set('conv_reg_slug',$conv_reg_slug);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions'])->first();
		//$this->prx($conventionRegD);
		$this->set('conventionRegD', $conventionRegD);
		if($conventionRegD->status == 2)
		{
			$this->Flash->error('Sorry, admin has not approved these events yet. You will receive an email and entries will be visible once approved.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		else
		if($conventionRegD->status == 0)
		{
			$this->Flash->error('Sorry, admin declined your registration. Please contact events team.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug]);
		}
		
		
		$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
		$this->set('eventD', $eventD);

        $condition = array();
		$condition[] = "(Eventsubmissions.convention_id = '".$conventionRegD->convention_id."')";
		$condition[] = "(Eventsubmissions.season_id = '".$conventionRegD->season_id."')";
		$condition[] = "(Eventsubmissions.season_year = '".$conventionRegD->season_year."')";
		$condition[] = "(Eventsubmissions.event_id = '".$eventD->id."')";
		
		if($eventD->group_event_yes_no == 1)
		{
			$condition[] = "(Eventsubmissions.student_id = '0')";
		}
		else
		{
			$condition[] = "(Eventsubmissions.student_id >0)";
		}
		
		$eventsubmissions = $this->Eventsubmissions->find()->where($condition)->contain(["Students","Users"])->order(['Eventsubmissions.id' => 'DESC'])->all();
		$this->set('eventsubmissions',$eventsubmissions);
		
		
		if ($this->request->is(['post']))
		{
			$this->prx($this->request->data);
			
			$posScoresArr = array(
				'pos_1' => 5,
				'pos_2' => 15,
				'pos_3' => 20,
				'pos_4' => 10,
				'pos_5' => 20,
				'pos_6' => 30,
				'pos_7' => 15,
				'pos_8' => 25,
				'pos_9' => 5
			);
			
			$total_records = $this->request->data['total_records'];
			
			for($cntrE=1;$cntrE<=$total_records;$cntrE++)
			{	
				$arrPosYN 		= array();
				$arrPosScore 	= array();
				
				for($cntPPS=1;$cntPPS<=9;$cntPPS++)
				{
					if(isset($this->request->data['pos_'.$cntPPS.'_'.$cntrE]) && !empty($this->request->data['pos_'.$cntPPS.'_'.$cntrE]))
					{
						$arrPosYN[$cntPPS] 			= 1;
						$arrPosScore[$cntPPS] 		= $posScoresArr['pos_'.$cntPPS];
					}
					else
					{
						$arrPosYN[$cntPPS] 			= 0;
						$arrPosScore[$cntPPS] 		= NULL;
					}
				}
				
				
				$pos_1						= $this->request->data['pos_1_'.$cntrE];
				$pos_2						= $this->request->data['pos_2_'.$cntrE];
				$pos_3						= $this->request->data['pos_3_'.$cntrE];
				$pos_4						= $this->request->data['pos_4_'.$cntrE];
				$pos_5						= $this->request->data['pos_5_'.$cntrE];
				$pos_6						= $this->request->data['pos_6_'.$cntrE];
				$pos_7						= $this->request->data['pos_7_'.$cntrE];
				$pos_8						= $this->request->data['pos_8_'.$cntrE];
				$pos_9						= $this->request->data['pos_9_'.$cntrE];
				
				$comp_place_1				= $this->request->data['comp_place_1_'.$cntrE];
				$comp_place_2				= $this->request->data['comp_place_2_'.$cntrE];
				$comp_place_3				= $this->request->data['comp_place_3_'.$cntrE];
				
				$submission_id			= $this->request->data['submission_id_'.$cntrE];
				
				// Position 1
				if(isset($pos_1) && !empty($pos_1))
				{
					$pos_1_yes_no 	= 1;
					$pos_1_score 	= $posScoresArr['pos_1'];
				}
				else
				{
					$pos_1_yes_no 	= 0;
					$pos_1_score 	= NULL
				}
				
				// Position 2
				if(isset($pos_2) && !empty($pos_2))
				{
					$pos_2_yes_no 	= 1;
					$pos_2_score 	= $posScoresArr['pos_2'];
				}
				else
				{
					$pos_2_yes_no 	= 0;
					$pos_2_score 	= NULL
				}
				
				// Position 3
				if(isset($pos_3) && !empty($pos_3))
				{
					$pos_3_yes_no 	= 1;
					$pos_3_score 	= $posScoresArr['pos_3'];
				}
				else
				{
					$pos_3_yes_no 	= 0;
					$pos_3_score 	= NULL
				}
				
				// Position 4
				if(isset($pos_4) && !empty($pos_4))
				{
					$pos_4_yes_no 	= 1;
					$pos_4_score 	= $posScoresArr['pos_4'];
				}
				else
				{
					$pos_4_yes_no 	= 0;
					$pos_4_score 	= NULL
				}
				
				// Position 5
				if(isset($pos_5) && !empty($pos_5))
				{
					$pos_5_yes_no 	= 1;
					$pos_5_score 	= $posScoresArr['pos_5'];
				}
				else
				{
					$pos_5_yes_no 	= 0;
					$pos_5_score 	= NULL
				}
				
				
				
				
				if(isset($this->request->data['withdrawn_'.$cntrE]))
				{
					$withdraw_yes_no = 1;
				}
				else
				{
					$withdraw_yes_no = 0;
				}
				
				$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.id' => $submission_id])->contain(["Users","Students"])->first();
				
				// now check if this judge submitted time score for this event submission
				$condCheckJS = array();
				$condCheckJS[] = "(Judgeevaluations.eventsubmission_id = '".$submission_id."')";
				$condCheckJS[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
				
				$checkJS = $this->Judgeevaluations->find()->where($condCheckJS)->first();
				if($checkJS)
				{
					// update record
					$this->Judgeevaluations->updateAll(
					[
						'pos_1_yes_no' 				=> $pos_1_yes_no,
						'pos_1_score' 				=> $pos_1_score,
						'pos_2_yes_no' 				=> $pos_2_yes_no,
						'pos_2_score' 				=> $pos_2_score,
						'pos_3_yes_no' 				=> $pos_3_yes_no,
						'pos_3_score' 				=> $pos_3_score,
						'pos_4_yes_no' 				=> $pos_4_yes_no,
						'pos_4_score' 				=> $pos_4_score,
						'pos_5_yes_no' 				=> $pos_5_yes_no,
						'pos_5_score' 				=> $pos_5_score,
						'pos_6_yes_no' 				=> $pos_6_yes_no,
						'pos_6_score' 				=> $pos_6_score,
						'pos_7_yes_no' 				=> $pos_7_yes_no,
						'pos_7_score' 				=> $pos_7_score,
						'pos_8_yes_no' 				=> $pos_8_yes_no,
						'pos_8_score' 				=> $pos_8_score,
						'pos_9_yes_no' 				=> $pos_9_yes_no,
						'pos_9_score' 				=> $pos_9_score,
						
						'withdraw_yes_no' 			=> $withdraw_yes_no,
					], 
					[
						"id" => $checkJS->id]
					);
				}
				else
				{
					// insert new record
					$judgeevaluations = $this->Judgeevaluations->newEntity();
					$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, array());
					
					$dataJ->slug 							= "judge-times-event-evaluation-".$eventsubmissionD->id.'-'.time();
					$dataJ->eventsubmission_id				= $eventsubmissionD->id;
					$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
					$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
					$dataJ->convention_id					= $eventsubmissionD->convention_id;
					$dataJ->user_id							= $eventsubmissionD->user_id;
					$dataJ->season_id						= $eventsubmissionD->season_id;
					$dataJ->season_year						= $eventsubmissionD->season_year;
					$dataJ->event_id						= $eventsubmissionD->event_id;
					$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
					$dataJ->group_name						= $eventsubmissionD->group_name;
					$dataJ->student_id						= $eventsubmissionD->student_id;
					$dataJ->uploaded_by_user_id				= $user_id;
					
					$dataJ->distance_attempt_1				= $distance_attempt_1;
					$dataJ->distance_attempt_2				= $distance_attempt_2;
					$dataJ->distance_attempt_3				= $distance_attempt_3;
					
					$dataJ->withdraw_yes_no					= $withdraw_yes_no;
					$dataJ->created 						= date('Y-m-d H:i:s');

					$resultJ = $this->Judgeevaluations->save($dataJ);
					
				}
			}
			
			$this->Flash->success('Distance score submitted successfully.');
			$this->redirect(['controller' => 'eventsubmissions', 'action' => 'distanceseventsentries',$conv_reg_slug,$eventD->slug]);
			
		}
    }

}

?>
