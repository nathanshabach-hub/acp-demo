<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;

class PastorsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Pastors.id' => 'desc']];

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
    }

    public function index() {
        $this->set('title', ADMIN_TITLE . 'Manage Pastors');
        $this->viewBuilder()->layout('admin');
        $this->set('managePastors', '1');
        $this->set('pastorsList', '1');

        $separator = array();
        $condition = array();

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Pastors->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Pastors->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Pastors->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Pastors']['search_name']) && $this->request->data['Pastors']['search_name'] != '') {
                $search_name = trim($this->request->data['Pastors']['search_name']);
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

        if (isset($search_name) && $search_name != '') {
            $separator[] = 'search_name:' . urlencode($search_name);
            $condition[] = "(Pastors.first_name LIKE '%".addslashes($search_name)."%' OR Pastors.last_name LIKE '%".addslashes($search_name)."%')";
            $this->set('search_name', $search_name);
        }

        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Pastors.id' => 'DESC']];
        $this->set('pastors', $this->paginate($this->Pastors));
        
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Pastors');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Pastor');
        $this->viewBuilder()->layout('admin');
        $this->set('managePastors', '1');
        $this->set('addPastor', '1');

        $pastor = $this->Pastors->newEntity();
        
        if ($this->request->is('post')) {
            $pastor = $this->Pastors->patchEntity($pastor, $this->request->getData());
            
            if ($this->Pastors->save($pastor)) {
                $this->Flash->success('Pastor has been added successfully.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Pastor could not be saved. Please, try again.');
        }
        
        $this->set(compact('pastor'));
    }

    public function edit($id = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Pastor');
        $this->viewBuilder()->layout('admin');
        $this->set('managePastors', '1');
        $this->set('editPastor', '1');

        $pastor = $this->Pastors->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $pastor = $this->Pastors->patchEntity($pastor, $this->request->getData());
            
            if ($this->Pastors->save($pastor)) {
                $this->Flash->success('Pastor has been updated successfully.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Pastor could not be saved. Please, try again.');
        }
        
        $this->set(compact('pastor'));
    }

    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        $pastor = $this->Pastors->get($id);
        
        if ($this->Pastors->delete($pastor)) {
            $this->Flash->success('Pastor has been deleted successfully.');
        } else {
            $this->Flash->error('Pastor could not be deleted. Please, try again.');
        }
        
        return $this->redirect(['action' => 'index']);
    }
}