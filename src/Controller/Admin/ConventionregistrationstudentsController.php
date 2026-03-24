<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class ConventionregistrationstudentsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Events.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
		$this->loadModel('Divisions');
		$this->loadModel('Books');
		$this->loadModel("Events");
		$this->loadModel("Users");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Eventsubmissions");
		$this->loadModel("Crstudentevents");
    }

	public function allstudents() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $condition = array();

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();

		$condition[] = "(Conventionregistrationstudents.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrationstudents.season_id = '".$convSeasonD->season_id."' AND Conventionregistrationstudents.season_year = '".$convSeasonD->season_year."')";

		$conventionregistrationstudents = $this->Conventionregistrationstudents->find()->contain(['Users','Students'])->where($condition)->order(["Conventionregistrationstudents.id" => "DESC"])->all();
		$this->set('conventionregistrationstudents', $conventionregistrationstudents);
    }

	public function studentevents($conv_reg_student_slug = NULL) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Student Events');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $condition = array();
		$condition[] = "(Conventionregistrationstudents.slug = '".$conv_reg_student_slug."')";

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();

		$condition[] = "(Conventionregistrationstudents.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrationstudents.season_id = '".$convSeasonD->season_id."' AND Conventionregistrationstudents.season_year = '".$convSeasonD->season_year."')";

		$convRegStudentD = $this->Conventionregistrationstudents->find()->contain(['Users','Students'])->where($condition)->first();
		$this->set('convRegStudentD', $convRegStudentD);
		//$this->prx($convRegStudentD);

		if(empty($convRegStudentD->event_ids) || $convRegStudentD->event_ids == NULL)
		{
			$this->Flash->error('No event found for this student.');
			$this->redirect(['controller' => 'conventionregistrationstudents', 'action' => 'allstudents']);
		}
		else
		{
			$event_ids_student 	= $convRegStudentD->event_ids;

			$condEvents = array();
			$condEvents[] = "(Events.id IN  ($event_ids_student))";

			$eventsList = $this->Events->find()->where($condEvents)->order(["Events.event_name" => "ASC"])->all();
			//$this->prx($eventsList);
			$this->set('eventsList', $eventsList);
		}
    }

    public function removestudentevent($conv_reg_student_slug=NULL, $event_slug = NULL) {

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");

		$convRegStudentD = $this->Conventionregistrationstudents->find()->where(["slug" => $conv_reg_student_slug])->first();

		$eventD = $this->Events->find()->where(["slug" => $event_slug])->first();

		if($convRegStudentD->id>0 && $eventD->id>0)
		{
			$student_event_ids_explode = explode(",",$convRegStudentD->event_ids);

			$keyE = array_search($eventD->id, $student_event_ids_explode);
			if ($keyE !== false) {
				unset($student_event_ids_explode[$keyE]);

				if(count($student_event_ids_explode))
				{
					$eventImplode = implode(",",$student_event_ids_explode);
				}
				else
				{
					$eventImplode = NULL;
				}

				// Step 1 :: Now remove if any grouping done for this student event
				$condCRSTE = array();
				$condCRSTE[] = "(Crstudentevents.conventionseason_id = '".$sess_admin_header_season_id."' AND Crstudentevents.convention_id = '".$convRegStudentD->convention_id."' AND Crstudentevents.season_id = '".$convRegStudentD->season_id."' AND Crstudentevents.season_year = '".$convRegStudentD->season_year."')";
				$condCRSTE[] = "(Crstudentevents.conventionregistration_id = '".$convRegStudentD->conventionregistration_id."' AND Crstudentevents.user_id = '".$convRegStudentD->user_id."' AND Crstudentevents.student_id = '".$convRegStudentD->student_id."')";
				$condCRSTE[] = "(Crstudentevents.event_id = '".$eventD->id."' AND Crstudentevents.event_id_number = '".$eventD->event_id_number."')";
				$crstED = $this->Crstudentevents->find()->where($condCRSTE)->first();
				$this->Crstudentevents->deleteAll(['id'=>$crstED->id]);


				// Step 2 :: Now we need to remove submissions if any
				$condES = array();
				$condES[] = "(Eventsubmissions.convention_id = '".$convRegStudentD->convention_id."' AND Eventsubmissions.season_id = '".$convRegStudentD->season_id."' AND Eventsubmissions.season_year = '".$convRegStudentD->season_year."')";

				$condES[] = "(Eventsubmissions.conventionregistration_id = '".$convRegStudentD->conventionregistration_id."' AND Eventsubmissions.user_id = '".$convRegStudentD->user_id."' AND Eventsubmissions.student_id = '".$convRegStudentD->student_id."')";

				$condES[] = "(Eventsubmissions.event_id = '".$eventD->id."' AND Eventsubmissions.event_id_number = '".$eventD->event_id_number."')";

				$eventSubmissionD = $this->Eventsubmissions->find()->where($condES)->first();
				if($eventSubmissionD)
				{
					if(!empty($eventSubmissionD->mediafile_file_system_name) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->mediafile_file_system_name))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->mediafile_file_system_name);
					}

					if(!empty($eventSubmissionD->report) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->report))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->report);
					}

					if(!empty($eventSubmissionD->score_sheet) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->score_sheet))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->score_sheet);
					}

					if(!empty($eventSubmissionD->additional_documents) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->additional_documents))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$eventSubmissionD->additional_documents);
					}

					// Remove submission
					$this->Eventsubmissions->deleteAll(["id"=>$eventSubmissionD->id]);
				}

				// now update student Events
				$this->Conventionregistrationstudents->updateAll(['event_ids' => $eventImplode], ["slug" => $conv_reg_student_slug]);

				$this->Flash->success('Event successfully removed for this student.');
			}

		}

		$this->redirect(['controller' => 'conventionregistrationstudents', 'action' => 'studentevents',$conv_reg_student_slug]);

	}



    public function events($conv_reg_slug=NULL) {

        $this->set('title', ADMIN_TITLE . 'Manage Events Registered');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

		$separator = array();
        $condition = array();

		if($conv_reg_slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions','Users'])->first();
			$this->set('CRDetails', $CRDetails);

			$this->set('conv_reg_slug', $conv_reg_slug);

			$condition = array('Conventionregistrationstudents.conventionregistration_id' => $CRDetails->id);

			$crstudents = $this->Conventionregistrationstudents->find()->where($condition)->contain(['Users'])->all();
			$this->set('crstudents', $crstudents);

			$allEventsArr = array();

			// now extract all events from students list
			foreach($crstudents as $crstudent)
			{
				$event_ids = $crstudent->event_ids;
				if(!empty($event_ids))
				{
					$event_ids_explode = explode(",",$event_ids);
					foreach($event_ids_explode as $event_id)
					{
						if(!in_array($event_id,(array)$allEventsArr))
						{
							$allEventsArr[] = $event_id;
						}
					}
				}
			}

			//$this->prx($allEventsArr);

			$this->set('allEventsArr', $allEventsArr);
		}



		//$this->prx($crstudents);

    }

	public function scriptureawardpdf($slug_convention_season = null, $school_slug = null, $event_slug = null) {

		//$this->helpers[] = 'Pdf';

		$this->viewBuilder()->setLayout('');

		// to get convention season details
		if ($slug_convention_season)
		{
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
        }

		// to get school details
		if ($school_slug)
		{
            $schoolD 			= $this->Users->find()->where(['Users.slug' => $school_slug])->first();
			$this->set('schoolD', $schoolD);
        }

		// to get event details
		if ($event_slug)
		{
            $eventD 			= $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$event_id 			= $eventD->id;
			$this->set('eventD', $eventD);
        }

		// now fetch all students who match with this event
		$condConvRegStudents = array();
		$condConvRegStudents[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND Conventionregistrationstudents.user_id = '".$schoolD->id."' AND Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condConvRegStudents[] = "(Conventionregistrationstudents.event_ids LIKE '".$event_id."' OR Conventionregistrationstudents.event_ids LIKE '".$event_id.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id."')";

		$conventionregistrationstudents = $this->Conventionregistrationstudents->find()->where($condConvRegStudents)->contain(['Students','Users','Conventions'])->all();
		$this->set('conventionregistrationstudents', $conventionregistrationstudents);

		//$this->prx($conventionregistrationstudents);



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


		$certificateTheme	=	$arrEventTheme[$event_id];
		$this->set('certificateTheme', $certificateTheme);

		//$this->prx($certificateTheme);


		//$this->prx($arrCertData);

		ini_set('memory_limit', '512M');
        set_time_limit(0);

	}
}

?>
