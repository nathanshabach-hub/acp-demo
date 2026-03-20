<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class TransactionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Transactions.name' => 'asc']];
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
		$this->loadModel('Transactionstudents');
		$this->loadModel('Settings');
		$this->loadModel('Seasons');
		$this->loadModel('Emailtemplates');
		$this->loadModel('Transactionteachers');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Transactions');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTransactions', '1');
        $this->set('transactionsList', '1');

        $separator = array();
        $condition = array();
        
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Transactions.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);
		
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);

        if ($this->request->is('post')) {
			$requestData = $this->request->getData();
			if (isset($requestData['action'])) {
				$idList = implode(',', $requestData['chkRecordId']);
				$action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Transactions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Transactions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Transactions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

			if (isset($requestData['Transactions']['convention_id']) && $requestData['Transactions']['convention_id'] != '') {
				$convention_id = trim($requestData['Transactions']['convention_id']);
            }
			if (isset($requestData['Transactions']['season_year']) && $requestData['Transactions']['season_year'] != '') {
				$season_year = trim($requestData['Transactions']['season_year']);
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

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Transactions.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Transactions.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Users'], 'conditions' => $condition, 'limit' => 50, 'order' => ['Transactions.id' => 'DESC']];
        $this->set('transactions', $this->paginate($this->Transactions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Transactions');
            $this->render('index');
        }
    }
	
	public function viewdetails($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Transaction details');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTransactions', '1');
        $this->set('transactionsList', '1');
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
		$changePaymentS = array(
			'1' => 'Yes'
		);
		$this->set('changePaymentS', $changePaymentS);
		
        if ($slug)
		{
            $transactionD = $this->Transactions->find()->where(['Transactions.slug' => $slug])->contain(['Conventions','Users'])->first();
			$this->set('transactionD', $transactionD);
			$prevPaymentStatus = $paymentStatus[$transactionD->status];
            
			if($transactionD)
			{
				// to get the students list of this transaction
				$transactionStudents = $this->Transactionstudents->find()->where(['Transactionstudents.transaction_id' => $transactionD->id])->order(["Transactionstudents.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionStudents', $transactionStudents);
				
				// to get the teachers list of this transaction
				$transactionTeachers = $this->Transactionteachers->find()->where(['Transactionteachers.transaction_id' => $transactionD->id])->order(["Transactionteachers.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionTeachers', $transactionTeachers);
				
				// save form data
				if ($this->request->is('post'))
				{
					//$this->prx($this->request->getData());
					$requestData = $this->request->getData();
					
					if($transactionD->status == 2 || $transactionD->status == 3)
					{
						$transaction_id_received 	= $requestData['Transactions']['transaction_id_received'];
						$transaction_data 			= $requestData['Transactions']['transaction_data'];
						
						$this->Transactions->updateAll(['status' => '1','transaction_id_received' => $transaction_id_received,'transaction_data' => $transaction_data], ["slug" => $slug]);
						
						// update transactionstudents table status as well
						$this->Transactionstudents->updateAll(['status' => '1'], ["transaction_id" => $transactionD->id]);
						
						// update transactionteachers table status as well
						$this->Transactionteachers->updateAll(['status' => '1'], ["transaction_id" => $transactionD->id]);
						
						// Now send email to school admin
						$emailId = $transactionD->Users['email_address'];
							
						$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '9'])->first();

						$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!previous_payment_status!]','[!customer_code!]');
						$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->total_amount,2),$prevPaymentStatus,$transactionD->Users['customer_code']);

						$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
						$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
						
						//echo $messageToSend; exit;
						
						$this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], HEADERS_CC);
						
						$this->Flash->success('Payment transaction status confirmed successfully.');
					}
					else
					{
						$this->Flash->error('You cannot change payment transaction status.');
					}
					
					$this->redirect(['controller' => 'transactions', 'action' => 'index']);
					
				}
				
			}
			else
			{
				$this->Flash->error('Transaction not found.');
				$this->redirect(['controller' => 'transactions', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid transaction.');
			$this->redirect(['controller' => 'transactions', 'action' => 'index']);
		}
		
    }

}

?>
