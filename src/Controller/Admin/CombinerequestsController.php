<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class CombinerequestsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Combinerequests.name' => 'asc']];
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
		
		$this->loadModel('Conventions');
		$this->loadModel('Events');
		$this->loadModel('Emailtemplates');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Combinerequests');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageCombinedRequests', '1');
        $this->set('combinedRequestsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Combinerequests.parent_id' => 0);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Combinerequests.conventionseason_id = '".$sess_admin_header_season_id."')";
		}

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Combinerequests->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Combinerequests->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Combinerequests->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Combinerequests']['keyword']) && $requestData['Combinerequests']['keyword'] != '') {
                $keyword = trim($requestData['Combinerequests']['keyword']);
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
            $condition[] = "(Combinerequests.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Users','Combineduser','Events'], 'conditions' => $condition, 'limit' => 20, 'order' => ['Combinerequests.name' => 'ASC']];
        $this->set('combinerequests', $this->paginate($this->Combinerequests));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Combinerequests');
            $this->render('index');
        }
    }
	
	public function approverequest($slug=null) {
        
		$requestD = $this->Combinerequests->find()->where(['Combinerequests.slug' => $slug,'Combinerequests.status' => 2])->contain(['Conventions','Users','Combineduser','Events'])->first();
		if($requestD)
		{
			$this->Combinerequests->updateAll(['status' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to user who added this request
			$emailId = $requestD->Users['email_address'];
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '23'])->first();

			$toRepArray = array('[!school_name!]','[!combine_with_school_name!]','[!event_name!]','[!event_id_number!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($requestD->Users['first_name'],$requestD->Combineduser['first_name'],$requestD->Events['event_name'],$requestD->Events['event_id_number'],$requestD->Conventions['name'],$requestD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Request approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'combinerequests', 'action' => 'index']);
    }
	
	public function declinerequest($slug=null) {
        
		$requestD = $this->Combinerequests->find()->where(['Combinerequests.slug' => $slug,'Combinerequests.status' => 2])->contain(['Conventions','Users','Combineduser','Events'])->first();
		if($requestD)
		{
			$this->Combinerequests->updateAll(['status' => '0','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to user who added this request
			$emailId = $requestD->Users['email_address'];
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '24'])->first();

			$toRepArray = array('[!school_name!]','[!combine_with_school_name!]','[!event_name!]','[!event_id_number!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($requestD->Users['first_name'],$requestD->Combineduser['first_name'],$requestD->Events['event_name'],$requestD->Events['event_id_number'],$requestD->Conventions['name'],$requestD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Request declined successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'combinerequests', 'action' => 'index']);
    }

}

?>
