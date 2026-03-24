<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class ResultsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Conventions');
		$this->loadModel('Eventsubmissions');
		$this->loadModel('Judgeevaluations');
		$this->loadModel('Results');
		$this->loadModel('Resultpositions');
		$this->loadModel('Crstudentevents');
		$this->loadModel('Divisions');

    }

    public function index($slug_convention_season = null,$slug_convention = null,$slug_event = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		global $resultPoints;

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);

		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'DESC'])->all();
			foreach($resultsPos as $resultp)
			{
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['position'] 				= $resultp->position;
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['avg_marks'] 				= $resultp->avg_marks;
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['points_obtained'] 		= $resultp->points_obtained;
			}
		}
		$this->set('checkResultsAlready',$checkResultsAlready);
		$this->set('arrAlreadySavedResults',$arrAlreadySavedResults);

		// to get total number of entries for this event in this conv season
		$eventSubmissionsCS 		= $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionseason_id' => $conventionSD->id,'Eventsubmissions.convention_id' => $conventionSD->convention_id,'Eventsubmissions.season_id' => $conventionSD->season_id,'Eventsubmissions.season_year' => $conventionSD->season_year,'Eventsubmissions.event_id' => $eventD->id])->contain(['Users','Students'])->all();
		$this->set('eventSubmissionsCS', $eventSubmissionsCS);

		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// to check if result already saved, then delete result positions data
			if($checkResultsAlready)
			{
				$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);

				$result_id = $checkResultsAlready->id;

				// make entry that original results modified
				$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			}
			else
			{
				// add new record to results first
				$results 	= $this->Results->newEntity();
				$dataR 		= $this->Results->patchEntity($results, array());

				$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
				$dataR->conventionseason_id 		= $conventionSD->id;
				$dataR->convention_id 				= $conventionSD->convention_id;
				$dataR->season_id 					= $conventionSD->season_id;
				$dataR->season_year 				= $conventionSD->season_year;
				$dataR->event_id 					= $eventD->id;
				$dataR->event_id_number 			= $eventD->event_id_number;
				$dataR->division_id 				= $eventD->division_id;
				$dataR->created 					= $conventionSD->created;

				$resultR = $this->Results->save($dataR);
				$result_id = $resultR->id;
			}


			foreach($eventSubmissionsCS as $datarecord)
			{
				$positionSub = $postData['result_position_'.$datarecord->id];

				// to check points_obtained
				if($positionSub>=1 && $positionSub<=6)
				{
					$points_obtained = $resultPoints[$positionSub];
				}
				else
				{
					$points_obtained = 0;
				}

				$resultpositions = $this->Resultpositions->newEntity();
				$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

				$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
				$dataRP->result_id							= $result_id;
				$dataRP->eventsubmission_id					= $datarecord->id;
				$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
				$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
				$dataRP->convention_id						= $datarecord->convention_id;
				$dataRP->user_id							= $datarecord->user_id;
				$dataRP->season_id							= $datarecord->season_id;
				$dataRP->season_year						= $datarecord->season_year;

				$dataRP->event_id							= $eventD->id;
				$dataRP->event_id_number					= $eventD->event_id_number;
				$dataRP->division_id						= $eventD->division_id;

				$dataRP->group_name							= $datarecord->group_name;
				$dataRP->student_id							= $datarecord->student_id;
				$dataRP->gender								= $datarecord->Students['gender'];
				$dataRP->position							= $postData['result_position_'.$datarecord->id];
				$dataRP->avg_marks							= $postData['result_avg_marks_'.$datarecord->id];
				$dataRP->points_obtained					= $points_obtained;

				$dataRP->created 							= date('Y-m-d H:i:s');
				$dataRP->modified 							= date('Y-m-d H:i:s');

				//$this->prx($dataRP);

				$resultRP = $this->Resultpositions->save($dataRP);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results saved sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);

		}
    }

	public function closejudging($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		global $resultPoints;



		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}



		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$eventSubmissionsCS 		= $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionseason_id' => $conventionSD->id,'Eventsubmissions.convention_id' => $conventionSD->convention_id,'Eventsubmissions.season_id' => $conventionSD->season_id,'Eventsubmissions.season_year' => $conventionSD->season_year,'Eventsubmissions.event_id' => $eventD->id])->contain(['Users','Students'])->all();

		foreach($eventSubmissionsCS as $datarecord)
		{
			// check how many judges judged this entry and get average
			$condAvg = array();
			$condAvg[] 	= "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
			$condAvg[] 	= "(Judgeevaluations.convention_id = '".$datarecord->convention_id."')";
			$condAvg[] 	= "(Judgeevaluations.season_id = '".$datarecord->season_id."')";
			$condAvg[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
			$judgeEvals = $this->Judgeevaluations->find()->where($condAvg)->all();

			$marksObtained = 0;
			$cntrJudging = 0;
			foreach($judgeEvals as $judgeeval)
			{
				$marksObtained = $marksObtained+$judgeeval->total_marks_obtained;
				$cntrJudging++;
			}

			if($cntrJudging>0)
			{
				$avgMarksSub = $marksObtained/$cntrJudging;
			}
			else
			{
				$avgMarksSub = 0;
			}

			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];

			$dataRP->avg_marks							= $avgMarksSub;

			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
		}



		// STEP 4 :: Create an array from resultpositions
		$resultPA = $this->Resultpositions->find()->where(['Resultpositions.result_id' => $result_id])->all();
		$records = array();
		foreach($resultPA as $resultpos)
		{
			$records[] = array("resultp_auto_id" => $resultpos->id,"avg_marks" => $resultpos->avg_marks);
		}



		// STEP 5 :: Sort this array from highest to lowest based on avg marks
		$key_values = array_column($records, 'avg_marks');
		array_multisort($key_values, SORT_DESC, $records);




		// STEP 6 :: Assign positions based on average marks
		$maxPoints = $records['0']['avg_marks'];
		if ($maxPoints < 80)
		{
			$positions = range(3, 6);
		}
		elseif ($maxPoints < 90)
		{
			$positions = range(2, 6);
		}
		else
		{
			$positions = range(1, 6);
		}

		$lastMark = 0;
		$lastPos = 0;
		foreach ($records as $i => $array)
		{
		  if (empty($positions) || $array['avg_marks'] < 70)
		  {
			$pos = null;
		  }
		  else
		  {
			$pos = $lastMark == $array['avg_marks'] ? $lastPos : array_shift($positions);
		  }

		  $lastMark 				= $array['avg_marks'];
		  $lastPos 					= $pos;
		  $records[$i]['position'] 	= $pos;
		}


		//$this->prx($records);



		// STEP 7 :: Update these positions in system
		foreach($records as $sortedArr)
		{
			// allocate points
			$positionSub = $sortedArr['position'];

			// to check points_obtained
			if($positionSub>=1 && $positionSub<=6)
			{
				$points_obtained = $resultPoints[$positionSub];
			}
			else
			{
				$points_obtained = 0;
			}

			$this->Resultpositions->updateAll(['position' => $sortedArr['position'],'points_obtained' => $points_obtained], ["id" => $sortedArr['resultp_auto_id'],"result_id" => $result_id]);
		}


		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);


		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function points($slug_convention_season = null,$slug_convention = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Points > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		// to get all Resultpositions of this convention season
		$arrAllResults = array();
		$allResultsConventionSeason 		= $this->Resultpositions->find()->where(['Resultpositions.conventionseason_id' => $conventionSD->id,'Resultpositions.convention_id' => $conventionSD->convention_id,'Resultpositions.season_id' => $conventionSD->season_id,'Resultpositions.season_year' => $conventionSD->season_year,'Resultpositions.points_obtained >' => 0])->order(['Resultpositions.id' => 'ASC'])->all();
		if($allResultsConventionSeason)
		{
			//$this->prx($allResultsConventionSeason);

			foreach($allResultsConventionSeason as $allresultcs)
			{
				// There are two conditions

				// 1. if its individual student
				if($allresultcs->student_id>0)
				{
					$arrAllResults[$allresultcs->division_id][$allresultcs->student_id] = $arrAllResults[$allresultcs->division_id][$allresultcs->student_id] + $allresultcs->points_obtained;
				}

				// 2. if its a group
				if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
				{
					//$this->prx($allresultcs);

					// now fetch all students of this group
					$groupStudents = $this->Crstudentevents->find()->where(['Crstudentevents.group_name' => $allresultcs->group_name,'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,'Crstudentevents.event_id' => $allresultcs->event_id])->all();
					foreach($groupStudents as $groupst)
					{
						//$this->prx($groupst);
						$arrAllResults[$allresultcs->division_id][$groupst->student_id] = $arrAllResults[$allresultcs->division_id][$groupst->student_id] + $allresultcs->points_obtained;
						//echo $groupst->student_id;echo '<br>';exit;
					}
				}
			}

			$this->set('arrAllResults', $arrAllResults);

			//$this->prx($arrAllResults);

		}
		else
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}

		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);

    }

	public function overallpoints($slug_convention_season = null,$slug_convention = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Points > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		// to get all Resultpositions of this convention season
		$arrAllResults = array();
		$allResultsConventionSeason 		= $this->Resultpositions->find()->where(['Resultpositions.conventionseason_id' => $conventionSD->id,'Resultpositions.convention_id' => $conventionSD->convention_id,'Resultpositions.season_id' => $conventionSD->season_id,'Resultpositions.season_year' => $conventionSD->season_year,'Resultpositions.points_obtained >' => 0])->order(['Resultpositions.points_obtained' => 'ASC'])->all();
		if($allResultsConventionSeason)
		{
			//$this->prx($allResultsConventionSeason);


			foreach($allResultsConventionSeason as $allresultcs)
			{
				// There are two conditions

				// 1. if its individual student
				if($allresultcs->student_id>0)
				{
					$arrAllResults[$allresultcs->student_id] = $arrAllResults[$allresultcs->student_id] + $allresultcs->points_obtained;
				}

				if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
				{
					//$this->prx($allresultcs);

					// now fetch all students of this group
					$groupStudents = $this->Crstudentevents->find()->where(['Crstudentevents.group_name' => $allresultcs->group_name,'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,'Crstudentevents.event_id' => $allresultcs->event_id])->all();
					foreach($groupStudents as $groupst)
					{
						$arrAllResults[$groupst->student_id] = $arrAllResults[$groupst->student_id] + $allresultcs->points_obtained;
					}
				}
			}

			$this->set('arrAllResults', $arrAllResults);

			//$this->prx($arrAllResults);

			// Step 1: Find max value
			$maxValue = max($arrAllResults);

			// Step 2: Find all keys with that max value
			$maxKeys = array_keys($arrAllResults, $maxValue);
			$this->set('maxKeys', $maxKeys);

			//$this->prx($maxKeys);
		}
		else
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}

		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);

    }

	public function overallpositions($slug_convention_season = null,$slug_convention = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Overall Positions > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;

		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);

		//$this->prx($arrConvSeasonEvent);

    }

	public function overallpositionsprint($slug_convention_season = null,$slug_convention = null) {

        $this->viewBuilder()->setLayout('print_reports');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Overall Positions > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;

		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);

		//$this->prx($arrConvSeasonEvent);

    }

	public function closejudgingtimes($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		global $resultPoints;


		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}



		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.time_score" => "ASC"])->all();
		//$this->prx($judgeEvals);

		$cntrRecord = 1;

		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{
			// Calculate tie breakers
			if($cntrRecord == 1)
			{
				$lastScore = $datarecord->time_score;
			}
			else
			{
				if($lastScore != $datarecord->time_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->time_score;
				}
			}

			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);


			$cntrRecord++;
		}

		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

		//echo $result_id;exit;

		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function resulttimes($slug_convention_season = null,$slug_convention = null,$slug_event = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		global $resultPoints;

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);

		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		$this->set('checkResultsAlready',$checkResultsAlready);
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;

			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Users','Students'])->all();
			$this->set('resultsPos',$resultsPos);
		}
		else
		{
			// redirect if no result
			$this->Flash->error('Result not found for this event.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}


		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];

				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}

				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }

	public function closejudgingdistances($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		// To get qualifying data
		$convSeasEventD = $this->Conventionseasonevents->find()
				->where([
				'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
				'Conventionseasonevents.event_id' => $eventD->id
				])->first();

		//$this->prx($convSeasEventD);

		$qualifying_distance = $convSeasEventD->convSeasEventD;

		global $resultPoints;


		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}


		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		//$condEval[] 	= "(Judgeevaluations.distance_score >0)";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.distance_score >= '".$qualifying_distance."')";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.distance_score" => "DESC"])->all();
		//$this->prx($judgeEvals);

		$cntrRecord = 1;

		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{
			// Calculate tie breakers
			if($cntrRecord == 1)
			{
				$lastScore = $datarecord->distance_score;
			}
			else
			{
				if($lastScore != $datarecord->distance_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->distance_score;
				}
			}

			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->distance_score;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);

			$cntrRecord++;
		}

		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

		//echo $result_id;exit;

		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function resultdistances($slug_convention_season = null,$slug_convention = null,$slug_event = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		global $resultPoints;

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);

		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();

		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Users','Students'])->all();
			$this->set('resultsPos', $resultsPos);

			$this->set('checkResultsAlready',$checkResultsAlready);

		}
		else
		{
			// Redirect if no result
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}



		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];

				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}

				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }

	public function closejudgingscores($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		global $resultPoints;


		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		//$this->prx($checkResultsAlready);
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}


		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.all_pos_score >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.all_pos_score" => "DESC"])->all();
		//$this->prx($judgeEvals);

		$cntrRecord = 1;

		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{
			// Calculate tie breakers
			if($cntrRecord == 1)
			{
				$lastScore = $datarecord->all_pos_score;
			}
			else
			{
				if($lastScore != $datarecord->all_pos_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->all_pos_score;
				}
			}

			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->all_pos_score;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);

			$cntrRecord++;
		}

		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

		//echo $result_id;exit;

		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function resultscores($slug_convention_season = null,$slug_convention = null,$slug_event = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		global $resultPoints;

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);

		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(["Users","Students"])->all();
			$this->set('resultsPos',$resultsPos);

			$this->set('checkResultsAlready',$checkResultsAlready);
		}
		else
		{
			// Redirect if no results
			$this->Flash->error('No result found..');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];

				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}

				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }

	/* Soccer Kick */
	public function closejudgingsoccerkick($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		global $resultPoints;


		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		//$this->prx($checkResultsAlready);
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}


		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.soccer_kick_best_kick >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.soccer_kick_best_kick" => "DESC"])->all();
		//$this->prx($judgeEvals);

		$cntrRecord = 1;

		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{
			// Calculate tie breakers
			if($cntrRecord == 1)
			{
				$lastScore = $datarecord->soccer_kick_best_kick;
			}
			else
			{
				if($lastScore != $datarecord->soccer_kick_best_kick)
				{
					$cntrPos++;
					$lastScore = $datarecord->soccer_kick_best_kick;
				}
			}


			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->soccer_kick_best_kick;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);

			$cntrRecord++;
		}

		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

		//echo $result_id;exit;

		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function resultsoccerkick($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);


		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Students','Users'])->all();
			$this->set('resultsPos', $resultsPos);

			$this->set('checkResultsAlready', $checkResultsAlready);
		}
		else
		{
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];

				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}

				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }

	//Spellings
	public function closejudgingspellings($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		global $resultPoints;


		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}


		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity();
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;



		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE

		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.spelling_score >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.spelling_score" => "DESC"])->all();
		//$this->prx($judgeEvals);

		$cntrRecord = 1;

		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{
			// Calculate tie breakers
			if($cntrRecord == 1)
			{
				$lastScore = $datarecord->spelling_score;
			}
			else
			{
				if($lastScore != $datarecord->spelling_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->spelling_score;
				}
			}

			// enter record
			$resultpositions = $this->Resultpositions->newEntity();
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->spelling_score;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');

			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}

			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);

			$cntrRecord++;
		}

		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

		//echo $result_id;exit;

		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);

		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function resultspellings($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);


		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Students','Users'])->all();
			$this->set('resultsPos', $resultsPos);
		}
		else
		{
			// Redirect if no result
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());

			$postData = $this->request->getData();

			//$this->prx($postData);

			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];

				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}

				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}

			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }

	// Division winners
	public function divisionwinners($slug_convention_season = null,$slug_convention = null) {

        $this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Division Winners > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		// To get all divisions
		$divisions 			= $this->Divisions->find()->where(['Divisions.status' => 1])->order(['Divisions.name' => 'ASC'])->all();
		$this->set('divisions', $divisions);
		//$this->prx($divisions);

		// to get all Resultpositions of this convention season
		$arrAllResults = array();
		$allResultsConventionSeason 		= $this->Resultpositions->find()->where(['Resultpositions.conventionseason_id' => $conventionSD->id,'Resultpositions.convention_id' => $conventionSD->convention_id,'Resultpositions.season_id' => $conventionSD->season_id,'Resultpositions.season_year' => $conventionSD->season_year,'Resultpositions.points_obtained >' => 0])->order(['Resultpositions.id' => 'ASC'])->all();
		if($allResultsConventionSeason)
		{
			//$this->prx($allResultsConventionSeason);

			foreach($allResultsConventionSeason as $allresultcs)
			{
				// There are two conditions

				// 1. if its individual student
				if($allresultcs->student_id>0)
				{
					$arrAllResults[$allresultcs->division_id][$allresultcs->student_id] = $arrAllResults[$allresultcs->division_id][$allresultcs->student_id] + $allresultcs->points_obtained;
				}

				// 2. if its a group
				if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
				{
					//$this->prx($allresultcs);

					// now fetch all students of this group
					$groupStudents = $this->Crstudentevents->find()->where(['Crstudentevents.group_name' => $allresultcs->group_name,'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,'Crstudentevents.event_id' => $allresultcs->event_id])->all();
					foreach($groupStudents as $groupst)
					{
						//$this->prx($groupst);
						$arrAllResults[$allresultcs->division_id][$groupst->student_id] = $arrAllResults[$allresultcs->division_id][$groupst->student_id] + $allresultcs->points_obtained;
						//echo $groupst->student_id;echo '<br>';exit;
					}
				}
			}

			$this->set('arrAllResults', $arrAllResults);

			//$this->prx($arrAllResults);
		}
		else
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}

		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);

    }

	public function divisionwinnercertificatepdf($slug_convention_season = null,$slug_division = null,$slug_student = null) {

        $this->viewBuilder()->setLayout('');

		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		$data = array();

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_division) {
            $divisionD 		= $this->Divisions->find()->where(['Divisions.slug' => $slug_division])->first();
			$this->set('divisionD', $divisionD);
        }
		if (!$divisionD)
		{
			$this->Flash->error('Division not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_student) {
            $studentD 		= $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
        }
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Division Winners > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		$arrCertData = array();

		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] 	= $studentD->first_name.' '.$studentD->last_name;
		$arrCertData['school_name'] 	= $studentD->Schools['first_name'];
		$arrCertData['division_name'] 	= $divisionD->name;


		$this->set('arrCertData', $arrCertData);
    }

	// 24/7 Certificate
	public function certificate24by7pdf($slug_convention_season = null,$slug_student = null,$points = null) {

        $this->viewBuilder()->setLayout('');

        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_student) {
            $studentD 		= $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
        }
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}


		$arrCertData = array();

		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] 	= $studentD->first_name.' '.$studentD->last_name;
		$arrCertData['school_name'] 	= $studentD->Schools['first_name'];
		$arrCertData['points'] 			= $points;

		//$this->prx($arrCertData);


		$this->set('arrCertData', $arrCertData);
    }


}

?>
