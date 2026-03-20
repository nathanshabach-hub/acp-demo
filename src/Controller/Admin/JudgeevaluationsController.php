<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class JudgeevaluationsController extends AppController {

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
		
		$this->loadModel('Conventions');
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Eventsubmissions');
		$this->loadModel('Judgeevaluationmarks');
    }
	
	public function index() {

        $this->set('title', ADMIN_TITLE . 'Judge Evaluations');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);
		
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
		
		$eventsDD = array();
		$eventsList = $this->Events->find()->where([])->order(['Events.event_name' => 'DESC'])->all();
		foreach($eventsList as $eventl)
		{
			$eventsDD[$eventl->id] = $eventl->event_name. ' ('.$eventl->event_id_number.')';
		}
		$this->set('eventsDD', $eventsDD);
		
		$separator = array();
        $condition = array();
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Judgeevaluations.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
		if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Judgeevaluations->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Judgeevaluations->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Judgeevaluations->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Judgeevaluations']['convention_id']) && $requestData['Judgeevaluations']['convention_id'] != '') {
                $convention_id = trim($requestData['Judgeevaluations']['convention_id']);
            }
			if (isset($requestData['Judgeevaluations']['season_year']) && $requestData['Judgeevaluations']['season_year'] != '') {
				$season_year = trim($requestData['Judgeevaluations']['season_year']);
            }
			if (isset($requestData['Judgeevaluations']['event_id']) && $requestData['Judgeevaluations']['event_id'] != '') {
				$event_id = trim($requestData['Judgeevaluations']['event_id']);
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

        /* if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Judgeevaluations.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Judgeevaluations.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        } */
		
		if (isset($event_id) && $event_id != '') {
            $separator[] = 'event_id:' . urlencode($event_id);
            $condition[] = "(Judgeevaluations.event_id = '".addslashes($event_id)."')";
            $this->set('event_id', $event_id);
        }
		
        /* //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Judgeevaluations.id' => 'DESC']];
        $this->set('judgeevaluations', $this->paginate($this->Judgeevaluations));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Judgeevaluations');
            $this->render('index');
        } */
		
		$judgeevaluations 		= $this->Judgeevaluations->find()->where($condition)->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'])->order(['Judgeevaluations.id' => 'DESC'])->limit(1000000)->all();
		
		$this->set('judgeevaluations', $judgeevaluations);
    }
	
	public function removejudgeevaluation($evaluation_slug=null) {
		
		$judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->first();
		if($judgeEvalD)
		{
			$this->Judgeevaluations->deleteAll(["slug" => $evaluation_slug]);
			
			// remove evaluation questions marks
			$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $judgeEvalD->id]);
			$this->Flash->success('Judge evaluation removed successfully.');
		}
		else
		{
			$this->Flash->error('Judge evaluation not found.');
		}
		
		$this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
    }
	
	public function timesscoreedit($evaluation_slug = null) {
		
		$this->set('title', ADMIN_TITLE . 'Edit Times Score');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
        if ($evaluation_slug) {
            $judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->contain(['Events','Schools','Students'])->first();
			$this->set('judgeEvalD', $judgeEvalD);
            $uid = $judgeEvalD->id;
        }
		
        $judgeevaluations = $this->Judgeevaluations->get($uid);
        if ($this->request->is(['post', 'put']))
		{
            //$this->prx($this->request->getData());
            $requestData = $this->request->getData();
			
			$this->Judgeevaluations->updateAll(
			[
                'time_score' 		=> $requestData['Judgeevaluations']['time_score'],
                'place' 			=> $requestData['Judgeevaluations']['place'],
                'withdraw_yes_no' 	=> $requestData['Judgeevaluations']['withdraw_yes_no'],
				'modified'			=> date("Y-m-d H:i:s")
			], 
			['slug' => $evaluation_slug]
			);
			
            $this->Flash->success('Times score updated successfully.');
            $this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
        }
        $this->set('judgeevaluations', $judgeevaluations);
    }

}

?>
