<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class EvaluationformsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Evaluationforms.name' => 'asc']];
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
		
		$this->loadModel('Events');
		$this->loadModel('Evaluationtags');
		$this->loadModel('Evaluationareas');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Forms');
        $this->viewBuilder()->layout('admin');
        $this->set('manageEvaluations', '1');
        $this->set('formsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Evaluationforms.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Evaluationforms->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Evaluationforms->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Evaluationforms->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Evaluationforms']['keyword']) && $this->request->data['Evaluationforms']['keyword'] != '') {
                $keyword = trim($this->request->data['Evaluationforms']['keyword']);
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
            $condition[] = "(Evaluationforms.name LIKE '%".addslashes($keyword)."%' OR Evaluationforms.id = '".addslashes($keyword)."' OR Evaluationforms.reference_pdf_file_name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 100, 'order' => ['Evaluationforms.id' => 'DESC']];
        $this->set('evaluationforms', $this->paginate($this->Evaluationforms));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Evaluationforms');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Form');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageEvaluations', '1');
        $this->set('formsList', '1');
		
		$tagsDD = $this->Evaluationtags->find()->where([])->order(['Evaluationtags.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('tagsDD', $tagsDD);
		
		$eventNameIDDD = array();
		$condEvents = array();
		//$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
		$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
		foreach($eventsList as $eventrec)
		{
			$eventNameIDDD[$eventrec->event_id_number] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
		}
		$this->set('eventNameIDDD', $eventNameIDDD);
		
        $evaluationforms = $this->Evaluationforms->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$tag_ids = $this->request->data['Evaluationforms']['tag_ids'];
			if(count($tag_ids))
			{
				$tag_ids_implode = implode(",",$tag_ids);
			}
			else
			{
				$tag_ids_implode = '';
			}
			
			$event_id_numbers = $this->request->data['Evaluationforms']['event_id_numbers'];
			$event_id_numbers_implode = implode(",",$event_id_numbers);
			
            $data = $this->Evaluationforms->patchEntity($evaluationforms, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Evaluationforms']['name'] . ' ' . time(), 'Evaluationforms');
                $data->name 			= trim($this->request->data['Evaluationforms']['name']);
                $data->slug 			= $slug;
				
                $data->tag_ids 			= $tag_ids_implode;
                $data->event_id_numbers = $event_id_numbers_implode;
				
                $data->status 			= 1;
                $data->created 			= date('Y-m-d H:i:s');
                $data->modified 		= NULL;
				
				// now upload pdf file
				if(!empty($this->request->data['Evaluationforms']['reference_pdf_file_name']['name']))
				{
					$target_file = UPLOAD_JUDGING_REFERENCE_PDF_PATH . basename($this->request->data['Evaluationforms']['reference_pdf_file_name']['name']);
					if (move_uploaded_file($this->request->data['Evaluationforms']['reference_pdf_file_name']["tmp_name"], $target_file))
					{
						$data->reference_pdf_file_name 		= $this->request->data['Evaluationforms']['reference_pdf_file_name']['name'];
					}
					else
					{
						echo "Sorry, there was an error uploading your file.";exit;
					}
				}
				
                if ($this->Evaluationforms->save($data)) {
                    $this->Flash->success('Form added successfully.');
                    $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationforms', $evaluationforms);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Form');
        $this->viewBuilder()->layout('admin');
        
		$this->set('manageEvaluations', '1');
        $this->set('formsList', '1');
		
		$tagsDD = $this->Evaluationtags->find()->where([])->order(['Evaluationtags.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('tagsDD', $tagsDD);
		
		$eventNameIDDD = array();
		$condEvents = array();
		//$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
		$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
		foreach($eventsList as $eventrec)
		{
			$eventNameIDDD[$eventrec->event_id_number] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
		}
		$this->set('eventNameIDDD', $eventNameIDDD);
		
        if ($slug) {
            $tagD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $slug])->first();
            $uid = $tagD->id;
        }
		
        $evaluationforms = $this->Evaluationforms->get($uid);
        if ($this->request->is(['post', 'put'])) {
            
			//$this->prx($this->request->data);
			
			$tag_ids = $this->request->data['Evaluationforms']['tag_ids'];
			$tag_ids_implode = implode(",",$tag_ids);
			
			$event_id_numbers = $this->request->data['Evaluationforms']['event_id_numbers'];
			$event_id_numbers_implode = implode(",",$event_id_numbers);
			
			$data = $this->Evaluationforms->patchEntity($evaluationforms, $this->request->data, ['validate' => 'edit']);
			
            if (count($data->errors()) == 0) {
                $data->name = trim($this->request->data['Evaluationforms']['name']);
				
				$data->tag_ids 			= $tag_ids_implode;
                $data->event_id_numbers = $event_id_numbers_implode;
				
				$data->modified = date("Y-m-d H:i:s");
				
				// now upload pdf file
				if(!empty($this->request->data['Evaluationforms']['reference_pdf_file_name']['name']))
				{
					$target_file = UPLOAD_JUDGING_REFERENCE_PDF_PATH . basename($this->request->data['Evaluationforms']['reference_pdf_file_name']['name']);
					if (move_uploaded_file($this->request->data['Evaluationforms']['reference_pdf_file_name']["tmp_name"], $target_file))
					{
						$data->reference_pdf_file_name 		= $this->request->data['Evaluationforms']['reference_pdf_file_name']['name'];
					}
					else
					{
						echo "Sorry, there was an error uploading your file.";exit;
					}
				}
				else				
				if(!empty($this->request->data['Evaluationforms']['hidd_icon']))
				{
					$data->reference_pdf_file_name =  $this->request->data['Evaluationforms']['hidd_icon'];
					unset($this->request->data['Evaluationforms']['hidd_icon']);
				}			
				else{
					$data->reference_pdf_file_name = '';
				}
				
				//$this->prx($data);
				
                if ($this->Evaluationforms->save($data)) {
                    $this->Flash->success('Form details updated successfully.');
                    $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('evaluationforms', $evaluationforms);
    }
	
	public function activateform($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationforms->updateAll(['status' => '1','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationforms/deactivateform/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateform($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->layout("");
            $this->Evaluationforms->updateAll(['status' => '0','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
            $this->set('action', '/admin/evaluationforms/activateform/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deleteform($slug = null) {//exit;
		
        // to chek if form exists
		if($slug)
		{
			// to get details of tag
			$recordD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $slug])->first();
			
			if($recordD)
			{
				// to check if any form is related with this form
				$condRecord = array();
				$condRecord[] = "(Evaluationareas.evaluationform_id = '".$recordD->id."')";
				
				$checkExists = $this->Evaluationareas->find()->where($condRecord)->first();
				if($checkExists)
				{
					$this->Flash->error('Sorry, you cannot delete this form. Evaluation area(s) are linked with this form.');
				}
				else
				{
					// remove pdf
					@unlink(UPLOAD_JUDGING_REFERENCE_PDF_PATH.$recordD->reference_pdf_file_name);
					
					$this->Evaluationareas->deleteAll(["evaluationform_id" => $recordD->id]);
					$this->Evaluationforms->deleteAll(["slug" => $slug]);
					$this->Flash->success('Form details deleted successfully.');
				}
			}
			else
			{
				$this->Flash->error('Form not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}
		
        $this->redirect(['controller' => 'evaluationforms', 'action' => 'index']);
    }
	
	public function deletepdf($slug = null) {
		
		// to get image name from slug
		if ($slug) {
            $catD = $this->Evaluationforms->find()->where(['Evaluationforms.slug' => $slug])->first();
            $catid = $catD->id;
			$reference_pdf_file_name = $catD->reference_pdf_file_name;
        }
		@unlink(UPLOAD_JUDGING_REFERENCE_PDF_PATH.$reference_pdf_file_name);
		$this->Evaluationforms->updateAll(['reference_pdf_file_name' => '','modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
		
        $this->Flash->success('PDF removed successfully.');
        $this->redirect(['controller' => 'evaluationforms', 'action' => 'edit/'.$slug]);
    }

}

?>
