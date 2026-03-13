<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;


class CombinerequestsController extends AppController {

    public $paginate = ['limit' => 50];
    var $components = array('RequestHandler', 'PImage', 'PImageTest');
	
	public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Events");
		$this->loadModel("Crstudentevents");
    }

    public function viewlist() {

        $this->userLoginCheck();
        //$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
        $this->set("title_for_layout", "Combined Team/Group Events " . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_studentgroups','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Combinerequests.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// if Teacher_Parent is logged in then need to choose school id
		if($user_type == "Teacher_Parent")
		{
			$condition[] = "(Combinerequests.user_id = '".$userDetails->school_id."')";
		}
		else
		{
			$condition[] = "(Combinerequests.user_id = '".$user_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Combinerequests->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Combinerequests->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Combinerequests->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Combinerequests']['keyword']) && $this->request->data['Combinerequests']['keyword'] != '') {
                $keyword = trim($this->request->data['Combinerequests']['keyword']);
            }
        } elseif ($this->request->params) {
            if (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] != '') {
                $searchArr = $this->request->params['pass'];
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
            $condition[] = "(Combinerequests.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
		
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Combineduser','Events'],'conditions' => $condition, 'limit' => 30, 'order' => ['combinerequests.season_year' => 'DESC']];
        $this->set('combinerequests', $this->paginate($this->Combinerequests));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Combinerequests');
            $this->render('viewlist');
        }
    }
	
	public function addrequest() {

		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Combined Team/Group Events - Add Request " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentgroups','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->contain(['Conventions'])->first();
			
			/* 1. to get the list of event ids chosen in this convention for this season */
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
			
			
			/* 2. To get list of schools participated in this season */
			$arrSchoolIDSCR = array();
			$arrSchoolIDSCR[] = 0;
			$convSchools = $this->Conventionregistrations->find()->where(["Conventionregistrations.conventionseason_id" => $conventionRegD->conventionseason_id])->order(['Conventionregistrations.id' => 'DESC'])->all();
			foreach($convSchools as $convsch)
			{
				$arrSchoolIDSCR[] = $convsch->user_id;
			}
			$arrSchoolIDSCRImplode = implode(",",$arrSchoolIDSCR);
			
			// now create dropdown for schools name
			$schoolNamesDD = array();
			$condSchoolsNameCR = array();
			$condSchoolsNameCR[] = "(Users.id IN ($arrSchoolIDSCRImplode) )";
			$condSchoolsNameCR[] = "(Users.user_type = 'School')";
			$schoolsListCR = $this->Users->find()->where($condSchoolsNameCR)->order(['Users.first_name' => 'ASC'])->all();
			foreach($schoolsListCR as $schoollistcr)
			{
				$schoolNamesDD[$schoollistcr->id] = $schoollistcr->first_name.' '.$schoollistcr->last_name;
			}
			$this->set('schoolNamesDD', $schoolNamesDD);
			
			//$this->prx($schoolNamesDD);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $combinerequests = $this->Combinerequests->newEntity();
		if ($this->request->is('post')) {
			
			$data = $this->Combinerequests->patchEntity($combinerequests, $this->request->data);
            if (count($data->errors()) == 0) {

				//$this->prx($data);
				
				$eventD 			= $this->Events->find()->where(['Events.id' => $data->event_id])->first();
				$combinedSchoolName = $this->Users->find()->where(['Users.id' => $data->combine_with_user_id])->first();
				
				$data->slug 						= 'combined-request-event-'.$sess_selected_convention_registration_id.'-'.$data->student_id.'-'.time();
				$data->conventionregistration_id	= $sess_selected_convention_registration_id;
				$data->conventionseason_id			= $conventionRegD->conventionseason_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->event_id_number 				= $eventD->event_id_number;
				$data->status 						= 2;
				$data->created 						= date('Y-m-d H:i:s');
				
                if ($this->Combinerequests->save($data))
				{
                    // now send email to admin back end
					$adminD = $this->Admins->find()->where(['Admins.id' => 1])->first();
					
					$emailId = $adminD->email;
					
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '22'])->first();

					$toRepArray = array('[!school_name!]','[!combine_with_school_name!]','[!event_name!]','[!event_id_number!]','[!convention_name!]','[!season_year!]');
					$fromRepArray = array($userDetails->first_name,$combinedSchoolName->first_name,$eventD->event_name,$eventD->event_id_number,$conventionRegD->Conventions['name'],$conventionRegD->season_year);

					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend; exit;
					
					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to($emailId)
						->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
					
					
					
					$this->Flash->success('We have received your request. Admin will review and you will get notified if request approved or decline.');
					$this->redirect(['controller' => 'combinerequests', 'action' => 'viewlist']);
                }
            } 
			else
			{
                // $this->Flash->error('Please below listed errors.');
            }
			
        }
        $this->set('conventionregistrationstudents', $conventionregistrationstudents);
    }
    

}

?>
