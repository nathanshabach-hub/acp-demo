<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EventcategoriesController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Eventcategories.name' => 'asc']];
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
		
		$this->loadModel('Conventions');
		$this->loadModel('Events');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Eventcategories');
        $this->viewBuilder()->layout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageEventCategories', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Eventcategories.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Eventcategories->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Eventcategories->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Eventcategories->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Eventcategories']['keyword']) && $this->request->data['Eventcategories']['keyword'] != '') {
                $keyword = trim($this->request->data['Eventcategories']['keyword']);
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
            $condition[] = "(Eventcategories.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Eventcategories.name' => 'ASC']];
        $this->set('eventcategories', $this->paginate($this->Eventcategories));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Eventcategories');
            $this->render('index');
        }
    }

    public function deletedivision($slug = null) {
		
        // to chek if division exists
		if($slug)
		{
			// to get details of division
			$divisionD = $this->Eventcategories->find()->where(['Eventcategories.slug' => $slug])->first();
			
			if($divisionD)
			{
				// to check if any event associated with this eventcategories
				$checkDivEvents = $this->Events->find()->where(['Events.division_id' => $divisionD->id])->first();
				if($checkDivEvents)
				{
					$this->Flash->error('Sorry, you cannot delete this division. Event(s) are linked with this division.');
				}
				else
				{
					$this->Eventcategories->deleteAll(["slug" => $slug]);
					$this->Flash->success('Division details deleted successfully.');
				}
			}
			else
			{
				$this->Flash->error('Division not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
        $this->redirect(['controller' => 'eventcategories', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Division');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageEvents', '1');
        $this->set('manageEventcategories', '1');
		
        $eventcategories = $this->Eventcategories->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Eventcategories->patchEntity($eventcategories, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Eventcategories']['name'] . ' ' . time(), 'Eventcategories');
                $data->name 			= trim($this->request->data['Eventcategories']['name']);
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
                if ($this->Eventcategories->save($data)) {
                    $this->Flash->success('Division added successfully.');
                    $this->redirect(['controller' => 'eventcategories', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('eventcategories', $eventcategories);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Division');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageEvents', '1');
        $this->set('manageEventcategories', '1');
		
        if ($slug) {
            $categories1 = $this->Eventcategories->find()->where(['Eventcategories.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $eventcategories = $this->Eventcategories->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Eventcategories->patchEntity($eventcategories, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name = trim($this->request->data['Eventcategories']['name']);
				$data->modified = date("Y-m-d H:i:s");
                if ($this->Eventcategories->save($data)) {
                    $this->Flash->success('Division details updated successfully.');
                    $this->redirect(['controller' => 'eventcategories', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('eventcategories', $eventcategories);
    }

}

?>
