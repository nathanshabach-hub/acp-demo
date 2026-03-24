<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Datasource\ConnectionManager;

class SchedulingtimingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];
	private $groupParticipantCache = [];
	private $timingByIdCache = [];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');

		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Events");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Schedulingtimings");
		$this->loadModel("Conventionseasonroomevents");
		$this->loadModel("Schedulings");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionrooms");
    }

	public function beforeFilter(\Cake\Event\EventInterface $event) {
		return parent::beforeFilter($event);
	}

	/**
	 * Returns all room IDs that share the same Room Allocation as $roomId,
	 * including $roomId itself. If the room has no allocation, returns [$roomId].
	 * Returns the room ID in an array for compatibility with call sites.
	 */
	private function getAllocationRoomIds($roomId) {
		return [(int)$roomId];
	}

	/**
	 * Returns room IDs in their original order.
	 */
	private function sortRoomsByAllocation($roomIds, $conventionSeasonId = null) {
		if (empty($roomIds) || $conventionSeasonId === null) {
			return $roomIds;
		}

		$roomLoad = [];
		foreach ($roomIds as $index => $roomId) {
			$roomLoad[(int)$roomId] = [
				'count' => 0,
				'index' => $index,
			];
		}

		$loadRows = $this->Schedulingtimings->find()
			->select(['room_id', 'cnt' => 'COUNT(*)'])
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSeasonId,
				'Schedulingtimings.room_id IN' => $roomIds,
				'Schedulingtimings.start_time IS NOT' => null,
			])
			->group(['Schedulingtimings.room_id'])
			->all();

		foreach ($loadRows as $loadRow) {
			$roomId = (int)$loadRow->room_id;
			if (isset($roomLoad[$roomId])) {
				$roomLoad[$roomId]['count'] = (int)$loadRow->cnt;
			}
		}

		usort($roomIds, function ($left, $right) use ($roomLoad) {
			$left = (int)$left;
			$right = (int)$right;
			if ($roomLoad[$left]['count'] === $roomLoad[$right]['count']) {
				return $roomLoad[$left]['index'] <=> $roomLoad[$right]['index'];
			}

			return $roomLoad[$left]['count'] <=> $roomLoad[$right]['count'];
		});

		return $roomIds;
	}

	private function isRoomTimeAllowed($room_id, $day, $start_time, $finish_time) {
		$room = $this->Conventionrooms->find()->where(['id' => $room_id])->first();
		if (!$room) return true;

		// Check restricted days
		if (!empty($room->restricted_days)) {
			$allowedDays = explode(',', $room->restricted_days);
			if (!in_array($day, $allowedDays)) {
				return false;
			}
		}

		// Check restricted times
		if (!empty($room->restricted_start_time) && !empty($room->restricted_finish_time)) {
			$r_start = strtotime($room->restricted_start_time);
			$r_finish = strtotime($room->restricted_finish_time);
			$t_start = strtotime($start_time);
			$t_finish = strtotime($finish_time);

			if ($t_start < $r_start || $t_finish > $r_finish) {
				return false;
			}
		}

		return true;
	}

	private function isSchedulableConventionDay($day) {
		// Allow spillover onto any week day; unresolved records should retain a concrete slot.
		return in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], true);
	}

	private function getConventionBalancingDays($firstDay, $windowDays = 4) {
		$week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
		$startIndex = array_search($firstDay, $week, true);
		if ($startIndex === false) {
			$startIndex = 0;
		}

		$days = [];
		for ($i = 0; $i < $windowDays; $i++) {
			$days[] = $week[($startIndex + $i) % 7];
		}

		return $days;
	}

	private function getDateForDayFromStart($startDate, $firstDay, $targetDay) {
		$week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
		$firstIndex = array_search($firstDay, $week, true);
		$targetIndex = array_search($targetDay, $week, true);

		if ($firstIndex === false || $targetIndex === false) {
			return $startDate;
		}

		$offset = ($targetIndex - $firstIndex + 7) % 7;
		return date('Y-m-d', strtotime($startDate . ' +' . $offset . ' day'));
	}

	private function pickLeastLoadedStartDay($conventionSeasonId, $candidateDays = []) {
		if (empty($candidateDays)) {
			return null;
		}

		$dayLoad = [];
		foreach ($candidateDays as $d) {
			$dayLoad[$d] = 0;
		}

		$rows = $this->Schedulingtimings->find()
			->select(['day', 'cnt' => 'COUNT(*)'])
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSeasonId,
				'Schedulingtimings.start_time IS NOT' => null,
				'Schedulingtimings.day IN' => $candidateDays,
			])
			->group(['Schedulingtimings.day'])
			->all();

		foreach ($rows as $r) {
			$dayLoad[$r->day] = (int)$r->cnt;
		}

		$chosenDay = $candidateDays[0];
		$minCount = $dayLoad[$chosenDay];
		foreach ($candidateDays as $dayName) {
			if ($dayLoad[$dayName] < $minCount) {
				$chosenDay = $dayName;
				$minCount = $dayLoad[$dayName];
			}
		}

		return $chosenDay;
	}

	private function moveToNextRoomOrDay($cntrRoomCSEvent, $totalRoomsForThisEvent, $schDay, $schStartDate,
		$cntrDays, $schedulingsD, $eventDuration, $firstDay = null, $startDate = null, $conventionSeasonId = null) {
		if ($cntrRoomCSEvent < ($totalRoomsForThisEvent - 1)) {
			$cntrRoomCSEvent++;
			$normalStartingTime = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
			$normalFinishTime = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

			return [
				'cntrRoomCSEvent' => $cntrRoomCSEvent,
				'day' => $schDay,
				'date' => $schStartDate,
				'cntrDays' => $cntrDays,
				'normal_starting_time' => $normalStartingTime,
				'normal_finish_time' => $normalFinishTime,
				'start_time' => $normalStartingTime,
				'finish_time' => date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStartingTime))),
			];
		}

		$cntrRoomCSEvent = 0;
		if ($conventionSeasonId !== null && $firstDay !== null && $startDate !== null) {
			$balancingDays = $this->getConventionBalancingDays($firstDay, 4);
			$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSeasonId, $balancingDays);
			if (!empty($balancedStartDay)) {
				$schDay = $balancedStartDay;
				$schStartDate = $this->getDateForDayFromStart($startDate, $firstDay, $schDay);
			} else {
				$schDay = $this->getNextWeekDay($schDay);
				$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
			}
		} else {
			$schDay = $this->getNextWeekDay($schDay);
			$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
		}

		$cntrDays++;
		$normalStartingTime = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
		$normalFinishTime = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

		return [
			'cntrRoomCSEvent' => $cntrRoomCSEvent,
			'day' => $schDay,
			'date' => $schStartDate,
			'cntrDays' => $cntrDays,
			'normal_starting_time' => $normalStartingTime,
			'normal_finish_time' => $normalFinishTime,
			'start_time' => $normalStartingTime,
			'finish_time' => date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStartingTime))),
		];
	}

	/**
	 * Advance to the next scheduling day, optionally using load balancing.
	 * Pass $conventionSeasonId, $firstDay, $startDate for balanced day picking.
	 */
	private function advanceDay($day, $dateStr, $cntrDays, $schedulingsD, $conventionSeasonId = null, $firstDay = null, $startDate = null) {
		if ($conventionSeasonId !== null && $firstDay !== null && $startDate !== null) {
			$balancingDays = $this->getConventionBalancingDays($firstDay, 4);
			$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSeasonId, $balancingDays);
			if (!empty($balancedStartDay)) {
				$day = $balancedStartDay;
				$dateStr = $this->getDateForDayFromStart($startDate, $firstDay, $day);
			} else {
				$day = $this->getNextWeekDay($day);
				$dateStr = date('Y-m-d', strtotime($dateStr . ' +1 day'));
			}
		} else {
			$day = $this->getNextWeekDay($day);
			$dateStr = date('Y-m-d', strtotime($dateStr . ' +1 day'));
		}
		$cntrDays++;
		return [
			'day' => $day,
			'date' => $dateStr,
			'cntrDays' => $cntrDays,
			'normalStart' => date("H:i:s", strtotime($schedulingsD->normal_starting_time)),
			'normalFinish' => date("H:i:s", strtotime($schedulingsD->normal_finish_time))
		];
	}

	/**
	 * Apply all time constraints: lunch, judging breaks, sports day, after-sport events, room restrictions.
	 * Loops until a valid slot is found or safety limit reached.
	 * Pass $conventionSeasonId/$firstDay/$startDate for balanced day advancement.
	 */
	private function applyTimeConstraints($schedulingsD, $eventDuration, $roomID,
		$startTime, $finishTime, $day, $dateStr, $cntrDays,
		$conventionSeasonId = null, $firstDay = null, $startDate = null) {

		$normalStart = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
		$normalFinish = date("H:i:s", strtotime($schedulingsD->normal_finish_time));
		$lunchStart = date("H:i:s", strtotime($schedulingsD->lunch_time_start));
		$lunchEnd = date("H:i:s", strtotime($schedulingsD->lunch_time_end));

		$roomAllowed = false;
		$safetyCounter = 0;
		while (!$roomAllowed && $safetyCounter < 100) {
			$safetyCounter++;

			// Lunch check
			if ((strtotime($startTime) >= strtotime($lunchStart) && strtotime($startTime) <= strtotime($lunchEnd)) ||
				(strtotime($finishTime) >= strtotime($lunchStart) && strtotime($finishTime) <= strtotime($lunchEnd))) {
				$startTime = $lunchEnd;
				$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($lunchEnd)));
				if (strtotime($finishTime) > strtotime($normalFinish)) {
					$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD, $conventionSeasonId, $firstDay, $startDate);
					$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
					$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
					$startTime = $normalStart;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
				}
			}

			// Judging breaks
			if ($schedulingsD->judging_breaks_yes_no == 1) {
				// Morning break
				$mbStart = date("H:i:s", strtotime($schedulingsD->judging_breaks_morning_break_starting_time));
				$mbFinish = date("H:i:s", strtotime($schedulingsD->judging_breaks_morning_break_finish_time));
				if ((strtotime($startTime) >= strtotime($mbStart) && strtotime($startTime) <= strtotime($mbFinish)) ||
					(strtotime($finishTime) >= strtotime($mbStart) && strtotime($finishTime) <= strtotime($mbFinish))) {
					$startTime = $mbFinish;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($mbFinish)));
				}
				if (strtotime($finishTime) > strtotime($normalFinish)) {
					$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD, $conventionSeasonId, $firstDay, $startDate);
					$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
					$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
					$startTime = $normalStart;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
				}

				// Afternoon break
				$abStart = date("H:i:s", strtotime($schedulingsD->judging_breaks_afternoon_break_start_time));
				$abFinish = date("H:i:s", strtotime($schedulingsD->judging_breaks_afternoon_break_finish_time));
				if ((strtotime($startTime) >= strtotime($abStart) && strtotime($startTime) <= strtotime($abFinish)) ||
					(strtotime($finishTime) >= strtotime($abStart) && strtotime($finishTime) <= strtotime($abFinish))) {
					$startTime = $abFinish;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($abFinish)));
				}
				if (strtotime($finishTime) > strtotime($normalFinish)) {
					$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD, $conventionSeasonId, $firstDay, $startDate);
					$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
					$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
					$startTime = $normalStart;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
				}
			}

			// Sports day
			if ($schedulingsD->sports_day_yes_no == 1) {
				$sportsDay = $schedulingsD->sports_day;
				$sdStart = date("H:i:s", strtotime($schedulingsD->sports_day_starting_time));
				$sdFinish = date("H:i:s", strtotime($schedulingsD->sports_day_finish_time));
				if ($sportsDay == $day) {
					if (strtotime($startTime) < strtotime($sdFinish) && strtotime($finishTime) > strtotime($sdStart)) {
						$startTime = $sdFinish;
						$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($sdFinish)));
					}
					if (strtotime($finishTime) >= strtotime($normalFinish)) {
						$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD);
						$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
						$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
						$startTime = $normalStart;
						$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
					}
				}
			}

			// After-sport events window
			if ($schedulingsD->sports_day_having_events_after_sport_yes_no == 1) {
				$sportsDay = $schedulingsD->sports_day;
				$soStart = date("H:i:s", strtotime($schedulingsD->sports_day_other_starting_time));
				$soFinish = date("H:i:s", strtotime($schedulingsD->sports_day_other_finish_time));
				if ($sportsDay == $day) {
					if (strtotime($startTime) < strtotime($soStart)) {
						$startTime = $soStart;
						$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($soStart)));
					}
					if (strtotime($finishTime) > strtotime($soFinish)) {
						$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD);
						$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
						$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
						$startTime = $normalStart;
						$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
					}
				}
			}

			// Room restriction check
			if ($this->isRoomTimeAllowed($roomID, $day, $startTime, $finishTime)) {
				$roomAllowed = true;
			} else {
				$startTime = date("H:i:s", strtotime('+30 minutes', strtotime($startTime)));
				$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($startTime)));
				if (strtotime($finishTime) > strtotime($normalFinish)) {
					$adv = $this->advanceDay($day, $dateStr, $cntrDays, $schedulingsD);
					$day = $adv['day']; $dateStr = $adv['date']; $cntrDays = $adv['cntrDays'];
					$normalStart = $adv['normalStart']; $normalFinish = $adv['normalFinish'];
					$startTime = $normalStart;
					$finishTime = date("H:i:s", strtotime('+ '.$eventDuration.' minutes', strtotime($normalStart)));
				}
			}
		}

		return [
			'start_time' => $startTime,
			'finish_time' => $finishTime,
			'day' => $day,
			'date' => $dateStr,
			'cntrDays' => $cntrDays
		];
	}

	/**
	 * Find rooms assigned to an event for a convention season.
	 * Returns ['rooms' => [room_id, ...], 'spb' => students_per_block_value]
	 */
	private function findRoomsForEvent($conventionSD, $eventId) {
		$condRoomCS = array();
		$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".(int)$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".(int)$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".(int)$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".(int)$conventionSD->season_year."')";
		$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".(int)$eventId."' OR
						Conventionseasonroomevents.event_ids LIKE '".(int)$eventId.",%' OR
						Conventionseasonroomevents.event_ids LIKE '%,".(int)$eventId.",%' OR
						Conventionseasonroomevents.event_ids LIKE '%,".(int)$eventId."')";
		$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id', 'students_per_block'])->where($condRoomCS)->all();
		$roomIds = array();
		$spbValue = 1;
		foreach ($roomCSEvent as $roomeventcs) {
			$roomIds[] = $roomeventcs->room_id;
			if (!empty($roomeventcs->students_per_block)) {
				$spbMap = (array)json_decode($roomeventcs->students_per_block, true);
				if (isset($spbMap[$eventId]) && (int)$spbMap[$eventId] > 0) {
					$spbValue = (int)$spbMap[$eventId];
				}
			}
		}

		if (empty($roomIds)) {
			// Fallback: if event-room mappings are missing, use all rooms for the convention.
			$fallbackRooms = $this->Conventionrooms->find()
				->select(['id'])
				->where(['Conventionrooms.convention_id' => (int)$conventionSD->convention_id])
				->order(['Conventionrooms.id' => 'ASC'])
				->all();
			foreach ($fallbackRooms as $fallbackRoom) {
				$roomIds[] = (int)$fallbackRoom->id;
			}
		}

		$roomIds = $this->sortRoomsByAllocation($roomIds, (int)$conventionSD->id);
		return ['rooms' => $roomIds, 'spb' => $spbValue];
	}

	/**
	 * Load scheduling configuration for a convention season.
	 * Returns associative array with all timing settings.
	 */
	private function loadSchedulingConfig($conventionSD) {
		$schedulingsD = $this->Schedulings->find()->where([
			"Schedulings.conventionseasons_id" => $conventionSD->id,
			"Schedulings.convention_id" => $conventionSD->convention_id,
			"Schedulings.season_id" => $conventionSD->season_id,
			"Schedulings.season_year" => $conventionSD->season_year
		])->first();

		$defaultStartDate = !empty($conventionSD->start_date) ? date("Y-m-d", strtotime($conventionSD->start_date)) : date("Y-m-d");
		$startDate = !empty($schedulingsD->start_date) ? date("Y-m-d", strtotime($schedulingsD->start_date)) : $defaultStartDate;
		$firstDay = !empty($schedulingsD->first_day) ? $schedulingsD->first_day : date('l', strtotime($startDate));
		$allowedStartDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
		if (!in_array($firstDay, $allowedStartDays, true)) {
			$firstDay = 'Monday';
		}

		$normalStartingTime = !empty($schedulingsD->normal_starting_time)
			? date("H:i:s", strtotime($schedulingsD->normal_starting_time))
			: '08:00:00';
		$normalFinishTime = !empty($schedulingsD->normal_finish_time)
			? date("H:i:s", strtotime($schedulingsD->normal_finish_time))
			: '17:00:00';
		if (strtotime($normalFinishTime) <= strtotime($normalStartingTime)) {
			$normalFinishTime = '17:00:00';
		}

		$lunchTimeStart = !empty($schedulingsD->lunch_time_start)
			? date("H:i:s", strtotime($schedulingsD->lunch_time_start))
			: '12:00:00';
		$lunchTimeEnd = !empty($schedulingsD->lunch_time_end)
			? date("H:i:s", strtotime($schedulingsD->lunch_time_end))
			: '13:00:00';

		$cfg = [];
		$cfg['schedulingsD'] = $schedulingsD;
		$cfg['start_date'] = $startDate;
		$cfg['first_day'] = $firstDay;
		$cfg['normal_starting_time'] = $normalStartingTime;
		$cfg['normal_finish_time'] = $normalFinishTime;
		$cfg['lunch_time_start'] = $lunchTimeStart;
		$cfg['lunch_time_end'] = $lunchTimeEnd;

		if ($schedulingsD->starting_different_time_first_day_yes_no == 1) {
			$cfg['different_first_day_start_time'] = !empty($schedulingsD->different_first_day_start_time)
				? date("H:i:s", strtotime($schedulingsD->different_first_day_start_time))
				: $cfg['normal_starting_time'];
			$cfg['different_first_day_end_time'] = !empty($schedulingsD->different_first_day_end_time)
				? date("H:i:s", strtotime($schedulingsD->different_first_day_end_time))
				: $cfg['normal_finish_time'];
		} else {
			$cfg['different_first_day_start_time'] = $cfg['normal_starting_time'];
			$cfg['different_first_day_end_time'] = $cfg['normal_finish_time'];
		}
		$cfg['starting_different_time_first_day_yes_no'] = $schedulingsD->starting_different_time_first_day_yes_no;

		return $cfg;
	}

	/**
	 * Get event IDs matching scheduling criteria for a convention season.
	 */
	private function getEventsForCategory($conventionSD, $isGroup, $eventKind, $isConsecutive) {
		$arrEvents = array();
		$condCSE = array();
		$condCSE[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		$allCSEvents = $this->Conventionseasonevents->find()->where($condCSE)->all();
		foreach ($allCSEvents as $csevent) {
			$eventD = $this->Events->find()->where(['Events.id' => $csevent->event_id])->first();
			if ($eventD && $eventD->needs_schedule == '1'
				&& $eventD->group_event_yes_no == ($isGroup ? '1' : '0')
				&& $eventD->event_kind_id == $eventKind
				&& $eventD->has_to_be_consecutive == ($isConsecutive ? '1' : '0')) {
				$arrEvents[] = $eventD->id;
			}
		}
		return $arrEvents;
	}

	/**
	 * Get students registered for an event (for individual categories: C2, C4).
	 */
	private function getStudentsForEvent($conventionSD, $eventId) {
		$students = array();
		$condST = array();
		$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
		$condST[] = "(Conventionregistrationstudents.event_ids LIKE '".$eventId."' OR Conventionregistrationstudents.event_ids LIKE '".$eventId.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$eventId.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$eventId."')";
		$result = $this->Conventionregistrationstudents->find()->where($condST)->all();
		if ($result) {
			foreach ($result as $studentEV) {
				$students[] = $studentEV->student_id;
			}
		}
		return $students;
	}

	/**
	 * Get groups registered for an event (for group categories: C1, C3).
	 * Returns array of "csid==convid==seasonid==year==crid==eventid==eventidnum==userid==groupname" strings.
	 */
	private function getGroupsForEvent($conventionSD, $eventId) {
		$mainArr = array();
		$eventD = $this->Events->find()->where(['Events.id' => $eventId])->first();
		if (!$eventD) return $mainArr;

		$condCR = array();
		$condCR[] = "(Conventionregistrations.conventionseason_id = '".$conventionSD->id."' AND Conventionregistrations.convention_id = '".$conventionSD->convention_id."')";
		$conventionRegistrations = $this->Conventionregistrations->find()->where($condCR)->all();
		foreach ($conventionRegistrations as $convreg) {
			$condCRSTEV = array();
			$condCRSTEV[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND Crstudentevents.convention_id = '".$conventionSD->convention_id."')";
			$condCRSTEV[] = "(Crstudentevents.conventionregistration_id = '".$convreg->id."' AND Crstudentevents.event_id = '".$eventId."')";
			$condCRSTEV[] = "(Crstudentevents.group_name != '')";
			$convRegSTEV = $this->Crstudentevents->find()->where($condCRSTEV)->select(['group_name'])->all();
			if ($convRegSTEV) {
				foreach ($convRegSTEV as $convregstev) {
					$varEventCombination = $conventionSD->id."=="
						.$conventionSD->convention_id."=="
						.$conventionSD->season_id."=="
						.$conventionSD->season_year."=="
						.$convreg->id."=="
						.$eventId."=="
						.$eventD->event_id_number."=="
						.$convreg->user_id."=="
						.$convregstev->group_name;
					if (!in_array($varEventCombination, (array)$mainArr)) {
						$mainArr[] = $varEventCombination;
					}
				}
			}
		}
		return $mainArr;
	}

	/* public function viewscheduling($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');

        $this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		//$this->prx($conventionSD);

		$this->set('conventionSD', $conventionSD);

		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		// to list all schedulings
		$schedulingTimingsList = $this->Schedulingtimings->find()->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->contain(["Events","Users","Conventionrooms","Opponentuser"])->order(["Schedulingtimings.id" => "ASC"])->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
    } */

	public function viewscheduling($convention_season_slug=null,$scheduling_category=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');

        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('scheduling_category', $scheduling_category);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		//$this->prx($conventionSD);

		$this->set('conventionSD', $conventionSD);

		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		// to list all schedulings
		$schedulingTimingsList = $this->Schedulingtimings->find()->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.schedule_category' => $scheduling_category])->contain(["Events","Users","Conventionrooms","Opponentuser"])->order(["Schedulingtimings.id" => "ASC"])->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
    }

	private function minutesBetweenTimes($startTime, $finishTime) {
		return (int) floor((strtotime($finishTime) - strtotime($startTime)) / 60);
	}

	private function calculateEventDurationMinutes($eventD) {
		$setup = (int)$eventD->setup_time;
		$round = (int)$eventD->round_time;
		$judging = (int)$eventD->judging_time;
		$total = $setup + $round + $judging;
		return $total > 0 ? $total : 1;
	}

	private function overlapsTimes($startA, $finishA, $startB, $finishB) {
		return (strtotime($startA) < strtotime($finishB) && strtotime($finishA) > strtotime($startB));
	}

	private function buildFreeSlotsForDay($intervals, $dayStart, $dayEnd, $durationMinutes) {
		$freeSlots = [];
		if ($durationMinutes <= 0) {
			return $freeSlots;
		}

		usort($intervals, function($a, $b) {
			return strtotime($a['start']) - strtotime($b['start']);
		});

		$cursor = $dayStart;
		foreach ($intervals as $interval) {
			$intStart = $interval['start'];
			$intFinish = $interval['finish'];

			if (strtotime($intStart) > strtotime($cursor)) {
				$gapMinutes = $this->minutesBetweenTimes($cursor, $intStart);
				if ($gapMinutes >= $durationMinutes) {
					$slotFinish = date('H:i:s', strtotime('+ '.$durationMinutes.' minutes', strtotime($cursor)));
					if (strtotime($slotFinish) <= strtotime($dayEnd)) {
						$freeSlots[] = ['start' => $cursor, 'finish' => $slotFinish];
					}
				}
			}

			if (strtotime($intFinish) > strtotime($cursor)) {
				$cursor = $intFinish;
			}
		}

		if (strtotime($cursor) < strtotime($dayEnd)) {
			$gapMinutes = $this->minutesBetweenTimes($cursor, $dayEnd);
			if ($gapMinutes >= $durationMinutes) {
				$slotFinish = date('H:i:s', strtotime('+ '.$durationMinutes.' minutes', strtotime($cursor)));
				if (strtotime($slotFinish) <= strtotime($dayEnd)) {
					$freeSlots[] = ['start' => $cursor, 'finish' => $slotFinish];
				}
			}
		}

		return $freeSlots;
	}

	private function hasRoomConflictForSlot($conventionSeasonId, $excludeTimingId, $roomId, $day, $startTime, $finishTime) {
		$cond = [];
		$cond[] = "(Schedulingtimings.conventionseasons_id = '".(int)$conventionSeasonId."')";
		$cond[] = "(Schedulingtimings.room_id = '".(int)$roomId."')";
		$cond[] = "(Schedulingtimings.day = '".addslashes($day)."')";
		$cond[] = "(Schedulingtimings.id != '".(int)$excludeTimingId."')";
		$cond[] = "('".$startTime."' < Schedulingtimings.finish_time AND '".$finishTime."' > Schedulingtimings.start_time)";

		return (bool)$this->Schedulingtimings->find()->where($cond)->first();
	}

	private function hasUserConflictForSlot($conventionSeasonId, $excludeTimingId, $timingRecord, $day, $startTime, $finishTime, $bufferMinutes = 5) {
		$userChecks = $this->getTimingParticipantIds($timingRecord, $conventionSeasonId);

		if (empty($userChecks)) {
			return false;
		}

		$checkConds = [];
		foreach ($userChecks as $uid) {
			$checkConds[] = "(Schedulingtimings.user_id = '".$uid."' OR Schedulingtimings.user_id_opponent = '".$uid."' OR FIND_IN_SET('".$uid."', Schedulingtimings.group_name_user_ids) OR FIND_IN_SET('".$uid."', Schedulingtimings.group_name_opponent_user_ids))";
		}

		$cond = [];
		$cond[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."')";
		$cond[] = "(Schedulingtimings.day = '".$day."')";
		$cond[] = "(Schedulingtimings.id != '".$excludeTimingId."')";
		$cond[] = "('".$startTime."' < ADDTIME(Schedulingtimings.finish_time, '00:".str_pad($bufferMinutes,2,'0',STR_PAD_LEFT).":00') AND '".$finishTime."' > SUBTIME(Schedulingtimings.start_time, '00:".str_pad($bufferMinutes,2,'0',STR_PAD_LEFT).":00'))";
		$cond[] = '('.implode(' OR ', $checkConds).')';

		return $this->Schedulingtimings->find()->where($cond)->count() > 0;
	}

	private function getGroupParticipantIds($conventionSeasonId, $userId, $eventId, $groupName) {
		$userId = (int)$userId;
		$eventId = (int)$eventId;
		$groupName = trim((string)$groupName);
		$cacheKey = $conventionSeasonId.'|'.$userId.'|'.$eventId.'|'.$groupName;

		if ($userId <= 0 || $eventId <= 0 || $groupName === '') {
			return [];
		}

		if (array_key_exists($cacheKey, $this->groupParticipantCache)) {
			return $this->groupParticipantCache[$cacheKey];
		}

		$groupUsers = $this->Crstudentevents->find()
			->where([
				'conventionseason_id' => (int)$conventionSeasonId,
				'user_id' => $userId,
				'event_id' => $eventId,
				'group_name' => $groupName,
			])
			->select('student_id')
			->order(['Crstudentevents.id' => 'ASC'])
			->all();

		$participantIds = array_values(array_unique(array_filter(array_map('intval', $groupUsers->extract('student_id')->toArray()))));
		$this->groupParticipantCache[$cacheKey] = $participantIds;
		return $participantIds;
	}

	private function getGroupParticipantCsv($conventionSeasonId, $userId, $eventId, $groupName) {
		$participantIds = $this->getGroupParticipantIds($conventionSeasonId, $userId, $eventId, $groupName);
		return !empty($participantIds) ? implode(',', $participantIds) : null;
	}

	private function getTimingParticipantIds($timingRecord, $conventionSeasonId, &$visitedTimingIds = []) {
		if (empty($timingRecord)) {
			return [];
		}

		$participants = [];
		$timingId = !empty($timingRecord->id) ? (int)$timingRecord->id : 0;
		if ($timingId > 0) {
			if (isset($visitedTimingIds[$timingId])) {
				return [];
			}
			$visitedTimingIds[$timingId] = true;
		}

		foreach (['group_name_user_ids', 'group_name_opponent_user_ids'] as $csvField) {
			if (!empty($timingRecord->{$csvField})) {
				$csvIds = array_map('intval', explode(',', $timingRecord->{$csvField}));
				$participants = array_merge($participants, array_filter($csvIds));
			}
		}

		$hasGroupParticipants = !empty($timingRecord->group_name) || !empty($timingRecord->group_name_opponent) || $timingRecord->user_type === 'School';
		if ($hasGroupParticipants) {
			$participants = array_merge(
				$participants,
				$this->getGroupParticipantIds($conventionSeasonId, $timingRecord->user_id, $timingRecord->event_id, $timingRecord->group_name),
				$this->getGroupParticipantIds($conventionSeasonId, $timingRecord->user_id_opponent, $timingRecord->event_id, $timingRecord->group_name_opponent)
			);
		} else {
			foreach (['user_id', 'user_id_opponent'] as $userField) {
				if (!empty($timingRecord->{$userField})) {
					$participants[] = (int)$timingRecord->{$userField};
				}
			}
		}

		foreach (['schtimeautoid1', 'schtimeautoid2'] as $sourceField) {
			$sourceTimingId = !empty($timingRecord->{$sourceField}) ? (int)$timingRecord->{$sourceField} : 0;
			if ($sourceTimingId <= 0 || isset($visitedTimingIds[$sourceTimingId])) {
				continue;
			}

			if (array_key_exists($sourceTimingId, $this->timingByIdCache)) {
				$sourceTiming = $this->timingByIdCache[$sourceTimingId];
			} else {
				$sourceTiming = $this->Schedulingtimings->find()
					->where(['Schedulingtimings.id' => $sourceTimingId])
					->first();
				$this->timingByIdCache[$sourceTimingId] = $sourceTiming ?: null;
			}
			if ($sourceTiming) {
				$participants = array_merge($participants, $this->getTimingParticipantIds($sourceTiming, $conventionSeasonId, $visitedTimingIds));
			}
		}

		return array_values(array_unique(array_filter(array_map('intval', $participants))));
	}

	/**
	 * Check if any of the given user IDs have a scheduling conflict at the
	 * proposed day/time.  Works during initial scheduling when group_name_user_ids
	 * may not yet be populated — checks user_id, user_id_opponent plus the
	 * FIND_IN_SET columns for already-saved records.
	 */
	private function hasUserIdsConflictForSlot($conventionSeasonId, $excludeTimingId, $userIds, $day, $startTime, $finishTime, $bufferMinutes = 5) {
		$userIds = array_filter(array_map('intval', (array)$userIds));
		if (empty($userIds)) {
			return false;
		}

		$checkConds = [];
		foreach ($userIds as $uid) {
			$checkConds[] = "(Schedulingtimings.user_id = '".$uid."' OR Schedulingtimings.user_id_opponent = '".$uid."' OR FIND_IN_SET('".$uid."', Schedulingtimings.group_name_user_ids) OR FIND_IN_SET('".$uid."', Schedulingtimings.group_name_opponent_user_ids))";
		}

		$bufPad = str_pad((int)$bufferMinutes, 2, '0', STR_PAD_LEFT);
		$cond = [];
		$cond[] = "(Schedulingtimings.conventionseasons_id = '".(int)$conventionSeasonId."')";
		$cond[] = "(Schedulingtimings.day = '".addslashes($day)."')";
		$cond[] = "(Schedulingtimings.id != '".(int)$excludeTimingId."')";
		$cond[] = "(Schedulingtimings.start_time IS NOT NULL)";
		$cond[] = "('".$startTime."' < ADDTIME(Schedulingtimings.finish_time, '00:".$bufPad.":00') AND '".$finishTime."' > SUBTIME(Schedulingtimings.start_time, '00:".$bufPad.":00'))";
		$cond[] = '('.implode(' OR ', $checkConds).')';

		return $this->Schedulingtimings->find()->where($cond)->count() > 0;
	}

	/**
	 * Advance start/finish times forward until the proposed user IDs have no
	 * scheduling conflict on the given day.  Returns adjusted times or advances
	 * to next day if the current day runs out of room.
	 *
	 * @return array ['start_time'=>..., 'finish_time'=>..., 'day'=>..., 'date'=>...]
	 */
	private function findUserConflictFreeSlot($conventionSeasonId, $userIds, $day, $startTime, $finishTime, $durationMinutes, $normalFinishTime, $schedulingsD, &$cntrDays) {
		$maxAttempts = 200;
		$attempt = 0;
		$bufferMinutes = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;

		while ($attempt < $maxAttempts) {
			$attempt++;

			if (!$this->hasUserIdsConflictForSlot($conventionSeasonId, 0, $userIds, $day, $startTime, $finishTime, $bufferMinutes)) {
				break;
			}

			// Advance by the event duration to try the next slot
			$startTime = date("H:i:s", strtotime('+'.$durationMinutes.' minutes', strtotime($startTime)));
			$finishTime = date("H:i:s", strtotime('+'.$durationMinutes.' minutes', strtotime($startTime)));

			// If past end of day, advance to next day
			if (strtotime($finishTime) > strtotime($normalFinishTime)) {
				$day = $this->getNextWeekDay($day);
				$cntrDays++;

				$normalStartTime = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
				$normalFinishTime = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

				$startTime = $normalStartTime;
				$finishTime = date("H:i:s", strtotime('+'.$durationMinutes.' minutes', strtotime($startTime)));
			}
		}

		return ['start_time' => $startTime, 'finish_time' => $finishTime, 'day' => $day];
	}

	private function retryUnscheduledRows($conventionSD, $schedulingsD, $startDate, $firstDay) {
		$unscheduledRows = $this->Schedulingtimings->find()
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.is_bye !=' => 1,
				'OR' => [
					['Schedulingtimings.day IS' => null],
					['Schedulingtimings.start_time IS' => null],
					['Schedulingtimings.finish_time IS' => null],
				],
			])
			->order(['Schedulingtimings.schedule_category' => 'ASC', 'Schedulingtimings.id' => 'ASC'])
			->all();

		$normalFinishTime = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

		foreach ($unscheduledRows as $timing) {
			$eventD = $this->Events->find()->where(['Events.id' => $timing->event_id])->first();
			if (empty($eventD)) {
				continue;
			}

			$durationMinutes = (int)$eventD->setup_time + (int)$eventD->round_time + (int)$eventD->judging_time;
			$roomResult = $this->findRoomsForEvent($conventionSD, $timing->event_id);
			$roomIds = $roomResult['rooms'];
			if (empty($roomIds)) {
				continue;
			}

			foreach ($roomIds as $roomId) {
				$lastRoomBooking = $this->Schedulingtimings->find()
					->where([
						'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
						'Schedulingtimings.room_id' => $roomId,
						'Schedulingtimings.start_time IS NOT' => null,
						'Schedulingtimings.finish_time IS NOT' => null,
					])
					->order(['Schedulingtimings.sch_date_time' => 'DESC', 'Schedulingtimings.finish_time' => 'DESC'])
					->first();

				if ($lastRoomBooking) {
					$candidateDay = $lastRoomBooking->day;
					$candidateDate = date('Y-m-d', strtotime($lastRoomBooking->sch_date_time));
					$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
					$candidateStart = date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($lastRoomBooking->finish_time)));
				} else {
					$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $this->getConventionBalancingDays($firstDay, 4));
					$candidateDay = !empty($balancedStartDay) ? $balancedStartDay : $firstDay;
					$candidateDate = $this->getDateForDayFromStart($startDate, $firstDay, $candidateDay);
					$candidateStart = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
				}

				$candidateFinish = date("H:i:s", strtotime('+ '.$durationMinutes.' minutes', strtotime($candidateStart)));
				$cntrDays = 1;
				$tc = $this->applyTimeConstraints(
					$schedulingsD,
					$durationMinutes,
					$roomId,
					$candidateStart,
					$candidateFinish,
					$candidateDay,
					$candidateDate,
					$cntrDays,
					$conventionSD->id,
					$firstDay,
					$startDate
				);

				$candidateStart = $tc['start_time'];
				$candidateFinish = $tc['finish_time'];
				$candidateDay = $tc['day'];
				$candidateDate = $tc['date'];

				if (!$this->isSchedulableConventionDay($candidateDay)) {
					continue;
				}

				if (strtotime($candidateFinish) > strtotime($normalFinishTime)) {
					continue;
				}

				if ($this->hasRoomConflictForSlot($conventionSD->id, $timing->id, $roomId, $candidateDay, $candidateStart, $candidateFinish)) {
					continue;
				}

				if (!empty($timing->user_id) && $timing->user_type !== 'School') {
					$userSlot = $this->findUserConflictFreeSlot(
						$conventionSD->id,
						[$timing->user_id],
						$candidateDay,
						$candidateStart,
						$candidateFinish,
						$durationMinutes,
						$normalFinishTime,
						$schedulingsD,
						$cntrDays
					);

					$candidateStart = $userSlot['start_time'];
					$candidateFinish = $userSlot['finish_time'];
					$candidateDay = $userSlot['day'];
					$candidateDate = $this->getDateForDayFromStart($startDate, $firstDay, $candidateDay);

					if (!$this->isSchedulableConventionDay($candidateDay) || $this->hasRoomConflictForSlot($conventionSD->id, $timing->id, $roomId, $candidateDay, $candidateStart, $candidateFinish)) {
						continue;
					}
				}

				$this->Schedulingtimings->updateAll(
				[
					'room_id' => $roomId,
					'day' => $candidateDay,
					'start_time' => $candidateStart,
					'finish_time' => $candidateFinish,
					'sch_date_time' => $candidateDate.' '.date("H:i:s", strtotime($candidateStart)),
					'modified' => date("Y-m-d H:i:s"),
				],
				['id' => $timing->id]
				);
				break;
			}
		}
	}

	private function buildOverflowSuggestionsForTiming($timing, $roomMap, $occupied, $allowedDays, $schedulingD, $firstDay, $conventionSeasonId, $limit = 8) {
		$durationMinutes = $this->calculateEventDurationMinutes($timing->Events);
		$suggestions = [];

		$lunchStart = !empty($schedulingD->lunch_time_start) ? date('H:i:s', strtotime($schedulingD->lunch_time_start)) : null;
		$lunchEnd = !empty($schedulingD->lunch_time_end) ? date('H:i:s', strtotime($schedulingD->lunch_time_end)) : null;

		foreach ($allowedDays as $dayName) {
			$dayStart = date('H:i:s', strtotime($schedulingD->normal_starting_time));
			$dayFinish = date('H:i:s', strtotime($schedulingD->normal_finish_time));
			if ((int)$schedulingD->starting_different_time_first_day_yes_no === 1 && $dayName === $firstDay) {
				$dayStart = date('H:i:s', strtotime($schedulingD->different_first_day_start_time));
				$dayFinish = date('H:i:s', strtotime($schedulingD->different_first_day_end_time));
			}

			foreach ($roomMap as $roomId => $roomName) {
				$intervals = isset($occupied[$roomId][$dayName]) ? $occupied[$roomId][$dayName] : [];

				if ($lunchStart && $lunchEnd && strtotime($lunchEnd) > strtotime($lunchStart)) {
					$intervals[] = ['start' => $lunchStart, 'finish' => $lunchEnd];
				}

				$freeSlots = $this->buildFreeSlotsForDay($intervals, $dayStart, $dayFinish, $durationMinutes);
				foreach ($freeSlots as $slot) {
					$bufferMinutes = isset($schedulingD->buffer_minutes) && $schedulingD->buffer_minutes !== null ? (int)$schedulingD->buffer_minutes : 5;
					if ($this->hasUserConflictForSlot($conventionSeasonId, $timing->id, $timing, $dayName, $slot['start'], $slot['finish'], $bufferMinutes)) {
						continue;
					}

					$suggestions[] = [
						'room_id' => $roomId,
						'room_name' => $roomName,
						'day' => $dayName,
						'start_time' => $slot['start'],
						'finish_time' => $slot['finish']
					];

					if (count($suggestions) >= $limit) {
						return $suggestions;
					}
				}
			}
		}

		return $suggestions;
	}

	private function ensureSchedulingAutoassignRunsTable() {
		try {
			$conn = ConnectionManager::get('default');
			$conn->execute(
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
		} catch (\Exception $e) {
			// Keep scheduling flow resilient if table creation fails.
		}
	}

	private function countOverflowForSeason($conventionSeasonId, $scheduleCategory = null) {
		$query = $this->Schedulingtimings->find()
			->where(['Schedulingtimings.conventionseasons_id' => (int)$conventionSeasonId])
			->andWhere(function($exp) {
				return $exp->or_([
					'Schedulingtimings.day IN' => ['Friday','Saturday','Sunday'],
					'Schedulingtimings.day IS' => null,
					'Schedulingtimings.start_time IS' => null,
					'Schedulingtimings.finish_time IS' => null,
				]);
			});

		if ($scheduleCategory !== null) {
			$query->where(['Schedulingtimings.schedule_category' => (int)$scheduleCategory]);
		}

		return (int)$query->count();
	}

	private function saveAutoassignRunSummary($conventionSeasonId, $scheduleCategory, $assignedCount, $remainingCount, $overflowBefore, $overflowAfter, $days = [], $rooms = [], $source = 'manual') {
		$this->ensureSchedulingAutoassignRunsTable();
		try {
			$conn = ConnectionManager::get('default');
			$conn->execute(
				"INSERT INTO scheduling_autoassign_runs
				(conventionseason_id, schedule_category, assigned_count, remaining_count, overflow_before, overflow_after, filter_days, filter_rooms, trigger_source, created)
				VALUES
				(:conventionseason_id, :schedule_category, :assigned_count, :remaining_count, :overflow_before, :overflow_after, :filter_days, :filter_rooms, :trigger_source, :created)",
				[
					'conventionseason_id' => (int)$conventionSeasonId,
					'schedule_category' => $scheduleCategory === null ? null : (int)$scheduleCategory,
					'assigned_count' => (int)$assignedCount,
					'remaining_count' => (int)$remainingCount,
					'overflow_before' => (int)$overflowBefore,
					'overflow_after' => (int)$overflowAfter,
					'filter_days' => !empty($days) ? implode(',', $days) : null,
					'filter_rooms' => !empty($rooms) ? implode(',', array_map('intval', (array)$rooms)) : null,
					'trigger_source' => (string)$source,
					'created' => date('Y-m-d H:i:s'),
				],
				[
					'conventionseason_id' => 'integer',
					'schedule_category' => 'integer',
					'assigned_count' => 'integer',
					'remaining_count' => 'integer',
					'overflow_before' => 'integer',
					'overflow_after' => 'integer',
					'filter_days' => 'string',
					'filter_rooms' => 'string',
					'trigger_source' => 'string',
					'created' => 'string',
				]
			);
		} catch (\Exception $e) {
			// Keep flow resilient if persistence fails.
		}
	}

	private function ensureSchedulingConflictAuditsTable() {
		try {
			$conn = ConnectionManager::get('default');
			$conn->execute(
				"CREATE TABLE IF NOT EXISTS scheduling_conflict_audits (
					id INT AUTO_INCREMENT PRIMARY KEY,
					conventionseason_id INT NOT NULL,
					convention_id INT NULL,
					season_id INT NULL,
					season_year VARCHAR(16) NULL,
					conflict_user_count INT NOT NULL DEFAULT 0,
					conflict_group_row_count INT NOT NULL DEFAULT 0,
					conflict_timing_row_count INT NOT NULL DEFAULT 0,
					trigger_source VARCHAR(64) NOT NULL DEFAULT 'listconflicts',
					notes VARCHAR(255) NULL,
					created DATETIME NOT NULL,
					INDEX idx_sca_csid_created (conventionseason_id, created)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
			);
		} catch (\Exception $e) {
			// Keep scheduling flow resilient if table creation fails.
		}
	}

	private function saveConflictAuditRun($conventionSD, $conflictUserCount, $conflictGroupRowCount, $conflictTimingRowCount, $notes = '') {
		if (empty($conventionSD) || empty($conventionSD->id)) {
			return;
		}

		$this->ensureSchedulingConflictAuditsTable();
		try {
			$conn = ConnectionManager::get('default');
			$conn->execute(
				"INSERT INTO scheduling_conflict_audits
				(conventionseason_id, convention_id, season_id, season_year, conflict_user_count, conflict_group_row_count, conflict_timing_row_count, trigger_source, notes, created)
				VALUES
				(:conventionseason_id, :convention_id, :season_id, :season_year, :conflict_user_count, :conflict_group_row_count, :conflict_timing_row_count, :trigger_source, :notes, :created)",
				[
					'conventionseason_id' => (int)$conventionSD->id,
					'convention_id' => (int)$conventionSD->convention_id,
					'season_id' => (int)$conventionSD->season_id,
					'season_year' => (string)$conventionSD->season_year,
					'conflict_user_count' => (int)$conflictUserCount,
					'conflict_group_row_count' => (int)$conflictGroupRowCount,
					'conflict_timing_row_count' => (int)$conflictTimingRowCount,
					'trigger_source' => 'listconflicts',
					'notes' => (string)$notes,
					'created' => date('Y-m-d H:i:s'),
				],
				[
					'conventionseason_id' => 'integer',
					'convention_id' => 'integer',
					'season_id' => 'integer',
					'season_year' => 'string',
					'conflict_user_count' => 'integer',
					'conflict_group_row_count' => 'integer',
					'conflict_timing_row_count' => 'integer',
					'trigger_source' => 'string',
					'notes' => 'string',
					'created' => 'string',
				]
			);
		} catch (\Exception $e) {
			// Keep scheduling flow resilient if audit insert fails.
		}
	}

	private function getOverflowTrendRows($conventionSeasonId, $limit = 10) {
		$this->ensureSchedulingAutoassignRunsTable();
		try {
			$conn = ConnectionManager::get('default');
			$rows = $conn->execute(
				"SELECT id, schedule_category, assigned_count, remaining_count, overflow_before, overflow_after, filter_days, trigger_source, created
				 FROM scheduling_autoassign_runs
				 WHERE conventionseason_id = :csid
				 ORDER BY id DESC
				 LIMIT :row_limit",
				['csid' => (int)$conventionSeasonId, 'row_limit' => (int)$limit],
				['csid' => 'integer', 'row_limit' => 'integer']
			)->fetchAll('assoc');

			return is_array($rows) ? $rows : [];
		} catch (\Exception $e) {
			return [];
		}
	}

	public function exportautoassignruns($convention_season_slug=null) {
		$conventionSD = $this->Conventionseasons->find()
			->where(['Conventionseasons.slug' => $convention_season_slug])
			->contain(['Conventions'])
			->first();

		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$rows = $this->getOverflowTrendRows((int)$conventionSD->id, 1000);
		$filename = 'autoassign-runs-' . $convention_season_slug . '-' . date('Ymd-His') . '.csv';

		$stream = fopen('php://temp', 'w+');
		fputcsv($stream, ['run_id', 'created', 'source', 'schedule_category', 'overflow_before', 'assigned_count', 'overflow_after', 'remaining_count', 'filter_days', 'filter_rooms']);
		foreach ($rows as $row) {
			fputcsv($stream, [
				isset($row['id']) ? (int)$row['id'] : '',
				isset($row['created']) ? $row['created'] : '',
				isset($row['trigger_source']) ? $row['trigger_source'] : '',
				array_key_exists('schedule_category', $row) && $row['schedule_category'] !== null ? (int)$row['schedule_category'] : 'All',
				isset($row['overflow_before']) ? (int)$row['overflow_before'] : 0,
				isset($row['assigned_count']) ? (int)$row['assigned_count'] : 0,
				isset($row['overflow_after']) ? (int)$row['overflow_after'] : 0,
				isset($row['remaining_count']) ? (int)$row['remaining_count'] : 0,
				isset($row['filter_days']) ? $row['filter_days'] : '',
				isset($row['filter_rooms']) ? $row['filter_rooms'] : '',
			]);
		}
		rewind($stream);
		$csv = stream_get_contents($stream);
		fclose($stream);

		$this->autoRender = false;
		return $this->response
			->withType('csv')
			->withDownload($filename)
			->withStringBody($csv === false ? '' : $csv);
	}

	private function getConflictAuditRows($conventionSeasonId, $limit = 100) {
		$this->ensureSchedulingConflictAuditsTable();
		try {
			$conn = ConnectionManager::get('default');
			$rows = $conn->execute(
				"SELECT id, conventionseason_id, convention_id, season_id, season_year, conflict_user_count, conflict_group_row_count, conflict_timing_row_count, trigger_source, notes, created
				 FROM scheduling_conflict_audits
				 WHERE conventionseason_id = :csid
				 ORDER BY id DESC
				 LIMIT :row_limit",
				['csid' => (int)$conventionSeasonId, 'row_limit' => (int)$limit],
				['csid' => 'integer', 'row_limit' => 'integer']
			)->fetchAll('assoc');

			return is_array($rows) ? $rows : [];
		} catch (\Exception $e) {
			return [];
		}
	}

	public function conflictaudits($convention_season_slug = null) {
		$conventionSD = $this->Conventionseasons->find()
			->where(['Conventionseasons.slug' => $convention_season_slug])
			->contain(['Conventions'])
			->first();

		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$rows = $this->getConflictAuditRows((int)$conventionSD->id, 300);

		$this->set('title', ADMIN_TITLE . 'Conflict Audit Trail');
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->convention->slug);
		$this->set('convention_season_slug', $convention_season_slug);
		$this->set('conflictAuditRows', $rows);
	}

	public function exportconflictaudits($convention_season_slug = null) {
		$conventionSD = $this->Conventionseasons->find()
			->where(['Conventionseasons.slug' => $convention_season_slug])
			->contain(['Conventions'])
			->first();

		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$rows = $this->getConflictAuditRows((int)$conventionSD->id, 2000);
		$filename = 'conflict-audits-' . $convention_season_slug . '-' . date('Ymd-His') . '.csv';

		$stream = fopen('php://temp', 'w+');
		fputcsv($stream, ['run_id', 'created', 'trigger_source', 'conventionseason_id', 'convention_id', 'season_id', 'season_year', 'conflict_user_count', 'conflict_group_row_count', 'conflict_timing_row_count', 'notes']);
		foreach ($rows as $row) {
			fputcsv($stream, [
				isset($row['id']) ? (int)$row['id'] : '',
				isset($row['created']) ? $row['created'] : '',
				isset($row['trigger_source']) ? $row['trigger_source'] : '',
				isset($row['conventionseason_id']) ? (int)$row['conventionseason_id'] : '',
				isset($row['convention_id']) ? (int)$row['convention_id'] : '',
				isset($row['season_id']) ? (int)$row['season_id'] : '',
				isset($row['season_year']) ? $row['season_year'] : '',
				isset($row['conflict_user_count']) ? (int)$row['conflict_user_count'] : 0,
				isset($row['conflict_group_row_count']) ? (int)$row['conflict_group_row_count'] : 0,
				isset($row['conflict_timing_row_count']) ? (int)$row['conflict_timing_row_count'] : 0,
				isset($row['notes']) ? $row['notes'] : '',
			]);
		}
		rewind($stream);
		$csv = stream_get_contents($stream);
		fclose($stream);

		$this->autoRender = false;
		return $this->response
			->withType('csv')
			->withDownload($filename)
			->withStringBody($csv === false ? '' : $csv);
	}

	private function buildScheduleHealthMetrics($conventionSeasonId, $schedulingD) {
		$metrics = [
			'room_conflicts' => 0,
			'same_category_participant_conflicts' => 0,
			'cross_category_participant_conflicts' => 0,
			'room_utilization' => [],
			'average_room_utilization_pct' => 0.0,
		];

		try {
			$conn = ConnectionManager::get('default');

			$roomConflictRow = $conn->execute(
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

			$sameCatRow = $conn->execute(
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
			$metrics['same_category_participant_conflicts'] = !empty($sameCatRow['cnt']) ? (int)$sameCatRow['cnt'] : 0;

			$crossCatRow = $conn->execute(
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
			$metrics['cross_category_participant_conflicts'] = !empty($crossCatRow['cnt']) ? (int)$crossCatRow['cnt'] : 0;

			$monThuDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
			$roomRows = $this->Schedulingtimings->find()
				->select(['room_id'])
				->where([
					'Schedulingtimings.conventionseasons_id' => (int)$conventionSeasonId,
					'Schedulingtimings.day IN' => $monThuDays,
					'Schedulingtimings.room_id IS NOT' => null,
					'Schedulingtimings.start_time IS NOT' => null,
					'Schedulingtimings.finish_time IS NOT' => null,
				])
				->group(['room_id'])
				->enableHydration(false)
				->toArray();

			$roomIds = array_map('intval', array_column($roomRows, 'room_id'));
			if (!empty($roomIds)) {
				$rooms = $this->Conventionrooms->find()
					->where(['Conventionrooms.id IN' => $roomIds])
					->all()
					->toArray();

				$roomNameMap = [];
				foreach ($rooms as $room) {
					$roomNameMap[(int)$room->id] = $room->room_name;
				}

				$capacityPerDay = 0;
				$firstDay = !empty($schedulingD->first_day) ? $schedulingD->first_day : 'Monday';
				$lunchStart = !empty($schedulingD->lunch_time_start) ? date('H:i:s', strtotime($schedulingD->lunch_time_start)) : null;
				$lunchEnd = !empty($schedulingD->lunch_time_end) ? date('H:i:s', strtotime($schedulingD->lunch_time_end)) : null;
				foreach ($monThuDays as $dayName) {
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

				$totalUtil = 0.0;
				$utilCount = 0;
				foreach ($roomIds as $rid) {
					$occupiedRow = $conn->execute(
						"SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_time, finish_time)), 0) AS minutes_used
						 FROM schedulingtimings
						 WHERE conventionseasons_id = :csid
						   AND room_id = :rid
						   AND day IN ('Monday','Tuesday','Wednesday','Thursday')
						   AND start_time IS NOT NULL
						   AND finish_time IS NOT NULL",
						['csid' => (int)$conventionSeasonId, 'rid' => (int)$rid],
						['csid' => 'integer', 'rid' => 'integer']
					)->fetch('assoc');

					$minutesUsed = !empty($occupiedRow['minutes_used']) ? (int)$occupiedRow['minutes_used'] : 0;
					$utilPct = $capacityPerDay > 0 ? round(($minutesUsed / $capacityPerDay) * 100, 1) : 0.0;
					$metrics['room_utilization'][] = [
						'room_id' => (int)$rid,
						'room_name' => isset($roomNameMap[(int)$rid]) ? $roomNameMap[(int)$rid] : ('Room '.$rid),
						'minutes_used' => $minutesUsed,
						'capacity_minutes' => $capacityPerDay,
						'utilization_pct' => $utilPct,
					];
					$totalUtil += $utilPct;
					$utilCount++;
				}

				if ($utilCount > 0) {
					$metrics['average_room_utilization_pct'] = round($totalUtil / $utilCount, 1);
				}
			}
		} catch (\Exception $e) {
			return $metrics;
		}

		return $metrics;
	}

	public function autoassignoverflow($convention_season_slug=null, $scheduling_category=null) {
		$overflowBefore = 0;
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}
		$overflowBefore = $this->countOverflowForSeason($conventionSD->id, $scheduling_category);

		$schedulingD = $this->Schedulings->find()->where([
			'Schedulings.conventionseasons_id' => $conventionSD->id,
			'Schedulings.convention_id' => $conventionSD->convention_id,
			'Schedulings.season_id' => $conventionSD->season_id,
			'Schedulings.season_year' => $conventionSD->season_year
		])->first();

		if (!$schedulingD) {
			$this->Flash->error('Scheduling setup not found. Please complete Scheduling Wizard first.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		$allAllowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
		$selectedDays = (array)$this->request->getQuery('days');
		$selectedDays = array_values(array_intersect($allAllowedDays, $selectedDays));
		if (empty($selectedDays)) {
			$selectedDays = $allAllowedDays;
		}
		$startDate = date('Y-m-d', strtotime($schedulingD->start_date));
		$firstDay = $schedulingD->first_day;

		$rooms = $this->Conventionrooms->find()
			->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
			->order(['Conventionrooms.room_name' => 'ASC'])
			->all();

		$allRoomMap = [];
		foreach ($rooms as $room) {
			$allRoomMap[$room->id] = $room->room_name;
		}

		$selectedRoomIds = array_map('intval', (array)$this->request->getQuery('rooms'));
		$selectedRoomIds = array_values(array_filter($selectedRoomIds, function($roomId) use ($allRoomMap) {
			return isset($allRoomMap[$roomId]);
		}));
		if (empty($selectedRoomIds)) {
			$selectedRoomIds = array_map('intval', array_keys($allRoomMap));
		}

		$roomMap = [];
		foreach ($selectedRoomIds as $roomId) {
			$roomMap[$roomId] = $allRoomMap[$roomId];
		}

		$filtersQuery = ['days' => $selectedDays, 'rooms' => $selectedRoomIds];

		$assignedRows = $this->Schedulingtimings->find()
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.day IN' => $selectedDays,
				'Schedulingtimings.room_id IN' => $selectedRoomIds,
				'Schedulingtimings.room_id IS NOT' => null,
				'Schedulingtimings.start_time IS NOT' => null,
				'Schedulingtimings.finish_time IS NOT' => null
			])
			->select(['id', 'room_id', 'day', 'start_time', 'finish_time'])
			->all();

		$occupied = [];
		foreach ($assignedRows as $row) {
			$rid = (int)$row->room_id;
			$day = $row->day;
			if (!isset($occupied[$rid])) {
				$occupied[$rid] = [];
			}
			if (!isset($occupied[$rid][$day])) {
				$occupied[$rid][$day] = [];
			}
			$occupied[$rid][$day][] = ['start' => date('H:i:s', strtotime($row->start_time)), 'finish' => date('H:i:s', strtotime($row->finish_time))];
		}

		$overflowCond = [];
		$overflowCond[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."')";
		$overflowCond[] = "(Schedulingtimings.schedule_category = '".$scheduling_category."')";
		$overflowCond[] = "(Schedulingtimings.day IN ('Friday','Saturday','Sunday') OR Schedulingtimings.day IS NULL OR Schedulingtimings.start_time IS NULL OR Schedulingtimings.finish_time IS NULL)";

		$overflowTimings = $this->Schedulingtimings->find()
			->where($overflowCond)
			->contain(['Events', 'Users', 'Conventionrooms'])
			->all()
			->toArray();

		usort($overflowTimings, function($a, $b) {
			$durA = $this->calculateEventDurationMinutes($a->Events);
			$durB = $this->calculateEventDurationMinutes($b->Events);
			if ($durA === $durB) {
				return $a->id - $b->id;
			}
			return $durB - $durA;
		});

		$assignedCount = 0;
		$remainingCount = 0;

		foreach ($overflowTimings as $timing) {
			$suggestions = $this->buildOverflowSuggestionsForTiming($timing, $roomMap, $occupied, $selectedDays, $schedulingD, $firstDay, $conventionSD->id, 1);
			if (empty($suggestions)) {
				$remainingCount++;
				continue;
			}

			$slot = $suggestions[0];
			$slotDate = $this->getDateForDayFromStart($startDate, $firstDay, $slot['day']);

			$this->Schedulingtimings->updateAll(
			[
				'room_id' => $slot['room_id'],
				'day' => $slot['day'],
				'start_time' => $slot['start_time'],
				'finish_time' => $slot['finish_time'],
				'sch_date_time' => $slotDate.' '.$slot['start_time'],
				'modified' => date('Y-m-d H:i:s')
			],
			['id' => $timing->id]
			);

			if (!isset($occupied[$slot['room_id']])) {
				$occupied[$slot['room_id']] = [];
			}
			if (!isset($occupied[$slot['room_id']][$slot['day']])) {
				$occupied[$slot['room_id']][$slot['day']] = [];
			}
			$occupied[$slot['room_id']][$slot['day']][] = ['start' => $slot['start_time'], 'finish' => $slot['finish_time']];

			$assignedCount++;
		}

		if ($assignedCount > 0) {
			$this->Flash->success('Auto-assign complete. Assigned '.$assignedCount.' overflow events. Remaining unassigned: '.$remainingCount.'.');
		} else {
			$this->Flash->error('Auto-assign could not place any overflow events with current constraints.');
		}

		$overflowAfter = $this->countOverflowForSeason($conventionSD->id, $scheduling_category);
		$this->saveAutoassignRunSummary(
			$conventionSD->id,
			(int)$scheduling_category,
			(int)$assignedCount,
			(int)$remainingCount,
			(int)$overflowBefore,
			(int)$overflowAfter,
			$selectedDays,
			$selectedRoomIds,
			'autoassignoverflow'
		);

		$this->request->getSession()->write('overflow_last_autoassign', [
			'conventionseason_id' => (int)$conventionSD->id,
			'scheduling_category' => (int)$scheduling_category,
			'assigned' => (int)$assignedCount,
			'remaining' => (int)$remainingCount,
			'created' => date('Y-m-d H:i:s')
		]);

		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'overflowallocator', $convention_season_slug, $scheduling_category, '?' => $filtersQuery]);
	}

	public function overflowallocator($convention_season_slug=null, $scheduling_category=null) {
		$this->set('title', ADMIN_TITLE . 'Overflow Reallocation');
		$this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);
		$this->set('scheduling_category', $scheduling_category);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where([
			'Schedulings.conventionseasons_id' => $conventionSD->id,
			'Schedulings.convention_id' => $conventionSD->convention_id,
			'Schedulings.season_id' => $conventionSD->season_id,
			'Schedulings.season_year' => $conventionSD->season_year
		])->first();

		if (!$schedulingD) {
			$this->Flash->error('Scheduling setup not found. Please complete Scheduling Wizard first.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		$allAllowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
		$selectedDays = (array)$this->request->getQuery('days');
		$selectedDays = array_values(array_intersect($allAllowedDays, $selectedDays));
		if (empty($selectedDays)) {
			$selectedDays = $allAllowedDays;
		}
		$startDate = date('Y-m-d', strtotime($schedulingD->start_date));
		$firstDay = $schedulingD->first_day;

		if ($this->request->is('post')) {
			$requestData = $this->request->getData();
			$data = isset($requestData['Overflow']) ? $requestData['Overflow'] : [];
			$timingId = isset($data['timing_id']) ? (int)$data['timing_id'] : 0;
			$roomId = isset($data['room_id']) ? (int)$data['room_id'] : 0;
			$day = isset($data['day']) ? trim($data['day']) : '';
			$startTime = isset($data['start_time']) ? trim($data['start_time']) : '';
			$finishTime = isset($data['finish_time']) ? trim($data['finish_time']) : '';

			$timingD = $this->Schedulingtimings->find()->where([
				'Schedulingtimings.id' => $timingId,
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category' => $scheduling_category
			])->contain(['Events'])->first();

			if (!$timingD) {
				$this->Flash->error('Selected overflow event was not found.');
			} elseif (!in_array($day, $allAllowedDays, true)) {
				$this->Flash->error('Only Monday to Thursday is allowed.');
			} elseif (strtotime($startTime) === false || strtotime($finishTime) === false || strtotime($startTime) >= strtotime($finishTime)) {
				$this->Flash->error('Invalid time slot selected.');
			} else {
				$dayStart = date('H:i:s', strtotime($schedulingD->normal_starting_time));
				$dayFinish = date('H:i:s', strtotime($schedulingD->normal_finish_time));
				if ((int)$schedulingD->starting_different_time_first_day_yes_no === 1 && $day === $firstDay) {
					$dayStart = date('H:i:s', strtotime($schedulingD->different_first_day_start_time));
					$dayFinish = date('H:i:s', strtotime($schedulingD->different_first_day_end_time));
				}

				$slotOk = true;
				$msg = '';

				if (strtotime($startTime) < strtotime($dayStart) || strtotime($finishTime) > strtotime($dayFinish)) {
					$slotOk = false;
					$msg = 'Selected slot is outside allowed day timings.';
				}

				if ($slotOk && !empty($schedulingD->lunch_time_start) && !empty($schedulingD->lunch_time_end)) {
					$lunchStart = date('H:i:s', strtotime($schedulingD->lunch_time_start));
					$lunchEnd = date('H:i:s', strtotime($schedulingD->lunch_time_end));
					if ($this->overlapsTimes($startTime, $finishTime, $lunchStart, $lunchEnd)) {
						$slotOk = false;
						$msg = 'Selected slot overlaps lunch break.';
					}
				}

				if ($slotOk && $this->hasRoomConflictForSlot($conventionSD->id, $timingId, $roomId, $day, $startTime, $finishTime)) {
					$slotOk = false;
					$msg = 'Selected room is not free at that time.';
				}

				$bufferMinutes = isset($schedulingD->buffer_minutes) && $schedulingD->buffer_minutes !== null ? (int)$schedulingD->buffer_minutes : 5;
				if ($slotOk && $this->hasUserConflictForSlot($conventionSD->id, $timingId, $timingD, $day, $startTime, $finishTime, $bufferMinutes)) {
					$slotOk = false;
					$msg = 'Selected user has another clash at that time.';
				}

				if ($slotOk) {
					$slotDate = $this->getDateForDayFromStart($startDate, $firstDay, $day);
					$filtersQuery = ['days' => $selectedDays, 'rooms' => array_map('intval', (array)$this->request->getQuery('rooms'))];
					$this->Schedulingtimings->updateAll(
					[
						'room_id' => $roomId,
						'day' => $day,
						'start_time' => date('H:i:s', strtotime($startTime)),
						'finish_time' => date('H:i:s', strtotime($finishTime)),
						'sch_date_time' => $slotDate.' '.date('H:i:s', strtotime($startTime)),
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => $timingId]
					);

					$this->Flash->success('Overflow event reallocated successfully.');
					return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'overflowallocator', $convention_season_slug, $scheduling_category, '?' => $filtersQuery]);
				}

				$this->Flash->error($msg);
			}
		}

		$overflowCond = [];
		$overflowCond[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."')";
		$overflowCond[] = "(Schedulingtimings.schedule_category = '".$scheduling_category."')";
		$overflowCond[] = "(Schedulingtimings.day IN ('Friday','Saturday','Sunday') OR Schedulingtimings.day IS NULL OR Schedulingtimings.start_time IS NULL OR Schedulingtimings.finish_time IS NULL)";

		$baseCountCond = [
			'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
			'Schedulingtimings.schedule_category' => $scheduling_category
		];

		$weekendOverflowCount = $this->Schedulingtimings->find()->where(array_merge($baseCountCond, [
			'Schedulingtimings.day IN' => ['Friday','Saturday','Sunday']
		]))->count();

		$unplacedCount = $this->Schedulingtimings->find()->where($baseCountCond)
			->andWhere(function($exp) {
				return $exp->or_([
					'Schedulingtimings.day IS' => null,
					'Schedulingtimings.start_time IS' => null,
					'Schedulingtimings.finish_time IS' => null,
				]);
			})
			->count();

		$lastAutoAssign = $this->request->getSession()->read('overflow_last_autoassign');
		if (
			empty($lastAutoAssign) ||
			(int)$lastAutoAssign['conventionseason_id'] !== (int)$conventionSD->id ||
			(int)$lastAutoAssign['scheduling_category'] !== (int)$scheduling_category
		) {
			$lastAutoAssign = null;
		}

		$trendRows = $this->getOverflowTrendRows($conventionSD->id, 30);
		if (!empty($trendRows)) {
			foreach ($trendRows as $trendRow) {
				if ((int)$trendRow['schedule_category'] === (int)$scheduling_category) {
					$lastAutoAssign = [
						'conventionseason_id' => (int)$conventionSD->id,
						'scheduling_category' => (int)$scheduling_category,
						'assigned' => (int)$trendRow['assigned_count'],
						'remaining' => (int)$trendRow['remaining_count'],
						'created' => $trendRow['created']
					];
					break;
				}
			}
		}

		$overflowTimings = $this->Schedulingtimings->find()
			->where($overflowCond)
			->contain(['Events', 'Users', 'Conventionrooms'])
			->order(['Schedulingtimings.id' => 'ASC'])
			->all();

		$rooms = $this->Conventionrooms->find()
			->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
			->order(['Conventionrooms.room_name' => 'ASC'])
			->all();

		$allRoomMap = [];
		foreach ($rooms as $room) {
			$allRoomMap[$room->id] = $room->room_name;
		}

		$selectedRoomIds = array_map('intval', (array)$this->request->getQuery('rooms'));
		$selectedRoomIds = array_values(array_filter($selectedRoomIds, function($roomId) use ($allRoomMap) {
			return isset($allRoomMap[$roomId]);
		}));
		if (empty($selectedRoomIds)) {
			$selectedRoomIds = array_map('intval', array_keys($allRoomMap));
		}

		$roomMap = [];
		foreach ($selectedRoomIds as $roomId) {
			$roomMap[$roomId] = $allRoomMap[$roomId];
		}

		$assignedRows = $this->Schedulingtimings->find()
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.day IN' => $selectedDays,
				'Schedulingtimings.room_id IN' => $selectedRoomIds,
				'Schedulingtimings.room_id IS NOT' => null,
				'Schedulingtimings.start_time IS NOT' => null,
				'Schedulingtimings.finish_time IS NOT' => null
			])
			->select(['id', 'room_id', 'day', 'start_time', 'finish_time'])
			->all();

		$occupied = [];
		foreach ($assignedRows as $row) {
			$rid = (int)$row->room_id;
			$day = $row->day;
			if (!isset($occupied[$rid])) {
				$occupied[$rid] = [];
			}
			if (!isset($occupied[$rid][$day])) {
				$occupied[$rid][$day] = [];
			}
			$occupied[$rid][$day][] = ['start' => date('H:i:s', strtotime($row->start_time)), 'finish' => date('H:i:s', strtotime($row->finish_time))];
		}

		$overflowRows = [];
		foreach ($overflowTimings as $timing) {
			$durationMinutes = $this->calculateEventDurationMinutes($timing->Events);
			$suggestions = $this->buildOverflowSuggestionsForTiming($timing, $roomMap, $occupied, $selectedDays, $schedulingD, $firstDay, $conventionSD->id, 8);

			$overflowRows[] = [
				'timing' => $timing,
				'duration_minutes' => $durationMinutes,
				'suggestions' => $suggestions
			];
		}

		$this->set('overflowRows', $overflowRows);
		$this->set('allowedDays', $allAllowedDays);
		$this->set('allRoomMap', $allRoomMap);
		$this->set('selectedDays', $selectedDays);
		$this->set('selectedRoomIds', $selectedRoomIds);
		$this->set('weekendOverflowCount', $weekendOverflowCount);
		$this->set('unplacedCount', $unplacedCount);
		$this->set('lastAutoAssign', $lastAutoAssign);
		$this->set('scheduleHealth', $this->buildScheduleHealthMetrics((int)$conventionSD->id, $schedulingD));
		$this->set('overflowTrendRows', $this->getOverflowTrendRows((int)$conventionSD->id, 12));
	}

    public function startschedulec1($convention_season_slug=null) {


        $this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		// first of all clear all scheduling for this category & convention, season
		/* $this->Schedulingtimings->deleteAll(["schedule_category" => 1, "conventionseasons_id" => $conventionSD->id, "convention_id" => $conventionSD->convention_id, "season_id" => $conventionSD->season_id, "season_year" => $conventionSD->season_year]); */

		/* We need to clear all scheduling for this convention season + clear conflicts */
		$this->clearSchedulingtimings($convention_season_slug);


		// to get details of schedule timings
		$cfg = $this->loadSchedulingConfig($conventionSD);
		$schedulingsD = $cfg['schedulingsD'];
		$start_date = $cfg['start_date'];
		$first_day = $cfg['first_day'];
		$normal_starting_time = $cfg['normal_starting_time'];
		$normal_finish_time = $cfg['normal_finish_time'];
		$lunch_time_start = $cfg['lunch_time_start'];
		$lunch_time_end = $cfg['lunch_time_end'];
		$starting_different_time_first_day_yes_no = $cfg['starting_different_time_first_day_yes_no'];
		$different_first_day_start_time = $cfg['different_first_day_start_time'];
		$different_first_day_end_time = $cfg['different_first_day_end_time'];


		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = yes || event_kind_id = sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$allEventsCS = $this->getEventsForCategory($conventionSD, true, 'Sequential', true);
		foreach($allEventsCS as $eventIDCS)
		{
			$mainArrForEvent = array();
			// to check if this event require schedule

			$eventD = $this->Events->find()->where(['Events.id' => $eventIDCS])->first();

			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $eventIDCS);
			$roomArrCSEvent = $roomResult['rooms'];
			$eventSpbValue = $roomResult['spb'];
			//$this->prx($roomArrCSEvent);

			// Check if there's only one room, then duplicate
			/* if (count($roomArrCSEvent) === 1) {
				// Duplicate the same record up to 4 times
				while (count($roomArrCSEvent) < 4) {
					$roomArrCSEvent[] = $roomArrCSEvent[0];
				}
			} */



			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent)>0)
			{
				$mainArrForEvent = $this->getGroupsForEvent($conventionSD, $eventIDCS);


				// now define timings for schedule for this event

				//echo 'eeee';
				//$this->prx($mainArrForEvent);

				if(count((array)$mainArrForEvent))
				{
					$cntrDays = 1;
					$resetTime = 1;
					$balancingDays = $this->getConventionBalancingDays($first_day, 4);
					// Initial day assignment
					$schDay = $first_day;
					$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);

					$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
					// now firstly choose first room
					$cntrRoomCSEvent = 0;

					shuffle($mainArrForEvent);
					//$this->prx($mainArrForEvent);
					// get each record and enter in database
					for($cntrEVSCH=0;$cntrEVSCH<count((array)$mainArrForEvent);$cntrEVSCH++)
					{
						// Recalculate least-loaded day before each assignment
						$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $balancingDays);
						$schDay = !empty($balancedStartDay) ? $balancedStartDay : $schDay;
						$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
						// data combination is
						// conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
						$stData = explode("==",$mainArrForEvent[$cntrEVSCH]);

						// now calculate timings
						//echo 'resetTime--upper--'.$resetTime;

						if($totalRoomsForThisEvent == 1)
						{
							$roomID = $roomArrCSEvent[0];
						}
						else
						{
							$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
						}


						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1)
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$finish_time = isset($finish_time) ? $finish_time : $normal_starting_time;
							$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
								$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;

						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}
						else
						{
							$slotShift = $this->moveToNextRoomOrDay(
								$cntrRoomCSEvent,
								$totalRoomsForThisEvent,
								$schDay,
								$schStartDate,
								$cntrDays,
								$schedulingsD,
								$eventSetupRoundJudTime,
								$first_day,
								$start_date,
								$conventionSD->id
							);
							$cntrRoomCSEvent = $slotShift['cntrRoomCSEvent'];
							$schDay = $slotShift['day'];
							$schStartDate = $slotShift['date'];
							$cntrDays = $slotShift['cntrDays'];
							$normal_starting_time = $slotShift['normal_starting_time'];
							$normal_finish_time = $slotShift['normal_finish_time'];
							$start_time = $slotShift['start_time'];
							$finish_time = $slotShift['finish_time'];
						}



						/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
						THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
						NOTE: We check ALL rooms in the same Room Allocation so that
						rooms sharing a physical space are scheduled sequentially.
						*/
						$condRAvail = array();
						$_allocRoomIds = $this->getAllocationRoomIds($roomID);
						$_allocRoomIdsStr = implode(',', $_allocRoomIds);
						$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."')";
						$condRAvail[] = "(Schedulingtimings.room_id IN ($_allocRoomIdsStr))";
						$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.finish_time" => "DESC"])->first();
						if($checkRoomAvailability)
						{

							$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));

							$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
							$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($room_finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

							$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
							$schDay 		= $checkRoomAvailability->day;


							// suppose in this case, finish time reach to day end time, then shift to next day
							if(strtotime($finish_time)>=strtotime($normal_finish_time))
							{
								$schDay = $this->getNextWeekDay($schDay);
								//echo $schDay;exit;

								// change to next date
								$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
								//echo 'here2';exit;
								$cntrDays++;

								$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
								$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));

								$start_time 	= $normal_starting_time;
								$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
							}
						}


						/* echo 'start_time->'.$start_time.'--';
						echo 'finish_time->'.$finish_time.'--';
						echo 'day->'.$schDay.'--';
						echo 'schStartDate->'.$schStartDate.'--';
						echo 'normal_finish_time->'.$normal_finish_time.'--';
						echo 'resetTime->'.$resetTime.'--';
						echo '<hr>'; */



						/* To check here if they are having more events after sport - ends */

						/* Apply time constraints (lunch, breaks, sports, room restrictions) — with load balancing */
						$tc = $this->applyTimeConstraints($schedulingsD, $eventSetupRoundJudTime, $roomID,
							$start_time, $finish_time, $schDay, $schStartDate, $cntrDays,
							$conventionSD->id, $first_day, $start_date);
						$start_time = $tc['start_time'];
						$finish_time = $tc['finish_time'];
						$schDay = $tc['day'];
						$schStartDate = $tc['date'];
						$cntrDays = $tc['cntrDays'];
						$normal_starting_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
						$normal_finish_time = date("H:i:s", strtotime($schedulingsD->normal_finish_time));





						/* Here we will check that this user_id is School Or student
						School means its a group event
						Student means it's an individual event
						*/
						$fetchUserType = $this->fetchUserType($stData[7]);
						$participantIds = $this->getGroupParticipantIds($stData[0], $stData[7], $stData[5], $stData[8]);

						/* USER CONFLICT CHECK: Before saving, verify the user_id
						   is not already scheduled at the same day/time */
						if (!empty($participantIds)) {
							$userConflictSlot = $this->findUserConflictFreeSlot(
								$stData[0],
								$participantIds,
								$schDay,
								$start_time,
								$finish_time,
								$eventSetupRoundJudTime,
								$normal_finish_time,
								$schedulingsD,
								$cntrDays
							);
							$start_time = $userConflictSlot['start_time'];
							$finish_time = $userConflictSlot['finish_time'];
							$schDay = $userConflictSlot['day'];
						}

						//now enter schedule timings
						$schedulingtimings = $this->Schedulingtimings->newEntity();
						$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

						$dataST->schedule_category				= 1;
						$dataST->conventionseasons_id			= $stData[0];
						$dataST->convention_id					= $stData[1];
						$dataST->season_id						= $stData[2];
						$dataST->season_year					= $stData[3];
						$dataST->conventionregistration_id 		= $stData[4];
						$dataST->event_id 						= $stData[5];
						$dataST->event_id_number 				= $stData[6];
						$dataST->user_id 						= $stData[7];
						$dataST->group_name 					= $stData[8];
						$dataST->group_name_user_ids 			= $this->getGroupParticipantCsv($stData[0], $stData[7], $stData[5], $stData[8]);

						$dataST->room_id 						= $roomID;
						$dataST->day 							= $schDay;
						$dataST->start_time 					= $start_time;
						$dataST->finish_time 					= $finish_time;

						$dataST->created 						= date('Y-m-d H:i:s');
						$dataST->modified 						= date('Y-m-d H:i:s');

						$dataST->user_type 						= $fetchUserType;

						//echo $start_time;exit;

						$dataST->sch_date_time 					= $schStartDate.' '.date("H:i:s", strtotime($start_time));

						//$this->prx($dataST);

						$resultST = $this->Schedulingtimings->save($dataST);
					}
				}

			}
			else
			{
				echo '<hr>no room found. <hr>';
			}
		}

		//exit;

		//$this->Flash->success($msgSuccess);
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec2', $convention_season_slug]);

    }


	public function startschedulec2($convention_season_slug=null) {

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		//$this->prx($conventionSD);

		// to get details of schedule timings
		$cfg = $this->loadSchedulingConfig($conventionSD);
		$schedulingsD = $cfg['schedulingsD'];
		$start_date = $cfg['start_date'];
		$first_day = $cfg['first_day'];
		$normal_starting_time = $cfg['normal_starting_time'];
		$normal_finish_time = $cfg['normal_finish_time'];
		$lunch_time_start = $cfg['lunch_time_start'];
		$lunch_time_end = $cfg['lunch_time_end'];
		$starting_different_time_first_day_yes_no = $cfg['starting_different_time_first_day_yes_no'];
		$different_first_day_start_time = $cfg['different_first_day_start_time'];
		$different_first_day_end_time = $cfg['different_first_day_end_time'];

		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = no || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC2 = $this->getEventsForCategory($conventionSD, false, 'Elimination', false);

		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC2 = array();
		foreach($arrEventsC2 as $event_id_c2)
		{
			$students = $this->getStudentsForEvent($conventionSD, $event_id_c2);
			if (!empty($students)) {
				$arrStudentsC2[$event_id_c2] = $students;
			}
		}


		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC2 as $event_id_c2 => $studentsListC2)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id_c2);
			$roomArrCSEvent = $roomResult['rooms'];
			//$this->prx($roomArrCSEvent);

			// shuffle array
			shuffle($studentsListC2);

			$totalStudentsEV 			= count($studentsListC2);
			$totalByePlayer 			= $this->getByePlayerScheduling($totalStudentsEV);
			$arrStudentsForSplice 		= $studentsListC2;

			//echo $totalByePlayer;exit;

			$match_number = 1;
			/* DEFINE SCHEDULING FOR BYE PLAYERS */
			if($totalByePlayer>0)
			{
				$arrByePlayer 			= array();

				// pick number of random players for bye
				for($cntrByeP=0;$cntrByeP<$totalByePlayer;$cntrByeP++)
				{
					// generate a random number from 0 to total count of students
					$randByeNumber 		= rand(0,count($arrStudentsForSplice)-1);
					$byeStudentID 		= $arrStudentsForSplice[$randByeNumber];
					$arrByePlayer[] 	= $byeStudentID;
					array_splice($arrStudentsForSplice, $randByeNumber, 1);

					/* Here we will check that this user_id is School Or student
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($byeStudentID);

					//now save bye player in database, opponent of bye player id will be 0
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 2;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $event_id_c2;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $byeStudentID;
					$dataBye->group_name 					= NULL;
					$dataBye->room_id 						= NULL;
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= 0;
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 1;
					$dataBye->created 						= date('Y-m-d H:i:s');

					$dataBye->sch_date_time 				= NULL;

					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);

					$match_number++;
				}
			}

			$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
			// now firstly choose first room
			$cntrRoomCSEvent = 0;
			$cntrEVSCH = 0;

			//$this->prx($arrStudentsForSplice);

			/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
			// To check how many matches are there
			$totalMatches = ($totalStudentsEV-$totalByePlayer)/2;
			for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
			{
				// to get first player id
				$randFirstP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$first_student_id 			= $arrStudentsForSplice[$randFirstP];
				array_splice($arrStudentsForSplice, $randFirstP, 1);

				// to get opponent user id
				$randSecondP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$second_student_id 			= $arrStudentsForSplice[$randSecondP];
				array_splice($arrStudentsForSplice, $randSecondP, 1);

				/* Here we will check that this user_id is School Or student
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($first_student_id);

				//now save remaining player in database with opponent user id
				$schedulingtimings = $this->Schedulingtimings->newEntity();
				$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataBye->schedule_category				= 2;
				$dataBye->conventionseasons_id			= $conventionSD->id;
				$dataBye->convention_id					= $conventionSD->convention_id;
				$dataBye->season_id						= $conventionSD->season_id;
				$dataBye->season_year 					= $conventionSD->season_year;
				$dataBye->conventionregistration_id 	= NULL;
				$dataBye->event_id 						= $event_id_c2;
				$dataBye->event_id_number 				= $eventD->event_id_number;
				$dataBye->user_id 						= $first_student_id;
				$dataBye->group_name 					= NULL;
				if($totalRoomsForThisEvent>0)
				{
					$dataBye->room_id 					= (int)$roomArrCSEvent[$cntrRoomCSEvent];
				}
				else
				{
					$dataBye->room_id 					= NULL;
				}
				$dataBye->day 							= NULL;
				$dataBye->start_time 					= NULL;
				$dataBye->finish_time 					= NULL;
				$dataBye->user_id_opponent 				= $second_student_id;
				$dataBye->round_number 					= 1;
				$dataBye->match_number 					= $match_number;
				$dataBye->is_bye 						= 0;
				$dataBye->created 						= date('Y-m-d H:i:s');

				$dataBye->sch_date_time 				= $start_date.' 00:00:00';

				$dataBye->user_type 					= $fetchUserType;

				$resultBye = $this->Schedulingtimings->save($dataBye);

				$match_number++;

				if($totalRoomsForThisEvent>1)
				{
					$cntrRoomCSEvent++;
					if($cntrRoomCSEvent>=$totalRoomsForThisEvent)
					{
						$cntrRoomCSEvent = 0;
					}
				}

				$cntrEVSCH++;
			}
		}




		/* After first round, we need to schedule next rounds till last round between 2 players */
		// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 2
		foreach(array_keys($arrStudentsC2) as $event_id_c2)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id_c2);
			$roomArrCSEvent = $roomResult['rooms'];
			$totalRoomsForThisEvent = count((array)$roomArrCSEvent);


			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->count();

			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			$lastMatchNumber = $evLastMatch->match_number;

			$lastMatchNumber = $lastMatchNumber+1;

			$loopNumber = $countTotalMatR1Event/2;
			$cntrRoomCSEvent = 0;
			$cntrEVSCH = 0;

			for($cntrOR=0;$cntrOR<$loopNumber;$cntrOR++)
			{
				$roundNumber = $cntrOR+1;

				// fetch matches of this round and save schedule
				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => $roundNumber])->all();
				foreach($nextRounds as $nextRound)
				{
					$arrNR[] = $nextRound->id;
				}

				//$this->prx($arrNR);

				$inLoopR = floor(count($arrNR)/2);

				//now run loop on this array and schedule
				for($cntrIn=0;$cntrIn<$inLoopR;$cntrIn++)
				{
					// to get first id
					$randFirstID 				= rand(0,count($arrNR)-1);
					$first_id 					= $arrNR[$randFirstID];
					array_splice($arrNR, $randFirstID, 1);

					// to get opponent user id
					$randSecondID 				= rand(0,count($arrNR)-1);
					$second_id 			= $arrNR[$randSecondID];
					array_splice($arrNR, $randSecondID, 1);


					//now save remaining player in database with opponent user id
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 2;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $event_id_c2;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= 0;
					$dataBye->group_name 					= NULL;
					if($totalRoomsForThisEvent>0)
					{
						$dataBye->room_id 					= (int)$roomArrCSEvent[$cntrRoomCSEvent];
					}
					else
					{
						$dataBye->room_id 					= NULL;
					}
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= 0;
					$dataBye->schtimeautoid1 				= $first_id;
					$dataBye->schtimeautoid2 				= $second_id;
					$dataBye->round_number 					= $roundNumber+1;
					$dataBye->match_number 					= $lastMatchNumber;
					$dataBye->is_bye 						= 0;
					$dataBye->created 						= date('Y-m-d H:i:s');

					$dataBye->sch_date_time 				= $start_date.' 00:00:00';

					$resultBye = $this->Schedulingtimings->save($dataBye);

					$lastMatchNumber++;

					if($totalRoomsForThisEvent>1)
					{
						$cntrRoomCSEvent++;
						if($cntrRoomCSEvent>=$totalRoomsForThisEvent)
						{
							$cntrRoomCSEvent = 0;
						}
					}

					$cntrEVSCH++;

				}

			}
		}

		//exit;



		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE
		DAY, START TIME AND END TIME */

		//exit;

		foreach($arrEventsC2 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();

			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id);
			$roomArrCSEvent = $roomResult['rooms'];
			$eventSpbValue = $roomResult['spb'];

			//$this->prx($roomArrCSEvent);


			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '2' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);

				$cntrDays 		= 1;
				$resetTime 		= 1;
				$balancingDays = $this->getConventionBalancingDays($first_day, 4);
				$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $balancingDays);
				$schDay 		= !empty($balancedStartDay) ? $balancedStartDay : $first_day;
				$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);

				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;
				$blockCounter 		= 0;
				$blockStartTime 	= null;
				$blockFinishTime 	= null;

				foreach($schedulingT as $schdata)
				{
					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}

					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					NOTE: We check ALL rooms in the same Room Allocation so that
					rooms sharing a physical space are scheduled sequentially.
					*/
					$condRAvail = array();
					$_allocRoomIds = $this->getAllocationRoomIds($roomID);
					$_allocRoomIdsStr = implode(',', $_allocRoomIds);
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.room_id IN ($_allocRoomIdsStr) AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";

					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.finish_time" => "DESC"])->first();


					if($checkRoomAvailability)
					{
						//$this->prx($checkRoomAvailability);
						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
						$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;
						/* echo $schDay;  echo '<br>';
						echo $normal_finish_time; echo '<br>';
						echo $start_time; echo '<br>';
						echo $finish_time; echo '<br>';
						exit; */

						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							$schDay = $this->getNextWeekDay($schDay);
							//echo $schDay;exit;

							// change to next date
							$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
							//echo 'here2';exit;
							$cntrDays++;

							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
					}
					else
					{
						///////////////////
						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1)
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$finish_time = isset($finish_time) ? $finish_time : $normal_starting_time;
							$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
								$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;

						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}
						else
						{
							$slotShift = $this->moveToNextRoomOrDay(
								$cntrRoomCSEvent,
								$totalRoomsForThisEvent,
								$schDay,
								$schStartDate,
								$cntrDays,
								$schedulingsD,
								$eventSetupRoundJudTime
							);
							$cntrRoomCSEvent = $slotShift['cntrRoomCSEvent'];
							$schDay = $slotShift['day'];
							$schStartDate = $slotShift['date'];
							$cntrDays = $slotShift['cntrDays'];
							$normal_starting_time = $slotShift['normal_starting_time'];
							$normal_finish_time = $slotShift['normal_finish_time'];
							$start_time = $slotShift['start_time'];
							$finish_time = $slotShift['finish_time'];
						}
						///////////////////
					}

					/* echo $start_time;echo '<br>'; */




					/* echo $start_time;echo '<br>';
					echo $finish_time;echo '<br>';
					echo $schDay;echo '<br>';
					echo '<hr>'; */

					/* Apply time constraints (lunch, breaks, sports, room restrictions) */
					$tc = $this->applyTimeConstraints($schedulingsD, $eventSetupRoundJudTime, $roomID,
						$start_time, $finish_time, $schDay, $schStartDate, $cntrDays);
					$start_time = $tc['start_time'];
					$finish_time = $tc['finish_time'];
					$schDay = $tc['day'];
					$schStartDate = $tc['date'];
					$cntrDays = $tc['cntrDays'];
					$normal_starting_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

					/* here we calculate root, day, start time and end time - ends */

					if (!$this->isSchedulableConventionDay($schDay)) {
						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> NULL,
						'day' 			=> NULL,
						'start_time' 	=> NULL,
						'finish_time' 	=> NULL,
						'sch_date_time' 	=> NULL,
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);
						continue;
					}

					$participantIds = $this->getTimingParticipantIds($schdata, $conventionSD->id);
					$slotSearchRetries = 0;
					while ($slotSearchRetries < 100) {
						if (!empty($participantIds)) {
							$userConflictSlot = $this->findUserConflictFreeSlot(
								$conventionSD->id,
								$participantIds,
								$schDay,
								$start_time,
								$finish_time,
								$eventSetupRoundJudTime,
								$normal_finish_time,
								$schedulingsD,
								$cntrDays
							);
							$start_time = $userConflictSlot['start_time'];
							$finish_time = $userConflictSlot['finish_time'];
							$schDay = $userConflictSlot['day'];
							$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
						}

						if (!$this->hasRoomConflictForSlot($conventionSD->id, $schdata->id, $roomID, $schDay, $start_time, $finish_time)) {
							break;
						}

						$slotSearchRetries++;
						$start_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

						if (strtotime($finish_time) > strtotime($normal_finish_time)) {
							$schDay = $this->getNextWeekDay($schDay);
							$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
							$cntrDays++;
							$start_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
							$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
					}

					// Apply students_per_block directly in main scheduler: reuse time for rows in same block
					if ($eventSpbValue > 1) {
						if ($blockCounter > 0 && $blockCounter < $eventSpbValue && $blockStartTime !== null) {
							$start_time = $blockStartTime;
							$finish_time = $blockFinishTime;
							$blockCounter++;
						} else {
							$blockCounter = 1;
							$blockStartTime = $start_time;
							$blockFinishTime = $finish_time;
						}
					}



					$arrPP = [
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,

					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),

					'modified' 		=> date("Y-m-d H:i:s")
					];

					//$this->pr($arrPP);
					//echo '<hr>';

					if (!$this->isSchedulableConventionDay($schDay)) {
						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> NULL,
						'day' 			=> NULL,
						'start_time' 	=> NULL,
						'finish_time' 	=> NULL,
						'sch_date_time' 	=> NULL,
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);
						continue;
					}

					// update day, start time and end time
					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,

					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),

					'modified' 		=> date("Y-m-d H:i:s")
					],
					["id" => $schdata->id]);

					$cntrEVSCH++;

				}

			}

			//exit;


		}

		//exit;

		//$this->Flash->success('Scheduling completed successfully for category 2.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec3', $convention_season_slug]);

	}

	public function startschedulec3($convention_season_slug=null) {

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		//$this->prx($conventionSD);

		// to get details of schedule timings
		$cfg = $this->loadSchedulingConfig($conventionSD);
		$schedulingsD = $cfg['schedulingsD'];
		$start_date = $cfg['start_date'];
		$first_day = $cfg['first_day'];
		$normal_starting_time = $cfg['normal_starting_time'];
		$normal_finish_time = $cfg['normal_finish_time'];
		$lunch_time_start = $cfg['lunch_time_start'];
		$lunch_time_end = $cfg['lunch_time_end'];
		$starting_different_time_first_day_yes_no = $cfg['starting_different_time_first_day_yes_no'];
		$different_first_day_start_time = $cfg['different_first_day_start_time'];
		$different_first_day_end_time = $cfg['different_first_day_end_time'];


		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = yes || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC3 = $this->getEventsForCategory($conventionSD, true, 'Elimination', false);


		$eventCTR = 0;
		// Now run loop on each event and get groups and schedule
		foreach($arrEventsC3 as $event_id_c3)
		{
			/* PART 1 OF THIS EVENT */

			$mainArrForEvent = array();

			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c3])->first();

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id_c3);
			$roomArrCSEvent = $roomResult['rooms'];
			$eventSpbValue = $roomResult['spb'];
			//$this->prx($roomArrCSEvent);

			// now get groups for this event from convention registration
			$mainArrForEvent = $this->getGroupsForEvent($conventionSD, $event_id_c3);



			if(count((array)$mainArrForEvent))
			{
				shuffle($mainArrForEvent);

				// now get total bye groups
				$totalGroupsEV 				= count($mainArrForEvent);
				$totalByeGroup 				= $this->getByePlayerScheduling($totalGroupsEV);
				$arrGroupsForSplice 		= $mainArrForEvent;

				//$this->prx($arrGroupsForSplice);

				//echo $totalByeGroup;exit;

				$match_number = 1;
				/* DEFINE SCHEDULING FOR BYE GROUPS */
				if($totalByeGroup>0)
				{
					// pick number of random players for bye
					for($cntrByeP=0;$cntrByeP<$totalByeGroup;$cntrByeP++)
					{
						// generate a random number from 0 to total count of students
						$randByeNumber 		= rand(0,count($arrGroupsForSplice)-1);

						// now explode data from array to get al details
						$dataGExplode = explode("==",$arrGroupsForSplice[$randByeNumber]);
						//$this->prx($dataGExplode);

						array_splice($arrGroupsForSplice, $randByeNumber, 1);

						/* Here we will check that this user_id is School Or student
						School means its a group event
						Student means it's an individual event
						*/
						$fetchUserType = $this->fetchUserType($dataGExplode[7]);



						//now save bye player in database, opponent of bye player id will be 0
						$schedulingtimings = $this->Schedulingtimings->newEntity();
						$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

						$dataBye->schedule_category				= 3;
						$dataBye->conventionseasons_id			= $conventionSD->id;
						$dataBye->convention_id					= $conventionSD->convention_id;
						$dataBye->season_id						= $conventionSD->season_id;
						$dataBye->season_year 					= $conventionSD->season_year;
						$dataBye->conventionregistration_id 	= $dataGExplode[4];
						$dataBye->event_id 						= $eventD->id;
						$dataBye->event_id_number 				= $eventD->event_id_number;
						$dataBye->user_id 						= $dataGExplode[7];
						$dataBye->group_name 					= $dataGExplode[8];
						$dataBye->group_name_user_ids 			= $this->getGroupParticipantCsv($conventionSD->id, $dataGExplode[7], $eventD->id, $dataGExplode[8]);
						$dataBye->room_id 						= NULL;
						$dataBye->day 							= NULL;
						$dataBye->start_time 					= NULL;
						$dataBye->finish_time 					= NULL;
						$dataBye->user_id_opponent 				= 0;
						$dataBye->round_number 					= 1;
						$dataBye->match_number 					= $match_number;
						$dataBye->is_bye 						= 1;
						$dataBye->created 						= date('Y-m-d H:i:s');

						$dataBye->sch_date_time 				= NULL;

						$dataBye->user_type 					= $fetchUserType;

						$resultBye = $this->Schedulingtimings->save($dataBye);

						$match_number++;
					}
				}

				//$this->prx($arrGroupsForSplice);

				/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
				// To check how many matches are there
				$totalMatches = ($totalGroupsEV-$totalByeGroup)/2;
				for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
				{
					// to get first group id
					$randFirstP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeFirst = explode("==",$arrGroupsForSplice[$randFirstP]);
					array_splice($arrGroupsForSplice, $randFirstP, 1);


					// to get opponent group id
					$randSecondP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeSecond = explode("==",$arrGroupsForSplice[$randSecondP]);
					array_splice($arrGroupsForSplice, $randSecondP, 1);

					/* Here we will check that this user_id is School Or student
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($dataGExplodeFirst[7]);

					//now save remaining player in database with opponent user id
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 3;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $eventD->id;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $dataGExplodeFirst[7];
					$dataBye->group_name 					= $dataGExplodeFirst[8];
					$dataBye->group_name_user_ids 			= $this->getGroupParticipantCsv($conventionSD->id, $dataGExplodeFirst[7], $eventD->id, $dataGExplodeFirst[8]);
					$dataBye->room_id 						= NULL;
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= $dataGExplodeSecond[7];
					$dataBye->group_name_opponent 			= $dataGExplodeSecond[8];
					$dataBye->group_name_opponent_user_ids = $this->getGroupParticipantCsv($conventionSD->id, $dataGExplodeSecond[7], $eventD->id, $dataGExplodeSecond[8]);
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 0;
					$dataBye->created 						= date('Y-m-d H:i:s');

					$dataBye->sch_date_time 				= $start_date.' 00:00:00';

					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);

					$match_number++;

				}
			}






			/* PART 2 OF THIS EVENT */

			/* After first round, we need to schedule next rounds till last round between 2 players */
			// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 3

			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->count();
			if ($countTotalMatR1Event < 2) {
				continue;
			}

			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			if (empty($evLastMatch)) {
				continue;
			}
			$lastMatchNumber = $evLastMatch->match_number;

			$lastMatchNumber = $lastMatchNumber+1;

			$loopNumber = $countTotalMatR1Event/2;

			for($cntrOR=0;$cntrOR<$loopNumber;$cntrOR++)
			{
				$roundNumber = $cntrOR+1;

				// fetch matches of this round and save schedule
				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => $roundNumber])->all();
				foreach($nextRounds as $nextRound)
				{
					$arrNR[] = $nextRound->id;
				}

				//$this->prx($arrNR);

				$inLoopR = floor(count($arrNR)/2);

				//echo $inLoopR;exit;

				//now run loop on this array and schedule
				for($cntrIn=0;$cntrIn<$inLoopR;$cntrIn++)
				{
					// to get first id
					$randFirstID 				= rand(0,count($arrNR)-1);
					$first_id 					= $arrNR[$randFirstID];
					array_splice($arrNR, $randFirstID, 1);

					// to get opponent user id
					$randSecondID 				= rand(0,count($arrNR)-1);
					$second_id 			= $arrNR[$randSecondID];
					array_splice($arrNR, $randSecondID, 1);


					//now save remaining player in database with opponent user id
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 3;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $eventD->id;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= 0;
					$dataBye->group_name 					= NULL;
					$dataBye->room_id 						= NULL;
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= 0;
					$dataBye->schtimeautoid1 				= $first_id;
					$dataBye->schtimeautoid2 				= $second_id;
					$dataBye->round_number 					= $roundNumber+1;
					$dataBye->match_number 					= $lastMatchNumber;
					$dataBye->is_bye 						= 0;
					$dataBye->created 						= date('Y-m-d H:i:s');

					$dataBye->sch_date_time 				= $start_date.' 00:00:00';

					$resultBye = $this->Schedulingtimings->save($dataBye);

					$lastMatchNumber++;

				}

			}









			/* PART 3 OF THIS EVENT */

			/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
			/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE
			DAY, START TIME AND END TIME */

			$event_id = $event_id_c3;

			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id);
			$roomArrCSEvent = $roomResult['rooms'];


			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '3' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);

				$cntrDays 		= 1;
				$resetTime 		= 1;
				$balancingDays = $this->getConventionBalancingDays($first_day, 4);
				$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $balancingDays);
				$schDay 		= !empty($balancedStartDay) ? $balancedStartDay : $first_day;
				$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);

				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;
				$blockCounter 		= 0;
				$blockStartTime 	= null;
				$blockFinishTime 	= null;

				foreach($schedulingT as $schdata)
				{
					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}

					/* here we calculate room, day, start time and end time - starts */
					if($resetTime == 1)
					{
						if($cntrDays == 1 && $cntrEVSCH == 0)
						{
							// check if there is a different time for first day
							if($starting_different_time_first_day_yes_no == 1)
							{
								$normal_starting_time 	= $different_first_day_start_time;
								$normal_finish_time 	= $different_first_day_end_time;
							}
						}

						$start_time 	= $normal_starting_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					}
					else
					{
						$finish_time = isset($finish_time) ? $finish_time : $normal_starting_time;
								$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
								$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					}
					if(strtotime($finish_time)<=strtotime($normal_finish_time))
					{
						$resetTime = 0;
					}
					else
					{
						$slotShift = $this->moveToNextRoomOrDay(
							$cntrRoomCSEvent,
							$totalRoomsForThisEvent,
							$schDay,
							$schStartDate,
							$cntrDays,
							$schedulingsD,
							$eventSetupRoundJudTime,
							$first_day,
							$start_date,
							$conventionSD->id
						);
						$cntrRoomCSEvent = $slotShift['cntrRoomCSEvent'];
						$schDay = $slotShift['day'];
						$schStartDate = $slotShift['date'];
						$cntrDays = $slotShift['cntrDays'];
						$normal_starting_time = $slotShift['normal_starting_time'];
						$normal_finish_time = $slotShift['normal_finish_time'];
						$start_time = $slotShift['start_time'];
						$finish_time = $slotShift['finish_time'];

						//echo $normal_starting_time;
						//echo $normal_finish_time;
					}


					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					NOTE: We check ALL rooms in the same Room Allocation so that
					rooms sharing a physical space are scheduled sequentially.
					*/

					$condRAvail = array();
					$_allocRoomIds = $this->getAllocationRoomIds($roomID);
					$_allocRoomIdsStr = implode(',', $_allocRoomIds);
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."')";
					$condRAvail[] = "(Schedulingtimings.room_id IN ($_allocRoomIdsStr))";

					$condRAvail[] = "(Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";

					if($eventCTR>0)
					{
						$condRAvail[] = "(Schedulingtimings.is_bye = 0)";
					}

					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.finish_time" => "DESC"])->first();

					/* echo '<pre>';print_r($condRAvail);
					echo '</pre>'; */

					if($checkRoomAvailability)
					{	//$this->prx($checkRoomAvailability);
						$availID = $checkRoomAvailability->id;

						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
						$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;

						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							$balancingDays = $this->getConventionBalancingDays($first_day, 4);
							$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $balancingDays);
							if (!empty($balancedStartDay)) {
								$schDay = $balancedStartDay;
								$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
							} else {
								$schDay = $this->getNextWeekDay($schDay);
								$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
							}
							$cntrDays++;

							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}

						if($schDay != $first_day)
						{
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
						}

						/* echo $schDay.'->'.$start_time.' :: '.$finish_time.'==eventid--'.$event_id.'---availID=====>'.$availID.'-----normal_starting_time==>'.$normal_starting_time.'--normal_finish_time==>'.$normal_finish_time;
						echo '<br>'; */
					}




					/* Apply time constraints (lunch, breaks, sports, room restrictions) */
					$tc = $this->applyTimeConstraints($schedulingsD, $eventSetupRoundJudTime, $roomID,
						$start_time, $finish_time, $schDay, $schStartDate, $cntrDays);
					$start_time = $tc['start_time'];
					$finish_time = $tc['finish_time'];
					$schDay = $tc['day'];
					$schStartDate = $tc['date'];
					$cntrDays = $tc['cntrDays'];
					$normal_starting_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time = date("H:i:s", strtotime($schedulingsD->normal_finish_time));




					if (!$this->isSchedulableConventionDay($schDay)) {
						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> NULL,
						'day' 			=> NULL,
						'start_time' 	=> NULL,
						'finish_time' 	=> NULL,
						'sch_date_time' 	=> NULL,
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);
						continue;
					}

					$participantIds = $this->getTimingParticipantIds($schdata, $conventionSD->id);
					$slotSearchRetries = 0;
					while ($slotSearchRetries < 100) {
						if (!empty($participantIds)) {
							$userConflictSlot = $this->findUserConflictFreeSlot(
								$conventionSD->id,
								$participantIds,
								$schDay,
								$start_time,
								$finish_time,
								$eventSetupRoundJudTime,
								$normal_finish_time,
								$schedulingsD,
								$cntrDays
							);
							$start_time = $userConflictSlot['start_time'];
							$finish_time = $userConflictSlot['finish_time'];
							$schDay = $userConflictSlot['day'];
							$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
						}

						if (!$this->hasRoomConflictForSlot($conventionSD->id, $schdata->id, $roomID, $schDay, $start_time, $finish_time)) {
							break;
						}

						$slotSearchRetries++;
						$start_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

						if (strtotime($finish_time) > strtotime($normal_finish_time)) {
							$schDay = $this->getNextWeekDay($schDay);
							$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
							$cntrDays++;
							$start_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
							$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
					}

					// Apply students_per_block directly in main scheduler: reuse time for rows in same block
					if ($eventSpbValue > 1) {
						if ($blockCounter > 0 && $blockCounter < $eventSpbValue && $blockStartTime !== null) {
							$start_time = $blockStartTime;
							$finish_time = $blockFinishTime;
							$blockCounter++;
						} else {
							$blockCounter = 1;
							$blockStartTime = $start_time;
							$blockFinishTime = $finish_time;
						}
					}

					// update day, start time and end time
					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,

					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),

					'modified' 		=> date("Y-m-d H:i:s")
					],
					["id" => $schdata->id]);

					$cntrEVSCH++;

					/* echo $schDay.'->'.$start_time.' :: '.$finish_time.'==eventid--'.$event_id.'---availID=====>'.$availID.'-----normal_finish_time==>'.$normal_finish_time;
					echo '<br>'; */

				}

			}

			//echo '<hr>';

		$eventCTR++;

		}

		//exit;
		//echo $cntrEVSCH;exit;

		//$this->Flash->success('Scheduling completed successfully for category 3.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec4', $convention_season_slug]);

	}


	public function startschedulec4($convention_season_slug=null) {

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		//$this->prx($conventionSD);

		// to get details of schedule timings
		$cfg = $this->loadSchedulingConfig($conventionSD);
		$schedulingsD = $cfg['schedulingsD'];
		$start_date = $cfg['start_date'];
		$first_day = $cfg['first_day'];
		$normal_starting_time = $cfg['normal_starting_time'];
		$normal_finish_time = $cfg['normal_finish_time'];
		$lunch_time_start = $cfg['lunch_time_start'];
		$lunch_time_end = $cfg['lunch_time_end'];
		$starting_different_time_first_day_yes_no = $cfg['starting_different_time_first_day_yes_no'];
		$different_first_day_start_time = $cfg['different_first_day_start_time'];
		$different_first_day_end_time = $cfg['different_first_day_end_time'];


		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = no || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC4 = $this->getEventsForCategory($conventionSD, false, 'Sequential', true);


		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC4 = array();
		foreach($arrEventsC4 as $event_id_c4)
		{
			$students = $this->getStudentsForEvent($conventionSD, $event_id_c4);
			if (!empty($students)) {
				$arrStudentsC4[$event_id_c4] = $students;
			}
		}


		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC4 as $event_id_c4 => $studentsListC4)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c4])->first();

			// shuffle array
			shuffle($studentsListC4);

			foreach($studentsListC4 as $student_id)
			{
				/* Here we will check that this user_id is School Or student
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($student_id);

				//now enter schedule timings
				$schedulingtimings = $this->Schedulingtimings->newEntity();
				$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataST->schedule_category				= 4;
				$dataST->conventionseasons_id			= $conventionSD->id;
				$dataST->convention_id					= $conventionSD->convention_id;
				$dataST->season_id						= $conventionSD->season_id;
				$dataST->season_year					= $conventionSD->season_year;
				$dataST->conventionregistration_id 		= NULL;
				$dataST->event_id 						= $eventD->id;
				$dataST->event_id_number 				= $eventD->event_id_number;
				$dataST->user_id 						= $student_id;
				$dataST->group_name 					= NULL;

				$dataST->room_id 						= NULL;
				$dataST->day 							= NULL;
				$dataST->start_time 					= NULL;
				$dataST->finish_time 					= NULL;

				$dataST->created 						= date('Y-m-d H:i:s');
				$dataST->modified 						= date('Y-m-d H:i:s');

				$dataST->sch_date_time 					= $start_date.' 00:00:00';

				$dataST->user_type 						= $fetchUserType;
				//$this->prx($dataST);

				$resultST = $this->Schedulingtimings->save($dataST);
			}


		}


		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY, START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE
		DAY, START TIME AND END TIME */

		foreach($arrEventsC4 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();

			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;

			// now check that if any room is allocated for this event
			$roomResult = $this->findRoomsForEvent($conventionSD, $event_id);
			$roomArrCSEvent = $roomResult['rooms'];
			$eventSpbValue = $roomResult['spb'];
			//$this->prx($roomArrCSEvent);


			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '4' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);

				$cntrDays 		= 1;
				$resetTime 		= 1;
				$balancingDays = $this->getConventionBalancingDays($first_day, 4);
				$balancedStartDay = $this->pickLeastLoadedStartDay($conventionSD->id, $balancingDays);
				$schDay 		= !empty($balancedStartDay) ? $balancedStartDay : $first_day;
				$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);

				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;
				$blockCounter 		= 0; // tracks students in current block for students_per_block grouping
				$blockStartTime 	= null;
				$blockFinishTime 	= null;

				foreach($schedulingT as $schdata)
				{
					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}

					// Students per block grouping: if still within a block, reuse same time
					if ($blockCounter > 0 && $blockCounter < $eventSpbValue && $blockStartTime !== null) {
						$start_time = $blockStartTime;
						$finish_time = $blockFinishTime;
						$blockCounter++;
					} else {
					// Start a new block — determine time via room availability check
					$blockCounter = 1;

					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					NOTE: We check ALL rooms in the same Room Allocation so that
					rooms sharing a physical space are scheduled sequentially.
					*/
					$condRAvail = array();
					$_allocRoomIds = $this->getAllocationRoomIds($roomID);
					$_allocRoomIdsStr = implode(',', $_allocRoomIds);
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.room_id IN ($_allocRoomIdsStr) AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";

					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.finish_time" => "DESC"])->first();


					if($checkRoomAvailability)
					{
						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
						$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;

						/* echo $schDay;  echo '<br>';
						echo $normal_finish_time; echo '<br>';
						echo $start_time; echo '<br>';
						echo $finish_time; echo '<br>';
						exit; */

						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							$schDay = $this->getNextWeekDay($schDay);
							//echo $schDay;exit;

							// change to next date
							$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
							//echo 'here2';exit;
							$cntrDays++;

							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
					}
					else
					{
						////////////////////////////
						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1)
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}

							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$finish_time = isset($finish_time) ? $finish_time : $normal_starting_time;
							$bufferMin = isset($schedulingsD->buffer_minutes) && $schedulingsD->buffer_minutes !== null ? (int)$schedulingsD->buffer_minutes : 5;
								$start_time 	= date("H:i:s", strtotime('+'.$bufferMin.' minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;

						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}
						else
						{
							$slotShift = $this->moveToNextRoomOrDay(
								$cntrRoomCSEvent,
								$totalRoomsForThisEvent,
								$schDay,
								$schStartDate,
								$cntrDays,
								$schedulingsD,
								$eventSetupRoundJudTime
							);
							$cntrRoomCSEvent = $slotShift['cntrRoomCSEvent'];
							$schDay = $slotShift['day'];
							$schStartDate = $slotShift['date'];
							$cntrDays = $slotShift['cntrDays'];
							$normal_starting_time = $slotShift['normal_starting_time'];
							$normal_finish_time = $slotShift['normal_finish_time'];
							$start_time = $slotShift['start_time'];
							$finish_time = $slotShift['finish_time'];
						}
						////////////////////////////
					}




					/* Apply time constraints (lunch, breaks, sports, room restrictions) */
					$room_id_tocheck = $roomArrCSEvent[$cntrRoomCSEvent];
					$tc = $this->applyTimeConstraints($schedulingsD, $eventSetupRoundJudTime, $room_id_tocheck,
						$start_time, $finish_time, $schDay, $schStartDate, $cntrDays);
					$start_time = $tc['start_time'];
					$finish_time = $tc['finish_time'];
					$schDay = $tc['day'];
					$schStartDate = $tc['date'];
					$cntrDays = $tc['cntrDays'];
					$normal_starting_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time = date("H:i:s", strtotime($schedulingsD->normal_finish_time));

					// Save the block's time so subsequent students in this block reuse it
					$blockStartTime = $start_time;
					$blockFinishTime = $finish_time;

					} // end of else (new block time determination)


					if (!$this->isSchedulableConventionDay($schDay)) {
						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> NULL,
						'day' 			=> NULL,
						'start_time' 	=> NULL,
						'finish_time' 	=> NULL,
						'sch_date_time' 	=> NULL,
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);
						continue;
					}

					// True when this row is reusing an already chosen block slot.
					$reuseBlockSlot = ($eventSpbValue > 1 && $blockCounter > 1 && $blockStartTime !== null);

					/* USER CONFLICT CHECK for Category 4 (individual events):
					   Ensure this student is not already scheduled at this time */
					if (!empty($schdata->user_id)) {
						$userConflictSlot = $this->findUserConflictFreeSlot(
							$conventionSD->id,
							[$schdata->user_id],
							$schDay,
							$start_time,
							$finish_time,
							$eventSetupRoundJudTime,
							$normal_finish_time,
							$schedulingsD,
							$cntrDays
						);
						$start_time = $userConflictSlot['start_time'];
						$finish_time = $userConflictSlot['finish_time'];
						$schDay = $userConflictSlot['day'];
						// Keep anchor stable for reused rows; only anchor a new block.
						if (!$reuseBlockSlot) {
							$blockStartTime = $start_time;
							$blockFinishTime = $finish_time;
						}
					}

					/* ROOM CONFLICT CHECK for Category 4: Verify the room is
					   not already booked at this time (including allocation siblings) */
					if (!$reuseBlockSlot) {
						$roomConflictRetries = 0;
						while ($roomConflictRetries < 100 && $this->hasRoomConflictForSlot(
							$conventionSD->id, $schdata->id,
							$roomArrCSEvent[$cntrRoomCSEvent], $schDay,
							$start_time, $finish_time
						)) {
							$roomConflictRetries++;
							$start_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
							$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

							if (strtotime($finish_time) > strtotime($normal_finish_time)) {
								$schDay = $this->getNextWeekDay($schDay);
								$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
								$cntrDays++;
								$start_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
								$finish_time = date("H:i:s", strtotime('+'.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
							}

							if (!empty($schdata->user_id)) {
								$userConflictSlot = $this->findUserConflictFreeSlot(
									$conventionSD->id,
									[$schdata->user_id],
									$schDay,
									$start_time,
									$finish_time,
									$eventSetupRoundJudTime,
									$normal_finish_time,
									$schedulingsD,
									$cntrDays
								);
								$start_time = $userConflictSlot['start_time'];
								$finish_time = $userConflictSlot['finish_time'];
								$schDay = $userConflictSlot['day'];
								$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
							}
							$blockStartTime = $start_time;
							$blockFinishTime = $finish_time;
						}

						if (!empty($schdata->user_id)) {
							$userConflictSlot = $this->findUserConflictFreeSlot(
								$conventionSD->id,
								[$schdata->user_id],
								$schDay,
								$start_time,
								$finish_time,
								$eventSetupRoundJudTime,
								$normal_finish_time,
								$schedulingsD,
								$cntrDays
							);
							$start_time = $userConflictSlot['start_time'];
							$finish_time = $userConflictSlot['finish_time'];
							$schDay = $userConflictSlot['day'];
							$schStartDate = $this->getDateForDayFromStart($start_date, $first_day, $schDay);
							$blockStartTime = $start_time;
							$blockFinishTime = $finish_time;
						}
					}

					$arrP = [
					'room_id' 		=> $roomArrCSEvent[$cntrRoomCSEvent],
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,

					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),

					'modified' 		=> date("Y-m-d H:i:s")
					];
					//$this->pr($arrP);
					//echo '<hr>';

					// update day, start time and end time
					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomArrCSEvent[$cntrRoomCSEvent],
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,

					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),

					'modified' 		=> date("Y-m-d H:i:s")
					],
					["id" => $schdata->id]);

					$cntrEVSCH++;

				}

				//exit;

			}


		}

		// Safety net: bye records should never consume a real slot.
		$this->Schedulingtimings->updateAll(
		[
			'room_id' => NULL,
			'day' => NULL,
			'start_time' => NULL,
			'finish_time' => NULL,
			'sch_date_time' => NULL,
			'modified' => date("Y-m-d H:i:s")
		],
		[
			'conventionseasons_id' => $conventionSD->id,
			'schedule_category IN' => [2, 3],
			'is_bye' => 1
		]
		);

		$this->retryUnscheduledRows($conventionSD, $schedulingsD, $start_date, $first_day);

		//$this->Flash->success('Scheduling done for category 4.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'fillgroupuserids', $convention_season_slug]);

	}

	public function fillgroupuserids($convention_season_slug=null)
	{
		$updateDateTime = date("Y-m-d H:i:s");

		// To get convention season details
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		// Now fetch group events from Schedulings
		$condGroupScht = array();
		$condGroupScht[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND
			Schedulingtimings.user_type = 'School' AND
			Schedulingtimings.is_bye != 1 AND
			(
				(Schedulingtimings.group_name IS NOT NULL AND Schedulingtimings.group_name != '' AND Schedulingtimings.user_id > 0)
				OR
				(Schedulingtimings.group_name_opponent IS NOT NULL AND Schedulingtimings.group_name_opponent != '' AND Schedulingtimings.user_id_opponent > 0)
			)
		)";

		// Fetch each schedule and check if there is any group name assigned
		$schGroup = $this->Schedulingtimings
					->find()
					->where($condGroupScht)
					->order(["Schedulingtimings.id" => "ASC"])
					->all();
		//$this->prx($schGroup);
		foreach($schGroup as $schrecord)
		{
			// Side A: fill group_name_user_ids from crstudentevents (guard: group_name + user_id present)
			if (!empty($schrecord->group_name) && $schrecord->user_id > 0) {
				$groupUsersID = $this->Crstudentevents
								->find()
								->where(
									[
										'conventionseason_id' => $conventionSD->id,
										'user_id'             => $schrecord->user_id,
										'event_id'            => $schrecord->event_id,
										'group_name'          => $schrecord->group_name,
									]
								)
								->select('student_id')
								->order(["Crstudentevents.id" => "ASC"])
								->all();
				$studentIds = $groupUsersID->extract('student_id')->toArray();
				if (count($studentIds)) {
					$this->Schedulingtimings->updateAll(
						['group_name_user_ids' => implode(',', $studentIds)],
						['id' => $schrecord->id]
					);
				}
			}

			// Side B: fill group_name_opponent_user_ids from crstudentevents (guard: group_name_opponent + user_id_opponent present)
			if (!empty($schrecord->group_name_opponent) && $schrecord->user_id_opponent > 0) {
				$groupUsersIDOpponent = $this->Crstudentevents
								->find()
								->where(
									[
										'conventionseason_id' => $conventionSD->id,
										'user_id'             => $schrecord->user_id_opponent,
										'event_id'            => $schrecord->event_id,
										'group_name'          => $schrecord->group_name_opponent,
									]
								)
								->select('student_id')
								->order(["Crstudentevents.id" => "ASC"])
								->all();
				$studentIdsOpponent = $groupUsersIDOpponent->extract('student_id')->toArray();
				if (count($studentIdsOpponent)) {
					$this->Schedulingtimings->updateAll(
						['group_name_opponent_user_ids' => implode(',', $studentIdsOpponent)],
						['id' => $schrecord->id]
					);
				}
			}
		}
		//exit;

		// Now check for conflicts
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'listconflicts', $convention_season_slug]);
	}


	public function listconflicts($convention_season_slug=null)
	{
		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (empty($conventionSD) || empty($conventionSD->id)) {
			throw new \Cake\Http\Exception\NotFoundException('Invalid convention season slug.');
		}

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$bufferMinutes = isset($schedulingD->buffer_minutes) && $schedulingD->buffer_minutes !== null ? (int)$schedulingD->buffer_minutes : 5;
		$bufferSeconds = $bufferMinutes * 60;

		// Fetch ALL non-bye placed rows for the season (both Student and School user_type)
		// so that group event participants are included in conflict detection.
		$condSchList = [
			"Schedulingtimings.conventionseasons_id = '".$conventionSD->id."'",
			"Schedulingtimings.is_bye != 1",
			"Schedulingtimings.start_time IS NOT NULL",
			"Schedulingtimings.day IS NOT NULL",
		];
		$schedulingtimings = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.id" => "ASC"])
			->all();

		// Step 2: Normalize - build a mapping of participant_id → their scheduled slots.
		// Uses getTimingParticipantIds() which does a live crstudentevents lookup for group
		// rows, so conflicts are detected even when group_name_user_ids CSVs are unpopulated.
		$userSchedules = [];

		foreach($schedulingtimings as $schrecord)
		{
			$day   = $schrecord->day;
			$start = strtotime($schrecord->start_time);
			$end   = strtotime($schrecord->finish_time);

			if (!$day || !$start || !$end) {
				continue;
			}

			$participantIds = $this->getTimingParticipantIds($schrecord, $conventionSD->id);

			foreach ($participantIds as $uid) {
				if ($uid > 0) {
					$userSchedules[$uid][] = [
						'id'    => $schrecord->id,
						'day'   => $day,
						'start' => $start,
						'end'   => $end,
					];
				}
			}
		}

		// Step 3: Detect conflicts
		$conflicts = [];

		foreach ($userSchedules as $uid => $entries) {
			// Compare each pair of schedules for same user
			for ($i = 0; $i < count($entries); $i++) {
				for ($j = $i + 1; $j < count($entries); $j++) {
					$a = $entries[$i];
					$b = $entries[$j];

					if ($a['day'] == $b['day']) {
						if ($a['start'] < ($b['end'] + $bufferSeconds) && $a['end'] > ($b['start'] - $bufferSeconds)) {
							$conflicts[$uid][] = [
								'schedule1' => $a['id'],
								'schedule2' => $b['id']
							];
						}
					}
				}
			}
		}

		//$this->prx($conflicts);

		// Step 4: Output
		$conflictUIDS 		= [];
		$conflictDBAutoID 	= [];
		foreach ($conflicts as $uid => $conflictList) {
			$conflictUIDS[] = $uid;

			// Get db ids of schedules
			foreach ($conflictList as $row) {
				$conflictDBAutoID[] = $row['schedule1'];
				$conflictDBAutoID[] = $row['schedule2'];
			}
		}

		$finalDBAutoIDUnique = array_values(array_unique($conflictDBAutoID));
		$finalGroupSchDBIDs = [];

		// Save conflicted user ids in database
		if(count($conflictUIDS)>0)
		{
			$this->Schedulings->updateAll(['conflict_user_ids' => implode(",",$conflictUIDS)], ["conventionseasons_id" => $conventionSD->id]);
		}
		else
		{
			$this->Schedulings->updateAll(['conflict_user_ids' => NULL], ["conventionseasons_id" => $conventionSD->id]);
		}

		// Now filter group db ids where conflict found
		if(count($finalDBAutoIDUnique)>0)
		{
			$finalGroupSchDBIDs = array();
			foreach($finalDBAutoIDUnique as $group_db_id)
			{
				$checkGroupGame = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $group_db_id])->first();
				if($checkGroupGame->user_type == 'School')
				{
					$finalGroupSchDBIDs[] = $group_db_id;
				}
			}
			if(count($finalGroupSchDBIDs)>0)
			{
				$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(",",$finalGroupSchDBIDs)], ["conventionseasons_id" => $conventionSD->id]);
			}
			else
			{
				$this->Schedulings->updateAll(['conflict_user_ids_group' => NULL], ["conventionseasons_id" => $conventionSD->id]);
			}
		}
		else
		{
			$this->Schedulings->updateAll(['conflict_user_ids_group' => NULL], ["conventionseasons_id" => $conventionSD->id]);
		}

		$this->saveConflictAuditRun(
			$conventionSD,
			count($conflictUIDS),
			count($finalGroupSchDBIDs),
			count($finalDBAutoIDUnique),
			'Auto-run after listconflicts computation'
		);

		// If conflicts exist, chain straight into auto-resolve — no manual button needed
		if(count($conflictUIDS) > 0 || count($finalGroupSchDBIDs ?? []) > 0)
		{
			$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflicts', $convention_season_slug, '?' => ['ref' => 'schedulecategory']]);
		}
		else
		{
			// Check for overflow events (pushed to Fri-Sun or unplaced)
			$overflowCheck = $this->Schedulingtimings->find()->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
			])->andWhere([
				'OR' => [
					['Schedulingtimings.day IN' => ['Friday','Saturday','Sunday']],
					['Schedulingtimings.day IS' => null],
					['Schedulingtimings.start_time IS' => null],
					['Schedulingtimings.finish_time IS' => null],
				]
			])->count();

			if ($overflowCheck > 0) {
				$this->Flash->warning('Scheduling completed but ' . $overflowCheck . ' event(s) could not be placed within Monday-Thursday. Review below and optionally create a new room to fit them.');
				$this->redirect(['controller' => 'schedulingtimings', 'action' => 'postscheduleoverview', $convention_season_slug]);
			} else {
				$this->Flash->success('Scheduling completed successfully. No conflicts found.');
				$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
			}
		}
	}


	/* public function listconflictsgroups($convention_season_slug=null)
	{
		// Sudhir - starts from here
		$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);

		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		// To get list of all conflict
		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND
			Schedulingtimings.user_type = 'School' AND
			Schedulingtimings.user_id > 0 AND
			Schedulingtimings.user_id_opponent > 0 AND
			(Schedulingtimings.group_name_user_ids != '' OR Schedulingtimings.group_name_user_ids != NULL) AND
			(Schedulingtimings.group_name_opponent_user_ids != '' OR Schedulingtimings.group_name_opponent_user_ids != NULL) AND
			Schedulingtimings.is_bye != 1

		)";
		// Fetch each schedule and check if there is any group name assigned
		$schedulingtimings = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.id" => "ASC"])
			->all();

		// Step 2: Normalize - build a mapping of user_id → their schedules
		$userSchedules = [];


		foreach($schedulingtimings as $schrecord)
		{
			$day 	= $schrecord->day;
			$start 	= strtotime($schrecord->start_time);
			$end   	= strtotime($schrecord->finish_time);

			// Direct user_id
			if ($schrecord->user_id) {
				$userSchedules[$schrecord->user_id][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}

			// Direct user_id_opponent
			if ($schrecord->user_id_opponent) {
				$userSchedules[$schrecord->user_id_opponent][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}

			// Group members
			foreach (['group_name_user_ids', 'group_name_opponent_user_ids'] as $col) {
				if (!empty($schrecord[$col])) {
					$ids = array_map('trim', explode(',', $schrecord[$col]));
					foreach ($ids as $uid) {
						if ($uid > 0) {
							$userSchedules[$uid][] = [
								'id' => $schrecord['id'],
								'day' => $day,
								'start' => $start,
								'end' => $end
							];
						}
					}
				}
			}

		}

		//$this->prx($userSchedules);

		// Step 3: Detect conflicts
		$conflicts = [];

		foreach ($userSchedules as $uid => $entries) {
			// Compare each pair of schedules for same user
			for ($i = 0; $i < count($entries); $i++) {
				for ($j = $i + 1; $j < count($entries); $j++) {
					$a = $entries[$i];
					$b = $entries[$j];

					if ($a['day'] == $b['day']) {
						// Check overlap: (startA < endB) and (endA > startB)
						if ($a['start'] < $b['end'] && $a['end'] > $b['start']) {
							$conflicts[$uid][] = [
								'schedule1' => $a['id'],
								'schedule2' => $b['id']
							];
						}
					}
				}
			}
		}

		//$this->prx($conflicts);

		// Step 4: Output
		$conflictUIDS 		= [];
		$conflictDBAutoID 	= [];
		foreach ($conflicts as $uid => $conflictList) {
			$conflictUIDS[] = $uid;

			// Get db ids of schedules
			foreach ($conflictList as $row) {
				$conflictDBAutoID[] = $row['schedule1'];
				$conflictDBAutoID[] = $row['schedule2'];
			}
		}

		$finalDBAutoIDUnique = array_values(array_unique($conflictDBAutoID));



		//$msG = 'Scheduling completed successfully.';

		// Save conflicted user ids in database
		if(count($conflictUIDS)>0)
		{
			$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(",",$conflictUIDS)], ["conventionseasons_id" => $conventionSD->id]);
			$msG .= ' There are some conflicts found. Click on resolve conflict button below and resolve conflicts.';

			// Now filter group db ids where conflict found
			if(count($finalDBAutoIDUnique)>0)
			{
				$finalGroupSchDBIDs = array();
				// filter group ids only and save to db
				foreach($finalDBAutoIDUnique as $group_db_id)
				{
					// check if its a group id
					$checkGroupGame = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $group_db_id])->first();
					if($checkGroupGame->user_type == 'School')
					{
						$finalGroupSchDBIDs[] = $group_db_id;
					}
				}

				// Now update to db
				if(count($finalGroupSchDBIDs)>0)
				{
					$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(",",$finalGroupSchDBIDs)], ["conventionseasons_id" => $conventionSD->id]);
				}
			}
		}

		$this->prx($finalDBAutoIDUnique);

		$this->Flash->success($msG);
		$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
	} */



	/* public function removeoverlapping_noneed($convention_season_slug=null)
	{
		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND
			Schedulingtimings.season_year = '".$conventionSD->season_year."'
		)";
		// Fetch each schedule and check if there is any group name assigned
		$schedulingtimings = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.id" => "ASC"])
			->all();

		$arrallStudents= array();

		foreach($schedulingtimings as $recordsch)
		{
			// Individual events
			if($recordsch->user_type == 'Student')
			{
				if(!in_array($recordsch->user_id,$arrallStudents) && $recordsch->user_id>0)
				{
					$arrallStudents[] = $recordsch->user_id;
				}

				if(!in_array($recordsch->user_id_opponent,$arrallStudents) && $recordsch->user_id_opponent>0)
				{
					$arrallStudents[] = $recordsch->user_id_opponent;
				}
			}

			// Group events
			if($recordsch->user_type == 'School')
			{
				if(!empty($recordsch->group_name_user_ids) && $recordsch->group_name_user_ids != NULL)
				{
					$group_name_user_ids_explode = explode(",",$recordsch->group_name_user_ids);
					foreach($group_name_user_ids_explode as $uIDG)
					{
						if(!in_array($uIDG,$arrallStudents) && $uIDG>0)
						{
							//$arrallStudents[] = $uIDG;
						}
					}
				}

				if(!empty($recordsch->group_name_opponent_user_ids) && $recordsch->group_name_opponent_user_ids != NULL)
				{
					$group_name_opponent_user_ids_explode = explode(",",$recordsch->group_name_opponent_user_ids);
					foreach($group_name_opponent_user_ids_explode as $uIDG)
					{
						if(!in_array($uIDG,$arrallStudents) && $uIDG>0)
						{
							//$arrallStudents[] = $uIDG;
						}
					}
				}
			}
		}

		echo implode(",",$arrallStudents);

		$this->prx($arrallStudents);
	} */


	public function conflictdone($convention_season_slug=null) {

		$this->Flash->success('Scheduling completed successfully. Overlapping and conflicts removed successfully.');

		$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);

	}

	/**
	 * Post-schedule overview: shows all overflow/unplaced events after scheduling completes.
	 * Allows the admin to create a new room inline and immediately re-schedule
	 * overflow events into that room.
	 */
	public function postscheduleoverview($convention_season_slug=null) {
		$this->set('title', ADMIN_TITLE . 'Post-Schedule Overview');
		$this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}

		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where([
			'Schedulings.conventionseasons_id' => $conventionSD->id,
			'Schedulings.convention_id' => $conventionSD->convention_id,
			'Schedulings.season_id' => $conventionSD->season_id,
			'Schedulings.season_year' => $conventionSD->season_year
		])->first();

		$allAllowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
		$firstDay = !empty($schedulingD) ? $schedulingD->first_day : 'Monday';

		// Count overflow per category
		$categories = [1, 2, 3, 4];
		$categoryLabels = [
			1 => 'Cat 1 — Group Sequential',
			2 => 'Cat 2 — Individual Elimination',
			3 => 'Cat 3 — Group Elimination',
			4 => 'Cat 4 — Individual Sequential',
		];
		$overflowByCategory = [];
		$totalOverflow = 0;

		foreach ($categories as $cat) {
			$weekendCount = $this->Schedulingtimings->find()->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category' => $cat,
				'Schedulingtimings.day IN' => ['Friday','Saturday','Sunday']
			])->count();

			$unplacedCount = $this->Schedulingtimings->find()->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category' => $cat,
			])->andWhere(function($exp) {
				return $exp->or_([
					'Schedulingtimings.day IS' => null,
					'Schedulingtimings.start_time IS' => null,
					'Schedulingtimings.finish_time IS' => null,
				]);
			})->count();

			$catOverflow = $weekendCount + $unplacedCount;
			$overflowByCategory[$cat] = [
				'label' => $categoryLabels[$cat],
				'weekend' => $weekendCount,
				'unplaced' => $unplacedCount,
				'total' => $catOverflow,
			];
			$totalOverflow += $catOverflow;
		}

		// Get overflow event details (all categories)
		$overflowCond = [];
		$overflowCond[] = "(Schedulingtimings.conventionseasons_id = '".(int)$conventionSD->id."')";
		$overflowCond[] = "(Schedulingtimings.day IN ('Friday','Saturday','Sunday') OR Schedulingtimings.day IS NULL OR Schedulingtimings.start_time IS NULL OR Schedulingtimings.finish_time IS NULL)";

		$overflowTimings = $this->Schedulingtimings->find()
			->where($overflowCond)
			->contain(['Events', 'Users', 'Conventionrooms'])
			->order(['Schedulingtimings.schedule_category' => 'ASC', 'Schedulingtimings.id' => 'ASC'])
			->all()
			->toArray();

		// Group by event name for cleaner display
		$overflowByEvent = [];
		foreach ($overflowTimings as $timing) {
			$eventName = !empty($timing->Events['event_name']) ? $timing->Events['event_name'] : 'Unknown';
			$eventCode = !empty($timing->Events['event_id_number']) ? $timing->Events['event_id_number'] : '';
			$key = $eventCode . ' - ' . $eventName;
			if (!isset($overflowByEvent[$key])) {
				$overflowByEvent[$key] = [
					'event_name' => $eventName,
					'event_code' => $eventCode,
					'category' => (int)$timing->schedule_category,
					'count' => 0,
					'timings' => [],
				];
			}
			$overflowByEvent[$key]['count']++;
			$overflowByEvent[$key]['timings'][] = $timing;
		}

		// Existing rooms — only those assigned to events in this season
		$assignedRoomIds = $this->Conventionseasonroomevents->find()
			->select(['room_id'])
			->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])
			->group(['room_id'])
			->enableHydration(false)
			->toArray();
		$assignedRoomIds = array_filter(array_column($assignedRoomIds, 'room_id'));

		if (!empty($assignedRoomIds)) {
			$rooms = $this->Conventionrooms->find()
				->where([
					'Conventionrooms.convention_id' => $conventionSD->convention_id,
					'Conventionrooms.id IN' => $assignedRoomIds,
				])
				->order(['Conventionrooms.room_name' => 'ASC'])
				->all()
				->toArray();
		} else {
			$rooms = $this->Conventionrooms->find()
				->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
				->order(['Conventionrooms.room_name' => 'ASC'])
				->all()
				->toArray();
		}

		$this->set('overflowByCategory', $overflowByCategory);
		$this->set('totalOverflow', $totalOverflow);
		$this->set('overflowByEvent', $overflowByEvent);
		$this->set('overflowTimings', $overflowTimings);
		$this->set('rooms', $rooms);
		$this->set('categoryLabels', $categoryLabels);

		$scheduleHealth = $this->buildScheduleHealthMetrics((int)$conventionSD->id, $schedulingD);
		$overflowTrendRows = $this->getOverflowTrendRows((int)$conventionSD->id, 12);
		$this->set('scheduleHealth', $scheduleHealth);
		$this->set('overflowTrendRows', $overflowTrendRows);
	}

	/**
	 * Create a new room inline and immediately run auto-assign overflow
	 * for all schedule categories that have overflow events.
	 */
	public function createroomandreschedule($convention_season_slug=null) {
		$overflowBefore = 0;
		if (!$this->request->is('post')) {
			return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
		}

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		}
		$overflowBefore = $this->countOverflowForSeason($conventionSD->id, null);

		$requestData = $this->request->getData();
		$roomName = isset($requestData['NewRoom']['room_name']) ? trim($requestData['NewRoom']['room_name']) : '';
		$shortDesc = isset($requestData['NewRoom']['short_description']) ? trim($requestData['NewRoom']['short_description']) : '';

		if (empty($roomName)) {
			$this->Flash->error('Please enter a room name.');
			return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
		}

		// Check for duplicate room name
		$existingRoom = $this->Conventionrooms->find()->where([
			'Conventionrooms.convention_id' => $conventionSD->convention_id,
			'Conventionrooms.room_name' => $roomName
		])->first();

		if ($existingRoom) {
			$this->Flash->error('A room with that name already exists. Please choose a different name.');
			return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
		}

		// Create room
		$newRoom = $this->Conventionrooms->newEntity();
		$newRoom->slug = 'convention-room-' . $conventionSD->convention_id . '-' . time() . '-' . mt_rand(10000,99999);
		$newRoom->convention_id = $conventionSD->convention_id;
		$newRoom->room_name = $roomName;
		$newRoom->short_description = !empty($shortDesc) ? $shortDesc : $roomName;
		$newRoom->created = date('Y-m-d H:i:s');
		$newRoom->modified = date('Y-m-d H:i:s');

		if (!$this->Conventionrooms->save($newRoom)) {
			$this->Flash->error('Failed to create the new room. Please try again.');
			return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
		}

		$newRoomId = $newRoom->id;

		// Now run auto-assign for each category that has overflow
		$schedulingD = $this->Schedulings->find()->where([
			'Schedulings.conventionseasons_id' => $conventionSD->id,
			'Schedulings.convention_id' => $conventionSD->convention_id,
			'Schedulings.season_id' => $conventionSD->season_id,
			'Schedulings.season_year' => $conventionSD->season_year
		])->first();

		if (!$schedulingD) {
			$this->Flash->success('Room "' . h($roomName) . '" created, but scheduling setup not found. Please run auto-assign manually.');
			return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
		}

		$allAllowedDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
		$startDate = date('Y-m-d', strtotime($schedulingD->start_date));
		$firstDay = $schedulingD->first_day;

		// Build room map including the new room
		$rooms = $this->Conventionrooms->find()
			->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
			->order(['Conventionrooms.room_name' => 'ASC'])
			->all();

		$roomMap = [];
		foreach ($rooms as $room) {
			$roomMap[$room->id] = $room->room_name;
		}

		$totalAssigned = 0;
		$totalRemaining = 0;

		$categories = [1, 2, 3, 4];
		foreach ($categories as $cat) {
			// Build occupied map
			$assignedRows = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.day IN' => $allAllowedDays,
					'Schedulingtimings.room_id IS NOT' => null,
					'Schedulingtimings.start_time IS NOT' => null,
					'Schedulingtimings.finish_time IS NOT' => null
				])
				->select(['id', 'room_id', 'day', 'start_time', 'finish_time'])
				->all();

			$occupied = [];
			foreach ($assignedRows as $row) {
				$rid = (int)$row->room_id;
				$day = $row->day;
				if (!isset($occupied[$rid])) $occupied[$rid] = [];
				if (!isset($occupied[$rid][$day])) $occupied[$rid][$day] = [];
				$occupied[$rid][$day][] = ['start' => date('H:i:s', strtotime($row->start_time)), 'finish' => date('H:i:s', strtotime($row->finish_time))];
			}

			// Find overflow timings for this category
			$overflowCond = [];
			$overflowCond[] = "(Schedulingtimings.conventionseasons_id = '".(int)$conventionSD->id."')";
			$overflowCond[] = "(Schedulingtimings.schedule_category = '".(int)$cat."')";
			$overflowCond[] = "(Schedulingtimings.day IN ('Friday','Saturday','Sunday') OR Schedulingtimings.day IS NULL OR Schedulingtimings.start_time IS NULL OR Schedulingtimings.finish_time IS NULL)";

			$overflowTimings = $this->Schedulingtimings->find()
				->where($overflowCond)
				->contain(['Events', 'Users', 'Conventionrooms'])
				->all()
				->toArray();

			if (empty($overflowTimings)) continue;

			usort($overflowTimings, function($a, $b) {
				$durA = $this->calculateEventDurationMinutes($a->Events);
				$durB = $this->calculateEventDurationMinutes($b->Events);
				return $durA === $durB ? $a->id - $b->id : $durB - $durA;
			});

			foreach ($overflowTimings as $timing) {
				$suggestions = $this->buildOverflowSuggestionsForTiming($timing, $roomMap, $occupied, $allAllowedDays, $schedulingD, $firstDay, $conventionSD->id, 1);
				if (empty($suggestions)) {
					$totalRemaining++;
					continue;
				}

				$slot = $suggestions[0];
				$slotDate = $this->getDateForDayFromStart($startDate, $firstDay, $slot['day']);

				$this->Schedulingtimings->updateAll(
				[
					'room_id' => $slot['room_id'],
					'day' => $slot['day'],
					'start_time' => $slot['start_time'],
					'finish_time' => $slot['finish_time'],
					'sch_date_time' => $slotDate.' '.$slot['start_time'],
					'modified' => date('Y-m-d H:i:s')
				],
				['id' => $timing->id]
				);

				if (!isset($occupied[$slot['room_id']])) $occupied[$slot['room_id']] = [];
				if (!isset($occupied[$slot['room_id']][$slot['day']])) $occupied[$slot['room_id']][$slot['day']] = [];
				$occupied[$slot['room_id']][$slot['day']][] = ['start' => $slot['start_time'], 'finish' => $slot['finish_time']];

				$totalAssigned++;
			}
		}

		// Link the new room to its assigned events in conventionseasonroomevents
		// so it appears in schedule category views
		if ($totalAssigned > 0) {
			$assignedEventIds = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.room_id' => $newRoomId,
					'Schedulingtimings.start_time IS NOT' => null,
					'Schedulingtimings.finish_time IS NOT' => null,
					'Schedulingtimings.start_time !=' => 'Schedulingtimings.finish_time'
				])
				->select(['event_id'])
				->distinct(['event_id'])
				->all();

			$eventIdList = [];
			$studentsPerBlock = [];
			foreach ($assignedEventIds as $row) {
				$eid = (int)$row->event_id;
				if ($eid > 0 && !in_array($eid, $eventIdList)) {
					$eventIdList[] = $eid;
					$studentsPerBlock[(string)$eid] = null;
				}
			}

			if (!empty($eventIdList)) {
				$csre = $this->Conventionseasonroomevents->newEntity();
				$csre->slug = 'csre-' . $conventionSD->id . '-' . $newRoomId . '-' . time();
				$csre->conventionseasons_id = $conventionSD->id;
				$csre->convention_id = $conventionSD->convention_id;
				$csre->season_id = $conventionSD->season_id;
				$csre->season_year = $conventionSD->season_year;
				$csre->room_id = $newRoomId;
				$csre->event_ids = implode(',', $eventIdList);
				$csre->students_per_block = json_encode($studentsPerBlock);
				$csre->created = date('Y-m-d H:i:s');
				$csre->modified = date('Y-m-d H:i:s');
				$this->Conventionseasonroomevents->save($csre);
			}
		}

		$msg = 'Room "' . h($roomName) . '" created successfully.';
		if ($totalAssigned > 0) {
			$msg .= ' Auto-assigned ' . $totalAssigned . ' overflow events.';
		}
		if ($totalRemaining > 0) {
			$msg .= ' ' . $totalRemaining . ' events could not be placed.';
		}

		$this->Flash->success($msg);
		$overflowAfter = $this->countOverflowForSeason($conventionSD->id, null);
		$this->saveAutoassignRunSummary(
			$conventionSD->id,
			null,
			(int)$totalAssigned,
			(int)$totalRemaining,
			(int)$overflowBefore,
			(int)$overflowAfter,
			['Monday','Tuesday','Wednesday','Thursday'],
			[$newRoomId],
			'create_room_reschedule'
		);
		return $this->redirect(['action' => 'postscheduleoverview', $convention_season_slug]);
	}

}

?>
