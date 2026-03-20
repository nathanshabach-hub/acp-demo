<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\I18n;

class HearteventsController extends AppController {

    public $paginate = ['limit' => 50];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];
	
	public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Users");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
    }
	
	public function viewlist() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
        $this->set("title_for_layout", "Events of the Heart" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_cr_eventsheart','active');
		
        $msgString = '';

		$user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Heartevents.conventionregistration_id = '".$this->request->getSession()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// if teacher is logged in, then only show documents uploaded by teacher
		if($user_type == "Teacher_Parent")
		{
			$condition[] = "(Heartevents.uploaded_by_user_id = '".$user_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Heartevents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Heartevents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Heartevents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Heartevents']['keyword']) && $this->request->getData()['Heartevents']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Heartevents']['keyword']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0] != '') {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($keyword) && $keyword != '') {
            $separator[] = 'keyword:' . urlencode($keyword);
            $condition[] = "(Heartevents.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Students','Uploadeduser'],'conditions' => $condition, 'limit' => 30, 'order' => ['Heartevents.season_year' => 'DESC','Heartevents.id' => 'DESC']];
        $this->set('heartevents', $this->paginate($this->Heartevents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Heartevents');
            $this->render('viewlist');
        }
    }
	
	public function addnew() {
		
		$this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Add Events of the Heart " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_eventsheart','active');
		
        $user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			$$conditionCR = array();
			$conditionCR[] = "(Conventionregistrationstudents.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			
			// to check if teacher is logged in then teacher can only see students assigned to him
			if($user_type == "Teacher_Parent")
			{
				$conditionCR[] = "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
			}
			
			// to get list of all students selected for this convention registrations
			$selectedSCR = $this->Conventionregistrationstudents->find()->where($conditionCR)->all();
			$selectedStudentsCR = array();
			$selectedStudentsCR[] = 0;
			foreach($selectedSCR as $sels)
			{
				$selectedStudentsCR[] = $sels->student_id;
			}
			$selectedStudentsCRImplode = implode(",",$selectedStudentsCR);
			
			// to get details of student selected for this convention registration
			$condSchoolS = array();
			$condSchoolS[] = "(Users.id IN ($selectedStudentsCRImplode) )";
			$condSchoolS[] = "(Users.user_type = 'Student')";
			$condSchoolS[] = "(Users.status != '2')";
			$studentsListSchool = $this->Users->find()->where($condSchoolS)->order(['Users.first_name' => 'ASC'])->all();
			
			$studentSchoolDD = array();
			foreach($studentsListSchool as $ssl)
			{
				$studentSchoolDD[$ssl->id] = $ssl->first_name.' '.$ssl->middle_name.' '.$ssl->last_name;
			}
			$this->set('studentSchoolDD', $studentSchoolDD);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $heartevents = $this->Heartevents->newEntity();
        if ($this->request->is('post')) {
            $data = $this->Heartevents->patchEntity($heartevents, $this->request->getData());
            if (count($data->getErrors()) == 0) {
				
				if(!empty($this->request->getData()['Heartevents']['event_document']['name']))
				{
					$data->mediafile_original_file_name =  $this->request->getData()['Heartevents']['event_document']['name'];
					
					$specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
					$toReplace = "-";
					$this->request->getData()['Heartevents']['event_document']['name'] = str_replace($specialCharacters, $toReplace, $this->request->getData()['Heartevents']['event_document']['name']);
					$imageArray = $this->request->getData()['Heartevents']['event_document'];
					$returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_EVENTS_HEART_PATH); 
					 
					$data->mediafile_file_system_name =  $returnedUploadImageArray[0];
					
				}
				
                $data->slug = 'events-heart-'.$sess_selected_convention_registration_id.'-'.time().'-'.rand(100,1000000);
				
				$data->conventionregistration_id	= $sess_selected_convention_registration_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->uploaded_by_user_id 			= $user_id;
				
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Heartevents->save($data)) {
					
					$this->Flash->success('Events of the heart saved successfully.');
                    $this->redirect(['controller' => 'heartevents', 'action' => 'viewlist']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('heartevents', $heartevents);
    }
	
	public function removedocument($eventheart_slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
        $user_id = $this->request->getSession()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			// to check if slug exists
			$checkExists = $this->Heartevents->find()->where(['Heartevents.slug' => $eventheart_slug,'Heartevents.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkExists)
			{
				// to remove document as well
				@unlink(UPLOAD_EVENTS_HEART_PATH.$checkExists->mediafile_file_system_name);
				
				$this->Flash->success('Events of the heart removed successfully.');
				$this->Heartevents->deleteAll(["slug" => $eventheart_slug]);
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
		
		$this->redirect(['controller' => 'heartevents', 'action' => 'viewlist']);
    }

}

?>
