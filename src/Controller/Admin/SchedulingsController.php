<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class SchedulingsController extends AppController {

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
		$this->loadModel("Events");
		$this->loadModel("Schedulingtimings");
		$this->loadModel("Crstudentevents");
    }

	private function ensureOverwriteAuditTable() {
		$connection = ConnectionManager::get('default');
		$connection->execute(
			"CREATE TABLE IF NOT EXISTS overwrite_timings_audits (
				id INT AUTO_INCREMENT PRIMARY KEY,
				conventionseason_id INT NOT NULL,
				admin_id INT NULL,
				affected_records INT NOT NULL DEFAULT 0,
				payload LONGTEXT NOT NULL,
				is_undone TINYINT(1) NOT NULL DEFAULT 0,
				undone_at DATETIME NULL,
				undo_admin_id INT NULL,
				created DATETIME NOT NULL,
				INDEX idx_ota_conventionseason (conventionseason_id),
				INDEX idx_ota_undone (is_undone)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
		);
	}

	private function getLatestOverwriteAudit($conventionSeasonId) {
		$this->ensureOverwriteAuditTable();
		$connection = ConnectionManager::get('default');
		$statement = $connection->execute(
			"SELECT * FROM overwrite_timings_audits WHERE conventionseason_id = :csid AND is_undone = 0 ORDER BY id DESC LIMIT 1",
			['csid' => (int)$conventionSeasonId]
		);
		$latest = $statement->fetch('assoc');
		return $latest ?: null;
	}

	private function insertOverwriteAudit($conventionSeasonId, $adminId, $affectedRecords, $payloadArray) {
		$this->ensureOverwriteAuditTable();
		$connection = ConnectionManager::get('default');
		$connection->execute(
			"INSERT INTO overwrite_timings_audits (conventionseason_id, admin_id, affected_records, payload, created)
			 VALUES (:csid, :admin_id, :affected_records, :payload, :created)",
			[
				'csid' => (int)$conventionSeasonId,
				'admin_id' => $adminId ? (int)$adminId : null,
				'affected_records' => (int)$affectedRecords,
				'payload' => json_encode($payloadArray),
				'created' => date('Y-m-d H:i:s'),
			]
		);
	}

	private function markOverwriteAuditUndone($auditId, $adminId) {
		$connection = ConnectionManager::get('default');
		$connection->execute(
			"UPDATE overwrite_timings_audits
			 SET is_undone = 1, undone_at = :undone_at, undo_admin_id = :undo_admin_id
			 WHERE id = :id",
			[
				'undone_at' => date('Y-m-d H:i:s'),
				'undo_admin_id' => $adminId ? (int)$adminId : null,
				'id' => (int)$auditId,
			]
		);
	}

    public function precheck($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		//$this->prx($conventionSD);
		
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to check that if record for this conv season entered in scheduling table..
		// ... if not entered, then entered
		$checkSchedulingRecord = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		if(!$checkSchedulingRecord)
		{
			// enter new record
			$schedulings = $this->Schedulings->newEntity();
			$dataSch = $this->Schedulings->patchEntity($schedulings, array());

			$dataSch->slug 						= "scheduling-conv-season-".$conventionSD->id.'-'.time();
			$dataSch->conventionseasons_id		= $conventionSD->id;
			$dataSch->convention_id				= $conventionSD->convention_id;
			$dataSch->season_id					= $conventionSD->season_id;
			$dataSch->season_year 				= $conventionSD->season_year;
			
			$dataSch->created 					= date('Y-m-d H:i:s');

			$resultSch = $this->Schedulings->save($dataSch);
		}
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		$this->set('schedulings', $schedulingD);
		$this->set('schedulings', $schedulingD);

		$latestOverwriteAudit = $this->getLatestOverwriteAudit($conventionSD->id);
		$this->set('latestOverwriteAudit', $latestOverwriteAudit);
    }
	
	public function precheckevents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		
		// to check events for this convention season
		$cntrPreCheckEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
		foreach($conventionSEventsList as $convevPreCheck)
		{
			if($convevPreCheck->Events['needs_schedule'] == 1)
			{
				$cntrPreCheckEvents++;
			}
		}
		
		
		//$this->prx($conventionSEvents);
		if($cntrPreCheckEvents>0)
		{
			// now update this precheck events in scheduling table
			$this->Schedulings->updateAll(['precheck_events' => 1,'total_events_found' => $cntrPreCheckEvents,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total event found: '.$cntrPreCheckEvents);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_events' => 0,'total_events_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no event found for this convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function prechecklocations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check location/rooms for this convention
		$conventionRoomsTotal = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])->count();
		if($conventionRoomsTotal>0)
		{
			// to check events for this convention season
			$cntrConvSeasonTotalEvents = 0;
			$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
			foreach($conventionSEventsList as $convEv)
			{
				if($convEv->Events['needs_schedule'] == 1)
				{
					$cntrConvSeasonTotalEvents++;
				}
			}
			
			$roomEventsArr = array();
			
			// now get events that is assigned to a room
			$convRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->all();
			foreach($convRoomEvents as $convroomev)
			{
				$roomEventIDSExplode = explode(",",$convroomev->event_ids);
				foreach($roomEventIDSExplode as $eventidexplode)
				{
					if(!in_array($eventidexplode,(array)$roomEventsArr))
					{
						$roomEventsArr[] = $eventidexplode;
					}
				}
			}
			
			if(count((array)$roomEventsArr) < $cntrConvSeasonTotalEvents)
			{
				$this->Flash->error('Sorry, '.($cntrConvSeasonTotalEvents-count((array)$roomEventsArr)).' event(s) not assigned to any room. Please assign.');
				
				$this->Schedulings->updateAll(['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			}
			else
			{
				$this->Schedulings->updateAll(['precheck_locations' => 1,'total_locations_found' => $conventionRoomsTotal,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
				$this->Flash->success('Total locations found: '.$conventionRoomsTotal);
			}
		}
		else
		{
			$this->Flash->error('Sorry no location found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckregistrations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check convention registrations
		$conventionRegCount = $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->count();
		if($conventionRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 1,'total_registrations_found' => $conventionRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total registrations found: '.$conventionRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 0,'total_registrations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no registration found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckstudents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check convention registrations
		$studentsRegCount = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,'Conventionregistrationstudents.season_id' => $conventionSD->season_id,'Conventionregistrationstudents.season_year' => $conventionSD->season_year])->count();
		if($studentsRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_students' => 1,'total_students_found' => $studentsRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total students found: '.$studentsRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_students' => 0,'total_students_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no stuednts found for this convention.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function resetallprecheck($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		//$this->prx($conventionSEvents);
		if($conventionSD)
		{
			// now reset all precheck
			$this->Schedulings->updateAll(
			[
			'precheck_events' => 0,'total_events_found' => NULL,
			'precheck_locations' => 0,'total_locations_found' => NULL,
			'precheck_registrations' => 0,'total_registrations_found' => NULL,
			'precheck_students' => 0,'total_students_found' => NULL,
			'modified' => date('Y-m-d H:i:s')
			], 
			["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Reset all pre-check prcessed successfully.');
		}
		else
		{	
			$this->Flash->error('Invalid convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function wizard($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		$schedulings = $this->Schedulings->get($schedulingD->id);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Schedulings->patchEntity($schedulings, $this->request->data);
			
            if (count($data->errors()) == 0) {
				$data->modified = date("Y-m-d");
                
				//$this->prx($data);
				
				$data->start_date = date("Y-m-d",strtotime($data->start_date));
				
				/* $time = '9:50 PM';
				$timestamp = strtotime($time);
				$mysqlFormat = date('Y-m-d H:i:s', $timestamp);

				echo $mysqlFormat;exit; */
				
				$data->normal_starting_time 	= $this->changeToMysqlTimeFormat($data->normal_starting_time);
				$data->normal_finish_time 		= $this->changeToMysqlTimeFormat($data->normal_finish_time);
				$data->lunch_time_start 		= $this->changeToMysqlTimeFormat($data->lunch_time_start);
				$data->lunch_time_end 			= $this->changeToMysqlTimeFormat($data->lunch_time_end);
				
				if($data->starting_different_time_first_day_yes_no)
				{
					$data->different_first_day_start_time 			= $this->changeToMysqlTimeFormat($data->different_first_day_start_time);
					$data->different_first_day_end_time 			= $this->changeToMysqlTimeFormat($data->different_first_day_end_time);
				}
				else
				{
					$data->different_first_day_start_time 			= NULL;
					$data->different_first_day_end_time 			= NULL;
				}
				
				if($data->judging_breaks_yes_no)
				{
					$data->judging_breaks_morning_break_starting_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_morning_break_starting_time);
					$data->judging_breaks_morning_break_finish_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_morning_break_finish_time);
					$data->judging_breaks_afternoon_break_start_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_afternoon_break_start_time);
					$data->judging_breaks_afternoon_break_finish_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_afternoon_break_finish_time);
				}
				else
				{
					$data->judging_breaks_morning_break_starting_time 			= NULL;
					$data->judging_breaks_morning_break_finish_time 			= NULL;
					$data->judging_breaks_afternoon_break_start_time 			= NULL;
					$data->judging_breaks_afternoon_break_finish_time 			= NULL;
				}
				
				if($data->sports_day_yes_no)
				{
					$data->sports_day_starting_time 				= $this->changeToMysqlTimeFormat($data->sports_day_starting_time);
					$data->sports_day_finish_time 					= $this->changeToMysqlTimeFormat($data->sports_day_finish_time);
				}
				else
				{
					$data->sports_day 								= NULL;
					$data->sports_day_starting_time 				= NULL;
					$data->sports_day_finish_time 					= NULL;
				}
				
				if($data->sports_day_having_events_after_sport_yes_no)
				{
					$data->sports_day_other_starting_time 			= $this->changeToMysqlTimeFormat($data->sports_day_other_starting_time);
					$data->sports_day_other_finish_time 			= $this->changeToMysqlTimeFormat($data->sports_day_other_finish_time);
				}
				else
				{
					$data->sports_day_other_starting_time 				= NULL;
					$data->sports_day_other_finish_time 				= NULL;
				}
				
				// Settling time defaults to 15 if not set
				if(!isset($data->settling_time_minutes) || $data->settling_time_minutes === '' || $data->settling_time_minutes === null) {
					$data->settling_time_minutes = 15;
				}
				
				// Elimination rounds buffer defaults to 3 if not set
				if(!isset($data->elimination_rounds_buffer) || $data->elimination_rounds_buffer === '' || $data->elimination_rounds_buffer === null) {
					$data->elimination_rounds_buffer = 3;
				}
				
				// Schedule release date
				if(!empty($data->schedule_release_date)) {
					$data->schedule_release_date = date("Y-m-d H:i:s", strtotime($data->schedule_release_date));
				} else {
					$data->schedule_release_date = NULL;
				}
				
				//$this->prx($data);
				
				if ($this->Schedulings->save($data)) {
                    $this->Flash->success('Data saved successfully.');
                    $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('schedulings', $schedulings);
		
    }
	
	public function schedulecategory($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Schedule category');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		/* Category :: 1 */
		// group_event = yes || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC1 = array();
		$condC1 = array();
		$condC1[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC1 = $this->Conventionseasonevents->find()->where($condC1)->all();
		foreach($eventsC1 as $eventc1)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc1->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC1[] = $eventc1->event_id;
			}
		}
		$this->set('arrEventsC1', $arrEventsC1);
		
		
		
		
		
		/* Category :: 2 */
		// group_event = no || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC2 = array();
		$condC2 = array();
		$condC2[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC2 = $this->Conventionseasonevents->find()->where($condC2)->all();
		foreach($eventsC2 as $eventc2)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc2->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC2[] = $eventc2->event_id;
			}
		}
		$this->set('arrEventsC2', $arrEventsC2);
		//$this->prx($arrEventsC2);
		
		
		/* Category :: 3 - this is similar to category 2 */
		// group_event = yes || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC3 = array();
		$condC3 = array();
		$condC3[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC3 = $this->Conventionseasonevents->find()->where($condC3)->all();
		foreach($eventsC3 as $eventc3)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc3->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC3[] = $eventc3->event_id;
			}
		}
		$this->set('arrEventsC3', $arrEventsC3);
		//$this->prx($arrEventsC3);
		
		
		/* Category :: 4 - this is similar to category 1 */
		// group_event = no || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC4 = array();
		$condC4 = array();
		$condC4[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC4 = $this->Conventionseasonevents->find()->where($condC4)->all();
		foreach($eventsC4 as $eventc4)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc4->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC4[] = $eventc4->event_id;
			}
		}
		$this->set('arrEventsC4', $arrEventsC4);
		//$this->prx($arrEventsC4);
		
		
    }
	
	
	public function reports($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
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
		
    }
	
	public function finalizeschedule($convention_season_slug=null) {
		$this->set('title', ADMIN_TITLE . 'Finalize Schedule');
		$this->viewBuilder()->layout('admin');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		if(!$schedulingD) {
			$this->Flash->error('Scheduling record not found.');
			return $this->redirect(['action'=>'reports', $convention_season_slug]);
		}

		if($this->request->is('post')) {
			$newState = ($schedulingD->is_finalized == 1) ? 0 : 1;
			$schedulingD->is_finalized = $newState;
			if($this->Schedulings->save($schedulingD)) {
				$msg = ($newState == 1) ? 'Schedule has been FINALIZED and locked.' : 'Schedule has been UNLOCKED for editing.';
				$this->Flash->success($msg);
			} else {
				$this->Flash->error('Could not update finalize status.');
			}
			return $this->redirect(['action'=>'schedulecategory', $convention_season_slug]);
		}

		$this->set('schedulingD', $schedulingD);
	}

	public function overwritetimings($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Overwrite Timings');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// Nathan provided these 3 events for Overwrite
		/* Spelling U16 - 003--3   Spelling OPEN - 053--11   Bible Memory OPEN - 1056--343 */
		$eventIDArr = array(343,11,3);
		
		// Now check if these events are chosen for this convention season
		
		$finalEventArr = array();
		$eventStats = array();
		
		foreach($eventIDArr as $event_id)
		{
			$checkEventCS = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonevents.event_id' => $event_id])->contain(["Events"])->first();
			if($checkEventCS)
			{
				// Now we need to show number of students in each event to show in dropdown
				$countStudentsEvent = $this->Crstudentevents
										->find()
										->where([
											'Crstudentevents.conventionseason_id' => $conventionSD->id,
											'Crstudentevents.event_id' => $event_id
										])
										->count();

				$countScheduledRecords = $this->Schedulingtimings
										->find()
										->where([
											'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
											'Schedulingtimings.event_id' => $event_id
										])
										->count();
				
				$finalEventArr[$event_id] = $checkEventCS->Events['event_name'].' ('.$checkEventCS->Events['event_id_number'].')'.' ('.$countStudentsEvent.')';
				$eventStats[$event_id] = [
					'label' => $checkEventCS->Events['event_name'].' ('.$checkEventCS->Events['event_id_number'].')',
					'event_id_number' => $checkEventCS->Events['event_id_number'],
					'students' => $countStudentsEvent,
					'scheduled_records' => $countScheduledRecords,
					'duration_minutes' => ((int)$checkEventCS->Events['setup_time']) + ((int)$checkEventCS->Events['round_time']) + ((int)$checkEventCS->Events['judging_time']),
				];
			}
		}
		$this->set('finalEventArr', $finalEventArr);
		$this->set('eventStats', $eventStats);

		// Build list of distinct convention dates from existing scheduled timings
		$allSchTimings = $this->Schedulingtimings->find()
			->select(['sch_date_time', 'day'])
			->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id])
			->order(['Schedulingtimings.sch_date_time' => 'ASC'])
			->all();
		$conventionDays = []; $seenDates = [];
		foreach ($allSchTimings as $t) {
			$dateStr = date('Y-m-d', strtotime($t->sch_date_time));
			if ($dateStr && $dateStr !== '1970-01-01' && !in_array($dateStr, $seenDates)) {
				$seenDates[] = $dateStr;
				$conventionDays[] = ['date' => $dateStr, 'day_name' => $t->day, 'display' => date('l, j F Y', strtotime($dateStr))];
			}
		}
		$this->set('conventionDays', $conventionDays);

		if ($this->request->is(['post', 'put'])) {
			$postData = (array)$this->request->getData();
			$schedulingInput = [];
			if (!empty($postData['Schedulings']) && is_array($postData['Schedulings'])) {
				$schedulingInput = $postData['Schedulings'];
			} elseif (!empty($postData['data']['Schedulings']) && is_array($postData['data']['Schedulings'])) {
				// Backward-compatible support for legacy input names like data[Schedulings][...]
				$schedulingInput = $postData['data']['Schedulings'];
			}

			$hasRowModeInput = !empty($schedulingInput['sched_rows']) && is_array($schedulingInput['sched_rows']);
			$event_ids = isset($schedulingInput['event_ids']) ? (array)$schedulingInput['event_ids'] : [];
			if (empty($event_ids) && !empty($schedulingInput['event_id'])) {
				$event_ids = [(int)$schedulingInput['event_id']];
			}

			$max_students = isset($schedulingInput['max_students']) ? (int)$schedulingInput['max_students'] : 0;
			$time_gap_mins = max(0, isset($schedulingInput['time_gap_mins']) ? (int)$schedulingInput['time_gap_mins'] : 0);
			if ($time_gap_mins === 0) {
				$time_gap_mins = 1;
			}

			$rowModeRows = [];
			if ($hasRowModeInput) {
				$seenRowEvents = [];
				foreach ($schedulingInput['sched_rows'] as $row) {
					$rowEventId = isset($row['event_id']) ? (int)$row['event_id'] : 0;
					$rowDate = isset($row['date']) ? trim($row['date']) : '';
					$rowTime = isset($row['time']) ? trim($row['time']) : '';
					$rowMaxStudents = isset($row['max_students']) ? (int)$row['max_students'] : 0;
					$rowGapMins = isset($row['time_gap_mins']) ? (int)$row['time_gap_mins'] : 0;

					$rowHasAny = ($rowEventId > 0 || $rowDate !== '' || $rowTime !== '');
					if (!$rowHasAny) {
						continue;
					}

					if ($rowEventId <= 0 || $rowDate === '' || $rowTime === '') {
						$this->Flash->error('For row mode, each configured row must include event, date, and time.');
						return;
					}

					if (isset($seenRowEvents[$rowEventId])) {
						$this->Flash->error('Please use each event only once in row mode.');
						return;
					}
					$seenRowEvents[$rowEventId] = true;

					$parsedDate = date('Y-m-d', strtotime($rowDate));
					$parsedTime = date('H:i:s', strtotime($rowTime));
					if (!$parsedDate || $parsedDate === '1970-01-01' || !$parsedTime) {
						$this->Flash->error('One or more row dates/times are invalid.');
						return;
					}

					if ($rowMaxStudents <= 0) {
						$rowMaxStudents = $max_students > 0 ? $max_students : 1;
					}
					if ($rowGapMins <= 0) {
						$rowGapMins = $time_gap_mins > 0 ? $time_gap_mins : 1;
					}

					$rowModeRows[] = [
						'event_id' => $rowEventId,
						'date' => $parsedDate,
						'time' => $parsedTime,
						'max_students' => $rowMaxStudents,
						'time_gap_mins' => $rowGapMins,
					];
				}
			}

			if ($hasRowModeInput && empty($rowModeRows)) {
				$this->Flash->error('Please configure at least one event row with event, date, and start time.');
				return;
			}

			if (!empty($rowModeRows)) {
				$event_ids = array_values(array_unique(array_map(function($r){ return (int)$r['event_id']; }, $rowModeRows)));

				$overwriteSnapshot = [];
				foreach ($event_ids as $snapshotEventId) {
					$existingRows = $this->Schedulingtimings->find()
						->select(['id', 'event_id', 'conventionseasons_id', 'sch_date_time', 'day', 'start_time', 'finish_time'])
						->where([
							'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
							'Schedulingtimings.event_id' => $snapshotEventId,
						])
						->order(['Schedulingtimings.id' => 'ASC'])
						->all();
					foreach ($existingRows as $row) {
						$overwriteSnapshot[] = [
							'id' => (int)$row->id,
							'event_id' => (int)$row->event_id,
							'conventionseasons_id' => (int)$row->conventionseasons_id,
							'sch_date_time' => (string)$row->sch_date_time,
							'day' => (string)$row->day,
							'start_time' => (string)$row->start_time,
							'finish_time' => (string)$row->finish_time,
						];
					}
				}

				$selectedScheduledRecords = 0;
				$selectedStudentRecords = 0;
				foreach ($event_ids as $rowEventId) {
					if (isset($eventStats[$rowEventId])) {
						$selectedScheduledRecords += (int)$eventStats[$rowEventId]['scheduled_records'];
						$selectedStudentRecords += (int)$eventStats[$rowEventId]['students'];
					}
				}

				if (!empty($schedulingInput['preview_only'])) {
					$this->Flash->success(
						'Dry Run: row mode selected '.count($rowModeRows).' row(s), '
						.count($event_ids).' event(s), '.$selectedScheduledRecords.' existing schedule record(s) would be updated '
						.'(participant rows found: '.$selectedStudentRecords.').'
					);
					return;
				}

				$cntrTotRec = 0;
				foreach ($rowModeRows as $cfgRow) {
					$eventD = $this->Events->find()->where(['Events.id' => $cfgRow['event_id']])->first();
					if (!$eventD) {
						continue;
					}

					$eventSetupRoundJudTime = $eventD->setup_time + $eventD->round_time + $eventD->judging_time;
					$start_date = $cfgRow['date'];
					$start_time = $cfgRow['time'];
					$finish_time = date('H:i:s', strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					$cntrSc = 0;

					$schedulingtimings = $this->Schedulingtimings->find()
						->where([
							'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
							'Schedulingtimings.event_id' => $cfgRow['event_id'],
						])
						->order(['Schedulingtimings.id' => 'ASC'])
						->all();

					foreach ($schedulingtimings as $schrecord) {
						$this->Schedulingtimings->updateAll(
							[
								'sch_date_time' => $start_date.' '.$start_time,
								'day' => date('l', strtotime($start_date)),
								'start_time' => $start_time,
								'finish_time' => $finish_time,
								'modified' => date('Y-m-d H:i:s'),
							],
							['id' => $schrecord->id]
						);

						$cntrSc++;
						$cntrTotRec++;
						if ($cntrSc >= $cfgRow['max_students']) {
							$cntrSc = 0;
							$start_time = date('H:i:s', strtotime('+ '.$cfgRow['time_gap_mins'].' minutes', strtotime($finish_time)));
							$finish_time = date('H:i:s', strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
					}
				}

				if ($cntrTotRec > 0) {
					$loggedAdminId = $this->request->session()->read('admin_id');
					$this->insertOverwriteAudit(
						$conventionSD->id,
						$loggedAdminId,
						$cntrTotRec,
						[
							'conventionseason_slug' => $convention_season_slug,
							'mode' => 'rows',
							'rows' => $rowModeRows,
							'created_at' => date('Y-m-d H:i:s'),
							'records' => $overwriteSnapshot,
						]
					);
					$this->Flash->success('Row mode overwrite completed. Total '.$cntrTotRec.' record(s) modified across '.count($rowModeRows).' configured row(s).');
				} else {
					$this->Flash->error('Sorry, no records updated in row mode.');
				}

				return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			}

			if (!$hasRowModeInput) {
				$this->Flash->error('Please use the Per-Event Scheduler rows: choose event, date, time, and students/block for at least one row.');
				return;
			}

			if ($max_students <= 0) {
				$this->Flash->error('Please enter a valid Students/Block value (must be greater than 0).');
				return;
			}

			if (empty($event_ids)) {
				$this->Flash->error('Please select at least one event.');
				return;
			}

			$daySlots = [];
			if (!empty($schedulingInput['days']) && is_array($schedulingInput['days'])) {
				foreach ($schedulingInput['days'] as $dayRow) {
					$isActive = !empty($dayRow['active']);
					$dayDate = isset($dayRow['date']) ? trim($dayRow['date']) : '';
					$dayTime = isset($dayRow['time']) ? trim($dayRow['time']) : '';
					if ($isActive && $dayDate !== '' && $dayTime !== '') {
						$parsedDate = date('Y-m-d', strtotime($dayDate));
						$parsedTime = date('H:i:s', strtotime($dayTime));
						if ($parsedDate && $parsedDate !== '1970-01-01' && $parsedTime) {
							$daySlots[] = ['date' => $parsedDate, 'time' => $parsedTime];
						}
					}
				}
			}

			// Backward compatible support for older form fields.
			if (empty($daySlots)) {
				$overwrite_date = isset($schedulingInput['overwrite_date']) ? $schedulingInput['overwrite_date'] : '';
				$overwrite_time = isset($schedulingInput['overwrite_time']) ? $schedulingInput['overwrite_time'] : '';
				if (!empty($overwrite_date) && !empty($overwrite_time)) {
					$daySlots[] = [
						'date' => date('Y-m-d', strtotime($overwrite_date)),
						'time' => date('H:i:s', strtotime($overwrite_time)),
					];
				}
			}

			if (empty($daySlots)) {
				$this->Flash->error('Please choose at least one convention day with start time.');
				return;
			}

			$overwriteSnapshot = [];
			foreach ($event_ids as $snapshotEventId) {
				$existingRows = $this->Schedulingtimings->find()
					->select(['id', 'event_id', 'conventionseasons_id', 'sch_date_time', 'day', 'start_time', 'finish_time'])
					->where([
						'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
						'Schedulingtimings.event_id' => $snapshotEventId,
					])
					->order(['Schedulingtimings.id' => 'ASC'])
					->all();
				foreach ($existingRows as $row) {
					$overwriteSnapshot[] = [
						'id' => (int)$row->id,
						'event_id' => (int)$row->event_id,
						'conventionseasons_id' => (int)$row->conventionseasons_id,
						'sch_date_time' => (string)$row->sch_date_time,
						'day' => (string)$row->day,
						'start_time' => (string)$row->start_time,
						'finish_time' => (string)$row->finish_time,
					];
				}
			}

			$selectedScheduledRecords = 0;
			$selectedStudentRecords = 0;
			foreach ($event_ids as $event_id) {
				if (isset($eventStats[$event_id])) {
					$selectedScheduledRecords += (int)$eventStats[$event_id]['scheduled_records'];
					$selectedStudentRecords += (int)$eventStats[$event_id]['students'];
				}
			}
			$estimatedBlocks = (int)ceil(max(1, $selectedScheduledRecords) / max(1, $max_students));

			if (!empty($schedulingInput['preview_only'])) {
				$this->Flash->success(
					'Dry Run: selected '.count($event_ids).' event(s), '.count($daySlots).' day(s), '
					.$selectedScheduledRecords.' existing schedule record(s) would be updated '
					.'(participant rows found: '.$selectedStudentRecords.', estimated blocks: '.$estimatedBlocks.').'
				);
				return;
			}

			$cntrTotRec = 0;
			foreach ($event_ids as $event_id) {
				$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
				if (!$eventD) {
					continue;
				}

				$eventSetupRoundJudTime = $eventD->setup_time + $eventD->round_time + $eventD->judging_time;
				$slotIndex = 0;
				$start_date = $daySlots[$slotIndex]['date'];
				$start_time = $daySlots[$slotIndex]['time'];
				$finish_time = date('H:i:s', strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

				$cntrSc = 0;
				$schedulingtimings = $this->Schedulingtimings->find()
					->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id, 'Schedulingtimings.event_id' => $event_id])
					->order(['Schedulingtimings.id' => 'ASC'])
					->all();

				foreach ($schedulingtimings as $schrecord) {
					$this->Schedulingtimings->updateAll(
						[
							'sch_date_time' => $start_date.' '.$start_time,
							'day' => date('l', strtotime($start_date)),
							'start_time' => $start_time,
							'finish_time' => $finish_time,
							'modified' => date('Y-m-d H:i:s'),
						],
						['id' => $schrecord->id]
					);

					$cntrSc++;
					$cntrTotRec++;
					if ($cntrSc >= $max_students) {
						$cntrSc = 0;
						if ($slotIndex < count($daySlots) - 1) {
							$slotIndex++;
							$start_date = $daySlots[$slotIndex]['date'];
							$start_time = $daySlots[$slotIndex]['time'];
						} else {
							$start_time = date('H:i:s', strtotime('+ '.$time_gap_mins.' minutes', strtotime($finish_time)));
						}
						$finish_time = date('H:i:s', strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					}
				}
			}

			if ($cntrTotRec > 0) {
				$loggedAdminId = $this->request->session()->read('admin_id');
				$this->insertOverwriteAudit(
					$conventionSD->id,
					$loggedAdminId,
					$cntrTotRec,
					[
						'conventionseason_slug' => $convention_season_slug,
						'event_ids' => $event_ids,
						'day_slots' => $daySlots,
						'max_students' => $max_students,
						'time_gap_mins' => $time_gap_mins,
						'created_at' => date('Y-m-d H:i:s'),
						'records' => $overwriteSnapshot,
					]
				);

				$this->Flash->success('Scheduling date/time overwrite successfully. Total '.$cntrTotRec.' record(s) modified across '.count($event_ids).' event(s) and '.count($daySlots).' day slot(s).');
			} else {
				$this->Flash->error('Sorry, no records updated.');
			}

			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}
		
    }

	public function undooverwritetimings($convention_season_slug=null) {
		if (!$this->request->is(['post', 'put'])) {
			return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
		}

		$conventionSD = $this->Conventionseasons->find()
			->where(['Conventionseasons.slug' => $convention_season_slug])
			->contain(["Conventions"])
			->first();

		if (!$conventionSD) {
			$this->Flash->error('Invalid convention season.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$latestAudit = $this->getLatestOverwriteAudit($conventionSD->id);
		if (empty($latestAudit)) {
			$this->Flash->error('No overwrite batch found to undo.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
		}

		$payload = json_decode($latestAudit['payload'], true);
		if (empty($payload) || empty($payload['records']) || !is_array($payload['records'])) {
			$this->Flash->error('Undo data is invalid for the latest overwrite batch.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
		}

		$restored = 0;
		foreach ($payload['records'] as $record) {
			if (empty($record['id'])) {
				continue;
			}

			$this->Schedulingtimings->updateAll(
				[
					'sch_date_time' => $record['sch_date_time'],
					'day' => $record['day'],
					'start_time' => $record['start_time'],
					'finish_time' => $record['finish_time'],
					'modified' => date('Y-m-d H:i:s'),
				],
				['id' => (int)$record['id'], 'conventionseasons_id' => $conventionSD->id]
			);
			$restored++;
		}

		$loggedAdminId = $this->request->session()->read('admin_id');
		$this->markOverwriteAuditUndone($latestAudit['id'], $loggedAdminId);

		if ($restored > 0) {
			$this->Flash->success('Undo completed. Restored '.$restored.' schedule record(s) from the latest overwrite batch.');
		} else {
			$this->Flash->error('Undo ran but no records were restored.');
		}

		return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
	}
	
	public function resolveconflicts($convention_season_slug=null) {
		
		// Add validation for the slug parameter
		if (empty($convention_season_slug)) {
			$this->Flash->error('Convention season slug is required.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'index']);
			return;
		}
		
		// First we need to collect all students list of all schools
		try {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
			
			if (empty($conventionSD)) {
				$this->Flash->error('Convention season not found.');
				$this->redirect(['controller' => 'schedulings', 'action' => 'index']);
				return;
			}
		} catch (Exception $e) {
			$this->Flash->error('Database error: ' . $e->getMessage());
			$this->redirect(['controller' => 'schedulings', 'action' => 'index']);
			return;
		}
		
		try {
			$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
			
			if (empty($schedulingD)) {
				$this->Flash->error('No scheduling data found for this convention season.');
				$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
				return;
			}
		} catch (Exception $e) {
			$this->Flash->error('Error retrieving scheduling data: ' . $e->getMessage());
			$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			return;
		}
		
		if(!empty($schedulingD->conflict_user_ids))
		{
			$userIDSConflict = explode(",",$schedulingD->conflict_user_ids);
			shuffle($userIDSConflict);
			
			/////////////////
			foreach ($userIDSConflict as $userId) {
			$resolveConflicts = false;
			do {
				try {
					$userConflictRecords = $this->userConflictRecordsByUserId($convention_season_slug, $userId);
				} catch (Exception $e) {
					$this->Flash->error('Error retrieving conflict records: ' . $e->getMessage());
					$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
					return;
				}
				
				if (empty($userConflictRecords))
				{
					// No conflict found, then remove this from Conflict
					$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
						return $item !== $userId;
					});
					
					// Now update record
					if(count($nextUserIDSConflicts))
					{
						$this->Schedulings->updateAll(
						[
							'conflict_user_ids'		=> implode(",",$nextUserIDSConflicts)
						]
						,
						[
							"id" => $schedulingD->id
						]);
					}
					else
					{
						$this->Schedulings->updateAll(
						[
							'conflict_user_ids'		=> NULL
						]
						,
						[
							"id" => $schedulingD->id
						]);
					}
				}
				
				//$this->prx($userConflictRecords);
				foreach ($userConflictRecords as $userConflictRecord)
				{
					$recordId = $userConflictRecord['id']; // Initialize recordId from the conflict record
					$base_start_time		= date("H:i:s",strtotime($userConflictRecord['start_time']));
					$base_finish_time		= date("H:i:s",strtotime($userConflictRecord['finish_time']));
					$base_sch_date_time 	= date("Y-m-d H:i:s",strtotime($userConflictRecord['sch_date_time']));
					
					foreach($userConflictRecord['conflicts'] as $conflict)
					{
						try {
							$resolveConflict	= $this->nextBookings($convention_season_slug,$conflict, $base_start_time, $base_finish_time, $base_sch_date_time,$recordId);
							
							if (empty($resolveConflict)) {
								continue;
							}

							$recordId			= $resolveConflict['id'];
							$start_time			= $resolveConflict['start_time'];
							$finish_time		= $resolveConflict['finish_time'];
							$sch_date_time		= $resolveConflict['sch_date_time'];
						} catch (Exception $e) {
							$this->Flash->error('Error resolving conflict: ' . $e->getMessage());
							continue;
						}

						/* $sqlExist = "UPDATE schedulingtimings
							SET start_time = '$start_time', finish_time = '$finish_time', sch_date_time = '$sch_date_time'
							WHERE id  = $recordId
							";
						$stmt = $pdo->query($sqlExist); */
						
						///////////////
						$this->Schedulingtimings->updateAll(
						[
							'start_time'		=> $start_time,
							'finish_time'		=> $finish_time,
							'sch_date_time'		=> $sch_date_time
						]
						,
						[
							"id" => $recordId
						]);
						
						// remove user id from database because conflict resolved
						$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
							return $item !== $userId;
						});
						
						// Now update record
						if(count($nextUserIDSConflicts))
						{
							$this->Schedulings->updateAll(
							[
								'conflict_user_ids'		=> implode(",",$nextUserIDSConflicts)
							]
							,
							[
								"id" => $schedulingD->id
							]);
						}
						else
						{
							$this->Schedulings->updateAll(
							[
								'conflict_user_ids'		=> NULL
							]
							,
							[
								"id" => $schedulingD->id
							]);
						}
						
						///////////////

						$base_start_time	= $start_time;
						$base_finish_time	= $finish_time;
						$base_sch_date_time	= $sch_date_time;
					}
				}

				try {
					$userConflictRecords	= $this->userConflictRecordsByUserId($convention_season_slug, $userId);
					$resolveConflicts		= !empty($userConflictRecords);
				} catch (Exception $e) {
					$resolveConflicts = false;
				}

			} while ($resolveConflicts);

		} // end foreach userId

		$this->Flash->success('All individual conflicts resolved successfully.');
			/////////////////
			
		}
		else
		{
			// no conflict found
			//$this->Flash->error('Sorry, no conflict found.');
		}
		
		$ref = $this->request->getQuery('ref', 'precheck');
		$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug, '?' => ['ref' => $ref]]);
	}
	
	// Simple test endpoint to check basic functionality
	public function testconflicts($convention_season_slug=null) {
		echo "<h2>Test Conflicts Debug</h2>";
		echo "<p>Convention season slug: " . ($convention_season_slug ?? 'null') . "</p>";
		
		try {
			if (empty($convention_season_slug)) {
				echo "<p>ERROR: No slug provided</p>";
				exit;
			}
			
			$conventionSD = $this->Conventionseasons->find()
				->where(['Conventionseasons.slug' => $convention_season_slug])
				->contain(["Conventions"])
				->first();
				
			if (empty($conventionSD)) {
				echo "<p>ERROR: Convention season not found</p>";
				exit;
			}
			
			echo "<p>SUCCESS: Convention found: " . $conventionSD->Conventions->name . "</p>";
			
			$schedulingD = $this->Schedulings->find()
				->where(['Schedulings.conventionseasons_id' => $conventionSD->id])
				->first();
				
			if (empty($schedulingD)) {
				echo "<p>ERROR: No scheduling data found</p>";
			} else {
				echo "<p>SUCCESS: Scheduling data found</p>";
				echo "<p>Individual conflicts: " . ($schedulingD->conflict_user_ids ?? 'None') . "</p>";
				echo "<p>Group conflicts: " . ($schedulingD->conflict_user_ids_group ?? 'None') . "</p>";
			}
			
		} catch (Exception $e) {
			echo "<p>EXCEPTION: " . $e->getMessage() . "</p>";
		}
		
		exit;
	}
	
	// Debug version of resolve conflicts to identify issues
	public function debugresolveconflicts($convention_season_slug=null) {
		$debug_output = [];
		
		if (empty($convention_season_slug)) {
			$debug_output[] = 'Convention season slug is required.';
			echo '<h3>Debug Resolve Conflicts</h3><pre>' . implode("\n", $debug_output) . '</pre>';
			return;
		}
		
		try {
			$conventionSD = $this->Conventionseasons->find()
				->where(['Conventionseasons.slug' => $convention_season_slug])
				->contain(["Conventions"])
				->first();
				
			if (empty($conventionSD)) {
				$debug_output[] = 'Convention season not found';
			} else {
				$debug_output[] = 'Convention found: ' . $conventionSD->Conventions->name;
			}
			
			$schedulingD = $this->Schedulings->find()
				->where(['Schedulings.conventionseasons_id' => $conventionSD->id])
				->first();
				
			if (empty($schedulingD)) {
				$debug_output[] = 'No scheduling data found';
			} else {
				$debug_output[] = 'Scheduling data found';
				$debug_output[] = 'Conflict user IDs: ' . ($schedulingD->conflict_user_ids ?? 'None');
			}
			
			// Test method calls
			if (!empty($schedulingD->conflict_user_ids)) {
				$userIDs = explode(',', $schedulingD->conflict_user_ids);
				if (!empty($userIDs[0])) {
					try {
						$testRecords = $this->userConflictRecordsByUserId($convention_season_slug, $userIDs[0]);
						$debug_output[] = 'User conflict records retrieved successfully for user: ' . $userIDs[0];
						$debug_output[] = 'Records count: ' . count($testRecords);
					} catch (Exception $e) {
						$debug_output[] = 'Error calling userConflictRecordsByUserId: ' . $e->getMessage();
					}
				}
			} else {
				$debug_output[] = 'No conflicts to resolve';
			}
			
		} catch (Exception $e) {
			$debug_output[] = 'General error: ' . $e->getMessage();
		}
		
		echo '<h3>Debug Resolve Conflicts</h3><pre>' . implode("\n", $debug_output) . '</pre>';
		exit;
	}
	
	public function resolveconflictsgroup($convention_season_slug=null) {
		
		// Add validation for the slug parameter
		if (empty($convention_season_slug)) {
			$this->Flash->error('Convention season slug is required.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'index']);
			return;
		}
		
		$ref = $this->request->getQuery('ref', 'precheck');

		// get convention season details
		try {
			$conventionSD = $this->Conventionseasons->find()
				->where(['Conventionseasons.slug' => $convention_season_slug])
				->contain(["Conventions"])
				->first();
				
			if (empty($conventionSD)) {
				$this->Flash->error('Convention season not found.');
				$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
				return;
			}
		} catch (Exception $e) {
			$this->Flash->error('Database error: ' . $e->getMessage());
			$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			return;
		}
		
		try {
			$schedulingD = $this->Schedulings->find()
				->where(['Schedulings.conventionseasons_id' => $conventionSD->id])
				->first();
				
			if (empty($schedulingD)) {
				$this->Flash->error('No scheduling data found for this convention season.');
				$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
				return;
			}
		} catch (Exception $e) {
			$this->Flash->error('Error retrieving scheduling data: ' . $e->getMessage());
			$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			return;
		}
		
		if(!empty($schedulingD->conflict_user_ids_group))
		{
			try {
				$schIDSConflict = explode(",",$schedulingD->conflict_user_ids_group);
				shuffle($schIDSConflict);
				
				/////////////////
				$schedulingId = $schIDSConflict[0];
				
				$schedulingTimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $schedulingId])->first();
				
				if (empty($schedulingTimingsD)) {
					// Stale ID — remove it and continue resolving remaining entries
					$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$schedulingId]));
					if (count($nextSchIDSConflict)) {
						$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(',', $nextSchIDSConflict)], ['id' => $schedulingD->id]);
						$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug, '?' => ['ref' => $ref]]);
					} else {
						$this->Schedulings->updateAll(['conflict_user_ids_group' => NULL], ['id' => $schedulingD->id]);
						if ($ref === 'schedulecategory') {
							$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
						} else {
							$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
						}
					}
					return;
				}
				
				// Validate group user IDs
				if (empty($schedulingTimingsD->group_name_user_ids) || empty($schedulingTimingsD->group_name_opponent_user_ids)) {
					// No group data — skip this entry and continue
					$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$schedulingId]));
					if (count($nextSchIDSConflict)) {
						$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(',', $nextSchIDSConflict)], ['id' => $schedulingD->id]);
						$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug, '?' => ['ref' => $ref]]);
					} else {
						$this->Schedulings->updateAll(['conflict_user_ids_group' => NULL], ['id' => $schedulingD->id]);
						if ($ref === 'schedulecategory') {
							$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
						} else {
							$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
						}
					}
					return;
				}
				
				$groupUserIds = explode(',', $schedulingTimingsD->group_name_user_ids);
				$opponentIds = explode(',', $schedulingTimingsD->group_name_opponent_user_ids);

				$allUserIds = array_merge($groupUserIds, $opponentIds);

				$base_start_time = $schedulingTimingsD->start_time;
				$base_finish_time = $schedulingTimingsD->finish_time;
				$base_sch_date_time = $schedulingTimingsD->sch_date_time;
				
				// Validate that we have the required data
				if (empty($base_start_time) || empty($base_finish_time) || empty($base_sch_date_time)) {
					$this->Flash->error('Invalid scheduling timing data.');
					$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
					return;
				}

				try {
					$resolveConflict = $this->findNextTime($schedulingTimingsD, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds);
					
					if (empty($resolveConflict)) {
						$this->Flash->error('Unable to find alternative time slot for group conflict.');
						$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
						return;
					}
				
					$recordId 			= $resolveConflict->id;
					$start_time 		= $resolveConflict->start_time;
					$finish_time 		= $resolveConflict->finish_time;
					$sch_date_time 		= $resolveConflict->sch_date_time;
					
				} catch (Exception $e) {
					$this->Flash->error('Error finding alternative time slot: ' . $e->getMessage());
					$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
					return;
				}
			
			try {
				// Update the scheduling timing record
				$this->Schedulingtimings->updateAll(
					[
						'start_time'		=> $start_time,
						'finish_time'		=> $finish_time,
						'sch_date_time'		=> $sch_date_time
					]
					,
					[
						"id" => $recordId
					]);
					
				// Remove resolved conflict from the list
				$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$recordId]));
							
				// Update the scheduling record to remove the resolved conflict
				if(count($nextSchIDSConflict))
				{
					$this->Schedulings->updateAll(
					[
						'conflict_user_ids_group'		=> implode(",",$nextSchIDSConflict)
					]
					,
					[
						"id" => $schedulingD->id
					]);
				}
				else
				{
					$this->Schedulings->updateAll(
					[
						'conflict_user_ids_group'		=> NULL
					]
					,
					[
						"id" => $schedulingD->id
					]);
				}
				
				$this->Flash->success('Group conflict resolved successfully.');
				
			} catch (Exception $e) {
				$this->Flash->error('Error updating scheduling records: ' . $e->getMessage());
				$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
				return;
			}
			
		} catch (Exception $e) {
			$this->Flash->error('Error processing group conflicts: ' . $e->getMessage());
			$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			return;
		}
		}
		else
		{
			$this->Flash->success('No group conflicts to resolve.');
		}
		
		$ref = $this->request->getQuery('ref', 'precheck');
		// Re-check: if more group conflicts remain, loop back to resolve them all
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		if (!empty($schedulingD->conflict_user_ids_group)) {
			$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug, '?' => ['ref' => $ref]]);
		} elseif ($ref === 'schedulecategory') {
			$this->Flash->success('All conflicts resolved successfully.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
		} else {
			$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}
	}
	
	public function editschedulingtimings($convention_season_slug=null,$sch_auto_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
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
		
		// to fetch scheduling timings data and send to template
		$schedulingtimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $sch_auto_id])->contain(['Events','Conventionrooms'])->first();
		$this->set('schedulingtimingsD', $schedulingtimingsD);
		
        if ($this->request->is(['post', 'put'])) {
            
			//$this->prx($this->request->data['Schedulingtimings']);
			
			$data = $this->request->data['Schedulingtimings'];
			
			
			
			$new_start_time 			= $this->changeToMysqlTimeFormat($data['new_start_time']);
			$new_finish_time 			= $this->changeToMysqlTimeFormat($data['new_finish_time']);
			
			echo $new_start_time;
			echo '<br>';
			echo $new_finish_time;
			
			echo '<hr>';
			
			echo date("H:i:s", strtotime($schedulingtimingsD->start_time));
			echo '<br>';
			echo date("H:i:s", strtotime($schedulingtimingsD->finish_time));
			
			$flagUpdate = 1;
			
			// 1. If save start and finish time not entered
			if($new_start_time == date("H:i:s", strtotime($schedulingtimingsD->start_time))  && $new_finish_time == date("H:i:s", strtotime($schedulingtimingsD->finish_time)))
			{
				$flagUpdate = 0;
				$msgEdit = 'You entered same start and finish timings.';
			}
			else
			{
				// 2. Need to check that if room is free or not for this start and end time
				$condRoomC = array();
				$condRoomC[] =  "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."')";
				$condRoomC[] =  "(Schedulingtimings.room_id = '".$schedulingtimingsD->room_id."')";
				$condRoomC[] =  "(Schedulingtimings.day = '".$schedulingtimingsD->day."')";
				$condRoomC[] =  "('".$new_start_time."' < Schedulingtimings.finish_time AND '".$new_finish_time."' > Schedulingtimings.start_time)";
				
				$checkRoomBusy = $this->Schedulingtimings->find()
					->where($condRoomC)
					->first();
				if($checkRoomBusy)
				{
					$flagUpdate = 0;
					$msgEdit = 'Sorry, room is not free on selected timings.';
				}
				else
				{
					/* To check if there is judging breaks */
					if($schedulingD->judging_breaks_yes_no == 1)
					{
						// 1. Morning break timings - Apply check for 
						$judging_breaks_morning_break_starting_time = date("H:i:s",strtotime($schedulingD->judging_breaks_morning_break_starting_time));
						$judging_breaks_morning_break_finish_time 	= date("H:i:s",strtotime($schedulingD->judging_breaks_morning_break_finish_time));
						
						if( (strtotime($new_start_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($new_start_time)<=strtotime($judging_breaks_morning_break_finish_time)) || 
						(strtotime($new_finish_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($new_finish_time)<=strtotime($judging_breaks_morning_break_finish_time)))
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, time conflict in judges morning breaks.';
						}
						
						
						// 2. Afternoon break timings - Apply check for 
						$judging_breaks_afternoon_break_start_time = date("H:i:s",strtotime($schedulingD->judging_breaks_afternoon_break_start_time));
						$judging_breaks_afternoon_break_finish_time 	= date("H:i:s",strtotime($schedulingD->judging_breaks_afternoon_break_finish_time));
						
						if( (strtotime($new_start_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($new_start_time)<=strtotime($judging_breaks_afternoon_break_finish_time)) || 
						(strtotime($new_finish_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($new_finish_time)<=strtotime($judging_breaks_afternoon_break_finish_time)))
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, time conflict in judges afternoon breaks.';
						}
						
						
						/* To check here if sports day is there, then exclude that time - starts */
						if($schedulingD->sports_day_yes_no == 1)
						{
							$sports_day					= $schedulingD->sports_day;
							$sports_day_starting_time	= date("H:i:s",strtotime($schedulingD->sports_day_starting_time));
							$sports_day_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_finish_time));
							
							// to check if day match
							if($sports_day == $schedulingtimingsD->day)
							{
								// Now check TIMINGS
								if( (strtotime($new_start_time)>=strtotime($sports_day_starting_time) &&  strtotime($new_start_time)<=strtotime($sports_day_finish_time)) || 
								(strtotime($new_finish_time)>=strtotime($sports_day_starting_time) &&  strtotime($new_finish_time)<=strtotime($sports_day_finish_time)))
								{
									$flagUpdate = 0;
									$msgEdit = 'Sorry, time conflict in sports day timings.';
								}
							}
						}
						
						/* To check here if they are having more events after sport - starts */
						if($schedulingD->sports_day_having_events_after_sport_yes_no == 1)
						{
							$sports_day							= $schedulingD->sports_day;
							$sports_day_other_starting_time		= date("H:i:s",strtotime($schedulingD->sports_day_other_starting_time));
							$sports_day_other_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_other_finish_time));
							
							// to check if day match
							if($sports_day == $schedulingtimingsD->day)
							{
								// Now check TIMINGS
								if( (strtotime($new_start_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($new_start_time)<=strtotime($sports_day_other_finish_time)) || 
								(strtotime($new_finish_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($new_finish_time)<=strtotime($sports_day_other_finish_time)))
								{
									$flagUpdate = 0;
									$msgEdit = 'Sorry, time conflict in events of sports day timings.';
								}
							}
						}
						
						/* To check if user_id is having any game */
						$userId = $schedulingtimingsD->user_id;
						$checkUIDBusy = $this->Schedulingtimings->find()
							->where([
								'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
								'Schedulingtimings.day' => $schedulingtimingsD->day,
							])
							->andWhere(function ($exp) use ($new_start_time, $new_finish_time) {
								return $exp->add(
									"'$new_start_time' < Schedulingtimings.finish_time 
									 AND '$new_finish_time' > Schedulingtimings.start_time"
								);
							})
							->andWhere(function ($exp) use ($userId) {
								return $exp->or_([
									'Schedulingtimings.user_id' => $userId,
									'Schedulingtimings.user_id_opponent' => $userId,
									$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_user_ids)"),
									$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_opponent_user_ids)")
								]);
							})
							->count();
							
						if($checkUIDBusy > 0)
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, user is having any other game.';
						}
						
						
						
						
						/* To check if user_id_opponent is having any game */
						$userIdOpponent = $schedulingtimingsD->user_id_opponent;
						$checkUIDOppBusy = $this->Schedulingtimings->find()
							->where([
								'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
								'Schedulingtimings.day' => $schedulingtimingsD->day,
							])
							->andWhere(function ($exp) use ($new_start_time, $new_finish_time) {
								return $exp->add(
									"'$new_start_time' < Schedulingtimings.finish_time 
									 AND '$new_finish_time' > Schedulingtimings.start_time"
								);
							})
							->andWhere(function ($exp) use ($userIdOpponent) {
								return $exp->or_([
									'Schedulingtimings.user_id' => $userIdOpponent,
									'Schedulingtimings.user_id_opponent' => $userIdOpponent,
									$exp->add("FIND_IN_SET($userIdOpponent, Schedulingtimings.group_name_user_ids)"),
									$exp->add("FIND_IN_SET($userIdOpponent, Schedulingtimings.group_name_opponent_user_ids)")
								]);
							})
							->count();
						if($checkUIDOppBusy > 0)
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, opponent user is having any other game.';
						}
						
					}
					
				}
			}
			
			//echo $msgEdit;exit;
			
			if($flagUpdate>0)
			{
				// Update
				$schStartDate = date("Y-m-d",strtotime($schedulingtimingsD->sch_date_time));
				$this->Schedulingtimings->updateAll(
				[
					'start_time' 	=> date("H:i:s", strtotime($new_start_time)),
					'finish_time' 	=> date("H:i:s", strtotime($new_finish_time)),
					'sch_date_time' => $schStartDate.' '.date("H:i:s", strtotime($new_start_time)),
					'modified' 		=> date("Y-m-d H:i:s")
				],
				["id" => $sch_auto_id]);
				
				$this->Flash->success('Time updated successfully.');
				
				$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory',$convention_season_slug]);
			}
			else
			{
				$this->Flash->error($msgEdit);
				
				$this->redirect(['controller' => 'schedulings', 'action' => 'editschedulingtimings', $convention_season_slug,$sch_auto_id]);
			}
			
			
        }
		
    }
	
	
	/**
	 * Room Restrictions - Manage room day/time restrictions after schedule has been generated
	 */
	public function roomrestrictions($convention_season_slug=null) {
		$this->set('title', ADMIN_TITLE . 'Room Restrictions');
		$this->viewBuilder()->layout('admin');
		
		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		
		$this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// Handle POST - save room restriction
		if ($this->request->is('post')) {
			$data = $this->request->getData();
			$room_id = isset($data['room_id']) ? $data['room_id'] : null;
			
			if ($room_id) {
				$restricted_days = null;
				if (!empty($data['restricted_days'])) {
					if (is_array($data['restricted_days'])) {
						$restricted_days = implode(',', $data['restricted_days']);
					} else {
						$restricted_days = $data['restricted_days'];
					}
				}
				
				$restricted_start_time = null;
				if (!empty($data['restricted_start_time'])) {
					if (is_array($data['restricted_start_time'])) {
						$h = isset($data['restricted_start_time']['hour']) ? $data['restricted_start_time']['hour'] : '00';
						$m = isset($data['restricted_start_time']['minute']) ? $data['restricted_start_time']['minute'] : '00';
						$restricted_start_time = sprintf('%02d:%02d:00', $h, $m);
					} else {
						$restricted_start_time = $data['restricted_start_time'];
					}
				}
				$restricted_finish_time = null;
				if (!empty($data['restricted_finish_time'])) {
					if (is_array($data['restricted_finish_time'])) {
						$h = isset($data['restricted_finish_time']['hour']) ? $data['restricted_finish_time']['hour'] : '00';
						$m = isset($data['restricted_finish_time']['minute']) ? $data['restricted_finish_time']['minute'] : '00';
						$restricted_finish_time = sprintf('%02d:%02d:00', $h, $m);
					} else {
						$restricted_finish_time = $data['restricted_finish_time'];
					}
				}
				
				$this->Conventionrooms->updateAll(
					[
						'restricted_days' => $restricted_days,
						'restricted_start_time' => $restricted_start_time,
						'restricted_finish_time' => $restricted_finish_time,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => $room_id]
				);
				
				$this->Flash->success('Room restriction updated successfully.');
			}
			
			$this->redirect(['action' => 'roomrestrictions', $convention_season_slug]);
		}
		
		// Get all rooms for this convention
		$convRooms = $this->Conventionrooms->find()
			->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
			->order(['Conventionrooms.room_name' => 'ASC'])
			->all();
		$this->set('convRooms', $convRooms);
		
		// Get count of scheduled events per room for this convention season
		$roomScheduleCounts = [];
		foreach($convRooms as $room) {
			$count = $this->Schedulingtimings->find()
				->where(['Schedulingtimings.room_id' => $room->id, 'Schedulingtimings.conventionseasons_id' => $conventionSD->id])
				->count();
			$roomScheduleCounts[$room->id] = $count;
			
			// Get days this room is scheduled on
			$days = $this->Schedulingtimings->find()
				->select(['day'])
				->distinct(['day'])
				->where(['Schedulingtimings.room_id' => $room->id, 'Schedulingtimings.conventionseasons_id' => $conventionSD->id])
				->all();
			$roomScheduleDays[$room->id] = [];
			foreach($days as $d) {
				$roomScheduleDays[$room->id][] = $d->day;
			}
		}
		$this->set('roomScheduleCounts', $roomScheduleCounts);
		$this->set('roomScheduleDays', $roomScheduleDays);
	}
	
	
	/**
	 * Save room restriction changes (POST)
	 */
	public function saveroomrestriction($convention_season_slug=null, $room_id=null) {
		$this->autoRender = false;
		
		try {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
			
			if ($this->request->is(['post', 'put'])) {
				$data = $this->request->getData();
				
				$restricted_days = null;
				if (!empty($data['restricted_days'])) {
					if (is_array($data['restricted_days'])) {
						$restricted_days = implode(',', $data['restricted_days']);
					} else {
						$restricted_days = $data['restricted_days'];
					}
				}
				
				$restricted_start_time = null;
				if (!empty($data['restricted_start_time'])) {
					if (is_array($data['restricted_start_time'])) {
						$h = isset($data['restricted_start_time']['hour']) ? $data['restricted_start_time']['hour'] : '00';
						$m = isset($data['restricted_start_time']['minute']) ? $data['restricted_start_time']['minute'] : '00';
						$restricted_start_time = sprintf('%02d:%02d:00', $h, $m);
					} else {
						$restricted_start_time = $data['restricted_start_time'];
					}
				}
				$restricted_finish_time = null;
				if (!empty($data['restricted_finish_time'])) {
					if (is_array($data['restricted_finish_time'])) {
						$h = isset($data['restricted_finish_time']['hour']) ? $data['restricted_finish_time']['hour'] : '00';
						$m = isset($data['restricted_finish_time']['minute']) ? $data['restricted_finish_time']['minute'] : '00';
						$restricted_finish_time = sprintf('%02d:%02d:00', $h, $m);
					} else {
						$restricted_finish_time = $data['restricted_finish_time'];
					}
				}
				
				$this->Conventionrooms->updateAll(
					[
						'restricted_days' => $restricted_days,
						'restricted_start_time' => $restricted_start_time,
						'restricted_finish_time' => $restricted_finish_time,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => $room_id]
				);
				
				$this->Flash->success('Room restriction updated successfully.');
			}
			
			return $this->redirect(['controller' => 'schedulings', 'action' => 'roomrestrictions', $convention_season_slug]);
		} catch (\Exception $e) {
			\Cake\Log\Log::error('saveroomrestriction error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
			$this->Flash->error('Error saving restriction: ' . $e->getMessage());
			return $this->redirect(['controller' => 'schedulings', 'action' => 'roomrestrictions', $convention_season_slug]);
		}
	}
	
	
	/**
	 * Re-apply room restrictions to existing schedule
	 * Moves events off restricted days/times by finding the next available slot
	 */
	public function reapplyroomrestrictions($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		
		$normal_starting_time = date("H:i:s", strtotime($schedulingD->normal_starting_time));
		$normal_finish_time = date("H:i:s", strtotime($schedulingD->normal_finish_time));
		$lunch_time_start = date("H:i:s", strtotime($schedulingD->lunch_time_start));
		$lunch_time_end = date("H:i:s", strtotime($schedulingD->lunch_time_end));
		
		// Get all rooms with restrictions
		$restrictedRooms = $this->Conventionrooms->find()
			->where([
				'Conventionrooms.convention_id' => $conventionSD->convention_id,
				'Conventionrooms.restricted_days IS NOT' => null
			])
			->all();
		
		$movedCount = 0;
		
		foreach($restrictedRooms as $room) {
			$allowedDays = explode(',', $room->restricted_days);
			
			// Find all scheduled events in this room that violate day restrictions
			$violatingEvents = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.room_id' => $room->id,
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.day NOT IN' => $allowedDays
				])
				->all();
			
			foreach($violatingEvents as $event) {
				// Calculate event duration
				$duration = strtotime($event->finish_time) - strtotime($event->start_time);
				$durationMinutes = $duration / 60;
				
				// Find the next available slot on an allowed day in this room
				$newSlot = $this->findNextAvailableSlot(
					$room->id,
					$conventionSD->id,
					$allowedDays,
					$durationMinutes,
					$normal_starting_time,
					$normal_finish_time,
					$lunch_time_start,
					$lunch_time_end,
					$schedulingD
				);
				
				if ($newSlot) {
					$this->Schedulingtimings->updateAll(
						[
							'day' => $newSlot['day'],
							'start_time' => $newSlot['start_time'],
							'finish_time' => $newSlot['finish_time'],
							'sch_date_time' => $newSlot['sch_date_time'],
							'modified' => date('Y-m-d H:i:s')
						],
						['id' => $event->id]
					);
					$movedCount++;
				}
			}
		}
		
		if ($movedCount > 0) {
			$this->Flash->success($movedCount . ' scheduled event(s) moved to comply with room restrictions.');
		} else {
			$this->Flash->success('All scheduled events already comply with room restrictions.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'roomrestrictions', $convention_season_slug]);
	}
	
	
	/**
	 * Find the next available time slot on allowed days for a room
	 */
	private function findNextAvailableSlot($room_id, $conventionseason_id, $allowedDays, $durationMinutes, $normal_start, $normal_finish, $lunch_start, $lunch_end, $schedulingD) {
		
		// Get the scheduling start date
		$start_date = date('Y-m-d', strtotime($schedulingD->start_date));
		$number_of_days = $schedulingD->number_of_days;
		
		// Try each day of the convention
		for ($dayOffset = 0; $dayOffset < $number_of_days; $dayOffset++) {
			$testDate = date('Y-m-d', strtotime($start_date . " +{$dayOffset} days"));
			$testDay = date('l', strtotime($testDate));
			
			// Skip if this day isn't in the allowed days
			if (!in_array($testDay, $allowedDays)) {
				continue;
			}
			
			// Get all existing bookings for this room on this day
			$existingBookings = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.room_id' => $room_id,
					'Schedulingtimings.conventionseasons_id' => $conventionseason_id,
					'Schedulingtimings.day' => $testDay
				])
				->order(['Schedulingtimings.start_time' => 'ASC'])
				->all();
			
			// Try to find a gap
			$candidate_start = $normal_start;
			
			for ($attempt = 0; $attempt < 100; $attempt++) {
				$candidate_finish = date('H:i:s', strtotime("+{$durationMinutes} minutes", strtotime($candidate_start)));
				
				// Check if we've exceeded the day's finish time
				if (strtotime($candidate_finish) > strtotime($normal_finish)) {
					break; // Move to next day
				}
				
				// Skip lunch time
				if ((strtotime($candidate_start) >= strtotime($lunch_start) && strtotime($candidate_start) < strtotime($lunch_end)) ||
					(strtotime($candidate_finish) > strtotime($lunch_start) && strtotime($candidate_finish) <= strtotime($lunch_end))) {
					$candidate_start = $lunch_end;
					continue;
				}
				
				// Check if slot conflicts with existing bookings
				$conflict = false;
				foreach ($existingBookings as $booking) {
					$bStart = date('H:i:s', strtotime($booking->start_time));
					$bFinish = date('H:i:s', strtotime($booking->finish_time));
					
					if (strtotime($candidate_start) < strtotime($bFinish) && strtotime($candidate_finish) > strtotime($bStart)) {
						$conflict = true;
						// Jump past this booking
						$candidate_start = date('H:i:s', strtotime('+1 minutes', strtotime($bFinish)));
						break;
					}
				}
				
				if (!$conflict) {
					return [
						'day' => $testDay,
						'start_time' => $candidate_start,
						'finish_time' => $candidate_finish,
						'sch_date_time' => $testDate . ' ' . $candidate_start
					];
				}
			}
		}
		
		return null; // No slot found
	}

}

?>
