<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class ConventionseasoneventsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Events.name' => 'asc']];
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
		
		$this->loadModel('Divisions');
    }
	
	public function allevents() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Events');
        $this->viewBuilder()->layout('admin');
        $this->set('dashboard', '1');
		
        $condition = array();
		
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		
		$this->set('convSeasonD', $convSeasonD);
		
		$condition[] = "(Conventionseasonevents.conventionseasons_id = '".$convSeasonD->id."')";
		
		$conventionseasonevents = $this->Conventionseasonevents->find()->contain(['Events'])->where($condition)->order(["Conventionseasonevents.id" => "DESC"])->all();
		$this->set('conventionseasonevents', $conventionseasonevents);
    }
}

?>
