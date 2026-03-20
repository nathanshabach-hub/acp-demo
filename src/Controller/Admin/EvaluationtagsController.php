<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EvaluationtagsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Evaluationtags.name' => 'asc']];
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
		
		$this->loadModel('Evaluationforms');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Tags');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvaluations', '1');
        $this->set('tagsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Evaluationtags.parent_id' => 0);

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Evaluationtags->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Evaluationtags->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Evaluationtags->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Evaluationtags']['keyword']) && $requestData['Evaluationtags']['keyword'] != '') {
                $keyword = trim($requestData['Evaluationtags']['keyword']);
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
            $condition[] = "(Evaluationtags.name LIKE '%".addslashes($keyword)."%' OR Evaluationtags.id = '".addslashes($keyword)."')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 100, 'order' => ['Evaluationtags.id' => 'DESC']];
        $this->set('evaluationtags', $this->paginate($this->Evaluationtags));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Evaluationtags');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Tag');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageEvaluations', '1');
        $this->set('tagsList', '1');
		
        $evaluationtags = $this->Evaluationtags->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			$requestData = $this->request->getData();
			
            $data = $this->Evaluationtags->patchEntity($evaluationtags, $requestData, ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

				$slug = $this->getSlug($requestData['Evaluationtags']['name'] . ' ' . time(), 'Evaluationtags');
                $data->name 			= trim($requestData['Evaluationtags']['name']);
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
                if ($this->Evaluationtags->save($data)) {
                    $this->Flash->success('Tag added successfully.');
                    $this->redirect(['controller' => 'evaluationtags', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationtags', $evaluationtags);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Tag');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageEvaluations', '1');
        $this->set('tagsList', '1');
		
        if ($slug) {
            $tagD = $this->Evaluationtags->find()->where(['Evaluationtags.slug' => $slug])->first();
            $uid = $tagD->id;
        }
		
        $evaluationtags = $this->Evaluationtags->get($uid);
        if ($this->request->is(['post', 'put'])) {
			$requestData = $this->request->getData();
            $data = $this->Evaluationtags->patchEntity($evaluationtags, $requestData, ['validate' => 'edit']);
			
            if (count($data->getErrors()) == 0) {
                $data->name = trim($requestData['Evaluationtags']['name']);
				$data->modified = date("Y-m-d H:i:s");
                if ($this->Evaluationtags->save($data)) {
                    $this->Flash->success('Tag details updated successfully.');
                    $this->redirect(['controller' => 'evaluationtags', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationtags', $evaluationtags);
    }
	
	public function activatetag($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Evaluationtags->updateAll(['status' => '1','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationtags/deactivatetag/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivatetag($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Evaluationtags->updateAll(['status' => '0','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationtags/activatetag/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deletetag($slug = null) {exit;
		
        // to chek if tag exists
		if($slug)
		{
			// to get details of tag
			$tagD = $this->Evaluationtags->find()->where(['Evaluationtags.slug' => $slug])->first();
			
			if($tagD)
			{
				// to check if any form is related with this tag
				$condTag = array();
				$condTag[] = "(Evaluationforms.tag_ids LIKE '".$tagD->id."' OR Evaluationforms.tag_ids LIKE '".$tagD->id.",%' OR Evaluationforms.tag_ids LIKE '%,".$tagD->id.",%' OR Evaluationforms.tag_ids LIKE '%,".$tagD->id."')";
				
				$checkTagForm = $this->Evaluationforms->find()->where($condTag)->first();
				if($checkTagForm)
				{
					$this->Flash->error('Sorry, you cannot delete this tag. Form(s) are linked with this tag.');
				}
				else
				{
					$this->Evaluationtags->deleteAll(["slug" => $slug]);
					$this->Flash->success('Tag details deleted successfully.');
				}
			}
			else
			{
				$this->Flash->error('Tag not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
        $this->redirect(['controller' => 'evaluationtags', 'action' => 'index']);
    }

}

?>
