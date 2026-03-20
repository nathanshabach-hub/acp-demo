<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\I18n;


class GroupsController extends AppController {

    public $paginate = ['limit' => 50];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];
	
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
		$this->loadModel("Eventsubmissions");
    }

    public function viewlist() {

        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent']);
		
        $this->set("title_for_layout", "Student Grouping" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_cr_studentgroups','active');
		
        $msgString = '';

		$user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to get list of all events selected for this convention season
			$condConvSeason = array();
			$condConvSeason[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionRegD->conventionseason_id."')";
			$condConvSeason[] = "(Conventionseasonevents.convention_id = '".$conventionRegD->convention_id."')";
			$condConvSeason[] = "(Conventionseasonevents.season_id = '".$conventionRegD->season_id."')";
			$condConvSeason[] = "(Conventionseasonevents.season_year = '".$conventionRegD->season_year."')";
			$allEventsOfThisConvS = $this->Conventionseasonevents->find()->where($condConvSeason)->all();
			$arrEvConvS = array();
			$arrEvConvS[] = 0;
			foreach($allEventsOfThisConvS as $dataevconvseason)
			{
				$arrEvConvS[] = $dataevconvseason->event_id;
			}
			$arrEvConvSImplode = implode(",",$arrEvConvS);
			
			//To list all events that selecyed for this conv season
			$condition[] = "(Events.id IN ($arrEvConvSImplode))";
			$condition[] = "(Events.status  = '1')";
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$events = $this->Events->find()->where($condition)->order(['Events.event_id_number' => 'ASC','Events.event_name' => 'ASC'])->all();
		$this->set('events',$events);
    }
	
	public function eventgroups($event_slug = null) {
		
		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Event groups " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentgroups','active');
		
		$this->set('event_slug',$event_slug);
		
        $user_id = $this->request->getSession()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			//echo $eventD->id;
			
			// to get all the students of this event
			$condStEvNoG = array();
			$condStEvNoG[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
			$condStEvNoG[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
			$condStEvNoG[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
			$condStEvNoG[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
			$condStEvNoG[] = "(Crstudentevents.event_id = '".$eventD->id."')";
			$condStEvNoG[] = "(Crstudentevents.group_name = '' OR Crstudentevents.group_name IS NULL)";
			$eventStudentsList = $this->Crstudentevents->find()->where($condStEvNoG)->all();
			$studentsArr = array();
			$studentsArr[] = 0;
			foreach($eventStudentsList as $evstudent)
			{
				$studentsArr[] = $evstudent->student_id;
			}
			//$this->prx($studentsArr);
			
			// to get list of students name
			$studentDD = array();
			$condSE = array();
			$condSE[] = "(Users.id IN (".implode(",",$studentsArr).") )";
			$studentL = $this->Users->find()->where($condSE)->order(["Users.first_name" => "ASC","Users.middle_name" => "ASC"])->all();
			foreach($studentL as $studentel)
			{
				$studentAge = date("Y") - $studentel->birth_year;
				$studentDD[$studentel->id] = $studentel->first_name.' '.$studentel->middle_name.' '.$studentel->last_name.' (Age: '.$studentAge.' Years)';
			}
			$this->set('studentDD', $studentDD);
			//$this->prx($condSE);
			
			// now group the students based on their selected groups
			$stGroups = $this->Crstudentevents->find()->where(['Crstudentevents.conventionregistration_id' => $conventionRegD->id,'Crstudentevents.convention_id' => $conventionRegD->convention_id,'Crstudentevents.season_id' => $conventionRegD->season_id,'Crstudentevents.season_year' => $conventionRegD->season_year,'Crstudentevents.event_id' => $eventD->id,'Crstudentevents.group_name != ' => ''])->all();
			$stGArr = array();
			foreach($stGroups as $stgroup)
			{
				if($stgroup->group_name != "" && !empty($stgroup->group_name))
				{
					$stGArr[$stgroup->group_name][] = $stgroup->student_id;
				}
			}
			$this->set('stGArr', $stGArr);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// to create group
		if ($this->request->is('post')) {
            
			$student_ids 	= $this->request->getData()['Groups']['student_id'];
			$group_name 	= $this->request->getData()['Groups']['group_name'];
            
			// now update group name
			foreach($student_ids as $student_id)
			{
				$this->Crstudentevents->updateAll(['group_name' => $group_name],
				[
				"conventionregistration_id" 		=> $conventionRegD->id,
				"convention_id" 					=> $conventionRegD->convention_id,
				"season_id" 						=> $conventionRegD->season_id,
				"season_year" 						=> $conventionRegD->season_year,
				"student_id" 						=> $student_id,
				"event_id" 							=> $eventD->id
				]);
				
				// now check if this event is a group event and auto submission is yes,
				// then submit once only
				//$this->prx($eventD);
				if($eventD->auto_submission == 1 && $eventD->group_event_yes_no == 1)
				{
					// now check if submission already done for this Group
					$checkSubmission = $this->Eventsubmissions->find()->where(['Eventsubmissions.event_id' => $eventD->id,'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,'Eventsubmissions.group_name' => $group_name])->first();
					//$this->prx($checkSubmission);
					if(!$checkSubmission)
					{
						// submit event
						$eventsubmissions = $this->Eventsubmissions->newEntity();
						$dataES = $this->Eventsubmissions->patchEntity($eventsubmissions, array());

						$dataES->slug 						= 'event-submission-'.$conventionRegD->id.'-'.time().'-'.rand(100,1000000);
						$dataES->conventionregistration_id	= $conventionRegD->id;
						$dataES->conventionseason_id		= $conventionRegD->conventionseason_id;
						$dataES->convention_id				= $conventionRegD->convention_id;
						$dataES->user_id					= $conventionRegD->user_id;
						$dataES->season_id 					= $conventionRegD->season_id;
						$dataES->season_year 				= $conventionRegD->season_year;
						$dataES->event_id 					= $eventD->id;
						$dataES->event_id_number 			= $eventD->event_id_number;
						$dataES->student_id 				= 0;
						$dataES->group_name 				= $group_name;
						
						$dataES->uploaded_by_user_id 			= $conventionRegD->user_id;
						
						//$data->book_ids 					= '';
						$dataES->created = date('Y-m-d H:i:s');
						$dataES->modified = date('Y-m-d H:i:s');

						$resultES = $this->Eventsubmissions->save($dataES);
					}
				}
			}
			
			$this->Flash->success('Group created successfully.');
			$this->redirect(['controller' => 'groups', 'action' => 'eventgroups',$event_slug]);
        }
    }
	
	public function removestudentfromgroup($event_slug = null,$student_id = null) {
		
		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			//echo $eventD->id;exit;
			
			// to get group details
			$groupD = $this->Crstudentevents->find()
						->where([
						'conventionregistration_id' => $conventionRegD->id,
						"convention_id"				=> $conventionRegD->convention_id,
						"season_id" 				=> $conventionRegD->season_id,
						"season_year" 				=> $conventionRegD->season_year,
						"student_id" 				=> $student_id,
						"event_id" 					=> $eventD->id
						])->first();
			$group_name = $groupD->group_name;
			
			$this->Crstudentevents->updateAll(['group_name' => '','modified' => date("Y-m-d H:i:s")],
				[
				"conventionregistration_id" 		=> $conventionRegD->id,
				"convention_id" 					=> $conventionRegD->convention_id,
				"season_id" 						=> $conventionRegD->season_id,
				"season_year" 						=> $conventionRegD->season_year,
				"student_id" 						=> $student_id,
				"event_id" 							=> $eventD->id
				]);
				
			// here check that how many students are in this group..
			// if no student left in this group, then remove submission for this Group
			// .. as well
			$checkStudentsInGroupCount = $this->Crstudentevents
								->find()
								->where([
								"conventionregistration_id" => $conventionRegD->id,
								"convention_id"				=> $conventionRegD->convention_id,
								"season_id"					=> $conventionRegD->season_id,
								"season_year"				=> $conventionRegD->season_year,
								"season_year"				=> $conventionRegD->season_year,
								"group_name"				=> $group_name
								])->count();
								
			if($checkStudentsInGroupCount == 0)
			{
				// remove submission of this group
				$this->Eventsubmissions->deleteAll(
				[
					"conventionregistration_id" => $conventionRegD->id,
					"convention_id"				=> $conventionRegD->convention_id,
					"season_id"					=> $conventionRegD->season_id,
					"season_year"				=> $conventionRegD->season_year,
					"season_year"				=> $conventionRegD->season_year,
					"event_id" 					=> $eventD->id
				]);
				
			}
				
			$this->Flash->success('Student removed from group successfully.');
			$this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
    

}

?>
