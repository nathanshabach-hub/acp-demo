<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class SeasonsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Seasons.name' => 'asc']];
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
		$this->loadModel("Conventionseasonevents");
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Seasons');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSeasons', '1');
        $this->set('seasonList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Seasons.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Seasons->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Seasons->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Seasons->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Seasons']['keyword']) && $this->request->getData()['Seasons']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Seasons']['keyword']);
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

        if (isset($keyword) && $keyword != '') {
            $separator[] = 'keyword:' . urlencode($keyword);
            $condition[] = "(Seasons.season_year LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Seasons.season_year' => 'DESC']];
        $this->set('seasons', $this->paginate($this->Seasons));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Seasons');
            $this->render('index');
        }
    }

    public function deleteseason($slug = null) {
        
		// first check that this season exists
		$seasonD = $this->Seasons->find()->where(['Seasons.slug' => $slug])->first();
		if($seasonD)
		{
			// to check if this season linked with any other data
			$season_id 	= $seasonD->id;
			$flagDelete = 1;
			
			//1. check in conventionseasons
			$checkConventionSeasons = $this->Conventionseasons->find()->where(['Conventionseasons.season_id' => $season_id])->first();
			if($checkConventionSeasons)
			{
				$flagDelete = 0;
				$this->Flash->error('Season cannot delete. Season is linked with Convention > Seasons.');
			}
			
			//2. check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.season_id' => $season_id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Season cannot delete. Season is linked with Convention > Seasons > Events.');
			}
			
			if($flagDelete == 1)
			{
				$this->Seasons->deleteAll(["slug" => $slug]);
				$this->Flash->success('Season details deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Season not found.');
		}
		
		
        $this->redirect(['controller' => 'seasons', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Season');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageSeasons', '1');
        $this->set('seasonAdd', '1');
		
        $seasons = $this->Seasons->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$flagC = 1;
			
			$seasonYear = $this->request->getData()['Seasons']['season_year'];
			
			if($seasonYear<2023 || $seasonYear>2030)
			{
				$flagC = 0;
				$this->Flash->error('Season year must be in between 2023 and 2030.');
			}
			
            $data = $this->Seasons->patchEntity($seasons, $this->request->getData(), ['validate' => 'add']);
            if (count($data->getErrors()) == 0 && $flagC == 1) {

				$slug = 'season-'.$this->request->getData()['Seasons']['season_year'];
				
                $data->slug = $slug;
                $data->status = 1;
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Seasons->save($data)) {
                    $this->Flash->success('Season added successfully.');
                    $this->redirect(['controller' => 'seasons', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('seasons', $seasons);
    }

    public function edit($slug = null) {
        $this->redirect(['controller' => 'seasons', 'action' => 'index']);
		
		$this->set('title', ADMIN_TITLE . 'Edit Season');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageSeasons', '1');
        $this->set('seasonList', '1');
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
        if ($slug) {
            $categories1 = $this->Seasons->find()->where(['Seasons.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $seasons = $this->Seasons->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Seasons->patchEntity($seasons, $this->request->getData());
			
			$flagC = 1;
			
			$seasonYear = $this->request->getData()['Seasons']['season_year'];
			
			if($seasonYear<2023 || $seasonYear>2030)
			{
				$flagC = 0;
				$this->Flash->error('Season year must be in between 2023 and 2030.');
			}
			
            if (count($data->getErrors()) == 0 && $flagC == 1) {
                $data->season_name = trim($this->request->getData()['Seasons']['season_name']);
				$data->modified = date("Y-m-d");
                if ($this->Seasons->save($data)) {
                    $this->Flash->success('Season details updated successfully.');
                    $this->redirect(['controller' => 'seasons', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('seasons', $seasons);
    }
	
	public function activateseason($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Seasons->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/seasons/deactivateseason/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateseason($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Seasons->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/seasons/activateseason/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

}

?>
