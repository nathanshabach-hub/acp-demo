<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class HearteventsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
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
		
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Conventionrooms');
		$this->loadModel('Conventionseasonroomevents');
		$this->loadModel('Conventionregistrationstudents');
    }
	
	public function listheartevents($slug_convention_season = null,$slug_convention = null) {
        
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
		
		$this->set('title', 'Events of the heart students > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		
		$heartevents 		= $this->Heartevents->find()->where(['Heartevents.convention_id' => $conventionSD->convention_id,'Heartevents.season_id' => $conventionSD->season_id,'Heartevents.season_year' => $conventionSD->season_year])->contain(['Conventions','Students','Users'])->all();
		$this->set('heartevents', $heartevents);
		//$this->prx($heartevents);
        
    }
	
	public function hearteventcertificatepdf($slug_convention_season = null,$event_heart_slug = null) {
		
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
		
		if ($event_heart_slug) {
            $eventHeartD 			= $this->Heartevents->find()->where(['Heartevents.slug' => $event_heart_slug])->contain(['Conventions','Students','Users'])->first();
			$this->set('eventHeartD', $eventHeartD);
        }
		if (!$eventHeartD)
		{
			$this->Flash->error('Event of the heart not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		
		///////////////
		$this->viewBuilder()->setLayout('');
		
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] 	= $eventHeartD->Students['first_name'].' '.$eventHeartD->Students['last_name'];
		$arrCertData['school_name'] 	= $eventHeartD->Users['first_name'];
		
		$this->set('arrCertData', $arrCertData);
		
	}

    
	
	

}

?>
