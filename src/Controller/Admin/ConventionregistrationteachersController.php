<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class ConventionregistrationteachersController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Events.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
		$this->loadComponent('Flash');
		$this->loadModel("Users");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventionregistrationstudents");
    }

	public function allteachers() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Teachers');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $condition = array();

        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();

		$condition[] = "(Conventionregistrationteachers.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrationteachers.season_id = '".$convSeasonD->season_id."' AND Conventionregistrationteachers.season_year = '".$convSeasonD->season_year."')";

		$conventionregistrationteachers = $this->Conventionregistrationteachers->find()->contain(['Users','Teachers'])->where($condition)->order(["Conventionregistrationteachers.id" => "DESC"])->all();
		$this->set('conventionregistrationteachers', $conventionregistrationteachers);
    }
}

?>
