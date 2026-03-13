<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EvaluationquestionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Evaluationquestions.name' => 'asc']];
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
		
		$this->loadModel('Evaluationcategories');
		$this->loadModel('Evaluationareas');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Questions');
        $this->viewBuilder()->layout('admin');
        $this->set('manageEvaluations', '1');
        $this->set('questionsList', '1');
		
		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);

        $separator = array();
        $condition = array();
        //$condition = array('Evaluationquestions.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Evaluationquestions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Evaluationquestions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Evaluationquestions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Evaluationquestions']['keyword']) && $this->request->data['Evaluationquestions']['keyword'] != '') {
                $keyword = trim($this->request->data['Evaluationquestions']['keyword']);
            }
			if (isset($this->request->data['Evaluationquestions']['evaluationcategory_id']) && $this->request->data['Evaluationquestions']['evaluationcategory_id'] != '') {
                $evaluationcategory_id = trim($this->request->data['Evaluationquestions']['evaluationcategory_id']);
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
            $condition[] = "(Evaluationquestions.question LIKE '%".addslashes($keyword)."%' OR Evaluationquestions.id = '".addslashes($keyword)."')";
            $this->set('keyword', $keyword);
        }
		if (isset($evaluationcategory_id) && $evaluationcategory_id != '') {
            $separator[] = 'evaluationcategory_id:' . urlencode($evaluationcategory_id);
            $condition[] = "(Evaluationquestions.evaluationcategory_id = '".addslashes($evaluationcategory_id)."')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Evaluationcategories'], 'conditions' => $condition, 'limit' => 100, 'order' => ['Evaluationquestions.id' => 'DESC']];
        $this->set('evaluationquestions', $this->paginate($this->Evaluationquestions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Evaluationquestions');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Question');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageEvaluations', '1');
        $this->set('questionsList', '1');
		
		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);
		
        $evaluationquestions = $this->Evaluationquestions->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Evaluationquestions->patchEntity($evaluationquestions, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Evaluationquestions']['question'] . ' ' . time(), 'Evaluationquestions');
                $data->name 			= trim($this->request->data['Evaluationquestions']['name']);
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
                if ($this->Evaluationquestions->save($data)) {
                    $this->Flash->success('Question added successfully.');
                    //$this->redirect(['controller' => 'evaluationquestions', 'action' => 'index']);
                    $this->redirect(['controller' => 'evaluationquestions', 'action' => 'add']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationquestions', $evaluationquestions);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Question');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageEvaluations', '1');
        $this->set('questionsList', '1');
		
		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);
		
        if ($slug) {
            $questionD = $this->Evaluationquestions->find()->where(['Evaluationquestions.slug' => $slug])->first();
            $uid = $questionD->id;
        }
		
        $evaluationquestions = $this->Evaluationquestions->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Evaluationquestions->patchEntity($evaluationquestions, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name = trim($this->request->data['Evaluationquestions']['name']);
				$data->modified = date("Y-m-d H:i:s");
                if ($this->Evaluationquestions->save($data)) {
                    $this->Flash->success('Question details updated successfully.');
                    $this->redirect(['controller' => 'evaluationquestions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationquestions', $evaluationquestions);
    }
	
	public function activatequestion($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationquestions->updateAll(['status' => '1','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationquestions/deactivatequestion/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivatequestion($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationquestions->updateAll(['status' => '0','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationquestions/activatequestion/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deletequestion($slug = null) {exit;
		
        // to chek if question exists
		if($slug)
		{
			// to get details of question
			$questionD = $this->Evaluationquestions->find()->where(['Evaluationquestions.slug' => $slug])->first();
			
			if($questionD)
			{
				// to check if any evaluation area is related with this question
				$condCheck = array();
				$condCheck[] = "(Evaluationareas.evaluationquestion_ids LIKE '".$questionD->id."' OR Evaluationareas.evaluationquestion_ids LIKE '".$questionD->id.",%' OR Evaluationareas.evaluationquestion_ids LIKE '%,".$questionD->id.",%' OR Evaluationareas.evaluationquestion_ids LIKE '%,".$questionD->id."')";
				
				$checkExists = $this->Evaluationareas->find()->where($condCheck)->first();
				if($checkExists)
				{
					$this->Flash->error('Sorry, you cannot delete this question. Evaluation area(s) is related with this category.');
				}
				else
				{
					$this->Evaluationquestions->deleteAll(["slug" => $slug]);
					$this->Flash->success('Question details deleted successfully.');
				}
			}
			else
			{
				$this->Flash->error('Question not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
        $this->redirect(['controller' => 'evaluationquestions', 'action' => 'index']);
    }

}

?>
