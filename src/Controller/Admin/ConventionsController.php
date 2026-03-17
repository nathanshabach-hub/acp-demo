<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Email;

class ConventionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
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
		
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Conventionrooms');
		$this->loadModel('Conventionseasonroomevents');
		$this->loadModel('Conventionregistrationstudents');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Conventions');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);

        $separator = array();
        $condition = array();
        //$condition = array('Conventions.parent_id' => 0);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// To get convention season details
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
			
			$condition[] = "(Conventions.id = '".$conventionSD->convention_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventions']['keyword']) && $this->request->data['Conventions']['keyword'] != '') {
                $keyword = trim($this->request->data['Conventions']['keyword']);
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
            $condition[] = "(Conventions.name LIKE '%".addslashes($keyword)."%' OR Conventions.location LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Conventions.id' => 'DESC']];
        $this->set('conventions', $this->paginate($this->Conventions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventions');
            $this->render('index');
        }
    }

    public function activateconvention($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Conventions->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/conventions/deactivateconvention/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateconvention($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Conventions->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/conventions/activateconvention/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deleteconvention($slug = null) {
        
		// first check that this convention exists
		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
		if($conventionD)
		{
			// to check if this conventions linked with any other data
			$convention_id 	= $conventionD->id;
			$flagDelete = 1;
			
			//1. check in conventionseasons
			$checkConventionSeasons = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id])->first();
			if($checkConventionSeasons)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention cannot delete. Convention is linked with Convention > Seasons.');
			}
			
			//2. check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.convention_id' => $convention_id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention cannot delete. Convention is linked with Convention > Seasons > Events.');
			}
			
			if($flagDelete == 1)
			{
				$this->Conventions->deleteAll(["slug" => $slug]);
				$this->Flash->success('Convention details deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Convention not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Convention');
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionAdd', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);
		
        $conventions = $this->Conventions->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Conventions->patchEntity($conventions, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Conventions']['name'] . ' ' . time(), 'Conventions');
                $data->name = trim($this->request->data['Conventions']['name']);
                $data->slug = $slug;
                $data->status = 1;
                $data->created = date('Y-m-d');
                $data->modified = date('Y-m-d');
                if ($this->Conventions->save($data)) {
                    $this->Flash->success('Convention added successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventions', $conventions);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Convention');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
        if ($slug) {
            $categories1 = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $conventions = $this->Conventions->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventions->patchEntity($conventions, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name = trim($this->request->data['Conventions']['name']);
				$data->modified = date("Y-m-d");
                if ($this->Conventions->save($data)) {
                    $this->Flash->success('Convention details updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventions', $conventions);
    }
	
	public function seasons($slug=null) {

        if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Manage Seasons - '.$conventionD->name);
		
		// to get list of seasons
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'ASC'])->combine('id', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
        
		$separator = array();
        $condition = array();
		$condition = array('Conventionseasons.convention_id' => $conventionD->id);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// To get convention season details
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
			
			$condition[] = "(Conventionseasons.id = '".$sess_admin_header_season_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionseasons->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionseasons->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionseasons->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventionseasons']['season_id']) && $this->request->data['Conventionseasons']['season_id'] != '') {
                $season_id = trim($this->request->data['Conventionseasons']['season_id']);
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

        if (isset($season_id) && $season_id != '') {
            $separator[] = 'season_id:' . urlencode($season_id);
            $condition[] = "(Conventionseasons.season_id = '".addslashes($season_id)."')";
            $this->set('season_id', $season_id);
        }
		
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Seasons'], 'conditions' => $condition, 'limit' => 20, 'order' => ['Conventionseasons.season_year' => 'DESC']];
        $this->set('convseasons', $this->paginate($this->Conventionseasons));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventions');
            $this->render('seasons');
        }
    }
	
	public function addseason($slug=null) {
        
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Add Season - '.$conventionD->name);
		
		// to get list of seasons
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'ASC'])->combine('id', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
		
        $conventionseasons = $this->Conventionseasons->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$flagC = 1;
			
			// to check registration start date must be less than end date
			if(strtotime($this->request->data['Conventionseasons']['registration_start_date']) > strtotime($this->request->data['Conventionseasons']['registration_end_date']))
			{
				$flagC = 0;
				$this->Flash->error('Registration start date must be less than end date.');
			}
			
			// to check if season already added for this convention or not
			$checkConvSeason	= $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $conventionD->id,'Conventionseasons.season_id' => $this->request->data['Conventionseasons']['season_id']])->first();
			//$this->prx($checkConvSeason);
			if($checkConvSeason)
			{
				$flagC = 0;
				$getSeasonY = $this->Seasons->find()->where(['Seasons.id' => $data->season_id])->first();
				$this->Flash->error('Season '.$getSeasonY->season_year.' already added for this convention.');
			}
			
            $data = $this->Conventionseasons->patchEntity($conventionseasons, $this->request->data);
            if (count($data->errors()) == 0 && $flagC == 1)
			{
                // to get season details from selected season from dropdown
				$seasonD 							= $this->Seasons->find()->where(['Seasons.id' => $data->season_id])->first();
				
				$data->slug 						= 'convention-season-'.$conventionD->id.'-'.$seasonD->season_year.'-'.time().'-'.rand(10,100000);
                $data->convention_id 				= $conventionD->id;
                $data->season_year 					= $seasonD->season_year;
                $data->status 						= 1;
                $data->created 						= date('Y-m-d H:i:s');
                $data->modified 					= NULL;
				
				$data->registration_start_date 		= date("Y-m-d",strtotime($data->registration_start_date));
				$data->registration_end_date 		= date("Y-m-d",strtotime($data->registration_end_date));
				
                if ($this->Conventionseasons->save($data)) {
                    $this->Flash->success('Season succesfully added to convention.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionseasons', $conventionseasons);
    }
	
	public function deleteconventionsseason($slug_convention_season = null,$slug_convention = null) {
        
		// first check that this convention season exists
		$conventionSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		if($conventionSeasonD)
		{
			// to check if this conventions seasons linked with any other data
			$convention_id 	= $conventionSeasonD->convention_id;
			$season_id 		= $conventionSeasonD->season_id;
			$flagDelete = 1;
			
			//check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSeasonD->id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention season cannot delete. Convention season is linked with Convention > Seasons > Events.');
			}
			
			// to check if any registration received for this
			$checkConventionRegistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.season_id' => $season_id])->first();
			if($checkConventionRegistrations)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention season cannot delete. Registration exists for this convention season.');
			}
			
			if($flagDelete == 1)
			{
				$this->Conventionseasons->deleteAll(["slug" => $slug_convention_season]);
				$this->Flash->success('Convention successfully unlinked from season '.$conventionSeasonD->season_year.'.');
			}
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug_convention]);
    }
	
	public function events($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
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
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Events > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		// to get previous season name
		$prevSeasonConventionFound = 0;
		$previousSeasonD 		= $this->Seasons->find()->where(['Seasons.season_year <' => $seasonD->season_year])->first();
		if($previousSeasonD)
		{
			// to check if this convention found in previous season
			$checkConventionPY = $this->Conventionseasons->find()->where(['Conventionseasons.season_id' => $previousSeasonD->id,'Conventionseasons.convention_id' => $convention_id])->first();
			if($checkConventionPY)
			{
				$this->set('prevSeasonConventionFound', 1);
				$this->set('prevConvSeasonAutoID', $checkConventionPY->id);
			}
		}
				
		$totalEventsConventions = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->count();
		$this->set('totalEventsConventions', $totalEventsConventions);
		
		$separator = array();
        $condition = array();
        $condition = array('Conventionseasonevents.conventionseasons_id' => $conventionSD->id);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionseasonevents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionseasonevents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionseasonevents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventionseasonevents']['keyword']) && $this->request->data['Conventionseasonevents']['keyword'] != '') {
                $keyword = trim($this->request->data['Conventionseasonevents']['keyword']);
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
            $condition[] = "(Conventionseasonevents.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        /* $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Seasons','Events'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Conventionseasonevents.id' => 'ASC']];
        $this->set('conventionseasonevents', $this->paginate($this->Conventionseasonevents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventions');
            $this->render('events');
        } */
		
		//$conventionseasonevents	= $this->Conventionseasonevents->contains(['Conventions','Seasons','Events'])->where($condition)->order(['Conventionseasonevents.id' => 'ASC'])->all();
		
		$conventionseasonevents 		= $this->Conventionseasonevents->find()->where($condition)->contain(['Conventions','Seasons','Events'])->order(['Conventionseasonevents.id' => 'ASC'])->all();
		
		$this->set('conventionseasonevents', $conventionseasonevents);
    }
	
	public function judges($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->layout('admin');
        
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
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Judges > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		$separator = array();
        $condition = array();
        $condition = array('Conventionregistrations.conventionseason_id' => $conventionSD->id);
		
		$judgeslist 		= $this->Conventionregistrations->find()->where($condition)->contain(['Users'])->order(["Conventionregistrations.id" => "DESC"])->all();
		$this->set('judgeslist', $judgeslist);

    }
	
	public function importeventsfromglobal($slug_convention_season = null,$slug_convention = null) {
		
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
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		// here we need to apply a condition based on convention type
		//Event Selection section
		//If ConventionType = 0 then  Filter= All Event Type visible
		//If ConventionType = 1 then Filter= Only Event Type 1 & 2 visible
		
		$condEvents = array();
		if($conventionD->convention_type == 0)
		{
			// no condition apply
		}
		if($conventionD->convention_type == 1)
		{
			$condEvents[] = "(Events.event_type = '1' OR Events.event_type = '2')";
		}
		
		// to get entire list of all events
		$eventsAll = $this->Events->find()->where($condEvents)->order(["Events.id" => "ASC"])->all();
		
		foreach($eventsAll as $event)
		{
			$conventionseasonevents = $this->Conventionseasonevents->newEntity();
			$dataCSE = $this->Conventionseasonevents->patchEntity($conventionseasonevents, $this->request->data);

			$dataCSE->slug 						= "cse-".$convention_id."-".$season_id."-".$event->id."-".time();
			$dataCSE->conventionseasons_id 		= $conventionSD->id;
			$dataCSE->convention_id				= $convention_id;
			$dataCSE->season_id					= $season_id;
			$dataCSE->season_year				= $conventionSD->season_year;
			$dataCSE->event_id					= $event->id;
			
			$dataCSE->created 					= date('Y-m-d H:i:s');
			$dataCSE->modified 					= date('Y-m-d H:i:s');

			$resultCSE = $this->Conventionseasonevents->save($dataCSE);
		}
		
		$this->Flash->success('Event successfully import from global events list.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function reseteventlist($slug_convention_season = null,$slug_convention = null) {
		
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		
		// to check if any events associated with this convention & season
		$conventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->order(['Conventionseasonevents.id' => 'ASC'])->contain(['Conventions','Seasons','Events'])->all();
		if($conventionSeasonEvents)
		{
			$this->Conventionseasonevents->deleteAll(["conventionseasons_id" => $conventionSD->id]);
			$this->Flash->success('Events removed from this season and convention.');
		}
		else
		{
			$this->Flash->error('Sorry, no event found.');
		}
		
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function seasonresultrelease($convention_season_slug = null, $convention_slug = null) {
        
		// first check that this convention season exists
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if($convSeasonD)
		{
			// release result
			$this->Conventionseasons->updateAll(['results_release' => '1'], ["slug" => $convention_season_slug]);
			$this->Flash->success('Results released succesfully.');
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$convention_slug]);
    }
	
	public function seasonresultreleasestop($convention_season_slug = null, $convention_slug = null) {
        
		// first check that this convention season exists
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if($convSeasonD)
		{
			// release result
			$this->Conventionseasons->updateAll(['results_release' => '0'], ["slug" => $convention_season_slug]);
			$this->Flash->success('Results stopped to released succesfully.');
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$convention_slug]);
    }
	
	public function importeventsfromprevyear($slug_convention_season = null,$slug_convention = null) {
		
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		
	}
	
	public function changeprices($conv_season_slug = null,$slug = null) {
        $this->set('title', ADMIN_TITLE . 'Change Prices');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		if ($conv_season_slug) {
            $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $conv_season_slug])->first();
			$uid = $convSeasonD->id;
			$this->set('conv_season_slug', $conv_season_slug);
			$this->set('convSeasonD', $convSeasonD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
		}
		
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
		}
		
        $conventionseasons = $this->Conventionseasons->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventionseasons->patchEntity($conventionseasons, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				//$this->prx($data);
				
				$data->registration_start_date 		= date("Y-m-d",strtotime($data->registration_start_date));
				$data->registration_end_date 		= date("Y-m-d",strtotime($data->registration_end_date));
                
				$data->modified = date("Y-m-d");
                if ($this->Conventionseasons->save($data)) {
                    $this->Flash->success('Convention season prices updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionseasons', $conventionseasons);
    }
	
	/* Manage Rooms for convention */
	public function rooms($slug=null) {

        if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Manage Rooms - '.$conventionD->name);
        
		$separator = array();
        $condition = array();
		$condition = array('Conventionrooms.convention_id' => $conventionD->id);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionrooms->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionrooms->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionrooms->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Conventionrooms']['keyword']) && $this->request->data['Conventionrooms']['keyword'] != '') {
                $keyword = trim($this->request->data['Conventionrooms']['keyword']);
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
            $condition[] = "(Conventionrooms.room_name LIKE '%".addslashes($keyword)."%' OR Conventionrooms.short_description LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
		
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Conventionrooms.room_name' => 'ASC']];
        $this->set('convrooms', $this->paginate($this->Conventionrooms));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventions');
            $this->render('rooms');
        }
    }
	
	public function addroom($slug=null) {
        
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Add Room - '.$conventionD->name);
		
        $conventionrooms = $this->Conventionrooms->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$flagC = 1;
			
			// to check if same room name added in this convention
			$checkConvRoom = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' =>$conventionD->id, 'Conventionrooms.room_name' => $this->request->data['Conventionrooms']['room_name']])->first();
			if($checkConvRoom)
			{
				$flagC = 0;
				$this->Flash->error('Room name already exists for this convention. Please use some another room name.');
			}
			
            $data = $this->Conventionrooms->patchEntity($conventionrooms, $this->request->data);
            if (count($data->errors()) == 0 && $flagC == 1)
			{
                // to get season details from selected season from dropdown
				$seasonD 							= $this->Seasons->find()->where(['Seasons.id' => $data->season_id])->first();
				
				$data->slug 						= 'convention-room-'.$conventionD->id.'-'.time().'-'.rand(10,100000);
                $data->convention_id 				= $conventionD->id;
                $data->status 						= 1;
                // Handle restricted days
                if (isset($this->request->data['Conventionrooms']['restricted_days']) && is_array($this->request->data['Conventionrooms']['restricted_days'])) {
                    $data->restricted_days = implode(',', $this->request->data['Conventionrooms']['restricted_days']);
                } else {
                    $data->restricted_days = NULL;
                }

                // Handle restricted times
                if (!empty($this->request->data['Conventionrooms']['restricted_start_time'])) {
                    $time = date('H:i:s', strtotime($this->request->data['Conventionrooms']['restricted_start_time']));
                    $data->restricted_start_time = $time;
                } else {
                    $data->restricted_start_time = NULL;
                }

                if (!empty($this->request->data['Conventionrooms']['restricted_finish_time'])) {
                    $time = date('H:i:s', strtotime($this->request->data['Conventionrooms']['restricted_finish_time']));
                    $data->restricted_finish_time = $time;
                } else {
                    $data->restricted_finish_time = NULL;
                }

                $data->created 						= date('Y-m-d H:i:s');
                $data->modified 					= NULL;
				
                if ($this->Conventionrooms->save($data)) {
                    $this->Flash->success('Room succesfully added to convention.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'rooms',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionrooms', $conventionrooms);
    }
	
	public function editroom($room_slug = null,$convention_slug = null) {
        
		if ($convention_slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
			$this->set('convention_slug', $convention_slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Edit Room - '.$conventionD->name);
        
        if ($room_slug) {
            $convRoomD = $this->Conventionrooms->find()->where(['Conventionrooms.slug' => $room_slug])->first();
            $uid = $convRoomD->id;
        }
		
        $conventionrooms = $this->Conventionrooms->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventionrooms->patchEntity($conventionrooms, $this->request->data);
			
			$flagC = 1;
			
			// to check if same room name added in this convention
			$checkConvRoom = $this->Conventionrooms->find()->where(['Conventionrooms.id !=' =>$convRoomD->id, 'Conventionrooms.convention_id' =>$conventionD->id, 'Conventionrooms.room_name' => $this->request->data['Conventionrooms']['room_name']])->first();
			if($checkConvRoom)
			{
				$flagC = 0;
				$this->Flash->error('Room name already exists for this convention. Please use some another room name.');
			}
			
            if (count($data->errors()) == 0 && $flagC == 1) {
                // Handle restricted days
                if (isset($this->request->data['Conventionrooms']['restricted_days']) && is_array($this->request->data['Conventionrooms']['restricted_days'])) {
                    $data->restricted_days = implode(',', $this->request->data['Conventionrooms']['restricted_days']);
                } else {
                    $data->restricted_days = NULL;
                }

                // Handle restricted times
                if (!empty($this->request->data['Conventionrooms']['restricted_start_time'])) {
                    $time = date('H:i:s', strtotime($this->request->data['Conventionrooms']['restricted_start_time']));
                    $data->restricted_start_time = $time;
                } else {
                    $data->restricted_start_time = NULL;
                }

                if (!empty($this->request->data['Conventionrooms']['restricted_finish_time'])) {
                    $time = date('H:i:s', strtotime($this->request->data['Conventionrooms']['restricted_finish_time']));
                    $data->restricted_finish_time = $time;
                } else {
                    $data->restricted_finish_time = NULL;
                }

                $data->name = trim($this->request->data['Conventionrooms']['name']);
				$data->modified = date("Y-m-d");
                if ($this->Conventionrooms->save($data)) {
                    $this->Flash->success('Room details updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'rooms', $convention_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionrooms', $conventionrooms);
    }
	
	public function deleteroom($room_slug = null,$convention_slug = null) {
        
		$flagDel = 1;
		
		if ($convention_slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
			if(!$conventionD)
			{
				$flagDel = 0;
			}
        }
		else
		{
			$flagDel = 0;
		}
		
		if ($room_slug) {
            $roomD = $this->Conventionrooms->find()->where(['Conventionrooms.slug' => $room_slug])->first();
			if(!$roomD)
			{
				$flagDel = 0;
			}
        }
		else
		{
			$flagDel = 0;
		}
		
		//echo $flagDel;exit;
		
		if($flagDel == 1)
		{
			$this->Conventionrooms->deleteAll(["id" => $roomD->id, "convention_id" => $conventionD->id]);
			$this->Flash->success('Room details deleted successfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'rooms', $convention_slug]);
		}
		else
		{
			$this->Flash->error('Error deleting convention room.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
    }
	
	public function roomevents($slug_convention_season = null) {
        
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		
		// to get a list of peding events that are not assigned to any room
		$pendingEventsToRoomsList = array();
		$convSeasonAllEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->all();
		foreach($convSeasonAllEvents as $convsallev)
		{
			if($convsallev->Events['needs_schedule'] == 1)
			{
				// to check that each event is assigned to a room or not
				$event_id_check = $convsallev->Events['id'];
				$condCheckE = array();
				$condCheckE[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."')";
				$condCheckE[] = "(Conventionseasonroomevents.event_ids = '".$event_id_check."' OR 
								Conventionseasonroomevents.event_ids LIKE '".$event_id_check.",%' OR 
								Conventionseasonroomevents.event_ids LIKE '%,".$event_id_check.",%' OR 
								Conventionseasonroomevents.event_ids LIKE '%,".$event_id_check."')";
				$getEventRoom 			= $this->Conventionseasonroomevents->find()->where($condCheckE)->first();
				if(!$getEventRoom)
				{
					$pendingEventsToRoomsList[] = $convsallev->Events['event_name'].' ('.$convsallev->Events['event_id_number'].')';
				}
			}
		}
		$this->set('pendingEventsToRoomsList', $pendingEventsToRoomsList);
		//$this->prx($pendingEventsToRoomsList);
		
		
		
		$separator = array();
        $condition = array('Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id);

        
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Seasons','Conventionrooms'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Conventionseasonroomevents.id' => 'ASC']];
        $this->set('conventionseasonroomevents', $this->paginate($this->Conventionseasonroomevents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Conventions');
            $this->render('roomevents');
        }
    }
	
	public function addroomevents($slug_convention_season=null) {
        
		$this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Add Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		// to get list of rooms for which events already added
		$alreadyRoomArr = array();
		$alreadyRoomArr[] = 0;
		$alreadyAddedRooms = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonroomevents.convention_id' => $conventionSD->convention_id,'Conventionseasonroomevents.season_id' => $conventionSD->season_id,'Conventionseasonroomevents.season_year' => $conventionSD->season_year])->all();
		foreach($alreadyAddedRooms as $alreadyAddedRoom)
		{
			$alreadyRoomArr[] = $alreadyAddedRoom->room_id;
		}
		$alreadyRoomArrImplode = implode(",",$alreadyRoomArr);
		//$this->prx($alreadyRoomArr);
        		
		
		// to get the room allocated for this convention
		$condConvRooms = array();
		$condConvRooms[] = "(Conventionrooms.convention_id = '".$conventionSD->convention_id."' AND Conventionrooms.id NOT IN ($alreadyRoomArrImplode) )";
		$convRooms 		= $this->Conventionrooms->find()->where($condConvRooms)->order(['Conventionrooms.room_name' => 'ASC'])->combine('id', 'room_name')->toArray();
		$this->set('convRooms', $convRooms);
		
		// to get events list for this season (with registered student counts)
		$convSeasEventDD = array();
		$crstudenteventsTable = \Cake\ORM\TableRegistry::get('Crstudentevents');
		$convSeasonEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->order(['Conventionseasonevents.id' => 'ASC'])->all();
		foreach($convSeasonEvents as $convSeasonEvent)
		{
			// to check that this event required scheduling
			if($convSeasonEvent->Events['needs_schedule'] == 1)
			{
				$regCount = $crstudenteventsTable->find()->where(['Crstudentevents.conventionseason_id' => $conventionSD->id, 'Crstudentevents.event_id' => $convSeasonEvent->event_id])->count();
				$convSeasEventDD[$convSeasonEvent->event_id] = $convSeasonEvent->Events['event_name']." (".$convSeasonEvent->Events['event_id_number'].") (".$regCount." students)";
			}
		}
		$this->set('convSeasEventDD', $convSeasEventDD);
		

        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$room_id 	= $this->request->data['Conventionseasonroomevents']['room_id'];
			$event_ids 	= $this->request->data['Conventionseasonroomevents']['event_ids'];
			
			if(count($event_ids))
			{
				$event_ids_implode = implode(",",$event_ids);
				
				// Build students_per_block JSON from POST data
				$spbPost = isset($this->request->data['students_per_block']) ? (array)$this->request->data['students_per_block'] : [];
				$spbMap = [];
				foreach ($event_ids as $eid) {
					$spbMap[$eid] = isset($spbPost[$eid]) && (int)$spbPost[$eid] > 0 ? (int)$spbPost[$eid] : null;
				}
				$spbJson = json_encode($spbMap);
				
				$conventionseasonroomevents = $this->Conventionseasonroomevents->newEntity();
				$data = $this->Conventionseasonroomevents->patchEntity($conventionseasonroomevents, $this->request->data);
				
				$slug = "conv-season-room-event-".time()."-".rand(100,10000);
				$data->name = trim($this->request->data['Conventions']['name']);
				$data->slug = $slug;
				
				$data->conventionseasons_id 	= $conventionSD->id;
				$data->convention_id 			= $conventionSD->convention_id;
				$data->season_id 				= $conventionSD->season_id;
				$data->season_year 				= $conventionSD->season_year;
				$data->room_id 					= $room_id;
				$data->event_ids 				= $event_ids_implode;
				$data->students_per_block 		= $spbJson;
				$data->created 					= date('Y-m-d');
				$data->modified 				= date('Y-m-d');
				$this->Conventionseasonroomevents->save($data);
				
				$this->Flash->success('Event successfully added to convention room.');
				$this->redirect(['controller' => 'conventions', 'action' => 'roomevents',$slug_convention_season]);
			}
			else
			{
				$this->Flash->error('Please choose event.');
			}
			
			
        }
    }
	
	public function deleteroomevents($slug = null,$slug_convention_season=null) {
        
		// first check that this convention season exists
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		if($conventionSD)
		{	
			//1. check in convention room events
			$checkConventionRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug,'Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->first();
			if($checkConventionRoomEvents)
			{
				$this->Conventionseasonroomevents->deleteAll(["slug" => $slug]);
				$this->Flash->success('Convention room events deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'roomevents',$slug_convention_season]);
    }
	
	public function editroomevents($slug = null,$slug_convention_season=null) {
        
		$this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug', $slug);
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
			
			// To get convention season room details
			$conventionSRoomD 			= $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug])->contain(["Conventionrooms"])->first();
			$this->set('conventionSRoomD', $conventionSRoomD);
			
			$roomEventIDS = 0;
			$checkRoomEVIDS = array();
			if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
			{
				$roomEventIDS 	= $conventionSRoomD->event_ids;
				$checkRoomEVIDS = explode(",",$conventionSRoomD->event_ids);
			}
			// To get list of events of this Room
			$condREvents = array();
			$condREvents[] = "(Events.id IN ($roomEventIDS))";
			
			$roomEventsL = $this->Events->find()->where($condREvents)->order(["Events.event_name" => "ASC"])->all();
			$this->set('roomEventsL', $roomEventsL);
			//$this->prx($roomEventsL);
			
			
			// to get events list for this season
			$convSeasEventDD = array();
			$convSeasonEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convSeasonEvent)
			{
				// to check that this event required scheduling
				if($convSeasonEvent->Events['needs_schedule'] == 1 && !in_array($convSeasonEvent->Events['id'],$checkRoomEVIDS))
				{
					$convSeasEventDD[$convSeasonEvent->event_id] = $convSeasonEvent->Events['event_name']." (".$convSeasonEvent->Events['event_id_number'].")";
				}
			}
			$this->set('convSeasEventDD', $convSeasEventDD);
			
			
			
			
		// Load existing students_per_block JSON
		$existingSpb = [];
		if (!empty($conventionSRoomD->students_per_block)) {
			$existingSpb = (array)json_decode($conventionSRoomD->students_per_block, true);
		}
		$this->set('existingSpb', $existingSpb);
		
		if ($this->request->is('post')) {
		
			//$this->prx($this->request->data);
			
			// Handle students_per_block update for existing events
			$spbPost = isset($this->request->data['students_per_block']) ? (array)$this->request->data['students_per_block'] : [];
			$updatedSpb = $existingSpb;
			foreach ($spbPost as $eid => $val) {
				$updatedSpb[$eid] = (int)$val > 0 ? (int)$val : null;
			}
			
			$new_event_ids 	= isset($this->request->data['Conventionseasonroomevents']['event_ids']) ? (array)$this->request->data['Conventionseasonroomevents']['event_ids'] : [];
			$new_event_ids = array_filter($new_event_ids);
			
			if(count($new_event_ids))
			{
				
				// there is already events in this conventin room, so we need to merge them as well
				if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
				{
					$old_event_ids = explode(",",$conventionSRoomD->event_ids);
					
					$merged_events = array_merge($new_event_ids, $old_event_ids);
				}
				else
				{
					$merged_events = $new_event_ids;
				}
				
				$merged_events_implode = implode(",",$merged_events);
				
				// Add new events to spb map (with their posted values or null)
				foreach ($new_event_ids as $neid) {
					if (!isset($updatedSpb[$neid])) {
						$updatedSpb[$neid] = isset($spbPost[$neid]) && (int)$spbPost[$neid] > 0 ? (int)$spbPost[$neid] : null;
					}
				}
				
				// To update room Events
				$this->Conventionseasonroomevents->updateAll(
				[
					'event_ids' => $merged_events_implode,
					'students_per_block' => json_encode($updatedSpb),
					'modified' => date("Y-m-d H:i:s"),
				], 
				[
					'id' => $conventionSRoomD->id
				]);
				
				$this->Flash->success('Event successfully added to convention room.');
			}
			else
			{
				// No new events, but still save students_per_block updates
				$this->Conventionseasonroomevents->updateAll(
				[
					'students_per_block' => json_encode($updatedSpb),
					'modified' => date("Y-m-d H:i:s"),
				], 
				[
					'id' => $conventionSRoomD->id
				]);
				
				$this->Flash->success('Students per block updated successfully.');
			}
			
			$this->redirect(['controller' => 'conventions', 'action' => 'editroomevents',$slug,$slug_convention_season]);
			
			
		}
			
			
			
			
			
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Edit Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		
    }
	
	public function deleteeventfromroom($slug = null,$slug_convention_season=null, $event_id=NULL) {
		
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
			
			// To get convention season room details
			$conventionSRoomD 			= $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug])->contain(["Conventionrooms"])->first();
			
			if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
			{
				$roomEventIDSArr = explode(",",$conventionSRoomD->event_ids);
				
				// Check if event exists
				if (in_array($event_id, $roomEventIDSArr)) {
					// Find the key of the value
					$key = array_search($event_id, $roomEventIDSArr);

					// Remove the value
					unset($roomEventIDSArr[$key]);
					
					if(count($roomEventIDSArr)>0)
					{
						$roomEventIDS = implode(",",$roomEventIDSArr);
					}
					else
					{
						$roomEventIDS = NULL;
					}
					
					// Also remove from students_per_block JSON
					$spbMap = [];
					if (!empty($conventionSRoomD->students_per_block)) {
						$spbMap = (array)json_decode($conventionSRoomD->students_per_block, true);
					}
					unset($spbMap[$event_id]);
					$spbJson = !empty($spbMap) ? json_encode($spbMap) : null;
					
					// To update room Events
					$this->Conventionseasonroomevents->updateAll(
					[
						'event_ids' => $roomEventIDS,
						'students_per_block' => $spbJson,
						'modified' => date("Y-m-d H:i:s"),
					], 
					[
						'id' => $conventionSRoomD->id
					]);
					
					$this->Flash->success('Event successfully removed from convention room.');
				}
			}
			
		}	
        else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->redirect(['controller' => 'conventions', 'action' => 'editroomevents',$slug,$slug_convention_season]);
		
	}
	
	
	// to show list of schools for scripture award
	public function scriptureawardslist($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		$data = array();
		
        if ($slug_convention_season)
		{
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
		
		$this->set('title', ADMIN_TITLE . 'Scripture Award > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		// to get list of events in which certificate print is allowed
		$arrEventCP = array();
		$eventCP = $this->Events->find()->where(['Events.certificate_print' => 1])->all();
		foreach($eventCP as $evcp)
		{
			$arrEventCP[] = $evcp->id;
		}
		$this->set('arrEventCP', $arrEventCP);
		
		
		$finalSchoolsList 		= array();
		$finalSchoolsEventsList = array();
		
		
		// to get all schools registered for this convention season
		$conventionRegList 		= $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->all();
		foreach($conventionRegList as $convreg)
		{
			// to check if any student of this school having any of the event for scripture award
			$convRegStudents = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.conventionregistration_id' => $convreg->id])->all();
			foreach($convRegStudents as $concregstudent)
			{
				// now check events of student and match with scripture award events // add event to school array
				if(isset($concregstudent->event_ids) && !empty($concregstudent->event_ids))
				{
					$studentEventExplode = explode(",",$concregstudent->event_ids);
					foreach($studentEventExplode as $steventid)
					{
						if(in_array($steventid,(array)$arrEventCP))
						{
							if(!in_array($convreg->user_id,(array)$finalSchoolsList))
							{
								// add school to list
								$finalSchoolsList[] = $convreg->user_id;
							}
							
							if(!in_array($steventid,(array)$finalSchoolsEventsList[$convreg->user_id]))
							{
								// add school to list
								$finalSchoolsEventsList[$convreg->user_id][] = $steventid;
							}
						}
					}
				}
				
				
				
			}
			
			//$this->prx($convRegStudents);
		}
		
		//$this->pr($finalSchoolsList);
		//$this->prx($finalSchoolsEventsList);
		
		$this->set('finalSchoolsList', $finalSchoolsList);
		$this->set('finalSchoolsEventsList', $finalSchoolsEventsList);
		
    }
	
	
	/* To show list of events of a judge from a convention registration */
	public function judgesevents($conv_reg_slug = null) {
        
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('conv_reg_slug', $conv_reg_slug);
		
		$data = array();
		
        if ($conv_reg_slug) {
            $conventionRegD 			= $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions','Conventionseasons','Users'])->first();
            $season_id 				= $conventionRegD->season_id;
			$this->set('conventionRegD', $conventionRegD);
        }
		if (!$conventionRegD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Judges events > '.$conventionRegD->Conventions['name'].' > Season '.$conventionRegD->season_year);
		
		if($conventionRegD->judges_event_ids)
		{
			$judges_event_ids_explode = explode(",",$conventionRegD->judges_event_ids);
		}
        
		$this->set('judges_event_ids', $judges_event_ids_explode);

    }
	
	public function sendremindertojudge($conv_reg_slug = null, $event_slug = null)
	{ 	
        if ($conv_reg_slug)
		{
            $conventionRegD	= $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions','Conventionseasons','Users'])->first();
			
			$eventD	= $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			
			if($conventionRegD && $eventD)
			{
				//to remind them when/which events still need to be judged
				$emailId = $conventionRegD->Users['email_address'];
									
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '26'])->first();

				$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]','[!event_name!]');
				$fromRepArray = array($conventionRegD->Users['first_name'],$conventionRegD->Conventions['name'],$conventionRegD->season_year,$eventD->event_name);

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
					
				$this->Flash->success('Reminder notification sent successfully to judge..');
				
			}
        }
		
		$this->redirect(['controller'=>'conventions', 'action' => 'judgesevents',$conv_reg_slug]);

    }
	
	
	public function qualifyingdata($slug_convention_season = null,$slug_convention=null,$event_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Edit Convention');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('slug_convention_season', $slug_convention_season);
        $this->set('slug_convention', $slug_convention);
		
        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $this->set('conventionSD', $conventionSD);
        }
		
		if ($event_slug) {
            $eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
        }
		
		// Now get conv season event Record
		$convSeasEventD = $this->Conventionseasonevents->find()
				->where([
				'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
				'Conventionseasonevents.event_id' => $eventD->id
				])->first();
		$this->set('convSeasEventD', $convSeasEventD);
		
		
		if ($this->request->is(['post']))
		{
			//$this->prx($eventD);
			
			if($eventD->event_judging_type == 'times')
			{
				$qualifying_time_score = $this->request->data['qualifying_time_score'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_time_score' 		=> $qualifying_time_score
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying time saved successfully.";
			}
			
			if($eventD->event_judging_type == 'distances')
			{
				$qualifying_distance = $this->request->data['qualifying_distance'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_distance' 		=> $qualifying_distance
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying distance saved successfully.";
			}
			
			if($eventD->event_judging_type == 'scores')
			{
				$qualifying_score = $this->request->data['qualifying_score'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_score' 		=> $qualifying_score
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying score saved successfully.";
			}
			
			$this->Flash->success($msgS);
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
        
    }
	
	public function brokenrecordcertificate($slug_convention_season=null,$slug_convention=null) {
        
		if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('slug_convention_season', $slug_convention_season);
			$this->set('conventionSD', $conventionSD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('slug_convention', $slug_convention);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Broken Record Certificate - '.$conventionD->name.' - '.$conventionSD->season_year);
		
		// To get list of all events of this convention season
		$eventCS = array();
		$convSEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($convSEvents as $convsev)
		{
			$eventCS[] = $convsev->event_id;
		}
		
		
		$eventCSImplode = implode(",",$eventCS);
		
		// Now fetch Events
		$eventNI = array();
		$condEvents = array();
		$condEvents[] = "(Events.id IN ($eventCSImplode) )";
		$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_name' => 'ASC'])->all();
		foreach($eventsList as $eventrec)
		{
			$eventNI[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
		}
		$this->set('eventNI', $eventNI);
		//$this->prx($eventNI);
    }
	
	public function brokenrecordcertificatepdf($slug_convention_season=null,$slug_convention=null)
	{
		$this->viewbuilder()->layout('');
		
		if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('slug_convention_season', $slug_convention_season);
			$this->set('conventionSD', $conventionSD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('slug_convention', $slug_convention);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		
		//$this->prx($this->request->data);
		
		$event_id 			= $this->request->data['Conventionseasons']['event_id'];
		$student_name 		= $this->request->data['Conventionseasons']['student_name'];
		$school_name 		= $this->request->data['Conventionseasons']['school_name'];
		
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		
		
		$arrCertData = array();
		
		$arrCertData['convention_name'] 	= $conventionD->name;
		$arrCertData['seadon_year'] 		= $conventionSD->season_year;
		$arrCertData['student_name'] 		= $student_name;
		$arrCertData['school_name'] 		= $school_name;
		$arrCertData['event_name'] 			= $eventD->event_name;
		
		//$this->prx($arrCertData);
		
		
		$this->set('arrCertData', $arrCertData); 
	
	}
	
	

}

?>
