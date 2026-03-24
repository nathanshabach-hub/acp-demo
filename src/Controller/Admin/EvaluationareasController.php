<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EvaluationareasController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Evaluationareas.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
		$this->loadModel('Evaluationforms');
		$this->loadModel('Evaluationcategories');
		$this->loadModel('Evaluationquestions');
    }

    public function index($form_slug=null) {

        $this->set('title', ADMIN_TITLE . 'Manage Areas');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvaluations', '1');
        $this->set('formsList', '1');

		if ($form_slug) {
            $formD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $form_slug])->first();
            $form_id = $formD->id;
			$this->set('formD', $formD);
			$this->set('form_slug', $form_slug);
			if(!$formD)
			{
				$this->Flash->error('Form not found.');
				$this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid action.');
            $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
		}

		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);

        $separator = array();
        $condition = array();
        $condition = array('Evaluationareas.evaluationform_id' => $formD->id);

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Evaluationareas->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Evaluationareas->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Evaluationareas->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Evaluationareas']['keyword']) && $requestData['Evaluationareas']['keyword'] != '') {
                $keyword = trim($requestData['Evaluationareas']['keyword']);
            }
			if (isset($requestData['Evaluationareas']['evaluationcategory_id']) && $requestData['Evaluationareas']['evaluationcategory_id'] != '') {
				$evaluationcategory_id = trim($requestData['Evaluationareas']['evaluationcategory_id']);
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
            $condition[] = "(Evaluationareas.question LIKE '%".addslashes($keyword)."%' OR Evaluationareas.id = '".addslashes($keyword)."')";
            $this->set('keyword', $keyword);
        }
		if (isset($evaluationcategory_id) && $evaluationcategory_id != '') {
            $separator[] = 'evaluationcategory_id:' . urlencode($evaluationcategory_id);
            $condition[] = "(Evaluationareas.evaluationcategory_id = '".addslashes($evaluationcategory_id)."')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Evaluationcategories'], 'conditions' => $condition, 'limit' => 100, 'order' => ['Evaluationareas.id' => 'DESC']];
        $this->set('evaluationareas', $this->paginate($this->Evaluationareas));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Evaluationareas');
            $this->render('index');
        }
    }

    public function add($form_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Add Area');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageEvaluations', '1');
        $this->set('formsList', '1');

		if ($form_slug) {
            $formD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $form_slug])->first();
            $form_id = $formD->id;
			$this->set('formD', $formD);
			$this->set('form_slug', $form_slug);
			if(!$formD)
			{
				$this->Flash->error('Form not found.');
				$this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid action.');
            $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
		}

		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);

		$questionsDD = $this->Evaluationquestions->find()->where([])->order(['Evaluationquestions.question' => 'ASC'])->combine('id', 'question')->toArray();
		$this->set('questionsDD', $questionsDD);

        $evaluationareas = $this->Evaluationareas->newEntity();
        if ($this->request->is('post')) {

            //$this->prx($this->request->getData());
            $requestData = $this->request->getData();

            $evaluationquestion_ids = isset($requestData['Evaluationareas']['evaluationquestion_ids']) ? (array)$requestData['Evaluationareas']['evaluationquestion_ids'] : [];
			$evaluationquestion_ids_implode = implode(",",$evaluationquestion_ids);

            $data = $this->Evaluationareas->patchEntity($evaluationareas, $requestData);

			// to check that this category already added for this form
			$checkFlag = 1;
			$checkCatForm = $this->Evaluationareas->find()->where(['Evaluationareas.evaluationform_id' => $form_id,'Evaluationareas.evaluationcategory_id' => $data->evaluationcategory_id])->first();
			if($checkCatForm)
			{
				$checkFlag = 0;
				$this->Flash->error('You have already added this category for this form.');

				$questionsDD = $this->Evaluationquestions->find()->where(['Evaluationquestions.evaluationcategory_id' => $data->evaluationcategory_id])->order(['Evaluationquestions.question' => 'ASC'])->combine('id', 'question')->toArray();
				$this->set('questionsDD', $questionsDD);
			}

            if (count($data->getErrors()) == 0 && $checkFlag == 1) {

				$slug = $this->getSlug('form-area-' . time(), 'Evaluationareas');
                $data->slug 			= $slug;
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;

				$data->evaluationform_id 			= $formD->id;
				$data->evaluationquestion_ids 		= $evaluationquestion_ids_implode;

                if ($this->Evaluationareas->save($data)) {
                    $this->Flash->success('Area added successfully.');
                    $this->redirect(['controller' => 'evaluationareas', 'action' => 'index',$form_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationareas', $evaluationareas);
    }

    public function edit($form_slug=null,$record_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Question');
        $this->viewBuilder()->setLayout('admin');

		$this->set('manageEvaluations', '1');
        $this->set('formsList', '1');

		if ($form_slug) {
            $formD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $form_slug])->first();
            $form_id = $formD->id;
			$this->set('formD', $formD);
			$this->set('form_slug', $form_slug);
			if(!$formD)
			{
				$this->Flash->error('Form not found.');
				$this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid action.');
            $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
		}

		if ($record_slug) {
            $areaD = $this->Evaluationareas->find()->where(['Evaluationareas.slug' => $record_slug])->first();
            $uid = $areaD->id;
        }

		$categoryDD = $this->Evaluationcategories->find()->where([])->order(['Evaluationcategories.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('categoryDD', $categoryDD);

		$questionsDD = array();
		$questionsListCat = $this->Evaluationquestions->find()->where(['Evaluationquestions.evaluationcategory_id' => $areaD->evaluationcategory_id])->order(['Evaluationquestions.question' => 'ASC'])->order(["Evaluationquestions.id" =>"ASC"]);
		foreach($questionsListCat as $catquestion)
		{
			$questionsDD[$catquestion->id] = $catquestion->question.' ('.$catquestion->max_points.')';
		}
		$this->set('questionsDD', $questionsDD);


        $evaluationareas = $this->Evaluationareas->get($uid);
        if ($this->request->is(['post', 'put'])) {

            $requestData = $this->request->getData();
            $evaluationquestion_ids = isset($requestData['Evaluationareas']['evaluationquestion_ids']) ? (array)$requestData['Evaluationareas']['evaluationquestion_ids'] : [];
			$evaluationquestion_ids_implode = implode(",",$evaluationquestion_ids);

            $data = $this->Evaluationareas->patchEntity($evaluationareas, $requestData);

			// to check that this category already added for this form
			$checkFlag = 1;
			if($data->evaluationcategory_id != $data->evaluationcategory_id_old)
			{
				$checkCatForm = $this->Evaluationareas->find()->where(['Evaluationareas.evaluationform_id' => $form_id,'Evaluationareas.evaluationcategory_id' => $data->evaluationcategory_id])->first();
				if($checkCatForm)
				{
					$checkFlag = 0;
					$this->Flash->error('You have already added this category for this form.');

					$questionsDD = $this->Evaluationquestions->find()->where(['Evaluationquestions.evaluationcategory_id' => $data->evaluationcategory_id])->order(['Evaluationquestions.question' => 'ASC'])->combine('id', 'question')->toArray();
					$this->set('questionsDD', $questionsDD);
				}
			}

            if (count($data->getErrors()) == 0 && $checkFlag == 1) {
				$data->modified = date("Y-m-d H:i:s");

				$data->evaluationquestion_ids 		= $evaluationquestion_ids_implode;

                if ($this->Evaluationareas->save($data)) {
                    $this->Flash->success('Area details updated successfully.');
                    $this->redirect(['controller' => 'evaluationareas', 'action' => 'index',$form_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationareas', $evaluationareas);
    }

	public function activatequestion($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Evaluationareas->updateAll(['status' => '1','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationareas/deactivatequestion/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivatequestion($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Evaluationareas->updateAll(['status' => '0','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationareas/activatequestion/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

	public function deletearea($form_slug = null,$record_slug = null) {//exit;

        if ($form_slug) {
            $formD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $form_slug])->first();
            $form_id = $formD->id;
			if(!$formD)
			{
				$this->Flash->error('Form not found.');
				$this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid action.');
            $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
		}

		// to chek if question exists
		if($record_slug)
		{
			// to get details of question
			$areaD = $this->Evaluationareas->find()->where(['Evaluationareas.slug' => $record_slug])->first();

			if($areaD)
			{
				$this->Evaluationareas->deleteAll(["slug" => $record_slug]);
				$this->Flash->success('Area details deleted successfully.');
			}
			else
			{
				$this->Flash->error('Area not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}

        $this->redirect(['controller' => 'evaluationareas', 'action' => 'index',$form_slug]);
    }

}

?>
