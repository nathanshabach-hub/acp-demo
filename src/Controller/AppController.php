<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\Mailer\Email;
use Cake\Controller\Component\FlashComponent;
use Cake\Datasource\ConnectionManager;


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller{
    
    public function initialize(){
    parent::initialize();
		$this->loadModel('Timezones');
		$this->loadModel('Eventtypes');
		$this->loadModel('Companies');
		$this->loadModel('Users');
		$this->loadModel('Admins');
		$this->loadModel('Settings');
		
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Schedulings");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Eventsubmissions");
		$this->loadModel("Schedulingtimings");
    }
	
	public function beforeRender(Event $event) {
        parent::beforeRender($event);
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        $this->set('adminInfo', $adminInfo);
		//$this->prx($adminInfo);
		
        
		
		// to check if school admin is logged in, then show header dropdown
		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		
		if($user_id>0)
		{
			// to get lists of registered conventions for this season
			$season_id = $this->getCurrentSeason();
			$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
			//$this->prx($seasonD);
			$userD = $this->Users->find()->where(['Users.id' => $user_id])->first();
			
			$conventionIDSHeader = array();
			$conventionIDSHeader[] 	= 0;
			
			$userConvHeaderDD = array();
			
			// now get convention id for school admin
			if($user_type == "School")
			{
				$conventionregistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id,'Conventionregistrations.season_year' => $seasonD->season_year])->order(['Conventionregistrations.id' => 'ASC'])->all();
				foreach($conventionregistrations as $convreg)
				{
					if(!in_array($convreg->convention_id,(array)$conventionIDSHeader))
					{
						$conventionIDSHeader[] 	= $convreg->convention_id;
					}
				}
			}
			
			// now get convention id for teacher
			if($user_type == "Teacher_Parent")
			{
				$conventionregistrationteachers = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.user_id' => $userD->school_id,'Conventionregistrationteachers.teacher_id' => $user_id,'Conventionregistrationteachers.season_id' => $season_id,'Conventionregistrationteachers.season_year' => $seasonD->season_year])->order(['Conventionregistrationteachers.id' => 'ASC'])->all();
				foreach($conventionregistrationteachers as $convregt)
				{
					if(!in_array($convregt->convention_id,(array)$conventionIDSHeader))
					{
						$conventionIDSHeader[] 	= $convregt->convention_id;
					}
				}
			}
			
			// now get convention id for judge + supervisor as a judge
			if($user_type == "Judge" || $this->request->session()->read("current_session_profile_type")  == "Judge")
			{
				$conventionregistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id,'Conventionregistrations.season_year' => $seasonD->season_year,'Conventionregistrations.status' => 1])->order(['Conventionregistrations.id' => 'ASC'])->all();
				foreach($conventionregistrations as $convreg)
				{
					if(!in_array($convreg->convention_id,(array)$conventionIDSHeader))
					{
						$conventionIDSHeader[] 	= $convreg->convention_id;
					}
				}
			}
			
			
			//$this->prx($conventionIDSHeader);
			
			$conventionIDSHeaderImploded = implode(",",$conventionIDSHeader);
			
			// to get conventions
			$condConventionH = array();
			$condConventionH[] = "(Conventions.id IN ($conventionIDSHeaderImploded))";
			//$condConventionH[] = "(Conventions.status  = '1')";
			$userConvHeaderDD = $this->Conventions->find()->where($condConventionH)->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
			$this->set('userConvHeaderDD', $userConvHeaderDD);
			
		}

	}
    
//  public function beforeFilter(Event $event) {
//        $this->set('loggedIn', $this->Auth->loggedIn());
//    }
    
    public function getAdminInfo() {
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        return $adminInfo;
	}
	
	public function fetchUserType($user_id=NULL) {
		
		$userInfo = $this->Users->find()->select(['user_type'])->where(['Users.id' => $user_id])->first();
        return $userInfo->user_type;
	}
	
	public function autoSubmitEvent($arrAutoSubmit=NULL) {
		
		if($arrAutoSubmit['event_id']>0)
		{
			$event_id 						= $arrAutoSubmit['event_id'];
			$conventionregistration_id 		= $arrAutoSubmit['conventionregistration_id'];
			$student_id 					= $arrAutoSubmit['student_id'];
			
			$eventD 		= $this->Events->find()->where(['Events.id' => $event_id])->first();
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $conventionregistration_id])->first();
			
			// to check if event submission done for this event, student, convention reg
			$checkSubmission = $this->Eventsubmissions->find()->where(['Eventsubmissions.event_id' => $event_id,'Eventsubmissions.conventionregistration_id' => $conventionregistration_id, 'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,'Eventsubmissions.student_id' => $student_id])->first();
			if(!$checkSubmission)
			{
				// submit event
				$eventsubmissions = $this->Eventsubmissions->newEntity();
				$dataES = $this->Eventsubmissions->patchEntity($eventsubmissions, array());

				$dataES->slug 						= 'event-submission-'.$conventionregistration_id.'-'.time().'-'.rand(100,1000000);
				$dataES->conventionregistration_id	= $conventionregistration_id;
				$dataES->conventionseason_id		= $conventionRegD->conventionseason_id;
				$dataES->convention_id				= $conventionRegD->convention_id;
				$dataES->user_id					= $conventionRegD->user_id;
				$dataES->season_id 					= $conventionRegD->season_id;
				$dataES->season_year 				= $conventionRegD->season_year;
				$dataES->event_id 					= $eventD->id;
				$dataES->event_id_number 			= $eventD->event_id_number;
				$dataES->student_id 				= $student_id;
				
				if($eventD->group_event_yes_no == 1)
				{
					$dataES->student_id 			= 0;
				}
				else
				{
					$dataES->group_name 			= NULL;
				}
				
				
				$dataES->uploaded_by_user_id 			= $conventionRegD->user_id;
				
				//$data->book_ids 					= '';
				$dataES->created = date('Y-m-d H:i:s');
				$dataES->modified = date('Y-m-d H:i:s');

				$resultES = $this->Eventsubmissions->save($dataES);
			}
			
			return true;
		}
		
		return false;
	}
	
	public function getNextWeekDay($schDay=null) {//echo 'here'; exit;
		
		$weekArr = array(
			0 => "Monday",
			1 => "Tuesday",
			2 => "Wednesday",
			3 => "Thursday",
			4 => "Friday",
			5 => "Saturday",
			6 => "Sunday",
		);
		
		if($schDay == "Sunday")
		{
			$schNextDay = 'Monday';
		}
		else
		{
			$keyWeek 		= array_search ($schDay, $weekArr);
			$schNextDay 	= $weekArr[$keyWeek+1];
		}
		
		//echo $schNextDay;exit;
		
		return $schNextDay;
	}
	
	public function sortAssocArr($assocArr=array()) {
		
		$values = array_values($assocArr);
		$keys 	= array_keys($assocArr);

		array_multisort($values, SORT_ASC, $keys);

		$assocArr = array_combine($keys, $values);
		
		return $assocArr;
		
	}
	public function getAgeFromBirthYear($birth_year=NULL) {
		
		if($birth_year)
		{
			return date("Y") - $birth_year;
		}
		else
		{
			return 0;
		}
	}
	
	public function checkAgeWithGroup($studentAge=NULL,$event_grp_name=NULL) {
		
		$returnVal = 0;
		
		//echo $studentAge;echo '--'.$event_grp_name;exit;
		
		if($studentAge>0 && $event_grp_name>0)
		{;
			//compare age based on event group
			if($event_grp_name == 1 && $studentAge<14)
			{
				// 1. U14
				$returnVal = 1;
			}
			else 
			if($event_grp_name == 2 && $studentAge<16)
			{
				// 2. U16
				$returnVal = 1;
			}
			else 
			if($event_grp_name == 3 && $studentAge<17)
			{
				// 3. U17
				$returnVal = 1;;
			}
			else
			if($event_grp_name == 4)
			{
				// 4. Open
				$returnVal = 1;
			}
		}
		
		return $returnVal;
	}
	
	public function checkGenderWithEvent($studentGender=NULL,$event_gender=NULL) {
		
		$returnVal = 0;
		
		if($studentGender == 'F' || $studentGender == 'M')
		{
			//compare age based on event gender
			if(empty($event_gender) || $event_gender == '')
			{
				// 1. No restriction
				$returnVal = 1;
			}
			else 
			if($event_gender == 'F' && $studentGender == 'F')
			{
				// 2. Gender match
				$returnVal = 1;
			}
			else 
			if($event_gender == 'M' && $studentGender == 'M')
			{
				// 3. Gender match
				$returnVal = 1;;
			}
		}
		
		return $returnVal;
	}
	
	public function changeToMysqlTimeFormat($time_data=NULL) {
		
		if($time_data)
		{
			$timestamp_data = strtotime($time_data);
			$mysqlFormat = date('Y-m-d H:i:s', $timestamp_data);
			$mysqlFormatExplode = explode(" ",$mysqlFormat);
			return $mysqlFormatExplode[1];
		}
		else
		{
			return 0;
		}
	}
	
	public function getSettingsInfo() {
		
		$settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        return $settingsInfo;
	}
	
	public function getMinMaxEvents($conv_reg_id = 0) {
		
		$min_events_student = 0;
		$max_events_student = 0;
		
		// first to get from convention season
		if($conv_reg_id)
		{
			// to get conv reg details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $conv_reg_id])->contain(['Conventionseasons'])->first();
			//$this->prx($conventionRegD);
			
			if($conventionRegD->Conventionseasons['min_events_student']>0 && $conventionRegD->Conventionseasons['max_events_student']>0)
			{
				$min_events_student = $conventionRegD->Conventionseasons['min_events_student'];
				$max_events_student = $conventionRegD->Conventionseasons['max_events_student'];
			}
			else
			{
				// get from Settings
				$getSettingsInfo = $this->getSettingsInfo();
				$min_events_student = $getSettingsInfo->min_events_student;
				$max_events_student = $getSettingsInfo->max_events_student;
			}
		}
		
		return array('min_events_student' => $min_events_student,'max_events_student' => $max_events_student);
		
	}
	
	public function getCurrentSeason(){
        /* $currYear = date("Y");
		$seasonD = $this->Seasons->find()->where(['Seasons.season_year' => $currYear])->first();
		return $seasonD->id; */
		
		// need to get last active season
		$seasonD = $this->Seasons->find()->where(['Seasons.status' => 1])->order(['Seasons.season_year' => 'DESC'])->first();
		return $seasonD->id;
    }
	
	/////////////////////////////////
	public function nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time,$recordId)
	{
		// First we need to collect all students list of all schools
		$conventionSD 	= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$schedulingD 	= $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		
		// To get schedulingtiming record details via auto id
		$schedulingTimingsD 	= $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $recordId])->first();
		
		
		$schDateTime	= date('Y-m-d', strtotime($base_sch_date_time));
		$starTime		= $base_start_time;
		$finishTime		= $base_finish_time;

		$playTime		= (strtotime($conflict['finish_time']) - strtotime($conflict['start_time'])) / 60;

		$userId			= $conflict['user_id'];

		$nextStartTime	= date("H:i:s", strtotime($finishTime . " +1 minute"));
		$nextFinishTime	= date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));

		$schDateTime				= $schDateTime . ' ' . $nextStartTime;
		$conflict['sch_date_time']	= $schDateTime;
		$conflict['start_time']		= $nextStartTime;
		$conflict['finish_time']	= $nextFinishTime;

		$base_start_time			= $nextStartTime;
		$base_finish_time			= $nextFinishTime;
		$base_sch_date_time			= $schDateTime;

		/* $sql = "SELECT *
			FROM schedulingtimings
			WHERE user_type = 'Student'
				AND is_bye = 0
				AND user_id = $userId
				AND DATE(sch_date_time) = '$schDateTime'
				AND start_time > '$nextStartTime'
			ORDER BY sch_date_time
			LIMIT 1
			";

		$stmt = $pdo->query($sql); 
		$nextBooking = $stmt->fetch(PDO::FETCH_ASSOC);
		*/
		
		///////////////
		// To get list of all conflict
		$cond = [
			'conventionseasons_id' => $conventionSD->id,
			'user_type'				=> 'Student',
			'is_bye'				=> 0,
			'user_id'				=> $userId,
			'DATE(sch_date_time) =' => $schDateTime,
			'start_time >'			=> $nextStartTime
		];
		// Fetch each schedule and check if there is any group name assigned
		$nextBooking = $this->Schedulingtimings
			->find()
			->where($cond)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->first();
		///////////////

		
		if (!empty($nextBooking)) {
			if (strtotime($nextFinishTime) >= strtotime($nextBooking->start_time)) { // available for user
				$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
			}
		}

		$opponentBooking = $this->checkForOpponent($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
		if (!empty($opponentBooking)) {
			if (strtotime($nextFinishTime) >= strtotime($opponentBooking->start_time)) { // available for opponent
				$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
			}
		}
		
		/* New check for lunch timings and judging breaks - Starts */
		$lunch_time_start 	= date("H:i:s", strtotime($schedulingD->lunch_time_start));
		$lunch_time_end 	= date("H:i:s", strtotime($schedulingD->lunch_time_end));
		if (
			(strtotime($base_start_time)>=strtotime($lunch_time_start) &&  strtotime($base_start_time)<=strtotime($lunch_time_end))
			||
			(strtotime($base_finish_time)>=strtotime($lunch_time_start) &&  strtotime($base_finish_time)<=strtotime($lunch_time_end))
		) {
			$base_start_time = $lunch_time_start;
			$base_finish_time = $lunch_time_end;

			$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
		}

		if ($schedulingD->judging_breaks_yes_no == 1) {
			$judging_breaks_morning_break_starting_time = date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_starting_time));
			$judging_breaks_morning_break_finish_time 	= date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_finish_time));
			if (
				(strtotime($base_start_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($base_start_time)<=strtotime($judging_breaks_morning_break_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($base_finish_time)<=strtotime($judging_breaks_morning_break_finish_time))
			) {
				$base_start_time = $judging_breaks_morning_break_starting_time;
				$base_finish_time = $judging_breaks_morning_break_finish_time;

				$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
			}

			$judging_breaks_afternoon_break_start_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_start_time));
			$judging_breaks_afternoon_break_finish_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_finish_time));
			if(
				(strtotime($base_start_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($base_start_time)<=strtotime($judging_breaks_afternoon_break_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($base_finish_time)<=strtotime($judging_breaks_afternoon_break_finish_time))
			) {
				$base_start_time = $judging_breaks_afternoon_break_start_time;
				$base_finish_time = $judging_breaks_afternoon_break_finish_time;

				$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
			}
		}
		/* New check for lunch timings and judging breaks - Ends */
		
		

		/* To check here if sports day is there, then exclude that time - starts */
		if($schedulingD->sports_day_yes_no == 1)
		{
			$sports_day					= $schedulingD->sports_day;
			$sports_day_starting_time	= date("H:i:s",strtotime($schedulingD->sports_day_starting_time));
			$sports_day_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_finish_time));
			
			// to check if day match
			if($sports_day == $schedulingTimingsD->day)
			{
				// Now check TIMINGS
				if(
				(strtotime($base_start_time)>=strtotime($sports_day_starting_time) &&  strtotime($base_start_time)<=strtotime($sports_day_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($sports_day_starting_time) &&  strtotime($base_finish_time)<=strtotime($sports_day_finish_time))
				) {
					$base_start_time = $sports_day_starting_time;
					$base_finish_time = $sports_day_finish_time;

					$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
				}
				
			}
		}
		/* To check here if sports day is there, then exclude that time - ends */
		
		
		/* To check here if they are having more events after sport - starts */
		if($schedulingD->sports_day_having_events_after_sport_yes_no == 1)
		{
			$sports_day					= $schedulingD->sports_day;
			$sports_day_other_starting_time	= date("H:i:s",strtotime($schedulingD->sports_day_other_starting_time));
			$sports_day_other_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_other_finish_time));
			
			// to check if day match
			if($sports_day == $schedulingTimingsD->day)
			{
				// Now check TIMINGS
				if(
				(strtotime($base_start_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($base_start_time)<=strtotime($sports_day_other_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($base_finish_time)<=strtotime($sports_day_other_finish_time))
				) {
					$base_start_time = $sports_day_other_starting_time;
					$base_finish_time = $sports_day_other_finish_time;

					$this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId);
				}
				
			}
		}
		/* To check here if they are having more events after sport - ends */
		

		return $conflict;
	}


	public function checkForOpponent($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time)
	{
		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$schDateTime		= date('Y-m-d', strtotime($base_sch_date_time));
		$starTime			= $base_start_time;
		$finishTime			= $base_finish_time;

		$userIdOpponent		= $conflict['user_id_opponent'];

		if (empty($userIdOpponent)) {
			return[];
		}
		
		///////////////
		// To get list of all conflict
		$cond = [
			'conventionseasons_id' => $conventionSD->id,
			'user_type'				=> 'Student',
			'is_bye'				=> 0,
			'user_id'				=> $userIdOpponent,
			'DATE(sch_date_time) =' => $schDateTime,
			'start_time >'			=> $finishTime
		];
		// Fetch each schedule and check if there is any group name assigned
		$stmt = $this->Schedulingtimings
			->find()
			->where($cond)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->first();
		///////////////
		
		return $stmt;

		/* $sql = "SELECT *
			FROM schedulingtimings
			WHERE user_type = 'Student'
				AND is_bye = 0
				AND user_id = $userIdOpponent
				AND DATE(sch_date_time) = '$schDateTime'
				AND start_time > '$finishTime'
			ORDER BY sch_date_time
			LIMIT 1
			";

		$stmt = $pdo->query($sql);

		return $stmt->fetch(PDO::FETCH_ASSOC); */
	}

	public function userConflictRecordsByUserId($convention_season_slug,$userId)
	{
		/* $sql = "SELECT *
			FROM schedulingtimings
			WHERE user_type = 'Student'
				AND is_bye = 0
				AND (user_id = $userId || user_id_opponent = $userId)
			ORDER BY sch_date_time
			";

		$stmt = $pdo->query($sql);

		$userConflictRecords = $stmt->fetchAll(PDO::FETCH_ASSOC); */
		
		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'Student' AND 
			Schedulingtimings.is_bye = 0 AND 
			(Schedulingtimings.user_id ='".$userId."' OR Schedulingtimings.user_id_opponent ='".$userId."')
			
		)";
		$userConflictRecords = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		
		$data		= [];
		$userData	= [];
		foreach ($userConflictRecords as $userConflictRecord) {
			//$this->prx($userConflictRecord);
			$userData[$userConflictRecord->id] = $userConflictRecord;
			$date = date('Y-m-d', strtotime($userConflictRecord->sch_date_time));
			$data[] = [
				'id'                    => $userConflictRecord->id,
				'user_id'               => $userConflictRecord->user_id,
				'user_id_opponent'      => $userConflictRecord->user_id_opponent,
				'sch_date_time'         => $userConflictRecord->sch_date_time,
				'start_time_with_date'  => $date . ' ' . date('H:i:s', strtotime($userConflictRecord->start_time)),
				'finish_time_with_date' => $date . ' ' . date('H:i:s', strtotime($userConflictRecord->finish_time)),
				'start_time'            => $userConflictRecord->start_time,
				'finish_time'           => $userConflictRecord->finish_time,
			];
		}
		//$this->prx($data);

		$conflictsById = [];
		for ($i = 0; $i < count($data); $i++) {
			for ($j = $i + 1; $j < count($data); $j++) {
				$aStart = strtotime($data[$i]['start_time_with_date']);
				$aEnd   = strtotime($data[$i]['finish_time_with_date']);
				$bStart = strtotime($data[$j]['start_time_with_date']);
				$bEnd   = strtotime($data[$j]['finish_time_with_date']);
				
				if ($aStart < $bEnd && $aEnd > $bStart) {
					$userConflictRecordId = $data[$i]['id'];
					$conflict = [
						'id'                    => $data[$j]['id'],
						'sch_date_time'         => $data[$j]['sch_date_time'],
						'start_time'            => $data[$j]['start_time'],
						'finish_time'           => $data[$j]['finish_time'],
						'user_id'               => $data[$j]['user_id'],
						'user_id_opponent'      => $data[$j]['user_id_opponent'],
					];

					if (array_key_exists($userConflictRecordId, $conflictsById)) {
						$conflictsById[$userConflictRecordId]['conflicts'][] = $conflict;
					} else {
						$conflictsById[$userConflictRecordId] = [
							'id'                    => $data[$i]['id'],
							'sch_date_time'         => $data[$i]['sch_date_time'],
							'start_time'            => $data[$i]['start_time'],
							'finish_time'           => $data[$i]['finish_time'],
							'user_id'               => $data[$i]['user_id'],
							'user_id_opponent'      => $data[$i]['user_id_opponent'],
							'conflicts'     		=> [$conflict]
						];
					}
				}
			}
		}

		return $conflictsById;
	}
	/////////////////////////////////
	
	
	/////////////////////////////////
	/* Group conflict resolve process */
	public function findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds)
	{
		$schDate 					= date('Y-m-d', strtotime($base_sch_date_time));
		$schDateTime 				= date('Y-m-d', strtotime($base_sch_date_time));
		$starTime 					= $base_start_time;
		$finishTime 				= $base_finish_time;

		$playTime 					= (strtotime($conflict->finish_time) - strtotime($conflict->start_time)) / 60;

		$nextStartTime 				= date("H:i:s", strtotime($finishTime . " +1 minute"));
		$nextFinishTime 			= date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));

		$schDateTime 				= $schDateTime . ' ' . $nextStartTime;
		$conflict->sch_date_time 	= $schDateTime;
		$conflict->start_time 		= $nextStartTime;
		$conflict->finish_time 		= $nextFinishTime;

		$base_start_time 			= $nextStartTime;
		$base_finish_time 			= $nextFinishTime;
		$base_sch_date_time 		= $schDateTime;
		//$this->prx($allUserIds);
		
		foreach ($allUserIds as $userId)
		{
			$checkGBusy = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.conventionseasons_id' => $conflict->conventionseasons_id,
				])
				->andWhere(function ($exp, $query) use ($schDate) {
					return $exp->eq(
						$query->func()->date([
							'Schedulingtimings.sch_date_time' => 'identifier'
						]),
						$schDate
					);
				})
				->andWhere(function ($exp) use ($starTime, $finishTime) {
					return $exp->add(
						"'$starTime' < Schedulingtimings.finish_time
						 AND '$finishTime' > Schedulingtimings.start_time"
					);
				})
				->andWhere(function ($exp) use ($userId) {
					return $exp->or_([
						$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_user_ids)"),
						$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_opponent_user_ids)")
					]);
				})
				->count();
				//echo $checkGBusy;exit;
				
				if ($checkGBusy>0) {
					return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds);
				}
		}

		

		$conventionSeasonsId = $conflict->conventionseasons_id;
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSeasonsId])->first();
		
		
		//$scheduling = findScheduling($pdo, $conventionSeasonsId);
		$lunch_time_start = date("H:i:s", strtotime($schedulingD->lunch_time_start));
		$lunch_time_end = date("H:i:s", strtotime($schedulingD->lunch_time_end));
		if (
			(strtotime($base_start_time)>=strtotime($lunch_time_start) &&  strtotime($base_start_time)<=strtotime($lunch_time_end))
			||
			(strtotime($base_finish_time)>=strtotime($lunch_time_start) &&  strtotime($base_finish_time)<=strtotime($lunch_time_end))
		) {
			return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds);
		}

		if ($schedulingD->judging_breaks_yes_no == 1) {
			$judging_breaks_morning_break_starting_time = date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_starting_time));
			$judging_breaks_morning_break_finish_time 	= date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_finish_time));
			if (
				(strtotime($base_start_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($base_start_time)<=strtotime($judging_breaks_morning_break_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($base_finish_time)<=strtotime($judging_breaks_morning_break_finish_time))
			) {
			   return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
			}

			$judging_breaks_afternoon_break_start_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_start_time));
			$judging_breaks_afternoon_break_finish_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_finish_time));
			if(
				(strtotime($base_start_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($base_start_time)<=strtotime($judging_breaks_afternoon_break_finish_time))
				||
				(strtotime($base_finish_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($base_finish_time)<=strtotime($judging_breaks_afternoon_break_finish_time))
			) {
			   return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
			}
		}
		
		// To check for sports day
		if ($schedulingD->sports_day_yes_no == 1)
		{
			$sports_day					= $schedulingD->sports_day;
			$sports_day_starting_time 	= date("H:i:s", strtotime($schedulingD->sports_day_starting_time));
			$sports_day_finish_time 	= date("H:i:s", strtotime($schedulingD->sports_day_finish_time));
			
			if($sports_day == $conflict->day)
			{
				if (
					(strtotime($base_start_time)>=strtotime($sports_day_starting_time) &&  strtotime($base_start_time)<=strtotime($sports_day_finish_time))
					||
					(strtotime($base_finish_time)>=strtotime($sports_day_starting_time) &&  strtotime($base_finish_time)<=strtotime($sports_day_finish_time))
				) {
				   return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
				}
			}
		}
		
		// To check events after sports day
		if ($schedulingD->sports_day_having_events_after_sport_yes_no == 1)
		{
			$sports_day					= $schedulingD->sports_day;
			$sports_day_other_starting_time 	= date("H:i:s", strtotime($schedulingD->sports_day_other_starting_time));
			$sports_day_other_finish_time 	= date("H:i:s", strtotime($schedulingD->sports_day_other_finish_time));
			
			if($sports_day == $conflict->day)
			{
				if (
					(strtotime($base_start_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($base_start_time)<=strtotime($sports_day_other_finish_time))
					||
					(strtotime($base_finish_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($base_finish_time)<=strtotime($sports_day_other_finish_time))
				) {
				   return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
				}
			}
		}
		
		
		
		//$this->prx($conflict);

		return $conflict;
	}
	
	/////////////////////////////////
	
	public function isAuthorized($user){
        // Admin can access every action
        if (isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }
        return false;
    }
	
	public function verifyRecatpcha($aData)
	{
		//echo 'ddddddddd<pre>';pr($aData);exit;
		if(!$aData)
		{
			return false;
		} 
		if(isset($aData['g-recaptcha-response']) && !empty($aData['g-recaptcha-response']))
		{
			$recaptcha_secret = SECRETKEY;
			$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha_secret."&response=".$aData['g-recaptcha-response']; 
			$response = json_decode(@file_get_contents($url));   

			if($response->success == true)
			{
				return true;
			}
			else
			{
				return false; 
			} 
		}
		else
		{
			return false;
		}
	}
    
    
	// general login check for user
	function userLoginCheck() {
		// $returnUrl = $this->request->params->url;
        $returnUrl = $this->request->url;
        $userid =$this->request->session()->read("user_id");
        $this->loadModel("Users");
        $isExists = $this->Users->find()->where(['Users.id' => $userid, 'Users.activation_status' => 1, 'Users.status' => 1])->select(['id'])->first();
        if (empty($isExists)) {
            $msgString = "Please Login"; 
            $this->request->session()->delete('user_id');
            $this->request->session()->delete('email_address');
            $this->request->session()->delete('user_type');
            $this->request->session()->delete('last_login');
			
            $this->Flash->error($msgString);
            $this->request->session()->write("returnUrl", $returnUrl);
            $this->redirect('/users/login');
        }
    }
	
	// to check subscribers type login
	function schoolAdminLoginCheck() {  
		if($this->request->session()->read("user_type") != "School")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	// to check individuals user type login
	function teacherLoginCheck() {  
		if($this->request->session()->read("user_type") != "Teacher_Parent")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	// to check individuals user type login
	function judgeLoginCheck() {  
		if($this->request->session()->read("user_type") != "Judge")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	function multiLoginCheck($usersTypesList=null) { 
        $user_type =$this->request->session()->read("user_type");
		//echo $user_type;exit;
        if (!in_array($user_type,(array)$usersTypesList)) {
            $msgString = "Unauthorize access !!!"; 
            $this->Flash->error($msgString);
            $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
        }
    }
	
	function checkRegistrationStillOpen($convention_registration_id=NULL) {
        
		$regAccepted = 0;
        $this->loadModel("Conventionregistrations");
		
		// to get conv reg details
        $convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $convention_registration_id])->contain(['Conventionseasons'])->first();
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
		
		if($regAccepted == 0)
		{
			$this->Flash->error('Sorry, registration has been closed.');
            $this->redirect('/users/dashboard');
		}
    }
	
	public function getByePlayerScheduling($total_students) {
		
		$byeTeamCount = 0;
		
		// based on call with karl in Aug 2024, he defined a range
		$byeArray = array(2,4,8,16,32,64,128,256,512,1024,2048);
		
		$teamDivBy2 = ($total_students/2);
		
		if(in_array($teamDivBy2,$byeArray))
		{
			// exact match
			$byeTeamCount = 0;
		}
		else
		{
			$teamDivBy2 = ceil($total_students/2);
			
			// to check if ceil value exact match in array
			if(in_array($teamDivBy2,$byeArray))
			{
				$byeTeamCount = $total_students - $teamDivBy2;
			}
			else
			{
				// now check where this count exists
				for($cntrB=0;$cntrB<count($byeArray);$cntrB++)
				{
					if($teamDivBy2 > $byeArray[$cntrB] && $teamDivBy2 < $byeArray[$cntrB+1])
					{
						$byeArrV = $byeArray[$cntrB+1];
						
						$byeTeamCount = $total_students - $byeArrV;
						
						break;
					}
				}
			}
		}
		return $byeTeamCount;
	}
	
	public function clearSchedulingtimings($convention_season_slug=NULL)
	{
		if($convention_season_slug)
		{
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
			//$this->prx($conventionSD);
			
			$this->Schedulingtimings->deleteAll(["conventionseasons_id" => $conventionSD->id, "convention_id" => $conventionSD->convention_id, "season_id" => $conventionSD->season_id, "season_year" => $conventionSD->season_year]);
			
			// Clear conflicts
			$this->Schedulings->updateAll(['conflict_user_ids' => NULL], ["conventionseasons_id" => $conventionSD->id]);
		}
		
		return true;
	}
    
    public function getSlug($str, $table='Admins'){
        $slug = Inflector::slug($str);
        $slug = strtolower($slug);
        //$slug = 'dinesh-dhaker';
        $isRecord =  $this->$table->find()->where([$table . '.slug like' => $slug . '%'])->order([$table.'.id'=>'DESC'])->first();
        
        if($isRecord){
            $oldslug = explode('-', $isRecord->slug);
            $last = array_pop($oldslug);
            $slug = $last;
            if(is_numeric($last)){
                $last = $last + 1;
                $slug = $slug.'-'.$last;
            }else{
               $slug = $slug.'-'.$last.'-1'; 
            }
            
            return $slug.time();
        }else{
            return $slug;
        }
    }
	
	function valid_email($str)
	{
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}
	
	function prx($arrV = NULL)
	{
		echo '<pre>';
		print_r($arrV);
		echo '</pre>';
		exit;
	}
	
	function pr($arrV = NULL)
	{
		echo '<pre>';
		print_r($arrV);
		echo '</pre>';
		return;
		//exit;
	}
    
    
            
    
}
?>