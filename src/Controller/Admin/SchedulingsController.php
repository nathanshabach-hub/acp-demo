<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Exception;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class SchedulingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
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
		$this->loadModel("Eventcategories");
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

	private function getEventBucketRules() {
		$rulesPath = ROOT . DS . 'config' . DS . 'event_bucket_rules.php';
		if (file_exists($rulesPath)) {
			$rules = include $rulesPath;
			if (is_array($rules)) {
				return $rules;
			}
		}

		return [
			'group_order' => ['Academics', 'Music Combined', 'Music Instrumental', 'Music Vocal', 'Platform', 'Scripture', 'Sports'],
			'name_rules' => [],
			'category_contains_rules' => [],
			'default_bucket' => 'Academics',
		];
	}

	private function classifyEventTypeGroup($eventName, $eventCode, $categoryName, $rules = null) {
		if ($rules === null) {
			$rules = $this->getEventBucketRules();
		}

		$name = strtolower((string)$eventName);
		$code = strtolower((string)$eventCode);
		$cat = strtolower((string)$categoryName);

		foreach ((array)$rules['name_rules'] as $rule) {
			if (empty($rule['bucket']) || empty($rule['pattern'])) {
				continue;
			}

			$source = !empty($rule['apply_to']) && $rule['apply_to'] === 'code' ? $code : $name;
			if (@preg_match($rule['pattern'], $source)) {
				if (preg_match($rule['pattern'], $source)) {
					return (string)$rule['bucket'];
				}
			}
		}

		foreach ((array)$rules['category_contains_rules'] as $rule) {
			if (empty($rule['bucket']) || empty($rule['contains'])) {
				continue;
			}
			if (strpos($cat, strtolower((string)$rule['contains'])) !== false) {
				return (string)$rule['bucket'];
			}
		}

		return !empty($rules['default_bucket']) ? (string)$rules['default_bucket'] : 'Academics';
	}

	private function getEventCategoryNameMap() {
		$eventCategoryNameById = [];
		$eventCategoryRows = $this->Eventcategories->find()
			->select(['id', 'name'])
			->enableHydration(false)
			->toArray();

		foreach ($eventCategoryRows as $catRow) {
			$eventCategoryNameById[(int)$catRow['id']] = (string)$catRow['name'];
		}

		return $eventCategoryNameById;
	}

	private function ensureSchedulingAutoassignRunsTable() {
		try {
			$connection = ConnectionManager::get('default');
			$connection->execute(
				"CREATE TABLE IF NOT EXISTS scheduling_autoassign_runs (
					id INT AUTO_INCREMENT PRIMARY KEY,
					conventionseason_id INT NOT NULL,
					schedule_category INT NULL,
					assigned_count INT NOT NULL DEFAULT 0,
					remaining_count INT NOT NULL DEFAULT 0,
					overflow_before INT NOT NULL DEFAULT 0,
					overflow_after INT NOT NULL DEFAULT 0,
					filter_days VARCHAR(255) NULL,
					filter_rooms TEXT NULL,
					trigger_source VARCHAR(64) NOT NULL DEFAULT 'manual',
					created DATETIME NOT NULL,
					INDEX idx_csid_created (conventionseason_id, created),
					INDEX idx_csid_category (conventionseason_id, schedule_category)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
			);
		} catch (Exception $e) {
			// Keep schedulecategory resilient if DDL fails.
		}
	}

	private function getOverflowTrendRows($conventionSeasonId, $limit = 12) {
		$this->ensureSchedulingAutoassignRunsTable();
		try {
			$connection = ConnectionManager::get('default');
			$rows = $connection->execute(
				"SELECT id, schedule_category, assigned_count, remaining_count, overflow_before, overflow_after, filter_days, filter_rooms, trigger_source, created
				 FROM scheduling_autoassign_runs
				 WHERE conventionseason_id = :csid
				 ORDER BY id DESC
				 LIMIT :row_limit",
				['csid' => (int)$conventionSeasonId, 'row_limit' => (int)$limit],
				['csid' => 'integer', 'row_limit' => 'integer']
			)->fetchAll('assoc');

			return is_array($rows) ? $rows : [];
		} catch (Exception $e) {
			return [];
		}
	}

	private function buildScheduleHealthMetrics($conventionSeasonId, $schedulingD) {
		$metrics = [
			'room_conflicts' => 0,
			'same_category_participant_conflicts' => 0,
			'cross_category_participant_conflicts' => 0,
			'room_utilization' => [],
			'average_room_utilization_pct' => 0.0,
		];

		if (empty($schedulingD)) {
			return $metrics;
		}

		try {
			$connection = ConnectionManager::get('default');

			$roomConflictRow = $connection->execute(
				"SELECT COUNT(*) AS cnt
				 FROM schedulingtimings a
				 JOIN schedulingtimings b
				   ON a.conventionseasons_id=b.conventionseasons_id
				  AND a.id < b.id
				  AND a.day=b.day
				  AND a.room_id=b.room_id
				  AND a.start_time < b.finish_time
				  AND a.finish_time > b.start_time
				 WHERE a.conventionseasons_id = :csid
				   AND a.day IS NOT NULL
				   AND a.room_id IS NOT NULL
				   AND a.start_time IS NOT NULL
				   AND a.finish_time IS NOT NULL
				   AND b.start_time IS NOT NULL
				   AND b.finish_time IS NOT NULL
				   AND IFNULL(a.is_bye,0) <> 1
				   AND IFNULL(b.is_bye,0) <> 1
				   AND NOT (
				     a.event_id = b.event_id
				     AND a.start_time = b.start_time
				     AND a.finish_time = b.finish_time
				   )",
				['csid' => (int)$conventionSeasonId],
				['csid' => 'integer']
			)->fetch('assoc');
			$metrics['room_conflicts'] = !empty($roomConflictRow['cnt']) ? (int)$roomConflictRow['cnt'] : 0;

			$sameCategoryRow = $connection->execute(
				"SELECT COUNT(*) AS cnt
				 FROM (
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				   UNION ALL
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id_opponent AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id_opponent IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				 ) a
				 JOIN (
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				   UNION ALL
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id_opponent AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id_opponent IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				 ) b
				   ON a.timing_id < b.timing_id
				  AND a.participant_id = b.participant_id
				  AND a.schedule_category = b.schedule_category
				  AND a.day = b.day
				  AND a.start_time < b.finish_time
				  AND a.finish_time > b.start_time
				  AND IFNULL(a.event_id_number,'') <> IFNULL(b.event_id_number,'')
				 WHERE a.participant_id > 0",
				['csid' => (int)$conventionSeasonId],
				['csid' => 'integer']
			)->fetch('assoc');
			$metrics['same_category_participant_conflicts'] = !empty($sameCategoryRow['cnt']) ? (int)$sameCategoryRow['cnt'] : 0;

			$crossCategoryRow = $connection->execute(
				"SELECT COUNT(*) AS cnt
				 FROM (
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				   UNION ALL
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id_opponent AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id_opponent IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				 ) a
				 JOIN (
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				   UNION ALL
				   SELECT id AS timing_id, schedule_category, day, start_time, finish_time, event_id_number, user_id_opponent AS participant_id
				   FROM schedulingtimings
				   WHERE conventionseasons_id = :csid AND user_id_opponent IS NOT NULL AND day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND IFNULL(is_bye,0) <> 1 AND IFNULL(user_type,'') <> 'School'
				 ) b
				   ON a.timing_id < b.timing_id
				  AND a.participant_id = b.participant_id
				  AND a.schedule_category <> b.schedule_category
				  AND a.day = b.day
				  AND a.start_time < b.finish_time
				  AND a.finish_time > b.start_time
				  AND IFNULL(a.event_id_number,'') <> IFNULL(b.event_id_number,'')
				 WHERE a.participant_id > 0",
				['csid' => (int)$conventionSeasonId],
				['csid' => 'integer']
			)->fetch('assoc');
			$metrics['cross_category_participant_conflicts'] = !empty($crossCategoryRow['cnt']) ? (int)$crossCategoryRow['cnt'] : 0;

			$roomRows = $this->Schedulingtimings->find()
				->select(['room_id'])
				->where([
					'Schedulingtimings.conventionseasons_id' => (int)$conventionSeasonId,
					'Schedulingtimings.day IN' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
					'Schedulingtimings.room_id IS NOT' => null,
					'Schedulingtimings.start_time IS NOT' => null,
					'Schedulingtimings.finish_time IS NOT' => null,
				])
				->group(['room_id'])
				->enableHydration(false)
				->toArray();

			$roomIds = array_map('intval', array_column($roomRows, 'room_id'));
			if (!empty($roomIds)) {
				$rooms = $this->Conventionrooms->find()->where(['Conventionrooms.id IN' => $roomIds])->all()->toArray();
				$roomNameMap = [];
				foreach ($rooms as $room) {
					$roomNameMap[(int)$room->id] = $room->room_name;
				}

				$firstDay = !empty($schedulingD->first_day) ? $schedulingD->first_day : 'Monday';
				$capacityPerDay = 0;
				$lunchStart = !empty($schedulingD->lunch_time_start) ? date('H:i:s', strtotime($schedulingD->lunch_time_start)) : null;
				$lunchEnd = !empty($schedulingD->lunch_time_end) ? date('H:i:s', strtotime($schedulingD->lunch_time_end)) : null;

				foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday'] as $dayName) {
					$dayStart = date('H:i:s', strtotime($schedulingD->normal_starting_time));
					$dayFinish = date('H:i:s', strtotime($schedulingD->normal_finish_time));
					if ((int)$schedulingD->starting_different_time_first_day_yes_no === 1 && $dayName === $firstDay) {
						$dayStart = date('H:i:s', strtotime($schedulingD->different_first_day_start_time));
						$dayFinish = date('H:i:s', strtotime($schedulingD->different_first_day_end_time));
					}
					$minutes = max(0, (int)round((strtotime($dayFinish) - strtotime($dayStart)) / 60));
					if (!empty($lunchStart) && !empty($lunchEnd) && strtotime($lunchEnd) > strtotime($lunchStart)) {
						$minutes -= (int)round((strtotime($lunchEnd) - strtotime($lunchStart)) / 60);
					}
					$capacityPerDay += max(0, $minutes);
				}

				$totalPct = 0.0;
				$roomCount = 0;
				foreach ($roomIds as $roomId) {
					$usedRow = $connection->execute(
						"SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_time, finish_time)), 0) AS used_minutes
						 FROM schedulingtimings
						 WHERE conventionseasons_id = :csid
						   AND room_id = :rid
						   AND day IN ('Monday','Tuesday','Wednesday','Thursday')
						   AND start_time IS NOT NULL
						   AND finish_time IS NOT NULL",
						['csid' => (int)$conventionSeasonId, 'rid' => (int)$roomId],
						['csid' => 'integer', 'rid' => 'integer']
					)->fetch('assoc');

					$minutesUsed = !empty($usedRow['used_minutes']) ? (int)$usedRow['used_minutes'] : 0;
					$utilPct = $capacityPerDay > 0 ? round(($minutesUsed / $capacityPerDay) * 100, 1) : 0.0;
					$metrics['room_utilization'][] = [
						'room_id' => (int)$roomId,
						'room_name' => isset($roomNameMap[(int)$roomId]) ? $roomNameMap[(int)$roomId] : ('Room '.$roomId),
						'minutes_used' => $minutesUsed,
						'capacity_minutes' => $capacityPerDay,
						'utilization_pct' => $utilPct,
					];
					$totalPct += $utilPct;
					$roomCount++;
				}

				if ($roomCount > 0) {
					$metrics['average_room_utilization_pct'] = round($totalPct / $roomCount, 1);
				}
			}
		} catch (Exception $e) {
			return $metrics;
		}

		return $metrics;
	}

    public function precheck($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');
		
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
		$result = $this->executePrecheckEvents($conventionSD);

		if ($result['ok']) {
			$this->Flash->success($result['message']);
		} else {
			$this->Flash->error($result['message']);
		}

		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function prechecklocations($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$result = $this->executePrecheckLocations($conventionSD);

		if ($result['ok']) {
			$this->Flash->success($result['message']);
		} else {
			$this->Flash->error($result['message']);
		}

		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckregistrations($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$result = $this->executePrecheckRegistrations($conventionSD);

		if ($result['ok']) {
			$this->Flash->success($result['message']);
		} else {
			$this->Flash->error($result['message']);
		}

		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckstudents($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$result = $this->executePrecheckStudents($conventionSD);

		if ($result['ok']) {
			$this->Flash->success($result['message']);
		} else {
			$this->Flash->error($result['message']);
		}

		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }

	public function runallprechecks($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()
			->where(['Conventionseasons.slug' => $convention_season_slug])
			->contain(["Conventions"])
			->first();

		if (!$conventionSD) {
			$this->Flash->error('Invalid convention season.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
		}

		$results = [
			'events' => $this->executePrecheckEvents($conventionSD),
			'locations' => $this->executePrecheckLocations($conventionSD),
			'registrations' => $this->executePrecheckRegistrations($conventionSD),
			'students' => $this->executePrecheckStudents($conventionSD),
		];

		$passed = 0;
		$failedLabels = [];
		foreach ($results as $label => $result) {
			if (!empty($result['ok'])) {
				$passed++;
			} else {
				$failedLabels[] = ucfirst($label);
			}
		}

		if ($passed === 4) {
			$this->Flash->success('All pre-checks passed successfully (4/4).');
		} else {
			$this->Flash->error('Pre-check run complete. Passed: '.$passed.'/4. Failed: '.implode(', ', $failedLabels).'.');
		}

		return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
	}

	private function executePrecheckEvents($conventionSD) {
		if (!$conventionSD) {
			return ['ok' => false, 'message' => 'Invalid convention season.'];
		}

		$cntrPreCheckEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])
			->contain(['Events'])
			->all();

		foreach($conventionSEventsList as $convevPreCheck) {
			if($convevPreCheck->Events['needs_schedule'] == 1) {
				$cntrPreCheckEvents++;
			}
		}

		if($cntrPreCheckEvents > 0) {
			$this->Schedulings->updateAll(
				['precheck_events' => 1,'total_events_found' => $cntrPreCheckEvents,'modified' => date('Y-m-d H:i:s')],
				["conventionseasons_id" => $conventionSD->id]
			);

			return ['ok' => true, 'message' => 'Total event found: '.$cntrPreCheckEvents, 'count' => $cntrPreCheckEvents];
		}

		$this->Schedulings->updateAll(
			['precheck_events' => 0,'total_events_found' => NULL,'modified' => date('Y-m-d H:i:s')],
			["conventionseasons_id" => $conventionSD->id]
		);

		return ['ok' => false, 'message' => 'Sorry no event found for this convention season.', 'count' => 0];
	}

	private function executePrecheckLocations($conventionSD) {
		if (!$conventionSD) {
			return ['ok' => false, 'message' => 'Invalid convention season.'];
		}

		$conventionRoomsTotal = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])->count();
		if($conventionRoomsTotal <= 0) {
			$this->Schedulings->updateAll(
				['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')],
				["conventionseasons_id" => $conventionSD->id]
			);
			return ['ok' => false, 'message' => 'Sorry no location found for this convention.', 'count' => 0];
		}

		$cntrConvSeasonTotalEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])
			->contain(['Events'])
			->all();

		foreach($conventionSEventsList as $convEv) {
			if($convEv->Events['needs_schedule'] == 1) {
				$cntrConvSeasonTotalEvents++;
			}
		}

		$roomEventsArr = array();
		$convRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($convRoomEvents as $convroomev) {
			$roomEventIDSExplode = explode(",",$convroomev->event_ids);
			foreach($roomEventIDSExplode as $eventidexplode) {
				if(!in_array($eventidexplode,(array)$roomEventsArr)) {
					$roomEventsArr[] = $eventidexplode;
				}
			}
		}

		if(count((array)$roomEventsArr) < $cntrConvSeasonTotalEvents) {
			$missingCount = $cntrConvSeasonTotalEvents - count((array)$roomEventsArr);
			$this->Schedulings->updateAll(
				['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')],
				["conventionseasons_id" => $conventionSD->id]
			);

			return ['ok' => false, 'message' => 'Sorry, '.$missingCount.' event(s) not assigned to any room. Please assign.', 'count' => $conventionRoomsTotal];
		}

		$this->Schedulings->updateAll(
			['precheck_locations' => 1,'total_locations_found' => $conventionRoomsTotal,'modified' => date('Y-m-d H:i:s')],
			["conventionseasons_id" => $conventionSD->id]
		);

		return ['ok' => true, 'message' => 'Total locations found: '.$conventionRoomsTotal, 'count' => $conventionRoomsTotal];
	}

	private function executePrecheckRegistrations($conventionSD) {
		if (!$conventionSD) {
			return ['ok' => false, 'message' => 'Invalid convention season.'];
		}

		$conventionRegCount = $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->count();
		if($conventionRegCount > 0) {
			$this->Schedulings->updateAll(
				['precheck_registrations' => 1,'total_registrations_found' => $conventionRegCount,'modified' => date('Y-m-d H:i:s')],
				["conventionseasons_id" => $conventionSD->id]
			);

			return ['ok' => true, 'message' => 'Total registrations found: '.$conventionRegCount, 'count' => $conventionRegCount];
		}

		$this->Schedulings->updateAll(
			['precheck_registrations' => 0,'total_registrations_found' => NULL,'modified' => date('Y-m-d H:i:s')],
			["conventionseasons_id" => $conventionSD->id]
		);

		return ['ok' => false, 'message' => 'Sorry no registration found for this convention.', 'count' => 0];
	}

	private function executePrecheckStudents($conventionSD) {
		if (!$conventionSD) {
			return ['ok' => false, 'message' => 'Invalid convention season.'];
		}

		$studentsRegCount = $this->Conventionregistrationstudents->find()->where([
			'Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,
			'Conventionregistrationstudents.season_id' => $conventionSD->season_id,
			'Conventionregistrationstudents.season_year' => $conventionSD->season_year
		])->count();

		if($studentsRegCount > 0) {
			$this->Schedulings->updateAll(
				['precheck_students' => 1,'total_students_found' => $studentsRegCount,'modified' => date('Y-m-d H:i:s')],
				["conventionseasons_id" => $conventionSD->id]
			);

			return ['ok' => true, 'message' => 'Total students found: '.$studentsRegCount, 'count' => $studentsRegCount];
		}

		$this->Schedulings->updateAll(
			['precheck_students' => 0,'total_students_found' => NULL,'modified' => date('Y-m-d H:i:s')],
			["conventionseasons_id" => $conventionSD->id]
		);

		return ['ok' => false, 'message' => 'Sorry no stuednts found for this convention.', 'count' => 0];
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
        $this->viewBuilder()->setLayout('admin');
		
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
		if (!$schedulingD) {
			// No scheduling record yet — precheck creates it; redirect there first
			return $this->redirect(['action' => 'precheck', $convention_season_slug]);
		}
		$this->set('schedulingD', $schedulingD);
		
		$schedulings = $this->Schedulings->get($schedulingD->id);
        if ($this->request->is(['post', 'put'])) {
			$requestData = $this->request->getData();
			$data = $this->Schedulings->patchEntity($schedulings, $requestData);
			
			if (count($data->getErrors()) == 0) {
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
				
				// Backward compatibility: if legacy field is posted, map it to persisted buffer_minutes.
				if ((isset($data->settling_time_minutes) && $data->settling_time_minutes !== '' && $data->settling_time_minutes !== null)
					&& (!isset($data->buffer_minutes) || $data->buffer_minutes === '' || $data->buffer_minutes === null)) {
					$data->buffer_minutes = (int)$data->settling_time_minutes;
				}

				// Settling/buffer time defaults to 15 if not set.
				if(!isset($data->buffer_minutes) || $data->buffer_minutes === '' || $data->buffer_minutes === null) {
					$data->buffer_minutes = 15;
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
        $this->viewBuilder()->setLayout('admin');
		
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

		// Read-only day load summary to help balance schedule distribution across convention days.
		$dayLoadRows = [];
		$dayLoadMeta = [
			'total_slots' => 0,
			'target_slots_per_day' => 0,
			'day_count' => 0,
		];

		$scheduleRows = $this->Schedulingtimings->find()
			->select([
				'sch_date_time',
				'day',
				'start_time',
				'finish_time',
				'event_id',
				'room_id',
				'user_id',
				'user_id_opponent',
				'group_name_user_ids',
				'group_name_opponent_user_ids',
			])
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category IN' => [1,2,3,4],
				'Schedulingtimings.sch_date_time IS NOT' => null,
			])
			->order(['Schedulingtimings.sch_date_time' => 'ASC'])
			->enableHydration(false)
			->toArray();

		$dayMap = [];
		$allDayEventIds = [];
		foreach ($scheduleRows as $row) {
			$dateKey = date('Y-m-d', strtotime($row['sch_date_time']));
			if (empty($dateKey) || $dateKey === '1970-01-01') {
				continue;
			}

			if (!isset($dayMap[$dateKey])) {
				$dayMap[$dateKey] = [
					'date' => $dateKey,
					'day_name' => !empty($row['day']) ? (string)$row['day'] : date('l', strtotime($dateKey)),
					'sessions' => 0,
					'participant_slots' => 0,
					'events' => [],
					'event_slots' => [],
					'rooms' => [],
					'rows' => [],
				];
			}

			$dayMap[$dateKey]['sessions']++;

			$eventId = (int)$row['event_id'];
			if ($eventId > 0) {
				$dayMap[$dateKey]['events'][$eventId] = true;
				$allDayEventIds[$eventId] = true;
			}

			$roomId = (int)$row['room_id'];
			if ($roomId > 0) {
				$dayMap[$dateKey]['rooms'][$roomId] = true;
			}

			$dayMap[$dateKey]['rows'][] = [
				'room_id' => $roomId,
				'start_time' => !empty($row['start_time']) ? (string)$row['start_time'] : '',
				'finish_time' => !empty($row['finish_time']) ? (string)$row['finish_time'] : '',
			];

			$rowSlots = 0;
			if ((int)$row['user_id'] > 0) {
				$rowSlots++;
			}
			if ((int)$row['user_id_opponent'] > 0) {
				$rowSlots++;
			}

			if (!empty($row['group_name_user_ids'])) {
				$ids = array_filter(array_map('trim', explode(',', (string)$row['group_name_user_ids'])));
				$ids = array_filter($ids, function($id) { return ctype_digit($id) && (int)$id > 0; });
				$rowSlots += count(array_unique($ids));
			}
			if (!empty($row['group_name_opponent_user_ids'])) {
				$ids = array_filter(array_map('trim', explode(',', (string)$row['group_name_opponent_user_ids'])));
				$ids = array_filter($ids, function($id) { return ctype_digit($id) && (int)$id > 0; });
				$rowSlots += count(array_unique($ids));
			}

			if ($rowSlots <= 0) {
				$rowSlots = 1;
			}

			$dayMap[$dateKey]['participant_slots'] += $rowSlots;
			if ($eventId > 0) {
				if (!isset($dayMap[$dateKey]['event_slots'][$eventId])) {
					$dayMap[$dateKey]['event_slots'][$eventId] = 0;
				}
				$dayMap[$dateKey]['event_slots'][$eventId] += $rowSlots;
			}
			$dayLoadMeta['total_slots'] += $rowSlots;
		}

		ksort($dayMap);

		$eventLabelMap = [];
		$eventIdsForLabels = array_keys($allDayEventIds);
		if (!empty($eventIdsForLabels)) {
			$eventRows = $this->Events->find()
				->select(['id', 'event_id_number', 'event_name'])
				->where(['Events.id IN' => $eventIdsForLabels])
				->enableHydration(false)
				->toArray();

			foreach ($eventRows as $evr) {
				$evId = (int)$evr['id'];
				$evCode = trim((string)$evr['event_id_number']);
				$evName = trim((string)$evr['event_name']);
				if ($evCode !== '') {
					$eventLabelMap[$evId] = $evCode.' - '.$evName;
				} else {
					$eventLabelMap[$evId] = $evName;
				}
			}
		}

		$dayLoadMeta['day_count'] = count($dayMap);
		if ($dayLoadMeta['day_count'] > 0) {
			$dayLoadMeta['target_slots_per_day'] = round($dayLoadMeta['total_slots'] / $dayLoadMeta['day_count'], 2);
		}

		$seasonRoomIdRows = $this->Conventionseasonroomevents->find()
			->select(['room_id'])
			->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])
			->group(['room_id'])
			->enableHydration(false)
			->toArray();
		$seasonRoomIds = array_values(array_unique(array_map(function($row) {
			return isset($row['room_id']) ? (int)$row['room_id'] : 0;
		}, (array)$seasonRoomIdRows)));
		$seasonRoomIds = array_values(array_filter($seasonRoomIds, function($id) {
			return $id > 0;
		}));

		// Fallback: if no season room events are configured yet, use all convention rooms.
		if (empty($seasonRoomIds)) {
			$allRoomIdRows = $this->Conventionrooms->find()
				->select(['id'])
				->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
				->enableHydration(false)
				->toArray();
			$seasonRoomIds = array_values(array_map(function($row) {
				return (int)$row['id'];
			}, (array)$allRoomIdRows));
		}

		$seasonRoomScope = array_fill_keys($seasonRoomIds, true);
		$totalRoomCount = count($seasonRoomIds);
		$schedulingConfig = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$dayStartTime = !empty($schedulingConfig->normal_starting_time) ? date('H:i:s', strtotime($schedulingConfig->normal_starting_time)) : '08:30:00';
		$dayFinishTime = !empty($schedulingConfig->normal_finish_time) ? date('H:i:s', strtotime($schedulingConfig->normal_finish_time)) : '17:30:00';

		if (strtotime($dayFinishTime) <= strtotime($dayStartTime)) {
			$dayStartTime = '08:30:00';
			$dayFinishTime = '17:30:00';
		}

		foreach ($dayMap as $dayData) {
			$slotPct = 0;
			$status = 'N/A';
			$statusClass = 'default';
			$overloadedEvents = [];
			$overloadedEventIds = [];
			$availableWindows = [];

			if ($dayLoadMeta['target_slots_per_day'] > 0) {
				$slotPct = round(($dayData['participant_slots'] / $dayLoadMeta['target_slots_per_day']) * 100, 1);
				if ($slotPct > 115) {
					$status = 'Overloaded';
					$statusClass = 'danger';
				} elseif ($slotPct < 85) {
					$status = 'Underloaded';
					$statusClass = 'warning';
				} else {
					$status = 'Balanced';
					$statusClass = 'success';
				}
			}

			if ($statusClass === 'danger' && !empty($dayData['event_slots'])) {
				arsort($dayData['event_slots']);
				$topEventSlots = array_slice($dayData['event_slots'], 0, 8, true);
				foreach ($topEventSlots as $topEventId => $topSlots) {
					$label = isset($eventLabelMap[(int)$topEventId]) ? $eventLabelMap[(int)$topEventId] : ('Event ID '.$topEventId);
					$overloadedEventIds[] = (int)$topEventId;
					$overloadedEvents[] = [
						'event_id' => (int)$topEventId,
						'label' => $label,
						'slots' => (int)$topSlots,
					];
				}
			}

			if ($totalRoomCount > 0) {
				$slotCandidates = [];
				$windowMinutes = 30;

				// Build blocked time ranges for this day (lunch + sports day)
				$blockedRanges = [];

				// Lunch block (applies every day)
				if (!empty($schedulingConfig->lunch_time_start) && !empty($schedulingConfig->lunch_time_end)) {
					$blockedRanges[] = [
						'start' => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->lunch_time_start))),
						'end'   => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->lunch_time_end))),
					];
				}

				// Sports day block (only on the sports day)
				if (!empty($schedulingConfig->sports_day_yes_no) && $schedulingConfig->sports_day_yes_no == 1) {
					if (!empty($schedulingConfig->sports_day) && $dayData['day_name'] == $schedulingConfig->sports_day) {
						$blockedRanges[] = [
							'start' => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->sports_day_starting_time))),
							'end'   => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->sports_day_finish_time))),
						];
					}
				}

				// Judging breaks (morning + afternoon)
				if (!empty($schedulingConfig->judging_breaks_yes_no) && $schedulingConfig->judging_breaks_yes_no == 1) {
					if (!empty($schedulingConfig->judging_breaks_morning_break_starting_time) && !empty($schedulingConfig->judging_breaks_morning_break_finish_time)) {
						$blockedRanges[] = [
							'start' => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->judging_breaks_morning_break_starting_time))),
							'end'   => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->judging_breaks_morning_break_finish_time))),
						];
					}
					if (!empty($schedulingConfig->judging_breaks_afternoon_break_start_time) && !empty($schedulingConfig->judging_breaks_afternoon_break_finish_time)) {
						$blockedRanges[] = [
							'start' => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->judging_breaks_afternoon_break_start_time))),
							'end'   => strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->judging_breaks_afternoon_break_finish_time))),
						];
					}
				}

				// On sports day with events after sport, override the day window
				$effectiveDayStart = strtotime($dayData['date'].' '.$dayStartTime);
				$effectiveDayEnd = strtotime($dayData['date'].' '.$dayFinishTime);
				if (!empty($schedulingConfig->sports_day_yes_no) && $schedulingConfig->sports_day_yes_no == 1
					&& !empty($schedulingConfig->sports_day) && $dayData['day_name'] == $schedulingConfig->sports_day
					&& !empty($schedulingConfig->sports_day_having_events_after_sport_yes_no) && $schedulingConfig->sports_day_having_events_after_sport_yes_no == 1) {
					$effectiveDayStart = strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->sports_day_other_starting_time)));
					$effectiveDayEnd = strtotime($dayData['date'].' '.date('H:i:s', strtotime($schedulingConfig->sports_day_other_finish_time)));
				}

				$cursor = $effectiveDayStart;

				while ($cursor < $effectiveDayEnd) {
					$slotStartTs = $cursor;
					$slotEndTs = strtotime('+'.$windowMinutes.' minutes', $slotStartTs);
					if ($slotEndTs > $effectiveDayEnd) {
						break;
					}

					// Skip slots that overlap any blocked range
					$slotBlocked = false;
					foreach ($blockedRanges as $br) {
						if ($slotStartTs < $br['end'] && $slotEndTs > $br['start']) {
							$slotBlocked = true;
							break;
						}
					}
					if ($slotBlocked) {
						$cursor = strtotime('+'.$windowMinutes.' minutes', $cursor);
						continue;
					}

					$occupiedRooms = [];
					foreach ((array)$dayData['rows'] as $rinfo) {
						if ((int)$rinfo['room_id'] <= 0 || empty($rinfo['start_time']) || empty($rinfo['finish_time'])) {
							continue;
						}
						if (!isset($seasonRoomScope[(int)$rinfo['room_id']])) {
							continue;
						}
						$rowStartTs = strtotime($dayData['date'].' '.$rinfo['start_time']);
						$rowEndTs = strtotime($dayData['date'].' '.$rinfo['finish_time']);
						if ($rowEndTs <= $rowStartTs) {
							continue;
						}
						if ($rowStartTs < $slotEndTs && $rowEndTs > $slotStartTs) {
							$occupiedRooms[(int)$rinfo['room_id']] = true;
						}
					}

					$availableRooms = $totalRoomCount - count($occupiedRooms);
					if ($availableRooms > 0) {
						$slotCandidates[] = [
							'start_ts' => $slotStartTs,
							'end_ts' => $slotEndTs,
							'available_rooms' => $availableRooms,
						];
					}

					$cursor = strtotime('+'.$windowMinutes.' minutes', $cursor);
				}

				usort($slotCandidates, function($a, $b) {
					if ($a['available_rooms'] === $b['available_rooms']) {
						return $a['start_ts'] <=> $b['start_ts'];
					}
					return $b['available_rooms'] <=> $a['available_rooms'];
				});

				$slotCandidates = array_slice($slotCandidates, 0, 5);
				foreach ($slotCandidates as $cand) {
					$availableWindows[] = [
						'label' => date('H:i', $cand['start_ts']).' - '.date('H:i', $cand['end_ts']),
						'start_time' => date('H:i', $cand['start_ts']),
						'rooms' => (int)$cand['available_rooms'],
					];
				}
			}

			$dayLoadRows[] = [
				'date' => $dayData['date'],
				'day_name' => $dayData['day_name'],
				'sessions' => (int)$dayData['sessions'],
				'participant_slots' => (int)$dayData['participant_slots'],
				'unique_events' => count($dayData['events']),
				'unique_rooms' => count($dayData['rooms']),
				'load_pct' => $slotPct,
				'status' => $status,
				'status_class' => $statusClass,
				'overloaded_events' => $overloadedEvents,
				'overloaded_event_ids' => $overloadedEventIds,
				'available_windows' => $availableWindows,
			];
		}

		$this->set('dayLoadRows', $dayLoadRows);
		$this->set('dayLoadMeta', $dayLoadMeta);
		$this->set('scheduleHealth', $this->buildScheduleHealthMetrics((int)$conventionSD->id, $schedulingConfig));
		$this->set('overflowTrendRows', $this->getOverflowTrendRows((int)$conventionSD->id, 12));
		
		
    }
	
	
	public function reports($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		$totalScheduleRows = 0;
		try {
			$totalScheduleRows = (int)$this->Schedulingtimings->find()
				->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id])
				->count();
		} catch (Exception $e) {
			$totalScheduleRows = 0;
		}

		$parityReady = $totalScheduleRows > 0;
		$phase4ReportParityRows = [
			[
				'legacy_key' => 'Schedule by Event',
				'acp_label' => 'Report By Events/Sport',
				'route' => ['controller' => 'schedulingreports', 'action' => 'byevents', $convention_season_slug],
				'printable' => true,
				'csv' => false,
				'status' => $parityReady ? 'Ready' : 'No schedule rows yet',
			],
			[
				'legacy_key' => 'Schedule by Location',
				'acp_label' => 'Report By Rooms/Location',
				'route' => ['controller' => 'schedulingreports', 'action' => 'byrooms', $convention_season_slug],
				'printable' => true,
				'csv' => true,
				'status' => $parityReady ? 'Ready' : 'No schedule rows yet',
			],
			[
				'legacy_key' => 'Schedule by Student',
				'acp_label' => 'Report By Students',
				'route' => ['controller' => 'schedulingreports', 'action' => 'bystudents', $convention_season_slug],
				'printable' => true,
				'csv' => false,
				'status' => $parityReady ? 'Ready' : 'No schedule rows yet',
			],
			[
				'legacy_key' => 'Schedule by Match',
				'acp_label' => 'Report By Match',
				'route' => ['controller' => 'schedulingreports', 'action' => 'bymatchshow', $convention_season_slug],
				'printable' => true,
				'csv' => true,
				'status' => $parityReady ? 'Ready' : 'No schedule rows yet',
			],
		];

		$this->set('phase4ReportParityRows', $phase4ReportParityRows);
		$this->set('phase4ReportParityReady', $parityReady);
		
    }
	
	public function finalizeschedule($convention_season_slug=null) {
		$this->set('title', ADMIN_TITLE . 'Finalize Schedule');
		$this->viewBuilder()->setLayout('admin');

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
        $this->viewBuilder()->setLayout('admin');
		
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
		
		// Build dynamic overwrite event list from all events currently in scheduling categories 1-4
		$eventIDArr = $this->Schedulingtimings->find()
			->select(['event_id'])
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category IN' => [1,2,3,4],
				'Schedulingtimings.event_id IS NOT' => null,
			])
			->group(['Schedulingtimings.event_id'])
			->order(['Schedulingtimings.event_id' => 'ASC'])
			->enableHydration(false)
			->toArray();

		$eventIDArr = array_map(function($row){
			return (int)$row['event_id'];
		}, $eventIDArr);

		// Fallback: if no scheduled rows exist yet, include all schedulable season events
		if (empty($eventIDArr)) {
			$eventIDArr = $this->Conventionseasonevents->find()
				->select(['Conventionseasonevents.event_id'])
				->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])
				->group(['Conventionseasonevents.event_id'])
				->enableHydration(false)
				->toArray();

			$eventIDArr = array_values(array_unique(array_filter(array_map(function($row){
				return !empty($row['event_id']) ? (int)$row['event_id'] : 0;
			}, $eventIDArr))));
		}
		
		// Now check if these events are chosen for this convention season
		
		$finalEventArr = array();
		$finalEventGrouped = array();
		$eventStats = array();
		$eventSortMeta = array();
		$eventIdByCode = array();
		$rules = $this->getEventBucketRules();

		$typeGroupOrder = isset($rules['group_order']) && is_array($rules['group_order']) ? $rules['group_order'] : [];
		if (empty($typeGroupOrder)) {
			$typeGroupOrder = ['Academics', 'Music Combined', 'Music Instrumental', 'Music Vocal', 'Platform', 'Scripture', 'Sports'];
		}
		foreach ($typeGroupOrder as $typeGroupLabel) {
			$finalEventGrouped[$typeGroupLabel] = [
				'label' => $typeGroupLabel,
				'events' => [],
			];
		}

		$eventCategoryNameById = $this->getEventCategoryNameMap();
		
		foreach($eventIDArr as $event_id)
		{
			$checkEventCS = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonevents.event_id' => $event_id])->contain(["Events"])->first();
			if($checkEventCS)
			{
				$eventName = (string)$checkEventCS->Events['event_name'];
				$eventCode = (string)$checkEventCS->Events['event_id_number'];
				$eventCategoryId = (int)$checkEventCS->Events['event_grp_name'];
				$eventCategoryName = isset($eventCategoryNameById[$eventCategoryId]) ? $eventCategoryNameById[$eventCategoryId] : '';

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
				
				$eventLabel = $eventName.' ('.$eventCode.')'.' ('.$countStudentsEvent.')';
				$finalEventArr[$event_id] = $eventLabel;
				$eventSortMeta[(int)$event_id] = [
					'code' => $eventCode,
					'name' => $eventName,
				];
				$eventTypeGroup = $this->classifyEventTypeGroup($eventName, $eventCode, $eventCategoryName, $rules);
				if (!isset($finalEventGrouped[$eventTypeGroup])) {
					$finalEventGrouped[$eventTypeGroup] = ['label' => $eventTypeGroup, 'events' => []];
				}
				$finalEventGrouped[$eventTypeGroup]['events'][$event_id] = $eventLabel;

				$eventCodeKey = strtoupper(trim($eventCode));
				if ($eventCodeKey !== '' && !isset($eventIdByCode[$eventCodeKey])) {
					$eventIdByCode[$eventCodeKey] = (int)$event_id;
				}
				if ($eventCodeKey !== '' && ctype_digit($eventCodeKey)) {
					$eventCodeNumericKey = (string)((int)$eventCodeKey);
					if ($eventCodeNumericKey !== '' && !isset($eventIdByCode[$eventCodeNumericKey])) {
						$eventIdByCode[$eventCodeNumericKey] = (int)$event_id;
					}
				}
				$eventStats[$event_id] = [
					'label' => $eventName.' ('.$eventCode.')',
					'event_id_number' => $eventCode,
					'students' => $countStudentsEvent,
					'scheduled_records' => $countScheduledRecords,
					'duration_minutes' => ((int)$checkEventCS->Events['setup_time']) + ((int)$checkEventCS->Events['round_time']) + ((int)$checkEventCS->Events['judging_time']),
				];
			}
		}
		foreach ($finalEventGrouped as $grpKey => $grpData) {
			if (!empty($grpData['events']) && is_array($grpData['events'])) {
				uksort($grpData['events'], function($a, $b) use ($eventSortMeta) {
					$codeA = isset($eventSortMeta[(int)$a]['code']) ? (string)$eventSortMeta[(int)$a]['code'] : '';
					$codeB = isset($eventSortMeta[(int)$b]['code']) ? (string)$eventSortMeta[(int)$b]['code'] : '';
					$codeCmp = strnatcasecmp($codeA, $codeB);
					if ($codeCmp !== 0) {
						return $codeCmp;
					}

					$nameA = isset($eventSortMeta[(int)$a]['name']) ? (string)$eventSortMeta[(int)$a]['name'] : '';
					$nameB = isset($eventSortMeta[(int)$b]['name']) ? (string)$eventSortMeta[(int)$b]['name'] : '';
					$nameCmp = strnatcasecmp($nameA, $nameB);
					if ($nameCmp !== 0) {
						return $nameCmp;
					}

					if ((int)$a === (int)$b) {
						return 0;
					}
					return ((int)$a < (int)$b) ? -1 : 1;
				});
				$finalEventGrouped[$grpKey]['events'] = $grpData['events'];
			}
		}
		$orderedEventGroups = [];
		foreach ($typeGroupOrder as $groupLabel) {
			if (isset($finalEventGrouped[$groupLabel])) {
				$orderedEventGroups[$groupLabel] = $finalEventGrouped[$groupLabel];
			}
		}
		foreach ($finalEventGrouped as $groupLabel => $groupData) {
			if (!isset($orderedEventGroups[$groupLabel])) {
				$orderedEventGroups[$groupLabel] = $groupData;
			}
		}
		$finalEventGrouped = $orderedEventGroups;
		$this->set('finalEventArr', $finalEventArr);
		$this->set('finalEventGrouped', $finalEventGrouped);
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

		// Optional automation prefill from Schedule Category dashboard links.
		$prefillRows = [];
		$prefillDate = trim((string)$this->request->getQuery('prefill_date'));
		$prefillTimeRaw = trim((string)$this->request->getQuery('prefill_time'));
		$prefillTime = '';
		if ($prefillTimeRaw !== '') {
			$prefillTime = date('H:i', strtotime($prefillTimeRaw));
			if ($prefillTime === '00:00' && stripos($prefillTimeRaw, '00:00') === false) {
				$prefillTime = '';
			}
		}

		$prefillEventIds = [];
		$prefillEventIdsCsv = trim((string)$this->request->getQuery('prefill_event_ids'));
		$prefillSingleEventId = (int)$this->request->getQuery('prefill_event_id');
		if ($prefillEventIdsCsv !== '') {
			$prefillEventIds = array_values(array_unique(array_filter(array_map('intval', explode(',', $prefillEventIdsCsv)))));
		}
		if ($prefillSingleEventId > 0) {
			$prefillEventIds[] = $prefillSingleEventId;
			$prefillEventIds = array_values(array_unique($prefillEventIds));
		}

		$presetMap = [
			'conservative' => ['label' => 'Conservative', 'max_students' => 4, 'time_gap_mins' => 2],
			'balanced' => ['label' => 'Balanced', 'max_students' => 6, 'time_gap_mins' => 1],
			'aggressive' => ['label' => 'Aggressive', 'max_students' => 8, 'time_gap_mins' => 1],
		];
		$prefillPreset = strtolower(trim((string)$this->request->getQuery('prefill_preset', 'balanced')));
		if (!isset($presetMap[$prefillPreset])) {
			$prefillPreset = 'balanced';
		}

		$defaultPrefillMax = (int)$presetMap[$prefillPreset]['max_students'];
		$defaultPrefillGap = (int)$presetMap[$prefillPreset]['time_gap_mins'];
		$prefillMaxQuery = $this->request->getQuery('prefill_max');
		$prefillGapQuery = $this->request->getQuery('prefill_gap');
		if ($prefillMaxQuery !== null && $prefillMaxQuery !== '') {
			$defaultPrefillMax = max(1, (int)$prefillMaxQuery);
		}
		if ($prefillGapQuery !== null && $prefillGapQuery !== '') {
			$defaultPrefillGap = max(1, (int)$prefillGapQuery);
		}

		if (!empty($prefillEventIds) && !empty($prefillDate)) {
			// Load room-event students_per_block mappings for this convention season
			$roomEventSpb = [];
			$convRoomEventsAll = $this->Conventionseasonroomevents->find()
				->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])
				->all();
			foreach ($convRoomEventsAll as $cre) {
				if (!empty($cre->students_per_block)) {
					$spbMap = (array)json_decode($cre->students_per_block, true);
					foreach ($spbMap as $eid => $val) {
						if ((int)$val > 0) {
							$roomEventSpb[(int)$eid] = (int)$val;
						}
					}
				}
			}
			
			foreach ($prefillEventIds as $prefillEventId) {
				if (!isset($finalEventArr[$prefillEventId])) {
					continue;
				}
				// Use room-event configured block size if available, otherwise preset default
				$eventMax = isset($roomEventSpb[$prefillEventId]) ? $roomEventSpb[$prefillEventId] : $defaultPrefillMax;
				$prefillRows[] = [
					'event_id' => (int)$prefillEventId,
					'event_code' => isset($eventStats[$prefillEventId]['event_id_number']) ? (string)$eventStats[$prefillEventId]['event_id_number'] : '',
					'date' => $prefillDate,
					'time' => $prefillTime,
					'max_students' => $eventMax,
					'time_gap_mins' => $defaultPrefillGap,
				];
			}
		}

		$this->set('overwritePrefillRows', $prefillRows);
		$this->set('overwriteAutoMode', !empty($prefillRows) && (int)$this->request->getQuery('auto') === 1);
		$this->set('overwritePresetMap', $presetMap);
		$this->set('overwriteSelectedPreset', $prefillPreset);
		$this->set('overwriteDefaultMax', $defaultPrefillMax);
		$this->set('overwriteDefaultGap', $defaultPrefillGap);

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
					$rowEventCode = isset($row['event_code']) ? strtoupper(trim($row['event_code'])) : '';
					if ($rowEventId <= 0 && $rowEventCode !== '' && isset($eventIdByCode[$rowEventCode])) {
						$rowEventId = (int)$eventIdByCode[$rowEventCode];
					}
					$rowDate = isset($row['date']) ? trim($row['date']) : '';
					$rowTime = isset($row['time']) ? trim($row['time']) : '';
					$rowMaxStudents = isset($row['max_students']) ? (int)$row['max_students'] : 0;
					$rowGapMins = isset($row['time_gap_mins']) ? (int)$row['time_gap_mins'] : 0;

					$rowHasAny = ($rowEventId > 0 || $rowEventCode !== '' || $rowDate !== '' || $rowTime !== '');
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
					$loggedAdminId = $this->request->getSession()->read('admin_id');
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
				$loggedAdminId = $this->request->getSession()->read('admin_id');
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

		$loggedAdminId = $this->request->getSession()->read('admin_id');
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
					$currentSchedulingD = $this->Schedulings->find()->select(['id', 'conflict_user_ids'])->where(['Schedulings.id' => $schedulingD->id])->first();
					$currentUserIDSConflict = !empty($currentSchedulingD->conflict_user_ids) ? explode(",", $currentSchedulingD->conflict_user_ids) : [];
					$nextUserIDSConflicts = array_values(array_filter($currentUserIDSConflict, function($item) use ($userId) {
						return (string)$item !== (string)$userId;
					}));
					
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
						$currentSchedulingD = $this->Schedulings->find()->select(['id', 'conflict_user_ids'])->where(['Schedulings.id' => $schedulingD->id])->first();
						$currentUserIDSConflict = !empty($currentSchedulingD->conflict_user_ids) ? explode(",", $currentSchedulingD->conflict_user_ids) : [];
						$nextUserIDSConflicts = array_values(array_filter($currentUserIDSConflict, function($item) use ($userId) {
							return (string)$item !== (string)$userId;
						}));
						
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
        $this->viewBuilder()->setLayout('admin');
		
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
            
			//$this->prx($this->request->getData()['Schedulingtimings']);
			
			$data = $this->request->getData()['Schedulingtimings'];
			
			
			
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
			$msgEdit = 'Unable to update time due to a scheduling conflict.';
			
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
		$this->viewBuilder()->setLayout('admin');
		
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
		$roomScheduleDays = [];
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
