<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EvaluationcategoriesController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Evaluationcategories.name' => 'asc']];
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
		
		$this->loadModel('Evaluationquestions');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Categories');
        $this->viewBuilder()->layout('admin');
        $this->set('manageEvaluations', '1');
        $this->set('evalcategoriesList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Evaluationcategories.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Evaluationcategories->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Evaluationcategories->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Evaluationcategories->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Evaluationcategories']['keyword']) && $this->request->data['Evaluationcategories']['keyword'] != '') {
                $keyword = trim($this->request->data['Evaluationcategories']['keyword']);
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
            $condition[] = "(Evaluationcategories.name LIKE '%".addslashes($keyword)."%' OR Evaluationcategories.id = '".addslashes($keyword)."')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 100, 'order' => ['Evaluationcategories.id' => 'DESC']];
        $this->set('evaluationcategories', $this->paginate($this->Evaluationcategories));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Evaluationcategories');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Category');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageEvaluations', '1');
        $this->set('evalcategoriesList', '1');
		
        $evaluationcategories = $this->Evaluationcategories->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Evaluationcategories->patchEntity($evaluationcategories, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Evaluationcategories']['name'] . ' ' . time(), 'Evaluationcategories');
                $data->name 			= trim($this->request->data['Evaluationcategories']['name']);
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
                if ($this->Evaluationcategories->save($data)) {
                    $this->Flash->success('Category added successfully.');
                    $this->redirect(['controller' => 'evaluationcategories', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationcategories', $evaluationcategories);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Category');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageEvaluations', '1');
        $this->set('evalcategoriesList', '1');
		
        if ($slug) {
            $tagD = $this->Evaluationcategories->find()->where(['Evaluationcategories.slug' => $slug])->first();
            $uid = $tagD->id;
        }
		
        $evaluationcategories = $this->Evaluationcategories->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Evaluationcategories->patchEntity($evaluationcategories, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name = trim($this->request->data['Evaluationcategories']['name']);
				$data->modified = date("Y-m-d H:i:s");
                if ($this->Evaluationcategories->save($data)) {
                    $this->Flash->success('Tag details updated successfully.');
                    $this->redirect(['controller' => 'evaluationcategories', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationcategories', $evaluationcategories);
    }
	
	public function activatecategory($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationcategories->updateAll(['status' => '1','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationcategories/deactivatecategory/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivatecategory($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationcategories->updateAll(['status' => '0','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationcategories/activatecategory/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deletecategory($slug = null) {exit;
		
        // to chek if category exists
		if($slug)
		{
			// to get details of tag
			$categoryD = $this->Evaluationcategories->find()->where(['Evaluationcategories.slug' => $slug])->first();
			
			if($categoryD)
			{
				// to check if any question is related with this category
				$condChk = array();
				$condChk[] = "(Evaluationquestions.evaluationcategory_id = '".$categoryD->id."')";
				
				$checkExists = $this->Evaluationquestions->find()->where($condChk)->first();
				if($checkExists)
				{
					$this->Flash->error('Sorry, you cannot delete this category. Question(s) is related with this category.');
				}
				else
				{
					$this->Evaluationcategories->deleteAll(["slug" => $slug]);
					$this->Flash->success('Category details deleted successfully.');
				}
			}
			else
			{
				$this->Flash->error('Category not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
        $this->redirect(['controller' => 'evaluationcategories', 'action' => 'index']);
    }

}

?>
