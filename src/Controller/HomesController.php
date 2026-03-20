<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;
use Cake\I18n\I18n;

class HomesController extends AppController {

    public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        // Load Files model
		$this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Evaluationquestions");
		$this->loadModel("Books");
		$this->loadModel("Settings");
		$this->loadModel("Eventcategories");

        // Set the layout
        // $this->layout = 'frontend';
    }

    public function index() {
		
		if($this->request->getSession()->read("user_id") > 0)
		{
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Welcome '.TITLE_FOR_PAGES);
		
		$this->set('header_menu_register_active', 'active');
		
		// first to get season_id for current year
		$season_id = $this->getCurrentSeason();
		$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
		$this->set('season_id',$season_id);
		
		$conventionIDS 		= array();
		$conventionIDS[] 	= 0;
		
		// We need to show conventions those are linked with current season
		$conventionSeasons = $this->Conventionseasons->find()->where(['Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->order(['Conventionseasons.id' => 'ASC'])->all();
		foreach($conventionSeasons as $convs)
		{
			if(!in_array($convs->convention_id,(array)$conventionIDS))
			{
				$conventionIDS[] 	= $convs->convention_id;
			}
		}
		//$this->prx($conventionIDS);
		
		$conventionIDSImploded = implode(",",$conventionIDS);
		
		// to get conventions
		$condConvention = array();
		$condConvention[] = "(Conventions.id IN ($conventionIDSImploded))";
		$condConvention[] = "(Conventions.status  = '1')";
		$conventionDD = $this->Conventions->find()->where($condConvention)->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionDD', $conventionDD);
		
    }
	
	public function chooseconvention($convention_id=null,$season_id=null){
        if ($convention_id>0) {
            
			$conventionD = $this->Conventions->find()->where(['Conventions.id' => $convention_id])->first();
			
			if($conventionD)
			{
				$this->viewBuilder()->setLayout("");
				//$this->Users->updateAll(['status' => '1', 'activation_status'=>'1'], ["slug"=>$slug]);
				//$this->set('action', '/admin/users/deactivateuser/' . $slug);
				
				$this->set('convention_slug', $conventionD->slug);
				$this->set('season_id', $season_id);
				$this->viewBuilder()->templatePath('Element' . DS . 'Homes');
				$this->render('index');
			}
        }
    }
	
	public function checkcode($convention_slug=null,$season_id=null) {
		
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Check Customer Code '.TITLE_FOR_PAGES);
		
		$this->set('header_menu_register_active', 'active');
		
		$this->set('convention_slug',$convention_slug);
		$this->set('season_id',$season_id);
		
		//$this->prx($this->request->getData()['Users']['customer_code']);
		
		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
		
		if(!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
		
		// to see if form posted, then go to next page
		if ($this->request->is('post')) {
			
			$customer_code = $this->request->getData()['Users']['customer_code'];
			
			if($customer_code)
			{
				
				// check code
				$codeC = $this->Users->find()->where(['Users.customer_code' => $customer_code])->first();
				
				if($codeC)
				{
					$this->set('code_found','1');
					$this->set('customer_code',$codeC->customer_code);
					
					// to check if account already verified
					if($codeC->activation_status == 1)
					{
						$this->set('account_verified','1');
						
						// to check that this user already registered for this convention and season or not
						$userCheckPrevReg = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $conventionD->id,'Conventionregistrations.user_id' => $codeC->id,'Conventionregistrations.season_id' => $season_id])->first();
						if($userCheckPrevReg)
						{
							$this->set('already_registered','1');
							$this->Flash->error('You have already registered for this convention. Please login and continue.');
						}
						else
						{
							$this->set('already_registered','0');
						}
						
					}
					else
					{
						$this->set('account_verified','0');
					}
				}
				else
				{
					$this->set('code_not_found','1');
					$this->set('customer_code',$customer_code);
				}
			}
			else
			{
				$this->Flash->error('Please enter code.');
				$this->redirect(['controller' => 'homes', 'action' => 'checkcode', $convention_slug]);
			}
		}
    }
	
	public function sendconvreglink($convention_slug=null,$customer_code=null,$season_id=null) {
		
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Welcome '.TITLE_FOR_PAGES);
		
		$this->set('header_menu_register_active', 'active');
		
		// to check if customer code exists
		$codeC = $this->Users->find()->where(['Users.customer_code' => $customer_code])->first();
		if($codeC)
		{
			// now check if email is valid or nots
			if($this->valid_email($codeC->email_address))
			{
				// check convention details
				$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
				if($conventionD)
				{
					// to get season details
					$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
					if($seasonD)
					{
						// enter this user record in conventionregistrations table
						$convention_id 	= $conventionD->id;
						$user_id 		= $codeC->id;
						
						// to get the convention season details
						$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id,'Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->first();
						
						// to check if this record already exists
						$checkRegExists = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
						if($checkRegExists)
						{
							$convRegID 		= $checkRegExists->id;
							$convRegSlug 	= $checkRegExists->slug;
							$this->Conventionregistrations->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $convRegID]);
						}
						else
						{
							// insert new record
							$conventionregistrations = $this->Conventionregistrations->newEntity();
							$dataCR = $this->Conventionregistrations->patchEntity($conventionregistrations, array());

							$dataCR->conventionseason_id 	= $convSeasonD->id;
							$dataCR->slug 					= "convention-registration-".$convention_id.'-'.$user_id.'-'.$season_id.'-'.time();
							$dataCR->convention_id			= $convention_id;
							$dataCR->user_id				= $user_id;
							$dataCR->season_id				= $season_id;
							$dataCR->season_year 			= $seasonD->season_year;
							$dataCR->status 				= 0;
							
							$dataCR->created 				= date('Y-m-d H:i:s');
							$dataCR->modified 				= NULL;

							$resultCR 		= $this->Conventionregistrations->save($dataCR);
							$convRegID 		= $resultCR->id;
							$convRegSlug 	= $resultCR->slug;
						}
					
						// now send email to user with a link to verify
						$emailId 				= $codeC->email_address;
						$school_name 			= $codeC->first_name;
						$convention_name 		= $conventionD->name;
						$season_year 			= $seasonD->season_year;
						$customerCode 			= $seasonD->customer_code;
						
						$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '2'])->first();

						$link = HTTP_PATH . "/homes/confirmconvregistration/" . $convRegSlug . "/" . md5($convRegID) . "/" . urlencode($convention_name);
						//$sitelink = '<a style="color:#000; text-decoration: underline;" href="mailto:' . MAIL_FROM . '">' . MAIL_FROM . '</a>';
						
						$toRepArray 	= array('[!school_name!]','[!convention_name!]','[!season_year!]','[!LINK!]','[!customer_code!]');
						$fromRepArray 	= array($school_name,$convention_name,$season_year,$link,$customerCode);
						
						$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
						$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
						
						//echo $messageToSend;exit;
						
						$mailer = new Mailer('default');
						if (method_exists($mailer, 'setEmailFormat')) {
							$mailer->setEmailFormat('html');
						}
						if (method_exists($mailer, 'setTo')) {
							$mailer->setTo($emailId);
						}
						if (method_exists($mailer, 'setCc')) {
							$mailer->setCc(HEADERS_CC);
						}
						if (method_exists($mailer, 'setFrom')) {
							$mailer->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME]);
						}
						if (method_exists($mailer, 'setSubject')) {
							$mailer->setSubject($subjectToSend);
						}

						if (method_exists($mailer, 'deliver')) {
							$mailer->deliver($messageToSend);
						} else {
							$mailer->send($messageToSend);
						}
							
						$this->Flash->success('Registration link sent successfully.');
					}
				}
			}
			else
			{
				$this->Flash->error('Invalid email address found. Please contact events team events@scee.edu.au.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
		$this->redirect(['controller' => 'homes', 'action' => 'index']);
		
    }
	
	public function confirmconvregistration($conventionregistration_slug=null,$conventionregistration_id_md5=null,$convention_name=null) {
		
		//http://localhost:8081/accelerate/site/homes/confirmconvregistration/convention-registration-4-1-1-1681470594/c81e728d9d4c2f636f067f89cc14862c/All+Saints+Convention+Centre
		
		// now check values received when link is clicked
		// to get conv reg details
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conventionregistration_slug])->first();
		if(md5($conventionRegD->id) == $conventionregistration_id_md5)
		{
			// to check if this link already used
			if($conventionRegD->status == 1)
			{
				$this->Flash->error('Registration link already used.');
				$this->redirect(['controller' => 'homes', 'action' => 'index']);
			}
			else
			{
				// now create first teacher of this school with main contact person
				// .. then send link to verify account and create password
				$schoolD = $this->Users->find()->where(['Users.id' => $conventionRegD->user_id])->first();
				
				$users = $this->Users->newEntity();
				$dataUT = $this->Users->patchEntity($users, array());

				$dataUT->slug 					= $this->getSlug($schoolD->middle_name.'-'.rand(100,1000000). time(), 'Users');
				$dataUT->user_type 				= "Teacher_Parent";
				$dataUT->school_id				= $conventionRegD->user_id;
				$dataUT->title					= '';
				$dataUT->first_name				= $schoolD->middle_name;
				$dataUT->last_name				= '';
				$dataUT->email_address 			= $schoolD->email_address;
				$dataUT->gender 				= '';
				$dataUT->is_judge 				= 0;
				
				$dataUT->status 				= 0;
				$dataUT->activation_status 		= 0;
				$dataUT->created 				= date('Y-m-d H:i:s');
				$dataUT->modified 				= date('Y-m-d H:i:s');

				$resultUT = $this->Users->save($dataUT);
				
				
				// now send email to this teacher to verify account and set password
				$emailId 				= $resultUT->email_address;
				$teacher_name 			= $resultUT->first_name;
				
				$school_name 			= $schoolD->first_name;
				
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '4'])->first();

				$link = HTTP_PATH . "/homes/teacherverifyaccount/" . $resultUT->slug . "/" . md5($resultUT->slug) . "/" . md5($resultUT->id);
				//$sitelink = '<a style="color:#000; text-decoration: underline;" href="mailto:' . MAIL_FROM . '">' . MAIL_FROM . '</a>';
				
				$toRepArray 	= array('[!teacher_name!]','[!school_name!]','[!teacher_email_address!]','[!LINK!]','[!customer_code!]');
				$fromRepArray 	= array($teacher_name,$school_name,$emailId,$link,$schoolD->customer_code);
				
				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend;exit;
				
				$mailer = new Mailer('default');
				if (method_exists($mailer, 'setEmailFormat')) {
					$mailer->setEmailFormat('html');
				}
				if (method_exists($mailer, 'setTo')) {
					$mailer->setTo($emailId);
				}
				if (method_exists($mailer, 'setFrom')) {
					$mailer->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME]);
				}
				if (method_exists($mailer, 'setSubject')) {
					$mailer->setSubject($subjectToSend);
				}

				if (method_exists($mailer, 'deliver')) {
					$mailer->deliver($messageToSend);
				} else {
					$mailer->send($messageToSend);
				}
				
				// confirm school account
				$this->Users->updateAll(['activation_status' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $conventionRegD->user_id]);
				
				// verify link
				$this->Conventionregistrations->updateAll(['status' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $conventionRegD->id]);
				$this->redirect(['controller' => 'homes', 'action' => 'reglinkverified',$conventionregistration_slug,$conventionregistration_id_md5,$convention_name]);
				
			}
		}
		else
		{
			$this->Flash->error('Invalid registration link.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
	}
	
	public function reglinkverified($conventionregistration_slug=null,$conventionregistration_id_md5=null,$convention_name=null) {
		
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Welcome '.TITLE_FOR_PAGES);
		
		$this->set('header_menu_register_active', 'active');
		
		$this->set('conventionregistration_slug', $conventionregistration_slug);
		
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conventionregistration_slug])->first();
		if($conventionRegD)
		{
			$this->set('conventionRegD', $conventionRegD);
		}
		else
		{
			$this->Flash->error('Invalid registration information.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
	
	}
	
	
	public function teacherverifyaccount($teacher_slug=null,$md5_teacher_slug=null,$md5_teacher_id=null) {
		
		
		// now check values received when link is clicked
		// to get conv reg details
		$teacherD = $this->Users->find()->where(['Users.slug' => $teacher_slug,'Users.activation_status' => 0,'Users.status' => 0])->first();
		if(md5($teacherD->id) == $md5_teacher_id && md5($teacher_slug) == $md5_teacher_slug)
		{	
			// verify teacher account
			$this->Users->updateAll(['activation_status' => 1,'status' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $teacherD->id]);
			$this->redirect(['controller' => 'users', 'action' => 'teachersetpassword',$teacherD->email_address,md5($teacherD->email_address),$teacherD->id,md5($teacherD->id)]);
			
			$this->Flash->success('Account verified successfully. Please set your passwords.');
		}
		else
		{
			$this->Flash->error('Invalid verification link.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
	}
	
	public function headerconvddfrmsubmit() {
		
		//$this->prx($this->request->getSession()->read("user_type"));
		
		$convention_id = $this->request->getData()['Conventions']['convention_id'];
		
		if ($this->request->getSession()->read("user_id") > 0)
		{
			if($convention_id >0)
			{
				// to get convention registration details
				$season_id = $this->getCurrentSeason();
				$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
				
				if ($this->request->getSession()->read("user_type") == "School")
				{
					$user_id = $this->request->getSession()->read("user_id");
					$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
				}
				
				if ($this->request->getSession()->read("user_type") == "Teacher_Parent")
				{
					$user_id = $this->request->getSession()->read("user_id");
					$userD = $this->Users->find()->where(['Users.id' => $user_id])->first();
					$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $userD->school_id,'Conventionregistrations.season_id' => $season_id])->first();
				}
				
				if ($this->request->getSession()->read("user_type") == "Judge")
				{
					$user_id = $this->request->getSession()->read("user_id");
					$userD = $this->Users->find()->where(['Users.id' => $user_id])->first();
					$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
					//$this->prx($convRegD);
				}
				
				$this->request->getSession()->write("sess_selected_convention_registration_id", $convRegD->id);
				$this->request->getSession()->write("sess_selected_convention_id", $convention_id);
			}
			else
			{
				// remove session data for convention registration
				$this->request->getSession()->delete('sess_selected_convention_registration_id');
				$this->request->getSession()->delete('sess_selected_convention_id');
			}
		}
		
		
		if ($this->request->getSession()->read("user_id") > 0 && ($this->request->getSession()->read("user_type") == "School"))
		{
			if($convention_id >0)
			{
				// to get convention registration details
				$season_id = $this->getCurrentSeason();
				$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
				$user_id = $this->request->getSession()->read("user_id");
				
				$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
				
				$this->request->getSession()->write("sess_selected_convention_registration_id", $convRegD->id);
				$this->request->getSession()->write("sess_selected_convention_id", $convention_id);
			}
			else
			{
				// remove session data for convention registration
				$this->request->getSession()->delete('sess_selected_convention_registration_id');
				$this->request->getSession()->delete('sess_selected_convention_id');
			}
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		
	}
	
	public function assignteachertostudent($crs_slug=null,$teacher_id=null)
	{
		$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
		if($sess_selected_convention_registration_id>0)
		{
			// update teacher_parent_id for this student
			$this->Conventionregistrationstudents->updateAll(['teacher_parent_id' => $teacher_id], ["slug" => $crs_slug, 'conventionregistration_id' => $sess_selected_convention_registration_id]);
			echo 'Supervisor assigned successfully.';
			exit;
		}
	}
	
	public function eventsubmissions($event_id=null)
	{
		$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
		if($event_id>0)
		{
			$returnArr 			= array();
			$dropdown_values 	= array();
			
			// to get conv reg details
			$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to check if Teacher_Parent is logged in, then we need to allow actions to those students
			$studentsArr = array();
			$user_id 	= $this->request->getSession()->read("user_id");
			$user_type 	= $this->request->getSession()->read("user_type");
			if($user_type == "Teacher_Parent")
			{
				// to get the students assigned to this teacher
				$condES 	= array();
				$condES[] 	= "(Conventionregistrationstudents.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
				$condES[] 	= "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
				
				$crStudentsList = $this->Conventionregistrationstudents->find()->where($condES)->all();
				foreach($crStudentsList as $studentR)
				{
					$studentsArr[] = $studentR->student_id;
				}
			}
			
			//$this->prx($studentsArr);
			
			
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			// to check if this event is group event or not
			if($eventD->group_event_yes_no == 1)
			{
				$event_type 			= 'group_event';
				$groupsListArr 			= array();
				$onlyGroupsListArr 		= array();
				
				// for group event.. show list of groups for this event and conv reg
				$condGroupList = array();
				$condGroupList[] = "(Crstudentevents.conventionregistration_id = '".$convRegD->id."' AND Crstudentevents.convention_id = '".$convRegD->convention_id."' AND Crstudentevents.season_id = '".$convRegD->season_id."' AND Crstudentevents.season_year = '".$convRegD->season_year."')";
				$condGroupList[] = "(Crstudentevents.event_id = '".$eventD->id."')";
				$grouplist = $this->Crstudentevents->find()->where($condGroupList)->order(["Crstudentevents.group_name" => "ASC"])->all();
				$cntrGL = 0;
				foreach($grouplist as $grpn)
				{
					if(!in_array($grpn->group_name,(array)$onlyGroupsListArr))
					{
						// to check how many students in this group
						$groupStudentListArr = array();
						$condGrpStudents = array();
						$condGrpStudents[] = "(Crstudentevents.conventionregistration_id = '".$convRegD->id."' AND Crstudentevents.convention_id = '".$convRegD->convention_id."' AND Crstudentevents.season_id = '".$convRegD->season_id."' AND Crstudentevents.season_year = '".$convRegD->season_year."')";
						$condGrpStudents[] = "(Crstudentevents.event_id = '".$eventD->id."')";
						$condGrpStudents[] = "(Crstudentevents.group_name = '".$grpn->group_name."')";
						$groupstudentlist = $this->Crstudentevents->find()->where($condGrpStudents)->all();
						foreach($groupstudentlist as $grpstudent)
						{
							// to get student details
							$studentD = $this->Users->find()->where(['Users.id' => $grpstudent->student_id])->first();
							$groupStudentListArr[] = $studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name;
						}
						
						if(count($groupStudentListArr) > 0)
						{
							asort($groupStudentListArr);
							
							$groupsListArr[$cntrGL]['id'] 	= $grpn->group_name;
							$groupsListArr[$cntrGL]['name'] = "Group ".$grpn->group_name.' ('.implode(", ",$groupStudentListArr).')';
							
							$onlyGroupsListArr[] = $grpn->group_name;
							
							$cntrGL++;
						}
					}
				}
				$dropdown_values = $groupsListArr;
			}
			else
			{
				$event_type = 'solo_event';
				
				$studentsIDsArr 	= array();
				$studentsIDsArr[] 	= 0;
				
				// for solo group.. show list of students
				$condEventStudents = array();
				$condEventStudents[] = "(Crstudentevents.conventionregistration_id = '".$convRegD->id."' AND Crstudentevents.convention_id = '".$convRegD->convention_id."' AND Crstudentevents.season_id = '".$convRegD->season_id."' AND Crstudentevents.season_year = '".$convRegD->season_year."')";
				$condEventStudents[] = "(Crstudentevents.event_id = '".$eventD->id."')";
				
				if(count($studentsArr) > 0)
				{
					$condEventStudents[] = "(Crstudentevents.student_id IN (".implode(",",$studentsArr)."))";
				}
				
				$eventstudentslist = $this->Crstudentevents->find()->where($condEventStudents)->all();
				
				foreach($eventstudentslist as $evstudentid)
				{
					$studentsIDsArr[] = $evstudentid->student_id;
				}
				
				if(count($studentsIDsArr) > 1)
				{
					$studentsIDsArrImplode = implode(",",$studentsIDsArr);
					
					$studentFMLNames = array();
					$cntrStFML = 0;
					// now get student names in ascending order
					$condStIDSUsers = array();
					$condStIDSUsers[] = "(Users.id IN ($studentsIDsArrImplode) )";
					$usersstudentslist = $this->Users->find()->where($condStIDSUsers)->order(["Users.first_name" => "ASC","Users.middle_name" => "ASC","Users.last_name" => "ASC"])->all();
					foreach($usersstudentslist as $useridstudent)
					{
						$studentFMLNames[$cntrStFML]['id'] = $useridstudent->id;
						$studentFMLNames[$cntrStFML]['name'] = $useridstudent->first_name.' '.$useridstudent->middle_name.' '.$useridstudent->last_name;
						
						$cntrStFML++;
					}
				}
				
				$dropdown_values = $studentFMLNames;
				
				
				//$this->prx($studentFMLNames);
				
				
			}
			
			// sudhir 17-April-2024
			// to get list of books associated with this event
			$bookDDArr = array();
			if(!empty($eventD->book_ids))
			{
				$evBookIDS = $eventD->book_ids;
				$bookCntr = 0;
				
				// to get list of books from name
				$condEventBooks = array();
				$condEventBooks[] = "(Books.id IN ($evBookIDS) )";
				$booksList = $this->Books->find()->where($condEventBooks)->order(["Books.book_name" => "ASC"])->all();
				foreach($booksList as $bookd)
				{
					$bookDDArr[$bookCntr]['id'] 	= $bookd->id;
					$bookDDArr[$bookCntr]['name'] 	= $bookd->book_name;
					
					$bookCntr++;
				}
				
			}
			
			$returnArr['event_type'] 		= $event_type;
			$returnArr['dropdown_values'] 	= $dropdown_values;
			
			// now send event values
			$returnArr['upload_type'] 			= $eventD->upload_type;
			$returnArr['report'] 				= $eventD->report;
			$returnArr['context_box'] 			= $eventD->context_box;
			$returnArr['score_sheet'] 			= $eventD->score_sheet;
			$returnArr['additional_documents'] 	= $eventD->additional_documents;
			
			$returnArr['book_dropdown_values'] 	= $bookDDArr;
			
			echo json_encode($returnArr);
			exit;
		}
	}
	
	public function getcategoryquestions($cat_id=null)
	{
		$cntrCatQ = 0;
		$arrCatQ = array();
		$returnArr 			= array();
		
		$catQuestionList = $this->Evaluationquestions->find()->where(["Evaluationquestions.evaluationcategory_id" => $cat_id])->order(["Evaluationquestions.question" => "ASC"])->all();
		
		
		foreach($catQuestionList as $catquestion)
		{
			$arrCatQ[$cntrCatQ]['id'] = $catquestion->id;
			$arrCatQ[$cntrCatQ]['name'] = $catquestion->question.' ('.$catquestion->max_points.')';
			
			$cntrCatQ++;
		}
		
		$returnArr['category_questions'] 	= $arrCatQ;
		
		echo json_encode($returnArr);
		exit;
		
	}
	
	//
	public function getstudentsofschool($conv_season_id=null,$school_id=null)
	{
		$cntrS 			= 0;
		$arrStudents 	= array();
		$returnArr 		= array();
		
		$convSeasonD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$conv_season_id])->first();
		//$this->prx($convSeasonD);
		
		$studentList = $this->Conventionregistrationstudents->find()->where(
		[
		"Conventionregistrationstudents.convention_id" => $convSeasonD->convention_id,
		"Conventionregistrationstudents.season_id" => $convSeasonD->season_id,
		"Conventionregistrationstudents.season_year" => $convSeasonD->season_year,
		"Conventionregistrationstudents.user_id" => $school_id,
		]
		)
		->contain(["Students"])
		->order(["Conventionregistrationstudents.id" => "ASC"])
		->all();
		
		
		foreach($studentList as $studentrec)
		{
			$arrStudents[$cntrS]['id'] 		= $studentrec->student_id;
			$arrStudents[$cntrS]['name'] 	= $studentrec->Students['first_name'].' '.$studentrec->Students['middle_name'].' '.$studentrec->Students['last_name'];
			
			$cntrS++;
		}
		
		// Sort by name
		usort($arrStudents, function ($a, $b) {
			return strcmp($a['name'], $b['name']);
		});
		
		$returnArr['student_list'] 	= $arrStudents;
		
		echo json_encode($returnArr);
		exit;
		
	}
	//
	
	public function checkstudentevent($crs_slug=null,$checkedEventIDS = null, $lastCheckedEVID = null)
	{
		$returnArr 					= array();
		$discardLastEventSelected 	= 0;
		$errorFlag 					= 0;
		$errorMsg 					= array();
		
		if($checkedEventIDS)
		{
			$checkedEventIDSExploded 	= explode(",",$checkedEventIDS);
			$totalEvChecked 			= count($checkedEventIDSExploded);
		}
		else
		{
			$errorFlag 		= 1;
			$errorMsg[] 	= 'Please choose minimum events.';
		}
		
		
		//echo $checkedEventIDS;
		if($crs_slug && $totalEvChecked>0)
		{	
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
			
			$minMaxEventsArr = $this->getMinMaxEvents($sess_selected_convention_registration_id);
			
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $crs_slug,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->contain(['Students','Users'])->first();
			//$this->prx($checkCRS);
			
			// first check count - how many events checked
			if($totalEvChecked<$minMaxEventsArr['min_events_student'])
			{
				//$errorFlag 		= 1;
				//$errorMsg[] 	= 'Minimum events required to select is '.$minMaxEventsArr['min_events_student'].'.';
			}
			else	
			if($totalEvChecked>$minMaxEventsArr['max_events_student'])
			{
				$errorFlag 		= 1;
				$errorMsg[] 	= 'Overall maximum events per student required to select is '.$minMaxEventsArr['max_events_student'].'.';
				$discardLastEventSelected = 1;
			}
			
			// now run through each event selected and check in which category it is, then compare
			$arrLiveEventCats = array();
			$arrLiveEventDivs = array();
			$condEv = array();
			$condEv[] = "(Events.id IN ($checkedEventIDS))";
			$eventC = $this->Events->find()->where($condEv)->contain(['Divisions'])->all();
			foreach($eventC as $eventrec)
			{	
				// for categories
				$eventcategory_id = $eventrec->Divisions['eventcategory_id'];
				// to check if counter for this event cat already added or not
				if(!in_array('cat_'.$eventcategory_id,$arrLiveEventCats))
				{
					$arrLiveEventCats[] = 'cat_'.$eventcategory_id;
				}
				$arrLiveEventCats['cat_'.$eventcategory_id][] = $eventcategory_id;
				
				// for divisions
				$division_id = $eventrec->division_id;
				// to check if counter for this event div already added or not
				if(!in_array('div_'.$division_id,$arrLiveEventDivs))
				{
					$arrLiveEventDivs[] = 'div_'.$division_id;
				}
				$arrLiveEventDivs['div_'.$division_id][] = $division_id;
			}
			
			// now check in each category, max events allowed
			$eventCats = $this->Eventcategories->find()->where([])->all();
			foreach($eventCats as $eventcat)
			{
				// now check that if this cat exists in selected events, then check max
				// if max exceeds, then generate error
				if(in_array('cat_'.$eventcat->id,$arrLiveEventCats))
				{
					// check max Events
					if(count($arrLiveEventCats['cat_'.$eventcat->id])>$eventcat->max_events)
					{
						$errorFlag 					= 1;
						$errorMsg[] 				= 'Maximum events reached in category '.$eventcat->name.'.';
						$discardLastEventSelected = 1;
					}
				}
			}
			
			// now check in each divisions, max events allowed
			$eventDivs = $this->Divisions->find()->where([])->all();
			foreach($eventDivs as $eventdiv)
			{
				// now check that if this div exists in selected events, then check max
				// if max exceeds, then generate error
				if(in_array('div_'.$eventdiv->id,$arrLiveEventDivs))
				{
					// check max Events
					if(count($arrLiveEventDivs['div_'.$eventdiv->id])>$eventdiv->max_events)
					{
						$errorFlag 					= 1;
						$errorMsg[] 				= 'Maximum events reached in division '.$eventdiv->name.'.';
						$discardLastEventSelected = 1;
					}
				}
			}
			
			
			$returnArr['errorFlag'] 					= $errorFlag;
			$returnArr['errorMsg'] 						= $errorMsg;
			$returnArr['totalEvChecked'] 				= $totalEvChecked;
			$returnArr['discardLastEventSelected'] 		= $discardLastEventSelected;
			$returnArr['lastEventIDChecked'] 			= $lastCheckedEVID;
		}
		
		//$this->prx($arrLiveEventCats);
		
		echo json_encode($returnArr);
		exit;
	}
	
	
	// to insert event for first time in empty events table
	public function eventscsvimport() { exit;
		
		// to get divisions values
		$divisionsArr = array();
		$divList = $this->Divisions->find()->where([])->all();
		foreach($divList as $divdata)
		{
			$divisionsArr[$divdata->name] = $divdata->id;
		}
		
		$totalImport = 0;
		$cntrR = 1;
		$csvFilePath = UPLOAD_SCHOOLS_CSV_PATH."230424Events.csv";
		if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				//$num = count($data);exit;
				//echo "<p> $num fields in line $cntrR: <br /></p>\n";
				//$this->prx($data);
				/*for ($c=0; $c < $num; $c++) {
					echo $data[$c] . "<br />\n";
				}*/
				
				
				if($cntrR >1)
				{
					echo 'dddddddddd';$this->prx($data);
			
					$division_name = trim($data[1]);
					$divSCond = array();
					$divSCond[] = "(Divisions.name LIKE '%".$division_name."%')";
					
					$divisionD = $this->Divisions->find()->where($divSCond)->first();
					//echo '<pre>';print_r($divisionD);exit;
					
					// to get division id
					
					$events = $this->Events->newEntity();
					$dataE 	= $this->Events->patchEntity($events, array());

					$dataE->event_id_number					= trim($data[0]);
					$dataE->division_id						= $divisionD->id;
					$dataE->event_name						= trim($data[2]);
					$dataE->upload_type						= trim($data[3]);
					$dataE->report 							= (trim($data[4]) == 'Yes') ? "1" : "0";
					$dataE->context_box 					= (trim($data[5]) == 'Yes') ? "1" : "0";
					$dataE->score_sheet 					= (trim($data[6]) == 'Yes') ? "1" : "0";
					$dataE->additional_documents 			= (trim($data[7]) == 'Yes') ? "1" : "0";
					$dataE->event_type 						= trim($data[8]);
					$dataE->event_grp_name 					= trim($data[9]);
					$dataE->event_gender 					= trim($data[10]);
					
					$dataE->group_event_yes_no 				= (trim($data[11]) == 'TRUE') ? "1" : "0";
					$dataE->min_no 							= trim($data[12]);
					$dataE->max_no 							= trim($data[13]);
					
					$dataE->status 							= 1;
					$dataE->created 						= date('Y-m-d H:i:s');
					$dataE->modified 						= date('Y-m-d H:i:s');
					
					$dataE->slug 							= $this->getSlug(trim($data[2]) . ' ' . time(), 'Events').'-'.rand(10,100000);
					
					//$this->prx($dataE);

					$resultE = $this->Events->save($dataE);
					
					$totalImport++;
				}
				
				$cntrR++;
			}
			fclose($handle);
		}
		echo 'Total Import = '.$totalImport;
		exit;
		
		
    }
	
	// to update event in 19-April-2024, client provided events csv with new fields.. so just updated existing events
	public function eventscsvupdate() { exit;
		
		// to get divisions values
		$divisionsArr = array();
		$divList = $this->Divisions->find()->where([])->all();
		foreach($divList as $divdata)
		{
			$divisionsArr[$divdata->name] = $divdata->id;
		}
		
		$totalAddNew = 0;
		$totalUpdate = 0;
		
		exit;
		
		// note: remove extra columns fromcsv file and it will work perfectly.
		
		$cntrR = 1;
		$csvFilePath = UPLOAD_SCHOOLS_CSV_PATH."240415Events.csv";
		if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				//$num = count($data);
				//echo "<p> $num fields in line $cntrR: <br /></p>\n";
				//$this->prx($data);
				/* for ($c=0; $c < $num; $c++) {
					echo $data[$c] . "<br />\n";
				} */
				
				/* echo '<pre>';
				print_r($data);
				echo '<pre>'; */
				
				
				if($cntrR >1 && $cntrR <=358)
				{
					//echo 'dddddddddd';$this->prx($data);
			
					$division_name = trim($data[1]);
					$divSCond = array();
					$divSCond[] = "(Divisions.name LIKE '%".$division_name."%')";
					
					$divisionD = $this->Divisions->find()->where($divSCond)->first();
					//echo '<pre>';print_r($divisionD);exit;
					
					// to get division id
					
					// first set up an array with data, it will use in update and insert
					$arrEventsData = array();
					
					$arrEventsData['event_id_number']			=	trim($data[0]);
					$arrEventsData['division_id']				=	$divisionD->id;
					$arrEventsData['event_name']				=	trim($data[2]);
					$arrEventsData['upload_type']				=	trim($data[3]);
					$arrEventsData['report']					=	(trim($data[4]) == 'Yes') ? "1" : "0";
					$arrEventsData['context_box'] 				= 	(trim($data[5]) == 'Yes') ? "1" : "0";
					$arrEventsData['score_sheet'] 				= 	(trim($data[6]) == 'Yes') ? "1" : "0";
					$arrEventsData['additional_documents'] 		= 	(trim($data[7]) == 'Yes') ? "1" : "0";
					$arrEventsData['event_type'] 				= 	trim($data[8]);
					$arrEventsData['event_grp_name'] 			= 	trim($data[9]);
					$arrEventsData['event_gender'] 				= 	trim($data[10]);
					
					$arrEventsData['group_event_yes_no'] 		= 	(trim($data[11]) == 'TRUE') ? "1" : "0";
					$arrEventsData['min_no ']					= 	trim($data[12]);
					$arrEventsData['max_no'] 					= 	trim($data[13]);
					
					// new columns - 19-april-2024
					$arrEventsData['team_event'] 				= 	(trim($data[14]) == 'TRUE') ? "1" : "0";
					$arrEventsData['setup_time'] 				= 	trim($data[15]);
					$arrEventsData['round_time'] 				= 	trim($data[16]);
					$arrEventsData['judging_time'] 				= 	trim($data[17]);
					$arrEventsData['event_kind_id'] 			= 	trim($data[18]);
					$arrEventsData['has_to_be_consecutive'] 	= 	(trim($data[19]) == 'TRUE') ? "1" : "0";
					$arrEventsData['competitors_per_round'] 	= 	trim($data[20]);
					$arrEventsData['needs_schedule'] 			= 	trim($data[21]);
					
					//$this->prx($arrEventsData);
					
					
					
					// to check that if this event already exists, then update the record..otherwise insert the record
					$eventD = $this->Events->find()->where(['Events.event_id_number' => $arrEventsData['event_id_number']])->first();
					if($eventD)
					{
						$this->Events->updateAll([
							'division_id' => $arrEventsData['division_id'],
							'event_name' => $arrEventsData['event_name'],
							'upload_type' => $arrEventsData['upload_type'],
							'report' => $arrEventsData['report'],
							'context_box' => $arrEventsData['context_box'],
							'score_sheet' => $arrEventsData['score_sheet'],
							'additional_documents' => $arrEventsData['additional_documents'],
							'event_type' => $arrEventsData['event_type'],
							'event_grp_name' => $arrEventsData['event_grp_name'],
							'event_gender' => $arrEventsData['event_gender'],
							'group_event_yes_no' => $arrEventsData['group_event_yes_no'],
							'min_no' => $arrEventsData['min_no'],
							'max_no' => $arrEventsData['max_no'],
							'team_event' => $arrEventsData['team_event'],
							'setup_time' => $arrEventsData['setup_time'],
							'round_time' => $arrEventsData['round_time'],
							'judging_time' => $arrEventsData['judging_time'],
							'event_kind_id' => $arrEventsData['event_kind_id'],
							'has_to_be_consecutive' => $arrEventsData['has_to_be_consecutive'],
							'competitors_per_round' => $arrEventsData['competitors_per_round'],
							'needs_schedule' => $arrEventsData['needs_schedule'],
							'modified' => date('Y-m-d H:i:s')
						
						], ["id" => $eventD->id]);
						
						
						$totalUpdate++;
						
						//exit;
						
						/* echo 'Update';
						echo '<pre>';
						print_r($dataUpdate);
						echo '<pre>'; */
					}
					else
					{
						// add new record
						
						$events = $this->Events->newEntity();
						$dataE 	= $this->Events->patchEntity($events, array());

						$dataE->event_id_number					= $arrEventsData['event_id_number'];
						$dataE->division_id						= $arrEventsData['division_id'];
						$dataE->event_name						= $arrEventsData['event_name'];
						$dataE->upload_type						= $arrEventsData['upload_type'];
						$dataE->report 							= $arrEventsData['report'];
						$dataE->context_box 					= $arrEventsData['context_box'];
						$dataE->score_sheet 					= $arrEventsData['score_sheet'];
						$dataE->additional_documents 			= $arrEventsData['additional_documents'];
						$dataE->event_type 						= $arrEventsData['event_type'];
						$dataE->event_grp_name 					= $arrEventsData['event_grp_name'];
						$dataE->event_gender 					= $arrEventsData['event_gender'];
						
						$dataE->group_event_yes_no 				= $arrEventsData['group_event_yes_no'];
						$dataE->min_no 							= $arrEventsData['min_no'];
						$dataE->max_no 							= $arrEventsData['max_no'];
						
						$dataE->team_event 						= $arrEventsData['team_event'];
						$dataE->setup_time 						= $arrEventsData['setup_time'];
						$dataE->round_time 						= $arrEventsData['round_time'];
						$dataE->judging_time 					= $arrEventsData['judging_time'];
						$dataE->event_kind_id 					= $arrEventsData['event_kind_id'];
						$dataE->has_to_be_consecutive 			= $arrEventsData['has_to_be_consecutive'];
						$dataE->competitors_per_round 			= $arrEventsData['competitors_per_round'];
						$dataE->needs_schedule 					= $arrEventsData['needs_schedule'];
						
						$dataE->status 							= 1;
						$dataE->created 						= date('Y-m-d H:i:s');
						$dataE->modified 						= date('Y-m-d H:i:s');
						
						$dataE->slug 							= $this->getSlug(trim($arrEventsData['event_name']) . ' ' . time(), 'Events').'-'.rand(10,100000);
						
						//$this->prx($dataE);

						$resultE = $this->Events->save($dataE);
						
						$totalAddNew++;
						
						/* echo 'insert';
						echo '<pre>';
						print_r($dataE);
						echo '<pre>'; */
					}
					
					
					
					
					
				}
				
				$cntrR++;
			}
			fclose($handle);
		}
		echo 'Total New Import = '.$totalAddNew.'<br /><br />';
		echo 'Total Update = '.$totalUpdate.'<br /><br />';
		exit;
		
		
    }
	
	
	public function testpdf() {
		
		$this->helpers[] = 'Pdf';
		
		ini_set('memory_limit', '512M');
        set_time_limit(0);
		
	}
	
	
	
	
	
	
	
	public function testsmtp()
	{	Configure::write('debug', 1);
		$headers_from_email = 'sudhirgupta.159@gmail.com';
		$headers_from_name = 'Sudhir Gupta';
		
		$emailId = "voizacinc@gmail.com";
		
		$messageToSend = "This is a newsletter message from eBen.";
		$subjectToSend = "Your new todo list from new headers ".time();
		
		//echo $messageToSend;exit;
		
		$mailer = new Mailer('default');
		if (method_exists($mailer, 'setEmailFormat')) {
			$mailer->setEmailFormat('html');
		}
		if (method_exists($mailer, 'setTo')) {
			$mailer->setTo($emailId);
		}
		if (method_exists($mailer, 'setFrom')) {
			$mailer->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME]);
		}
		if (method_exists($mailer, 'setSubject')) {
			$mailer->setSubject($subjectToSend);
		}

		if (method_exists($mailer, 'deliver')) {
			$mailer->deliver($messageToSend);
		} else {
			$mailer->send($messageToSend);
		}
			
			$this->prx($mailer);
	}

}

?>
