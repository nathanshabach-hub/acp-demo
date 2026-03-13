<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class DivisionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Divisions.name' => 'asc']];
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
		$this->loadModel('Eventcategories');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Divisions');
        $this->viewBuilder()->layout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageDivisions', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Divisions.parent_division_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Divisions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Divisions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Divisions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Divisions']['keyword']) && $this->request->data['Divisions']['keyword'] != '') {
                $keyword = trim($this->request->data['Divisions']['keyword']);
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
            $condition[] = "(Divisions.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'contain' => ['Eventcategories'], 'limit' => 20, 'order' => ['Divisions.eventcategory_id' => 'ASC']];
        $this->set('divisions', $this->paginate($this->Divisions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Divisions');
            $this->render('index');
        }
    }

    public function deletedivision($slug = null) {
		
        // to chek if division exists
		/* if($slug)
		{
			// to get details of division
			$divisionD = $this->Divisions->find()->where(['Divisions.slug' => $slug])->first();
			
			if($divisionD)
			{
				// to check if any event associated with this divisions
				$checkDivEvents = $this->Events->find()->where(['Events.division_id' => $divisionD->id])->first();
				if($checkDivEvents)
				{
					$this->Flash->error('Sorry, you cannot delete this division. Event(s) are linked with this division.');
				}
				else
				{
					$this->Divisions->deleteAll(["slug" => $slug]);
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
		} */
		
		$this->Flash->error('Division not allowed to delete.');
        $this->redirect(['controller' => 'divisions', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Division');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageEvents', '1');
        $this->set('manageDivisions', '1');
		
		// to get values of event categories
		$eventCatDD = $this->Eventcategories->find()->where([])->order(['Eventcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('eventCatDD', $eventCatDD);
		
        $divisions = $this->Divisions->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Divisions->patchEntity($divisions, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Divisions']['name'] . ' ' . time(), 'Divisions');
                $data->name 					= trim($this->request->data['Divisions']['name']);
                $data->slug 					= $slug;
				$data->eventcategory_id 		= trim($this->request->data['Divisions']['eventcategory_id']);
                $data->status 					= 1;
                $data->created 					= date('Y-m-d H:i:s');
                $data->modified 				= NULL;
                if ($this->Divisions->save($data)) {
                    $this->Flash->success('Division added successfully.');
                    $this->redirect(['controller' => 'divisions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('divisions', $divisions);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Division');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageEvents', '1');
        $this->set('manageDivisions', '1');
		
		// to get values of event categories
		$eventCatDD = $this->Eventcategories->find()->where([])->order(['Eventcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('eventCatDD', $eventCatDD);
		
        if ($slug) {
            $categories1 = $this->Divisions->find()->where(['Divisions.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $divisions = $this->Divisions->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Divisions->patchEntity($divisions, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name 			= trim($this->request->data['Divisions']['name']);
                $data->eventcategory_id = trim($this->request->data['Divisions']['eventcategory_id']);
				$data->modified = date("Y-m-d H:i:s");
				//$this->prx($data);
                if ($this->Divisions->save($data)) {
                    $this->Flash->success('Division details updated successfully.');
                    $this->redirect(['controller' => 'divisions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('divisions', $divisions);
    }

}

?>
