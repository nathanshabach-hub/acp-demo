<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EventsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Events.name' => 'asc']];
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
		
		$this->loadModel('Divisions');
		$this->loadModel('Books');
		$this->loadModel("Conventionseasonevents");
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Global Events');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvents', '1');
        $this->set('eventList', '1');
		
		$divisionDD = $this->Divisions->find()->where([])->order(['Divisions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('divisionDD', $divisionDD);

        $separator = array();
        $condition = array();
        //$condition = array('Events.parent_id' => 0);

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Events->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Events->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Events->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Events']['keyword']) && $requestData['Events']['keyword'] != '') {
                $keyword = trim($requestData['Events']['keyword']);
            }
			
			if (isset($requestData['Events']['division_id']) && $requestData['Events']['division_id'] != '') {
				$division_id = trim($requestData['Events']['division_id']);
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
            $condition[] = "(Events.event_name LIKE '%".addslashes($keyword)."%' OR Events.event_id_number LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
		if (isset($division_id) && $division_id != '') {
            $separator[] = 'division_id:' . urlencode($division_id);
            $condition[] = "(Events.division_id = '".addslashes($division_id)."')";
            $this->set('division_id', $division_id);
        }
		
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Divisions'], 'conditions' => $condition, 'limit' => 50, 'order' => ['Events.event_id_number' => 'ASC']];
        $this->set('events', $this->paginate($this->Events));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Events');
            $this->render('index');
        }
    }

    public function activateevent($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Events->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/events/deactivatev/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateevent($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Events->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/events/activateevent/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deleteevent($slug = null) {
        
		// first check that this event exists
		$eventD = $this->Events->find()->where(['Events.slug' => $slug])->first();
		if($eventD)
		{
			// to check if this event linked with any other data
			$event_id 	= $eventD->id;
			$flagDelete = 1;
			
			//check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.event_id' => $event_id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Event cannot delete. Event is linked with Convention > Seasons > Events.');
			}
			
			if($flagDelete == 1)
			{
				$this->Events->deleteAll(["slug" => $slug]);
				$this->Flash->success('Event details deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Event not found.');
		}
		
		
        $this->redirect(['controller' => 'events', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Event');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageEvents', '1');
        $this->set('eventAdd', '1');
		
		$divisionDD = $this->Divisions->find()->where([])->order(['Divisions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('divisionDD', $divisionDD);
		
		$bookDD = $this->Books->find()->where([])->order(['Books.book_name' => 'ASC'])->combine('id', 'book_name')->toArray();
		$this->set('bookDD', $bookDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $eventUploadTypeDD;
		$this->set('eventUploadTypeDD', $eventUploadTypeDD);
		
		global $eventGroupNameDD;
		$this->set('eventGroupNameDD', $eventGroupNameDD);
		
		global $eventGenderDD;
		$this->set('eventGenderDD', $eventGenderDD);
		
		global $eventKindID;
		$this->set('eventKindID', $eventKindID);
		
		global $eventJudgeType;
		$this->set('eventJudgeType', $eventJudgeType);
		
        $events = $this->Events->newEntity();
        if ($this->request->is('post')) {
			
            //$this->prx($this->request->getData());
            $requestData = $this->request->getData();
			
            $data = $this->Events->patchEntity($events, $requestData, ['validate' => 'add']);
			
            $book_ids = $requestData['Events']['book_ids'];
			
			if(isset($book_ids) && count($book_ids))
			{
				$data->book_ids = implode(",",$book_ids);
			}
			else
			{
				$data->book_ids = '';
			}
			
            if (count($data->getErrors()) == 0) {

				$slug = $this->getSlug($requestData['Events']['event_name'] . ' ' . time(), 'Events');
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
                if ($this->Events->save($data)) {
                    $this->Flash->success('Event added successfully.');
                    $this->redirect(['controller' => 'events', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('events', $events);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Event');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageEvents', '1');
        $this->set('eventList', '1');
		
		$divisionDD = $this->Divisions->find()->where([])->order(['Divisions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('divisionDD', $divisionDD);
		
		$bookDD = $this->Books->find()->where([])->order(['Books.book_name' => 'ASC'])->combine('id', 'book_name')->toArray();
		$this->set('bookDD', $bookDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $eventUploadTypeDD;
		$this->set('eventUploadTypeDD', $eventUploadTypeDD);
		
		global $eventGroupNameDD;
		$this->set('eventGroupNameDD', $eventGroupNameDD);
		
		global $eventGenderDD;
		$this->set('eventGenderDD', $eventGenderDD);
		
		global $eventKindID;
		$this->set('eventKindID', $eventKindID);
		
		global $eventJudgeType;
		$this->set('eventJudgeType', $eventJudgeType);
		
        if ($slug) {
            $categories1 = $this->Events->find()->where(['Events.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $events = $this->Events->get($uid);
        if ($this->request->is(['post', 'put'])) {
			$requestData = $this->request->getData();
            $data = $this->Events->patchEntity($events, $requestData);
			
			$book_ids = $requestData['Events']['book_ids'];
			
			
			
			if(isset($book_ids) && count((array)$book_ids))
			{
				$data->book_ids = implode(",",(array)$book_ids);
			}
			else
			{
				$data->book_ids = '';
			}
			
            if (count($data->getErrors()) == 0) {
				
				$data->modified = date("Y-m-d H:i:s");
                if ($this->Events->save($data)) {
                    $this->Flash->success('Event details updated successfully.');
                    $this->redirect(['controller' => 'events', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('events', $events);
    }

}

?>
