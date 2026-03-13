<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class EventsubmissionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
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
		
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Judgeevaluations');
    }
	
	public function index($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Event submissions');
        $this->viewBuilder()->layout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		$separator = array();
        $condition = array();
		
		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);
			
			$this->set('slug', $slug);
			
			$condition = array('Eventsubmissions.conventionregistration_id' => $CRDetails->id);
		}
		
        //$this->prx($condition);exit;
        /* $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Students','Events','Uploadeduser'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Eventsubmissions.id' => 'DESC']];
        $this->set('eventsubmissions', $this->paginate($this->Eventsubmissions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Eventsubmissions');
            $this->render('index');
        } */
		
		$eventsubmissions 		= $this->Eventsubmissions->find()->where($condition)->contain(['Conventions','Students','Events','Uploadeduser'])->order(['Eventsubmissions.id' => 'DESC'])->all();
		
		$this->set('eventsubmissions', $eventsubmissions);
    }
	
	public function removesubmission($submission_slug = null, $conv_reg_slug = null) {
		
		$convRedG = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->first();
		if($convRedG)
		{
			// check if submission exists
			$checkExists = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $submission_slug,'Eventsubmissions.conventionregistration_id' => $convRedG->id])->first();
			
			if($checkExists)
			{	
				// to check if any judge submitted evaluation or not
				$checkJudgeEval = $this->Judgeevaluations->find()->where(['Judgeevaluations.eventsubmission_id' => $checkExists->id,'Judgeevaluations.conventionregistration_id' => $convRedG->id])->first();
				if($checkJudgeEval)
				{
					$this->Flash->error('Event submission cannot delete. Judge submitted evaluation for this submission.');
				}
				else
				{
					// to remove document as well
					if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->mediafile_file_system_name) && !empty($checkExists->mediafile_file_system_name))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->mediafile_file_system_name);
					}
					
					if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->report) && !empty($checkExists->report))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->report);
					}
					
					if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->score_sheet) && !empty($checkExists->score_sheet))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->score_sheet);
					}
					
					if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->additional_documents) && !empty($checkExists->additional_documents))
					{
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$checkExists->additional_documents);
					}
					
					$this->Flash->success('Events submission removed successfully.');
					$this->Eventsubmissions->deleteAll(["slug" => $submission_slug]);
				}
			}
			else
			{
				$this->Flash->error('Invalid document.');
			}
		}
		else
		{
			$this->Flash->error('Invalid registration.');
		}
		
		$this->redirect(['controller' => 'eventsubmissions', 'action' => 'index', $conv_reg_slug]);
    }
	
	public function guidelinebreach($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Guideline Breach');
        $this->viewBuilder()->layout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('guidelineBreachList', '1');
		
		$separator = array();
        $condition = array();
		
		$condition = array('Eventsubmissions.guideline_breach !=' => 0);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Eventsubmissions.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventionregistrations','Conventions','Users','Events','Students','Uploadeduser','Judge'], 'conditions' => $condition, 'limit' => 100000, 'order' => ['Eventsubmissions.modified' => 'DESC']];
        $this->set('eventsubmissions', $this->paginate($this->Eventsubmissions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Eventsubmissions');
            $this->render('guidelinebreach');
        }
    }
	
	public function approveguidelinebreach($slug=null) {
        
		$eventSubmissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $slug])->first();
		if($eventSubmissionD)
		{
			$this->Eventsubmissions->updateAll(['guideline_breach' => '2','modified' => date('Y-m-d H:i:s')], ["slug"=>$slug]);
			
			// check if judge evaluated, then set marks to 0
			$this->Judgeevaluations->updateAll(['total_marks_obtained' => '0','modified' => date('Y-m-d H:i:s')], ["eventsubmission_id"=>$eventSubmissionD->id]);
			
			$this->Flash->success('Guideline breach approved successfully. Marks has been set to 0.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'eventsubmissions', 'action' => 'guidelinebreach']);
    }
	
	public function rejectguidelinebreach($slug=null) {
        
		$eventSubmissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $slug])->first();
		if($eventSubmissionD)
		{
			$this->Eventsubmissions->updateAll(['guideline_breach' => '0','guideline_breach_by_judge_id' => '0','modified' => date('Y-m-d H:i:s')], ["slug"=>$slug]);
			
			$this->Flash->success('Guideline breach declined successfully.');
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'eventsubmissions', 'action' => 'guidelinebreach']);
    }
	
	public function deleteguidelinebreach($slug=null) {
        
		$eventSubmissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $slug])->first();
		if($eventSubmissionD)
		{
			$this->Eventsubmissions->updateAll(['guideline_breach' => '0','guideline_breach_by_judge_id' => '0','modified' => date('Y-m-d H:i:s')], ["slug"=>$slug]);
			
			$this->Flash->success('Guideline breach deleted successfully.');
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'eventsubmissions', 'action' => 'guidelinebreach']);
    }
	
	public function commandperformance($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Command Performance');
        $this->viewBuilder()->layout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('commandPerformanceList', '1');
		
		$separator = array();
        $condition = array();
		
		$condition = array('Eventsubmissions.command_performance' => 1);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Eventsubmissions.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventionregistrations','Conventions','Users','Events','Students','Uploadeduser','Judgecommand'], 'conditions' => $condition, 'limit' => 100000, 'order' => ['Eventsubmissions.id' => 'DESC']];
        $this->set('eventsubmissions', $this->paginate($this->Eventsubmissions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Eventsubmissions');
            $this->render('commandperformance');
        }
    }
	
	public function removecommandperformance($slug=null) {
        
		$eventSubmissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $slug])->first();
		if($eventSubmissionD)
		{
			$this->Eventsubmissions->updateAll(['command_performance' => '0','mark_command_by_judge_id' => NULL,'modified' => date('Y-m-d H:i:s')], ["slug"=>$slug]);
			
			$this->Flash->success('Command performance removed successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'eventsubmissions', 'action' => 'commandperformance']);
    }
	
	

}

?>
