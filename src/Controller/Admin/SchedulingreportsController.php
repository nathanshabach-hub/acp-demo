<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class SchedulingreportsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    var $components = array('RequestHandler', 'PImage', 'PImageTest');

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->params['action'];
        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }
		
		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Conventionrooms");
		$this->loadModel("Conventionseasonroomevents");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Events");
		$this->loadModel("Eventcategories");
		$this->loadModel("Schedulings");
		$this->loadModel("Schedulingtimings");
    }
	
	/* By Students */
	public function bystudents($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools/Students');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get all the schools who participated in this convention season
		$arrSchoolList = array();
		$schoolsList = $this->Conventionregistrations->find()
			->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id,
			'Conventionregistrations.status' => 1])
			->select(['user_id'])
			->all();
		foreach($schoolsList as $school)
		{
			$arrSchoolList[] = $school->user_id;
		}
		
		if(count($arrSchoolList)>0)
		{
			// now fetch schools name and their id
			$allSchoolsImploded = implode(',',$arrSchoolList);
			$condS = array();
			$condS[] = "(Users.id IN ($allSchoolsImploded) )";
			$condS[] = "(Users.user_type = 'School' )";
				
			$schoolsDD = $this->Users->find()
				->where($condS)
				->order(['Users.first_name' => 'ASC'])
				->combine('id', 'first_name')
				->toArray();
			$this->set('schoolsDD', $schoolsDD);
		}
		else
		{
			$this->Flash->error('Sorry, no school found.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
		}
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$school_id 	= $this->request->data['Schedulingreports']['school_id'];
			$student_id = $this->request->data['Schedulingreports']['student_id'];
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'bystudentsshow',$convention_season_slug,$school_id,$student_id]);
		}
    }
	
	public function bystudentsshow($convention_season_slug=null,$school_id=null,$student_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Students');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
        $this->set('student_id', $student_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// now get all students of this School
		$arrStudentsCS = array();
		$condST = array();
		$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
		$condST[] = "(Conventionregistrationstudents.user_id = '".$school_id."')";
		
		if($student_id>0)
		{
			$condST[] = "(Conventionregistrationstudents.student_id = '".$student_id."')";
		}
		
		$studentsCS = $this->Conventionregistrationstudents->find()
			->where($condST)
			->select(['student_id'])
			->all();
		
		if($studentsCS)
		{
			foreach($studentsCS as $studentEV)
			{
				$arrStudentsCS[] = $studentEV->student_id;
			}
		}
		$arrStudentsCSImploded = implode(',',$arrStudentsCS);
		//echo $arrStudentsCSImploded;exit;
		
		
		// Now arrange students in alphabetical order
		$arrStudentNames = array();
		$arrStudentSorted 	= array();
		$condStudentSch 	= array();
		$condStudentSch[] = "(Users.id IN ($arrStudentsCSImploded) )";
		$studentsLSch  = $this->Users->find()
			->where($condStudentSch)
			->order(["Users.first_name" => "ASC", "Users.last_name" => "ASC"])
			->all();
		foreach($studentsLSch as $studentSort)
		{
			$arrStudentSorted[] = $studentSort->id;
			
			// save name of students
			$arrStudentNames[$studentSort->id] = $studentSort->first_name.' '.$studentSort->last_name;
		}
		$this->set('arrStudentSorted', $arrStudentSorted);
		$this->set('arrStudentNames', $arrStudentNames);
		
	}
	
	public function bystudentsshowprint($convention_season_slug=null,$school_id=null,$student_id=null) {
		
        $this->viewBuilder()->layout('print_reports');
		
		$this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
        $this->set('student_id', $student_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// now get all students of this School
		$arrStudentsCS = array();
		$condST = array();
		$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
		$condST[] = "(Conventionregistrationstudents.user_id = '".$school_id."')";
		
		if($student_id>0)
		{
			$condST[] = "(Conventionregistrationstudents.student_id = '".$student_id."')";
		}
		
		$studentsCS = $this->Conventionregistrationstudents->find()
			->where($condST)
			->select(['student_id'])
			->all();
		
		if($studentsCS)
		{
			foreach($studentsCS as $studentEV)
			{
				$arrStudentsCS[] = $studentEV->student_id;
			}
		}
		$arrStudentsCSImploded = implode(',',$arrStudentsCS);
		
		
		// Now arrange students in alphabetical order
		$arrStudentNames = array();
		$arrStudentSorted 	= array();
		$condStudentSch 	= array();
		$condStudentSch[] = "(Users.id IN ($arrStudentsCSImploded) )";
		$studentsLSch  = $this->Users->find()
			->where($condStudentSch)
			->order(["Users.first_name" => "ASC", "Users.last_name" => "ASC"])
			->all();
		foreach($studentsLSch as $studentSort)
		{
			$arrStudentSorted[] = $studentSort->id;
			
			// save name of students
			$arrStudentNames[$studentSort->id] = $studentSort->first_name.' '.$studentSort->last_name;
		}
		$this->set('arrStudentSorted', $arrStudentSorted);
		$this->set('arrStudentNames', $arrStudentNames);
		
	}
	
	/* By Schools */
	public function byschools($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get all the schools who participated in this convention season
		$arrSchoolList = array();
		$schoolsList = $this->Conventionregistrations->find()
			->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id,
			'Conventionregistrations.status' => 1])
			->select(['user_id'])
			->all();
		foreach($schoolsList as $school)
		{
			$arrSchoolList[] = $school->user_id;
		}
		
		if(count($arrSchoolList)>0)
		{
			// now fetch schools name and their id
			$allSchoolsImploded = implode(',',$arrSchoolList);
			$condS = array();
			$condS[] = "(Users.id IN ($allSchoolsImploded) )";
			$condS[] = "(Users.user_type = 'School' )";
				
			$schoolsDD = $this->Users->find()
				->where($condS)
				->order(['Users.first_name' => 'ASC'])
				->combine('id', 'first_name')
				->toArray();
			$this->set('schoolsDD', $schoolsDD);
		}
		else
		{
			$this->Flash->error('Sorry, no school found.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
		}
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$school_id 	= $this->request->data['Schedulingreports']['school_id'];
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byschoolsshow',$convention_season_slug,$school_id]);
		}
    }
	
	public function byschoolsshow($convention_season_slug=null,$school_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// Now we need to get list of students of this school so that we will show individual games as well
		$studentsList = $this->Conventionregistrationstudents->find()
		->select(['student_id'])
		->where(
			[
			"Conventionregistrationstudents.convention_id" => $conventionSD->convention_id,
			"Conventionregistrationstudents.season_id" => $conventionSD->season_id,
			"Conventionregistrationstudents.season_year" => $conventionSD->season_year,
			"Conventionregistrationstudents.user_id" => $school_id,
			]
		)
		->extract('student_id')
		->toList();
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		//$condSch[] = "(Schedulingtimings.user_id = '".$school_id."')";
		
		if(count($studentsList))
		{
			$studentsListImplode = implode(",",$studentsList);
			$condSch[] = "(Schedulingtimings.user_id = '".$school_id."' OR Schedulingtimings.user_id IN ($studentsListImplode) OR Schedulingtimings.user_id_opponent IN ($studentsListImplode))";
		}
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
			
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byschoolsshowprint($convention_season_slug=null,$school_id=null) {
		
        $this->viewBuilder()->layout('print_reports');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// Now we need to get list of students of this school so that we will show individual games as well
		$studentsList = $this->Conventionregistrationstudents->find()
		->select(['student_id'])
		->where(
			[
			"Conventionregistrationstudents.convention_id" => $conventionSD->convention_id,
			"Conventionregistrationstudents.season_id" => $conventionSD->season_id,
			"Conventionregistrationstudents.season_year" => $conventionSD->season_year,
			"Conventionregistrationstudents.user_id" => $school_id,
			]
		)
		->extract('student_id')
		->toList();
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		//$condSch[] = "(Schedulingtimings.user_id = '".$school_id."')";
		
		if(count($studentsList))
		{
			$studentsListImplode = implode(",",$studentsList);
			$condSch[] = "(Schedulingtimings.user_id = '".$school_id."' OR Schedulingtimings.user_id IN ($studentsListImplode) OR Schedulingtimings.user_id_opponent IN ($studentsListImplode))";
		}
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	
	/* By Events */
	public function byevents($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Events/Sport');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// To get list of events selected in this convention season
		$eventsList = array();
		$conventionseasonevents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->all();
		foreach($conventionseasonevents as $convseventrec)
		{
			$eventsList[$convseventrec->event_id] = $convseventrec->Events['event_name'].' ('.$convseventrec->Events['event_id_number'].')';
		}
		asort($eventsList);
		$this->set('eventsList', $eventsList);
		
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$event_id 	= $this->request->data['Schedulingreports']['event_id'];
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byeventsshow',$convention_season_slug,$event_id]);
		}
    }
	
	public function byeventsshow($convention_season_slug=null,$event_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Events/Sport');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('event_id', $event_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.event_id = '".$event_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byeventsshowprint($convention_season_slug=null,$event_id=null) {
        
        $this->viewBuilder()->layout('print_reports');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);
		
		$this->set('event_id', $event_id);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.event_id = '".$event_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	
	/* By Rooms/Location */
	public function byrooms($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Rooms/Location');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// To get list of rooms selected in this convention season
		$allRoomsArr = array();
		$roomsList = array();
		$conventionseasonroomevents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->contain(["Conventionrooms"])->all();
		foreach($conventionseasonroomevents as $convroomrec)
		{
			if(!in_array($convroomrec->room_id,$allRoomsArr))
			{
				$roomsList[$convroomrec->room_id] = $convroomrec->Conventionrooms['room_name'];
				
				$allRoomsArr[] = $convroomrec->room_id;
			}
			
		}
		asort($roomsList);
		$this->set('roomsList', $roomsList);
		
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$room_id 	= $this->request->data['Schedulingreports']['room_id'];
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byroomsshow',$convention_season_slug,$room_id]);
		}
    }
	
	public function byroomsshow($convention_season_slug=null,$room_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Rooms/Location');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('room_id', $room_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $room_id])->first();
		$this->set('roomD', $roomD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.room_id = '".$room_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byroomsshowprint($convention_season_slug=null,$room_id=null) {
        
        $this->viewBuilder()->layout('print_reports');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('room_id', $room_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $room_id])->first();
		$this->set('roomD', $roomD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.room_id = '".$room_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}

/* By Sponsors */
public function bysponsors($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Sponsor');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);

$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);

$sponsorsRaw = $this->Conventionregistrationteachers->find()
->where(['Conventionregistrationteachers.convention_id' => $conventionSD->convention_id])
->select(['teacher_id', 'user_id'])
->all();

$sponsorSchoolMap = [];
$allSponsorIds = [];
foreach ($sponsorsRaw as $sr) {
if ($sr->teacher_id && !in_array($sr->teacher_id, $allSponsorIds)) {
$allSponsorIds[] = $sr->teacher_id;
$sponsorSchoolMap[$sr->teacher_id] = $sr->user_id;
}
}

if (count($allSponsorIds) == 0) {
$this->Flash->error('Sorry, no sponsors found for this convention.');
$this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
return;
}

$allSponsorIdsStr = implode(',', $allSponsorIds);
$sponsorUsers = $this->Users->find()
->where(["Users.id IN ($allSponsorIdsStr)"])
->order(['Users.first_name' => 'ASC', 'Users.last_name' => 'ASC'])
->all();

$sponsorsDD = [];
foreach ($sponsorUsers as $su) {
$school = $this->Users->find()->where(['Users.id' => $sponsorSchoolMap[$su->id]])->first();
$schoolName = $school ? $school->first_name : '';
$sponsorsDD[$su->id] = $su->first_name . ' ' . $su->last_name . ' (' . $schoolName . ')';
}
$this->set('sponsorsDD', $sponsorsDD);

if ($this->request->is('post')) {
$sponsor_id = $this->request->data['Schedulingreports']['sponsor_id'];
$this->redirect(['controller' => 'schedulingreports', 'action' => 'bysponsorsshow', $convention_season_slug, $sponsor_id]);
}
}

public function bysponsorsshow($convention_season_slug=null, $sponsor_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Sponsor');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);
        // Support both URL segment and GET query string (form uses GET)
        if ($sponsor_id === null) {
            $sponsor_id = $this->request->getQuery('sponsor_id');
        }
        $this->set('sponsor_id', $sponsor_id);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);

$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);

$sponsorD = $this->Users->find()->where(['Users.id' => $sponsor_id])->first();
$this->set('sponsorD', $sponsorD);

$sponsorReg = $this->Conventionregistrationteachers->find()
->where(['Conventionregistrationteachers.teacher_id' => $sponsor_id, 'Conventionregistrationteachers.convention_id' => $conventionSD->convention_id])
->first();
$schoolD = null;
if ($sponsorReg) { $schoolD = $this->Users->find()->where(['Users.id' => $sponsorReg->user_id])->first(); }
$this->set('schoolD', $schoolD);

$condST = [];
$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
$condST[] = "(Conventionregistrationstudents.teacher_parent_id = '".$sponsor_id."')";
$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";

$studentsCS = $this->Conventionregistrationstudents->find()->where($condST)->select(['student_id'])->all();
$arrStudentsCS = [];
foreach ($studentsCS as $s) { $arrStudentsCS[] = $s->student_id; }

if (count($arrStudentsCS) == 0) {
$arrStudentSorted = []; $arrStudentNames = [];
} else {
$arrStudentsCSStr = implode(',', $arrStudentsCS);
$studentsL = $this->Users->find()->where(["Users.id IN ($arrStudentsCSStr)"])->order(["Users.first_name"=>"ASC","Users.last_name"=>"ASC"])->all();
$arrStudentSorted = []; $arrStudentNames = [];
foreach ($studentsL as $st) { $arrStudentSorted[] = $st->id; $arrStudentNames[$st->id] = $st->first_name.' '.$st->last_name; }
}
$this->set('arrStudentSorted', $arrStudentSorted);
$this->set('arrStudentNames', $arrStudentNames);
}

public function bysponsorsshowprint($convention_season_slug=null, $sponsor_id=null) {
        $this->viewBuilder()->layout('print_reports');
        $this->set('convention_season_slug', $convention_season_slug);
        if ($sponsor_id === null) {
            $sponsor_id = $this->request->getQuery('sponsor_id');
        }
        $this->set('sponsor_id', $sponsor_id);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);
$sponsorD = $this->Users->find()->where(['Users.id' => $sponsor_id])->first();
$this->set('sponsorD', $sponsorD);
$sponsorReg = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.teacher_id' => $sponsor_id, 'Conventionregistrationteachers.convention_id' => $conventionSD->convention_id])->first();
$schoolD = null;
if ($sponsorReg) { $schoolD = $this->Users->find()->where(['Users.id' => $sponsorReg->user_id])->first(); }
$this->set('schoolD', $schoolD);
$condST = [];
$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
$condST[] = "(Conventionregistrationstudents.teacher_parent_id = '".$sponsor_id."')";
$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
$studentsCS = $this->Conventionregistrationstudents->find()->where($condST)->select(['student_id'])->all();
$arrStudentsCS = [];
foreach ($studentsCS as $s) { $arrStudentsCS[] = $s->student_id; }
if (count($arrStudentsCS) == 0) { $arrStudentSorted = []; $arrStudentNames = []; }
else {
$arrStudentsCSStr = implode(',', $arrStudentsCS);
$studentsL = $this->Users->find()->where(["Users.id IN ($arrStudentsCSStr)"])->order(["Users.first_name"=>"ASC","Users.last_name"=>"ASC"])->all();
$arrStudentSorted = []; $arrStudentNames = [];
foreach ($studentsL as $st) { $arrStudentSorted[] = $st->id; $arrStudentNames[$st->id] = $st->first_name.' '.$st->last_name; }
}
$this->set('arrStudentSorted', $arrStudentSorted);
$this->set('arrStudentNames', $arrStudentNames);
}


/* Location Time Allocation */
public function locationtimeallocation($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Location Time Allocation');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);

$lunchMinutes = (strtotime($schedulingD->lunch_time_end) - strtotime($schedulingD->lunch_time_start)) / 60;
$dailyMinutes = (strtotime($schedulingD->normal_finish_time) - strtotime($schedulingD->normal_starting_time)) / 60 - $lunchMinutes;
if ($schedulingD->judging_breaks_yes_no) {
$dailyMinutes -= (strtotime($schedulingD->judging_breaks_morning_break_finish_time) - strtotime($schedulingD->judging_breaks_morning_break_starting_time)) / 60;
$dailyMinutes -= (strtotime($schedulingD->judging_breaks_afternoon_break_finish_time) - strtotime($schedulingD->judging_breaks_afternoon_break_start_time)) / 60;
}

$roomData = [];
$allRoomIds = [];
$conventionseasonroomevents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->contain(["Conventionrooms"])->all();
foreach ($conventionseasonroomevents as $rr) {
if (in_array($rr->room_id, $allRoomIds)) continue;
$allRoomIds[] = $rr->room_id;
$roomInfo = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $rr->room_id])->first();
$allowedDaysCount = $schedulingD->number_of_days;
if (!empty($roomInfo->restricted_days)) { $allowedDaysCount = count(explode(',', $roomInfo->restricted_days)); }
$availableMinutes = $dailyMinutes * $allowedDaysCount;
$condSched = ["(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.room_id = '".$rr->room_id."')"];
$scheduledInRoom = $this->Schedulingtimings->find()->where($condSched)->contain(['Events'])->all();
$requiredMinutes = 0;
$eventBreakdown = [];
foreach ($scheduledInRoom as $sc) {
    $mins = (strtotime($sc->finish_time)-strtotime($sc->start_time))/60;
    $requiredMinutes += $mins;
    $evName = $sc->Events['event_name'];
    if (!isset($eventBreakdown[$evName])) { $eventBreakdown[$evName] = ['event_name'=>$evName,'minutes'=>0,'count'=>0]; }
    $eventBreakdown[$evName]['minutes'] += $mins;
    $eventBreakdown[$evName]['count']++;
}
uasort($eventBreakdown, function($a,$b){ return $b['minutes'] - $a['minutes']; });
$roomData[] = ['room_id'=>$rr->room_id,'room_name'=>$rr->Conventionrooms['room_name'],'available_minutes'=>$availableMinutes,'required_minutes'=>$requiredMinutes,'event_count'=>count($scheduledInRoom),'status'=>($requiredMinutes<=$availableMinutes)?'ok':'over','events'=>array_values($eventBreakdown)];
}
usort($roomData, function($a,$b){if($a['status']!==$b['status'])return $a['status']=='over'?-1:1;return strcmp($a['room_name'],$b['room_name']);});
$this->set('roomData', $roomData);
$this->set('dailyMinutes', $dailyMinutes);
}


/* Small Program v2 - PDF-style multi-column layout (rooms as columns, split by session) */
public function smallprogramv2($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Small Program (PDF Style)');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);

$lunchStart = $schedulingD && $schedulingD->lunch_time_start ? $schedulingD->lunch_time_start : '12:30:00';
$lunchEnd   = $schedulingD && $schedulingD->lunch_time_end   ? $schedulingD->lunch_time_end   : '13:30:00';

$condSch = ["(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."' AND Schedulingtimings.is_bye = '0')"];
$allTimings = $this->Schedulingtimings->find()->where($condSch)->contain(["Events","Conventionrooms"])->order(["Schedulingtimings.sch_date_time"=>"ASC","Schedulingtimings.room_id"=>"ASC"])->all();

$allConventionEvents = $this->Conventionseasonevents->find()
	->where([
		'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
		'Conventionseasonevents.convention_id' => $conventionSD->convention_id,
	])
	->contain(['Events'])
	->all();

$categoryMap = [];
$allCategories = $this->Eventcategories->find()->select(['id','name'])->all();
foreach ($allCategories as $cat) {
	$categoryMap[(int)$cat->id] = $cat->name;
}

$scheduledEventIds = [];
foreach ($allTimings as $t) {
	if (!empty($t->event_id)) {
		$scheduledEventIds[(int)$t->event_id] = true;
	}
}

$unscheduledEvents = [];
$seenUnscheduled = [];
foreach ($allConventionEvents as $cse) {
	if (empty($cse->Events) || empty($cse->event_id)) {
		continue;
	}
	$eid = (int)$cse->event_id;
	if (isset($scheduledEventIds[$eid]) || isset($seenUnscheduled[$eid])) {
		continue;
	}
	$seenUnscheduled[$eid] = true;
	$unscheduledEvents[] = [
		'event_name' => $cse->Events['event_name'],
		'event_id_number' => $cse->Events['event_id_number'],
		'category_name' => !empty($cse->Events['event_grp_name']) && isset($categoryMap[(int)$cse->Events['event_grp_name']]) ? $categoryMap[(int)$cse->Events['event_grp_name']] : '',
	];
}

usort($unscheduledEvents, function($a, $b) {
	$catCmp = strcmp((string)$a['category_name'], (string)$b['category_name']);
	if ($catCmp !== 0) return $catCmp;
	return strcmp((string)$a['event_name'], (string)$b['event_name']);
});

// Build: $dayData[day]['date'] = '30 June 2025'
//        $dayData[day]['morning|afternoon'][$roomName][] = ['event'=>..., 'start'=>..., 'finish'=>...]
//        $dayData[day]['morningRange'] = '09:30 AM – 12:30 PM'
//        $dayData[day]['afternoonRange'] = '01:30 PM – 05:00 PM'
//        $dayData[day]['rooms'] = ordered list of unique room names
$dayData = []; $seenSlots = [];
$dayOrder = [];

foreach ($allTimings as $t) {
    $day      = $t->day;
    $roomName = $t->Conventionrooms['room_name'];
    $eventName = $t->Events['event_name'];
    $startH   = $t->start_time ? strtotime($t->start_time) : null;
    $finishH  = $t->finish_time ? strtotime($t->finish_time) : null;

    if (!isset($dayData[$day])) {
        $dayData[$day] = ['date'=>'','morning'=>[],'afternoon'=>[],'rooms'=>[],'morningRange'=>'','afternoonRange'=>'','morningStart'=>null,'morningEnd'=>null,'afternoonStart'=>null,'afternoonEnd'=>null];
        $dayOrder[] = $day;
    }
    if (empty($dayData[$day]['date']) && $t->sch_date_time) {
        $dayData[$day]['date'] = date('j F Y', strtotime($t->sch_date_time));
    }

    // Determine session: morning = before lunch, afternoon = after lunch
    $isAfternoon = ($startH && $startH >= strtotime($lunchEnd));
    $session = $isAfternoon ? 'afternoon' : 'morning';

    // Track session time ranges for ALL rows (accurate range display)
    if ($session === 'morning') {
        if ($dayData[$day]['morningStart'] === null || $startH < $dayData[$day]['morningStart']) { $dayData[$day]['morningStart'] = $startH; }
        if ($dayData[$day]['morningEnd'] === null || $finishH > $dayData[$day]['morningEnd']) { $dayData[$day]['morningEnd'] = $finishH; }
    } else {
        if ($dayData[$day]['afternoonStart'] === null || $startH < $dayData[$day]['afternoonStart']) { $dayData[$day]['afternoonStart'] = $startH; }
        if ($dayData[$day]['afternoonEnd'] === null || $finishH > $dayData[$day]['afternoonEnd']) { $dayData[$day]['afternoonEnd'] = $finishH; }
    }

    // Only list each event name once per room per session (no individual matches)
    $slotKey = $day.'|'.$session.'|'.$roomName.'|'.$eventName;
    if (in_array($slotKey, $seenSlots)) continue;
    $seenSlots[] = $slotKey;

    if (!isset($dayData[$day][$session][$roomName])) {
        $dayData[$day][$session][$roomName] = [];
        if (!in_array($roomName, $dayData[$day]['rooms'])) {
            $dayData[$day]['rooms'][] = $roomName;
        }
    }
    $dayData[$day][$session][$roomName][] = $eventName;
}

// Format time ranges
foreach ($dayData as $day => &$dd) {
    if ($dd['morningStart'])   $dd['morningRange']   = date('g:i a', $dd['morningStart']).' – '.date('g:i a', strtotime($lunchStart));
    if ($dd['afternoonStart']) $dd['afternoonRange']  = date('g:i a', strtotime($lunchEnd)).' – '.date('g:i a', $dd['afternoonEnd']);
}
unset($dd);

$this->set('dayData', $dayData);
$this->set('dayOrder', $dayOrder);
$this->set('lunchStart', date('g:i a', strtotime($lunchStart)));
$this->set('lunchEnd',   date('g:i a', strtotime($lunchEnd)));
$this->set('unscheduledEvents', $unscheduledEvents);
}

public function smallprogramv2print($convention_season_slug=null) {
        $this->viewBuilder()->layout('print_reports');
        $this->set('convention_season_slug', $convention_season_slug);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();

$lunchStart = $schedulingD && $schedulingD->lunch_time_start ? $schedulingD->lunch_time_start : '12:30:00';
$lunchEnd   = $schedulingD && $schedulingD->lunch_time_end   ? $schedulingD->lunch_time_end   : '13:30:00';

$condSch = ["(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."' AND Schedulingtimings.is_bye = '0')"];
$allTimings = $this->Schedulingtimings->find()->where($condSch)->contain(["Events","Conventionrooms"])->order(["Schedulingtimings.sch_date_time"=>"ASC","Schedulingtimings.room_id"=>"ASC"])->all();

$allConventionEvents = $this->Conventionseasonevents->find()
	->where([
		'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
		'Conventionseasonevents.convention_id' => $conventionSD->convention_id,
	])
	->contain(['Events'])
	->all();

$categoryMap = [];
$allCategories = $this->Eventcategories->find()->select(['id','name'])->all();
foreach ($allCategories as $cat) {
	$categoryMap[(int)$cat->id] = $cat->name;
}

$scheduledEventIds = [];
foreach ($allTimings as $t) {
	if (!empty($t->event_id)) {
		$scheduledEventIds[(int)$t->event_id] = true;
	}
}

$unscheduledEvents = [];
$seenUnscheduled = [];
foreach ($allConventionEvents as $cse) {
	if (empty($cse->Events) || empty($cse->event_id)) {
		continue;
	}
	$eid = (int)$cse->event_id;
	if (isset($scheduledEventIds[$eid]) || isset($seenUnscheduled[$eid])) {
		continue;
	}
	$seenUnscheduled[$eid] = true;
	$unscheduledEvents[] = [
		'event_name' => $cse->Events['event_name'],
		'event_id_number' => $cse->Events['event_id_number'],
		'category_name' => !empty($cse->Events['event_grp_name']) && isset($categoryMap[(int)$cse->Events['event_grp_name']]) ? $categoryMap[(int)$cse->Events['event_grp_name']] : '',
	];
}

usort($unscheduledEvents, function($a, $b) {
	$catCmp = strcmp((string)$a['category_name'], (string)$b['category_name']);
	if ($catCmp !== 0) return $catCmp;
	return strcmp((string)$a['event_name'], (string)$b['event_name']);
});

$dayData = []; $seenSlots = []; $dayOrder = [];
foreach ($allTimings as $t) {
    $day      = $t->day;
    $roomName = $t->Conventionrooms['room_name'];
    $eventName = $t->Events['event_name'];
    $startH   = $t->start_time ? strtotime($t->start_time) : null;
    $finishH  = $t->finish_time ? strtotime($t->finish_time) : null;

    if (!isset($dayData[$day])) {
        $dayData[$day] = ['date'=>'','morning'=>[],'afternoon'=>[],'rooms'=>[],'morningRange'=>'','afternoonRange'=>'','morningStart'=>null,'morningEnd'=>null,'afternoonStart'=>null,'afternoonEnd'=>null];
        $dayOrder[] = $day;
    }
    if (empty($dayData[$day]['date']) && $t->sch_date_time) {
        $dayData[$day]['date'] = date('j F Y', strtotime($t->sch_date_time));
    }

    $isAfternoon = ($startH && $startH >= strtotime($lunchEnd));
    $session = $isAfternoon ? 'afternoon' : 'morning';

    if ($session === 'morning') {
        if ($dayData[$day]['morningStart'] === null || $startH < $dayData[$day]['morningStart']) { $dayData[$day]['morningStart'] = $startH; }
        if ($dayData[$day]['morningEnd'] === null || $finishH > $dayData[$day]['morningEnd']) { $dayData[$day]['morningEnd'] = $finishH; }
    } else {
        if ($dayData[$day]['afternoonStart'] === null || $startH < $dayData[$day]['afternoonStart']) { $dayData[$day]['afternoonStart'] = $startH; }
        if ($dayData[$day]['afternoonEnd'] === null || $finishH > $dayData[$day]['afternoonEnd']) { $dayData[$day]['afternoonEnd'] = $finishH; }
    }

    $slotKey = $day.'|'.$session.'|'.$roomName.'|'.$eventName;
    if (in_array($slotKey, $seenSlots)) continue;
    $seenSlots[] = $slotKey;

    if (!isset($dayData[$day][$session][$roomName])) {
        $dayData[$day][$session][$roomName] = [];
        if (!in_array($roomName, $dayData[$day]['rooms'])) { $dayData[$day]['rooms'][] = $roomName; }
    }
    $dayData[$day][$session][$roomName][] = $eventName;
}
foreach ($dayData as $day => &$dd) {
    if ($dd['morningStart'])   $dd['morningRange']   = date('g:i a', $dd['morningStart']).' – '.date('g:i a', strtotime($lunchStart));
    if ($dd['afternoonStart']) $dd['afternoonRange']  = date('g:i a', strtotime($lunchEnd)).' – '.date('g:i a', $dd['afternoonEnd']);
}
unset($dd);

$this->set('dayData', $dayData);
$this->set('dayOrder', $dayOrder);
$this->set('lunchStart', date('g:i a', strtotime($lunchStart)));
$this->set('lunchEnd',   date('g:i a', strtotime($lunchEnd)));
$this->set('unscheduledEvents', $unscheduledEvents);
}


/* Small Program */
public function smallprogram($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Small Program');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);

$condSch = ["(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')"];
$allTimings = $this->Schedulingtimings->find()->where($condSch)->contain(["Events","Conventionrooms"])->order(["Schedulingtimings.sch_date_time"=>"ASC","Schedulingtimings.room_id"=>"ASC"])->all();

$programByDay = []; $seenSlots = []; $dayDates = [];
foreach ($allTimings as $t) {
$day=$t->day; $roomName=$t->Conventionrooms['room_name']; $eventName=$t->Events['event_name'];
$startTime=$t->start_time?date("h:i A",strtotime($t->start_time)):'';
$finishTime=$t->finish_time?date("h:i A",strtotime($t->finish_time)):'';
$slotKey=$day.'|'.$roomName.'|'.$eventName.'|'.$startTime;
if(in_array($slotKey,$seenSlots))continue;
$seenSlots[]=$slotKey;
if(!isset($dayDates[$day]) && $t->sch_date_time) { $dayDates[$day] = date('j F Y', strtotime($t->sch_date_time)); }
$programByDay[$day][]=['start_time'=>$startTime,'finish_time'=>$finishTime,'sch_ts'=>strtotime($t->sch_date_time),'event_name'=>$eventName,'event_id_number'=>$t->Events['event_id_number'],'room_name'=>$roomName];
}
foreach($programByDay as $day=>&$entries){usort($entries,function($a,$b){return $a['sch_ts']-$b['sch_ts'];});}
unset($entries);
$this->set('programByDay', $programByDay);
$this->set('dayDates', $dayDates);
}

public function smallprogramprint($convention_season_slug=null) {
        $this->viewBuilder()->layout('print_reports');
        $this->set('convention_season_slug', $convention_season_slug);
$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$this->set('conventionSD', $conventionSD);
$this->set('convention_slug', $conventionSD->Conventions['slug']);
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$this->set('schedulingD', $schedulingD);
$condSch = ["(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')"];
$allTimings = $this->Schedulingtimings->find()->where($condSch)->contain(["Events","Conventionrooms"])->order(["Schedulingtimings.sch_date_time"=>"ASC","Schedulingtimings.room_id"=>"ASC"])->all();
$programByDay=[]; $seenSlots=[]; $dayDates=[];
foreach($allTimings as $t){
$day=$t->day;$roomName=$t->Conventionrooms['room_name'];$eventName=$t->Events['event_name'];
$startTime=$t->start_time?date("h:i A",strtotime($t->start_time)):'';$finishTime=$t->finish_time?date("h:i A",strtotime($t->finish_time)):'';
$slotKey=$day.'|'.$roomName.'|'.$eventName.'|'.$startTime;
if(in_array($slotKey,$seenSlots))continue;$seenSlots[]=$slotKey;
if(!isset($dayDates[$day]) && $t->sch_date_time) { $dayDates[$day] = date('j F Y', strtotime($t->sch_date_time)); }
$programByDay[$day][]=['start_time'=>$startTime,'finish_time'=>$finishTime,'sch_ts'=>strtotime($t->sch_date_time),'event_name'=>$eventName,'event_id_number'=>$t->Events['event_id_number'],'room_name'=>$roomName];
}
foreach($programByDay as $day=>&$entries){usort($entries,function($a,$b){return $a['sch_ts']-$b['sch_ts'];});}unset($entries);
$this->set('programByDay', $programByDay);
$this->set('dayDates', $dayDates);
}


/* CSV Export */
public function exportcsv($convention_season_slug=null, $report_type=null) {
$this->autoRender = false;
$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
$filename = 'schedule_'.$report_type.'_'.date('Ymd').'.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');
$out = fopen('php://output', 'w');
if ($report_type == 'byrooms') {
fputcsv($out, ['Room','Day','Start','Finish','Event','Match/Student']);
$rooms=$this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id'=>$conventionSD->id])->contain(["Conventionrooms"])->all();
$allRoomIds=[];
foreach($rooms as $r){
if(in_array($r->room_id,$allRoomIds))continue;$allRoomIds[]=$r->room_id;
$condSch=["(Schedulingtimings.conventionseasons_id='".$conventionSD->id."' AND Schedulingtimings.room_id='".$r->room_id."')"];
$timings=$this->Schedulingtimings->find()->where($condSch)->contain(["Events","Users","Opponentuser"])->order(["Schedulingtimings.sch_date_time"=>"ASC"])->all();
foreach($timings as $t){$match=$t->Users['first_name'].' '.$t->Users['last_name'];if($t->user_id_opponent>0)$match.=' VS '.$t->Opponentuser['first_name'].' '.$t->Opponentuser['last_name'];fputcsv($out,[$r->Conventionrooms['room_name'],$t->day,$t->start_time?date("h:i A",strtotime($t->start_time)):'', $t->finish_time?date("h:i A",strtotime($t->finish_time)):'',$t->Events['event_name'],$match]);}
}
} elseif ($report_type == 'smallprogram') {
fputcsv($out,['Day','Start','Finish','Event','Room']);
$condSch=["(Schedulingtimings.conventionseasons_id='".$conventionSD->id."')"];
$timings=$this->Schedulingtimings->find()->where($condSch)->contain(["Events","Conventionrooms"])->order(["Schedulingtimings.sch_date_time"=>"ASC"])->all();
$seenSlots=[];
foreach($timings as $t){$slotKey=$t->day.'|'.$t->Conventionrooms['room_name'].'|'.$t->Events['event_name'].'|'.$t->start_time;if(in_array($slotKey,$seenSlots))continue;$seenSlots[]=$slotKey;fputcsv($out,[$t->day,$t->start_time?date("h:i A",strtotime($t->start_time)):'',$t->finish_time?date("h:i A",strtotime($t->finish_time)):'',$t->Events['event_name'],$t->Conventionrooms['room_name']]);}
} elseif ($report_type == 'locationtimeallocation') {
fputcsv($out,['Room','Available (mins)','Required (mins)','Events Scheduled','Status']);
$lunchMinutes=(strtotime($schedulingD->lunch_time_end)-strtotime($schedulingD->lunch_time_start))/60;
$dailyMinutes=(strtotime($schedulingD->normal_finish_time)-strtotime($schedulingD->normal_starting_time))/60-$lunchMinutes;
$rooms=$this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id'=>$conventionSD->id])->contain(["Conventionrooms"])->all();
$allRoomIds=[];
foreach($rooms as $r){
if(in_array($r->room_id,$allRoomIds))continue;$allRoomIds[]=$r->room_id;
$roomInfo=$this->Conventionrooms->find()->where(['Conventionrooms.id'=>$r->room_id])->first();
$daysCount=$schedulingD->number_of_days;if(!empty($roomInfo->restricted_days)){$daysCount=count(explode(',',$roomInfo->restricted_days));}
$availMins=$dailyMinutes*$daysCount;
$condSched=["(Schedulingtimings.conventionseasons_id='".$conventionSD->id."' AND Schedulingtimings.room_id='".$r->room_id."')"];
$scheduled=$this->Schedulingtimings->find()->where($condSched)->all();
$reqMins=0;foreach($scheduled as $sc){$reqMins+=(strtotime($sc->finish_time)-strtotime($sc->start_time))/60;}
fputcsv($out,[$r->Conventionrooms['room_name'],$availMins,$reqMins,count($scheduled),$reqMins<=$availMins?'OK':'OVER CAPACITY']);
}
}
fclose($out);
exit;
}



}

?>
