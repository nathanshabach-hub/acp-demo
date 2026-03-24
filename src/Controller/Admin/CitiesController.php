<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class CitiesController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Cities.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage City');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageCities', '1');
        $this->set('locationList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Cities.parent_id' => 0);

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Cities->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Cities->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Cities->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Cities']['keyword']) && $requestData['Cities']['keyword'] != '') {
                $keyword = trim($requestData['Cities']['keyword']);
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
            $condition[] = "(Cities.name LIKE '%" . addslashes($keyword) . "%'  )";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Cities.id' => 'DESC']];
        $this->set('cities', $this->paginate($this->Cities));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Cities');
            $this->render('index');
        }
    }

    public function activateamenity($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Cities->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/cities/deactivateamenity/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateamenity($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Cities->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/cities/activateamenity/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deleteamenity($slug = null) {

		// to get details of category
		$catDetails = $this->Cities->find()->where(['Cities.slug' => $slug])->first();

		$this->Cities->deleteAll(["slug" => $slug]);
        $this->Flash->success('City details deleted successfully.');
        $this->redirect(['controller' => 'cities', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add City');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageCities', '1');
        $this->set('locationAdd', '1');

        $cities = $this->Cities->newEntity();

        if ($this->request->is('post')) {

			//$this->prx($this->request->getData());
			$requestData = $this->request->getData();

            $data = $this->Cities->patchEntity($cities, $requestData, ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

				$slug = $this->getSlug($requestData['Cities']['name'] . ' ' . time(), 'Cities');
                $data->name = trim($requestData['Cities']['name']);
                $data->slug = $slug;
                $data->status = 1;
                $data->created = date('Y-m-d');
                $data->modified = date('Y-m-d');
                if ($this->Cities->save($data)) {
                    $this->Flash->success('City added successfully.');
                    $this->redirect(['controller' => 'cities', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('cities', $cities);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit City');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageCities', '1');
        $this->set('locationList', '1');

        if ($slug) {
            $categories1 = $this->Cities->find()->where(['Cities.slug' => $slug])->first();
            $uid = $categories1->id;
        }

        $cities = $this->Cities->get($uid);
        if ($this->request->is(['post', 'put'])) {
			$requestData = $this->request->getData();
            $data = $this->Cities->patchEntity($cities, $requestData, ['validate' => 'edit']);

            if (count($data->getErrors()) == 0) {
                $data->name = trim($requestData['Cities']['name']);
				$data->modified = date("Y-m-d");
                if ($this->Cities->save($data)) {
                    $this->Flash->success('City details updated successfully.');
                    $this->redirect(['controller' => 'cities', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('cities', $cities);
    }

}

?>
