<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class ConventionregistrationsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventionregistrations.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
		$this->loadModel('Conventions');
		$this->loadModel('Events');
		$this->loadModel('Settings');
		$this->loadModel('Seasons');
		$this->loadModel('Emailtemplates');
		$this->loadModel('Conventionregistrationteachers');
		$this->loadModel('Conventionregistrationstudents');
		$this->loadModel('Heartevents');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionseasons');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Convention Registrations');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Conventionregistrations.conventionseason_id = '".$sess_admin_header_season_id."')";
		}

		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);

		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);

		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrations->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrations->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrations->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionregistrations']['convention_id']) && $this->request->getData()['Conventionregistrations']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrations']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrations']['season_year']) && $this->request->getData()['Conventionregistrations']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrations']['season_year']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0] != '') {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrations.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrations.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }

		//$this->pr($condition);

        /* //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Users'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Conventionregistrations.id' => 'DESC']];
        $this->set('conventionregistrations', $this->paginate($this->Conventionregistrations));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('index');
        } */

		$conventionregistrations 		= $this->Conventionregistrations->find()->where($condition)->contain(['Conventions','Users'])->order(['Conventionregistrations.id' => 'DESC'])->all();
		$this->set('conventionregistrations', $conventionregistrations);

    }

	public function teachers($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

		$separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);

		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);

			$this->set('slug', $slug);

			$condition = array('Conventionregistrationteachers.conventionregistration_id' => $CRDetails->id);
		}



        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
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

            if (isset($this->request->getData()['Conventionregistrationteachers']['convention_id']) && $this->request->getData()['Conventionregistrationteachers']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrationteachers']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrationteachers']['season_year']) && $this->request->getData()['Conventionregistrationteachers']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrationteachers']['season_year']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0] != '') {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrationteachers.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrationteachers.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }

        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Users','Teachers'], 'conditions' => $condition, 'limit' => 500, 'order' => ['Conventionregistrationteachers.id' => 'DESC']];
        $this->set('conventionregistrationteachers', $this->paginate($this->Conventionregistrationteachers));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('teachers');
        }
    }

	public function students($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

		$separator = array();
        $condition = array();

		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);

			$this->set('slug', $slug);

			$condition = array('Conventionregistrationstudents.conventionregistration_id' => $CRDetails->id);
		}



        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
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

            if (isset($this->request->getData()['Conventionregistrationstudents']['convention_id']) && $this->request->getData()['Conventionregistrationstudents']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrationstudents']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrationstudents']['season_year']) && $this->request->getData()['Conventionregistrationstudents']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrationstudents']['season_year']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0] != '') {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrationstudents.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrationstudents.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }

        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Users','Students','Teachers'], 'conditions' => $condition, 'limit' => 500, 'order' => ['Conventionregistrationstudents.id' => 'DESC']];
        $this->set('conventionregistrationstudents', $this->paginate($this->Conventionregistrationstudents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('students');
        }
    }

	public function heartevents($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Events of the heart');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

		$separator = array();
        $condition = array();

		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);

			$this->set('slug', $slug);

			$condition = array('Heartevents.conventionregistration_id' => $CRDetails->id);
		}



        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Heartevents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Heartevents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Heartevents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Heartevents']['convention_id']) && $this->request->getData()['Heartevents']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Heartevents']['convention_id']);
            }
			if (isset($this->request->getData()['Heartevents']['season_year']) && $this->request->getData()['Heartevents']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Heartevents']['season_year']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0] != '') {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Heartevents.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Heartevents.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }

        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Students','Uploadeduser'], 'conditions' => $condition, 'limit' => 50, 'order' => ['Heartevents.id' => 'DESC']];
        $this->set('heartevents', $this->paginate($this->Heartevents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Heartevents');
            $this->render('heartevents');
        }
    }

	public function removedocument($eventheart_slug = null, $conv_reg_slug = null) {

		$convRedG = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->first();
		if($convRedG)
		{
			// check if events of heart exists
			$checkExists = $this->Heartevents->find()->where(['Heartevents.slug' => $eventheart_slug,'Heartevents.conventionregistration_id' => $convRedG->id])->first();

			if($checkExists)
			{
				// to remove document as well
				@unlink(UPLOAD_EVENTS_HEART_PATH.$checkExists->mediafile_file_system_name);

				$this->Flash->success('Events of the heart removed successfully.');
				$this->Heartevents->deleteAll(["slug" => $eventheart_slug]);
			}
			else
			{
				$this->Flash->error('Invalid document.');
			}
		}
		else
		{
			$this->Flash->error('Invalid registration.');
		}

		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'heartevents', $conv_reg_slug]);
    }

	public function approvejudgeregistration($slug=null) {

		$convRegEnteredD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug,'Conventionregistrations.status' => 2])->contain(['Conventions','Users'])->first();
		if($convRegEnteredD)
		{
			$this->Conventionregistrations->updateAll(['status' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);

			// now sendning email to judge that account is active
			$emailId = $convRegEnteredD->Users['email_address'];

			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '19'])->first();

			$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($convRegEnteredD->Users['first_name'],$convRegEnteredD->Conventions['name'],$convRegEnteredD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

			//echo $messageToSend; exit;

            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);

			$this->Flash->success('Registration approved successfully.');

		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
    }

	public function declinejudgeregistration($slug=null) {

		$convRegEnteredD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug,'Conventionregistrations.status' => 2])->contain(['Conventions','Users'])->first();
		if($convRegEnteredD)
		{
			$this->Conventionregistrations->updateAll(['status' => '0','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);

			// now sendning email to judge that account is active
			$emailId = $convRegEnteredD->Users['email_address'];

			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '20'])->first();

			$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($convRegEnteredD->Users['first_name'],$convRegEnteredD->Conventions['name'],$convRegEnteredD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

			//echo $messageToSend; exit;

            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);

			$this->Flash->success('Registration approved successfully.');

		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
    }

	public function judgeregevents($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Judge Events');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions','Users'])->first();
			$this->set('CRDetails', $CRDetails);

			// sometimes conventionseason_id is null
			if($CRDetails->conventionseason_id >0)
			{
				$conventionseason_id = $CRDetails->conventionseason_id;
			}
			else
			{
				// get conv season
				$getConvSeason = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $CRDetails->convention_id,'Conventionseasons.season_id' => $CRDetails->season_id,'Conventionseasons.season_year' => $CRDetails->season_year])->first();

				if($getConvSeason->id >0)
				{
					// update conv season id
					$this->Conventionregistrations->updateAll(['conventionseason_id' => $getConvSeason->id], ["slug" => $slug]);

					$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions','Users'])->first();
				}

			}

			$this->set('slug', $slug);

			// to get the list of event ids chosen in this convention for this season
			$arrConvSeasonEvents = array();
			$arrConvSeasonEvents[] = 0;
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $CRDetails->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
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
				//$this->prx($this->request->getData());

				$send_email_notification = $this->request->getData()['send_email_notification'];

				if(count($this->request->getData()['Conventionregistrations']['judges_event_ids']))
				{
					$judges_event_ids 			= implode(",",$this->request->getData()['Conventionregistrations']['judges_event_ids']);
				}
				else
				{
					$judges_event_ids 			= '';
				}

				$this->Conventionregistrations->updateAll(['judges_event_ids' => $judges_event_ids, 'modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);


				// for us to send email notification that events have been added to their judges portal
				$msgNot = "";
				if($send_email_notification)
				{
					$emailId = $CRDetails->Users['email_address'];

					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '25'])->first();

					$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
					$fromRepArray = array($CRDetails->Users['first_name'],$CRDetails->Conventions['name'],$CRDetails->season_year);

					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

					//echo $messageToSend; exit;

                    $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);

					$msgNot = " Email notification sent successfully to judge.";
				}


				$this->Flash->success('Events list updated successfully.'.$msgNot);
				$this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);

			}


		}
		else
		{
			$this->Flash->error('Invalid action.');
			$this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
		}

    }

	public function allschools($conv_season_slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Schools');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();

		$this->set('convSeasonD', $convSeasonD);

		$condition = array();

		$condition[] = "(Conventionregistrations.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrations.season_id = '".$convSeasonD->season_id."' AND Conventionregistrations.season_year = '".$convSeasonD->season_year."')";


		$conventionregistrations = $this->Conventionregistrations->find()->contain(['Users'])->where($condition)->order(["Conventionregistrations.id" => "DESC"])->all();
		$this->set('conventionregistrations', $conventionregistrations);
    }

	public function alljudges() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
        //$this->set('registrationsList', '1');

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if (empty($convSeasonD)) {
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }
        $this->set('convSeasonD', $convSeasonD);

		$condition[] = "(Conventionregistrations.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrations.season_id = '".$convSeasonD->season_id."' AND Conventionregistrations.season_year = '".$convSeasonD->season_year."')";

		$conventionregistrations = $this->Conventionregistrations->find()->contain(['Users'])->where($condition)->order(["Conventionregistrations.id" => "DESC"])->all();
		$this->set('conventionregistrations', $conventionregistrations);

        $eventNameIDDD = $this->getSeasonEventNameMap((int)$convSeasonD->id);
        $this->set('eventNameIDDD', $eventNameIDDD);

        $conventionD = $this->Conventions->find()->where(['Conventions.id' => $convSeasonD->convention_id])->first();
        $slugConvention = !empty($conventionD) ? (string)$conventionD->slug : '';
        $slugConventionSeason = (string)$convSeasonD->slug;
        $this->set('slugConvention', $slugConvention);
        $this->set('slugConventionSeason', $slugConventionSeason);
        $this->set('eventResultRouteMap', $this->getSeasonEventResultRouteMap($convSeasonD));
    }

    public function addjudgeevent($slug = null) {
        $isAjax = $this->request->is('ajax');
        if (!$this->request->is('post')) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Invalid request.');
            }
            $this->Flash->error('Invalid request.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $convSeasonD = $this->getSelectedConventionSeasonFromSession();
        if (empty($convSeasonD)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Please select a convention season first.');
            }
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        $eventId = (int)$this->request->getData('event_id');
        if ($eventId <= 0 || empty($slug)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Invalid event selection.');
            }
            $this->Flash->error('Invalid event selection.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $allowedEventMap = $this->getSeasonEventNameMap((int)$convSeasonD->id);
        $eventResultRouteMap = $this->getSeasonEventResultRouteMap($convSeasonD);
        if (!isset($allowedEventMap[$eventId])) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Selected event is not available in this season.');
            }
            $this->Flash->error('Selected event is not available in this season.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $judgeReg = $this->Conventionregistrations->find()
            ->contain(['Users'])
            ->where(['Conventionregistrations.slug' => $slug])
            ->first();

        if (empty($judgeReg)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Judge registration not found.');
            }
            $this->Flash->error('Judge registration not found.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $isJudge = (($judgeReg->Users['user_type'] == 'Judge' || $judgeReg->Users['user_type'] == 'Teacher_Parent') && (int)$judgeReg->Users['is_judge'] === 1);
        if (!$isJudge) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Selected registration is not a judge.');
            }
            $this->Flash->error('Selected registration is not a judge.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $currentEventIds = [];
        if (!empty($judgeReg->judges_event_ids)) {
            $currentEventIds = array_values(array_unique(array_map('intval', array_filter(array_map('trim', explode(',', (string)$judgeReg->judges_event_ids))))));
        }

        if (in_array($eventId, $currentEventIds)) {
            if ($isAjax) {
                $payload = $this->buildJudgeEventPayload($judgeReg, $allowedEventMap, $eventResultRouteMap, $convSeasonD);
                return $this->jsonJudgeEventResponse(true, 'Event already assigned to this judge.', $payload);
            }
            $this->Flash->success('Event already assigned to this judge.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $currentEventIds[] = $eventId;
        sort($currentEventIds);
        $this->Conventionregistrations->updateAll([
            'judges_event_ids' => implode(',', $currentEventIds),
            'modified' => date('Y-m-d H:i:s'),
        ], ['id' => $judgeReg->id]);

        $judgeReg->judges_event_ids = implode(',', $currentEventIds);
        if ($isAjax) {
            $payload = $this->buildJudgeEventPayload($judgeReg, $allowedEventMap, $eventResultRouteMap, $convSeasonD);
            return $this->jsonJudgeEventResponse(true, 'Event added for judge successfully.', $payload);
        }

        $this->Flash->success('Event added for judge successfully.');
        return $this->redirect(['action' => 'alljudges']);
    }

    public function removejudgeevent($slug = null) {
        $isAjax = $this->request->is('ajax');
        if (!$this->request->is('post')) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Invalid request.');
            }
            $this->Flash->error('Invalid request.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $convSeasonD = $this->getSelectedConventionSeasonFromSession();
        if (empty($convSeasonD)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Please select a convention season first.');
            }
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        $eventId = (int)$this->request->getData('event_id');
        if ($eventId <= 0 || empty($slug)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Invalid event selection.');
            }
            $this->Flash->error('Invalid event selection.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $allowedEventMap = $this->getSeasonEventNameMap((int)$convSeasonD->id);
        $eventResultRouteMap = $this->getSeasonEventResultRouteMap($convSeasonD);

        $judgeReg = $this->Conventionregistrations->find()
            ->contain(['Users'])
            ->where(['Conventionregistrations.slug' => $slug])
            ->first();

        if (empty($judgeReg)) {
            if ($isAjax) {
                return $this->jsonJudgeEventResponse(false, 'Judge registration not found.');
            }
            $this->Flash->error('Judge registration not found.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $currentEventIds = [];
        if (!empty($judgeReg->judges_event_ids)) {
            $currentEventIds = array_values(array_unique(array_map('intval', array_filter(array_map('trim', explode(',', (string)$judgeReg->judges_event_ids))))));
        }

        if (!in_array($eventId, $currentEventIds)) {
            if ($isAjax) {
                $payload = $this->buildJudgeEventPayload($judgeReg, $allowedEventMap, $eventResultRouteMap, $convSeasonD);
                return $this->jsonJudgeEventResponse(false, 'Selected event is not assigned to this judge.', $payload);
            }
            $this->Flash->error('Selected event is not assigned to this judge.');
            return $this->redirect(['action' => 'alljudges']);
        }

        $updatedEventIds = [];
        foreach ($currentEventIds as $currEventId) {
            if ((int)$currEventId !== (int)$eventId) {
                $updatedEventIds[] = (int)$currEventId;
            }
        }

        $this->Conventionregistrations->updateAll([
            'judges_event_ids' => implode(',', $updatedEventIds),
            'modified' => date('Y-m-d H:i:s'),
        ], ['id' => $judgeReg->id]);

        $judgeReg->judges_event_ids = implode(',', $updatedEventIds);
        if ($isAjax) {
            $payload = $this->buildJudgeEventPayload($judgeReg, $allowedEventMap, $eventResultRouteMap, $convSeasonD);
            return $this->jsonJudgeEventResponse(true, 'Event removed from judge successfully.', $payload);
        }

        $this->Flash->success('Event removed from judge successfully.');
        return $this->redirect(['action' => 'alljudges']);
    }

    private function buildJudgeEventPayload($judgeReg, $eventNameMap, $eventResultRouteMap = [], $convSeasonD = null) {
        $assignedEventIds = [];
        if (!empty($judgeReg->judges_event_ids)) {
            $assignedEventIds = array_values(array_unique(array_map('intval', array_filter(array_map('trim', explode(',', (string)$judgeReg->judges_event_ids))))));
        }

        $assignedEvents = [];
        foreach ($assignedEventIds as $eventId) {
            $assignedEvents[] = [
                'id' => (int)$eventId,
                'label' => isset($eventNameMap[$eventId]) ? (string)$eventNameMap[$eventId] : ('Event #' . $eventId),
            ];
        }

        $resultRoutes = [];
        foreach ($assignedEventIds as $eventId) {
            if (isset($eventResultRouteMap[$eventId])) {
                $resultRoutes[] = $eventResultRouteMap[$eventId];
            }
        }

        $slugConvention = '';
        $slugConventionSeason = '';
        if (!empty($convSeasonD)) {
            $slugConventionSeason = (string)$convSeasonD->slug;
            $conventionD = $this->Conventions->find()->where(['Conventions.id' => $convSeasonD->convention_id])->first();
            if (!empty($conventionD)) {
                $slugConvention = (string)$conventionD->slug;
            }
        }

        $availableEvents = [];
        foreach ($eventNameMap as $eventId => $eventLabel) {
            if (!in_array((int)$eventId, $assignedEventIds)) {
                $availableEvents[] = [
                    'id' => (int)$eventId,
                    'label' => (string)$eventLabel,
                ];
            }
        }

        return [
            'judge_id' => (int)$judgeReg->id,
            'assigned_event_ids' => $assignedEventIds,
            'assigned_events' => $assignedEvents,
            'available_events' => $availableEvents,
            'event_count' => count($assignedEventIds),
            'result_routes' => $resultRoutes,
            'slug_convention' => $slugConvention,
            'slug_convention_season' => $slugConventionSeason,
        ];
    }

    private function getSeasonEventResultRouteMap($convSeasonD) {
        $routeMap = [];
        if (empty($convSeasonD) || empty($convSeasonD->id)) {
            return $routeMap;
        }

        $seasonEvents = $this->Conventionseasonevents->find()
            ->where(['Conventionseasonevents.conventionseasons_id' => $convSeasonD->id])
            ->contain(['Events'])
            ->all();

        foreach ($seasonEvents as $seasonEvent) {
            if (empty($seasonEvent->Events)) {
                continue;
            }
            $eventId = (int)$seasonEvent->event_id;
            $routeMap[$eventId] = [
                'event_id' => $eventId,
                'event_slug' => (string)$seasonEvent->Events['slug'],
                'event_label' => (string)$seasonEvent->Events['event_name'] . ' (' . (string)$seasonEvent->Events['event_id_number'] . ')',
                'judging_type' => (string)$seasonEvent->Events['event_judging_type'],
                'judging_ends' => (int)$seasonEvent->judging_ends,
                'action_results' => $this->getResultActionByJudgingType((string)$seasonEvent->Events['event_judging_type']),
            ];
        }

        return $routeMap;
    }

    private function getResultActionByJudgingType($judgingType) {
        if ($judgingType == 'times') {
            return 'resulttimes';
        }
        if ($judgingType == 'distances') {
            return 'resultdistances';
        }
        if ($judgingType == 'scores') {
            return 'resultscores';
        }
        if ($judgingType == 'soccer_kick') {
            return 'resultsoccerkick';
        }
        if ($judgingType == 'spellings') {
            return 'resultspellings';
        }
        return 'index';
    }

    private function jsonJudgeEventResponse($success, $message, $payload = []) {
        $responseData = [
            'success' => (bool)$success,
            'message' => (string)$message,
            'data' => is_array($payload) ? $payload : [],
        ];
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($responseData));
    }

    private function getSelectedConventionSeasonFromSession() {
        $sessAdminHeaderSeasonId = (int)$this->request->getSession()->read('sess_admin_header_season_id');
        if ($sessAdminHeaderSeasonId <= 0) {
            return null;
        }

        return $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sessAdminHeaderSeasonId])->first();
    }

    private function getSeasonEventNameMap($conventionSeasonId) {
        $eventNameIDDD = array();
        $convSeasonEvents = $this->Conventionseasonevents->find()
            ->where(['Conventionseasonevents.conventionseasons_id' => $conventionSeasonId])
            ->order(['Conventionseasonevents.id' => 'ASC'])
            ->all();

        $seasonEventIds = array();
        foreach ($convSeasonEvents as $convSeasonEvent) {
            $seasonEventIds[] = (int)$convSeasonEvent->event_id;
        }
        $seasonEventIds = array_values(array_unique(array_filter($seasonEventIds)));

        if (!empty($seasonEventIds)) {
            $eventsList = $this->Events->find()
                ->where(['Events.id IN' => $seasonEventIds])
                ->order(['Events.event_id_number' => 'ASC'])
                ->all();

            foreach ($eventsList as $eventrec) {
                $eventNameIDDD[(int)$eventrec->id] = $eventrec->event_name . ' (' . $eventrec->event_id_number . ')';
            }
        }

        return $eventNameIDDD;
    }


}

?>
