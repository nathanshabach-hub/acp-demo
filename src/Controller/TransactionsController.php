<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;

class TransactionsController extends AppController {

    public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Transactions");
		$this->loadModel("Transactionstudents");
		$this->loadModel("Settings");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Transactionteachers");
    }
	
	public function paymentsummary() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Payment Summary" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_cr_students','active');

		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		// to get admin details
		$adminInfo = $this->getAdminInfo();
		
		// to get price structure
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		// to get all the events having discount applicable
		$discountEvents = $this->Events->find()->where(['Events.discount_allowed' => 1])->all();
		$arrEventsDiscount = array();
		foreach($discountEvents as $dicsEv)
		{
			$arrEventsDiscount[] = $dicsEv->id;
		}
		
		// to get % of discount applicable
		$settingsDiscount = $this->Settings->find()->where(['Settings.id' => 1])->first();
		
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get price of supervisor registration
			$ConvSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $CRDetails->conventionseason_id])->first();
			
			// to check price structure
			if(!($CRDetails->price_per_student>0))
			{
				$this->Flash->error('Please choose price structure before payment.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'pricestructure']);
			}
			
			$this->set('CRDetails', $CRDetails);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// now check total students registered in this convention registration
		$totalStudentsReg = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('totalStudentsReg', $totalStudentsReg);
		
		// now calculate for how many students already paid for
		$alreadyPaidStudents = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('alreadyPaidStudents', $alreadyPaidStudents);
		
		// now calculate
		$pendingPaymentStudents = $totalStudentsReg-$alreadyPaidStudents;
		$this->set('pendingPaymentStudents', $pendingPaymentStudents);
		
		// price per student
		$pricePerStudent = $CRDetails->price_per_student;
		$this->set('pricePerStudent', $pricePerStudent);
		
		$subTotalPaymentStudents = ($pendingPaymentStudents*$pricePerStudent);
		$this->set('subTotalPaymentStudents', ($pendingPaymentStudents*$pricePerStudent));
		
		
		/* Now Calculate Discount applicable for which students - Starts */
		$studentApplicableForDiscount = array();
		
		// 1. To get list of all students
		$allStudentsRegD = $this->Conventionregistrationstudents->find()->where(["Conventionregistrationstudents.conventionregistration_id" => $sess_selected_convention_registration_id])->order(["id" => "ASC"])->all();
		//$this->prx($allStudentsRegD);
		foreach($allStudentsRegD as $convregdisc)
		{
			// to check if this student is having an event that is applicable for discount or not
			if(!empty($convregdisc->event_ids) && $convregdisc->event_ids != NULL)
			{
				$thisStudentEventsExplode = explode(",",$convregdisc->event_ids);
				
				// check each event of this student, if its allowed for discount
				foreach($thisStudentEventsExplode as $st_event_id)
				{
					if(in_array($st_event_id,(array)$arrEventsDiscount))
					{
						// push student id in array, if not exists
						if(!in_array($convregdisc->student_id,(array)$studentApplicableForDiscount))
						{
							// to check if this student already get discount benefit or not
							$checkStDiscount = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $CRDetails->id, 'Transactionstudents.conventionregistrationstudent_id' => $convregdisc->id, 'Transactionstudents.student_id' => $convregdisc->student_id])->first();
							
							if(!$checkStDiscount)
							{
								$studentApplicableForDiscount[] = $convregdisc->student_id;
							}
						}
					}
				}
			}
		}
		//$this->prx($settingsDiscount);
		
		$totalDiscountAmount = 0;
		if(count($studentApplicableForDiscount))
		{
			$totalStudentsApplicableDiscount = count((array)$studentApplicableForDiscount);
			$totalDiscountAmount = (($totalStudentsApplicableDiscount*$pricePerStudent*$settingsDiscount->scripture_trophy_discount)/100);
		}
		
		$this->set('totalStudentsApplicableDiscount', $totalStudentsApplicableDiscount);
		$this->set('perStudentDiscountAmount', $settingsDiscount->scripture_trophy_discount);
		$this->set('totalDiscountAmount', $totalDiscountAmount);
		
		
		
		
		$netPayableAmountStudent = ($subTotalPaymentStudents)-($totalDiscountAmount);
		$this->set('netPayableAmountStudent', $netPayableAmountStudent);
		
		/* End to calculate Discount */
		
		
		
		
		
		
		
		
		
		
		
		
		//now check for total supervisors registered for this convention registration
		$totalTeachersReg = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('totalTeachersReg', $totalTeachersReg);
		
		// now calculate for how many teachers already paid for
		$alreadyPaidTeachers = $this->Transactionteachers->find()->where(['Transactionteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('alreadyPaidTeachers', $alreadyPaidTeachers);
		
		// now calculate
		$pendingPaymentTeachers = $totalTeachersReg-$alreadyPaidTeachers;
		$this->set('pendingPaymentTeachers', $pendingPaymentTeachers);
		
		// price per teacher
		$pricePerTeacher = $ConvSeasonD->supervisor_registration_fees;
		$this->set('pricePerTeacher', $pricePerTeacher);
		
		
		// calculate payable amount
		$payableAmount = ($netPayableAmountStudent)+($pendingPaymentTeachers*$pricePerTeacher);
		$this->set('payableAmount', $payableAmount);
		
		
		
		
		
		
		
		
		
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->data);
			
			$payType = $this->request->data['hidd_pay_type'];
			
			if($payType == "online")
			{
				$transactionStatus = 2;
			}
			else
			if($payType == "invoice")
			{
				$transactionStatus = 3;
			}
			
			// Step 1:: Add 1 record into transactions
			$transactions = $this->Transactions->newEntity();
			$dataT = $this->Transactions->patchEntity($transactions, array());

			$dataT->slug 										= "transaction-cr-".$sess_selected_convention_registration_id.'-'.time();
			$dataT->conventionregistration_id					= $sess_selected_convention_registration_id;
			$dataT->conventionseason_id							= $CRDetails->conventionseason_id;
			$dataT->convention_id								= $CRDetails->convention_id;
			$dataT->user_id										= $CRDetails->user_id;
			$dataT->season_id 									= $CRDetails->season_id;
			$dataT->season_year 								= $CRDetails->season_year;
			
			$dataT->price_structure 							= $CRDetails->price_structure;
			$dataT->price_per_student 							= $pricePerStudent;
			$dataT->price_per_teacher 							= $pricePerTeacher;
			$dataT->payable_amount 								= $payableAmount;
			$dataT->tax_percent 								= $adminInfo->tax_percent;
			$dataT->tax_amount 									= ($payableAmount*$adminInfo->tax_percent)/100;
			$dataT->total_amount 								= $payableAmount+(($payableAmount*$adminInfo->tax_percent)/100);
			
			// discount related
			$dataT->total_students_applicable_for_discount 		= $totalStudentsApplicableDiscount;
			$dataT->discount_per_student 						= $settingsDiscount->scripture_trophy_discount;
			$dataT->total_discount_applied 						= $totalDiscountAmount;
			$dataT->final_amount_paid 							= $payableAmount;
			
			$dataT->status 										= $transactionStatus;
			$dataT->created 									= date('Y-m-d H:i:s');
			
			$resultT 											= $this->Transactions->save($dataT);
			
			$transaction_id 									= $resultT->id;
			$transaction_slug 									= $resultT->slug;
			
			
			
			// Step 2:: Add multiple records into transactionstudents
			// to get list of total students registered
			$condAllStudReg = array();
			$condAllStudReg[] = "(Conventionregistrationstudents.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			$allStudentsReg = $this->Conventionregistrationstudents->find()->where($condAllStudReg)->all();
			
			$cntrPendingStudents = 0;
			foreach($allStudentsReg as $allst)
			{
				// to check if amount paid for this student or not
				$checkStudentF = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $sess_selected_convention_registration_id,'Transactionstudents.student_id' => $allst->student_id])->first();
				if(!$checkStudentF)
				{
					$applicableForDiscount 		= 0;
					$percentDiscountApplied 	= 0;
					$amountDiscountApplied 		= 0;
					$finalPaidAmount 			= $pricePerStudent;
					
					// to check if this student is applicable for discount or not
					if(in_array($allst->student_id,(array)$studentApplicableForDiscount))
					{
						$applicableForDiscount 		= 1;
						$percentDiscountApplied 	= $settingsDiscount->scripture_trophy_discount;
						$amountDiscountApplied 		= (($pricePerStudent*$settingsDiscount->scripture_trophy_discount)/100);
						$finalPaidAmount 			= $pricePerStudent-$amountDiscountApplied;
					}
					
					// add new record to transactionstudents table
					$transactionstudents = $this->Transactionstudents->newEntity();
					$dataTS = $this->Transactionstudents->patchEntity($transactionstudents, $this->request->data);
					
					$dataTS->transaction_id							= $transaction_id;
					$dataTS->conventionregistration_id				= $sess_selected_convention_registration_id;
					$dataTS->conventionregistrationstudent_id		= $allst->id;
					$dataTS->student_id								= $allst->student_id;
					$dataTS->user_id								= $CRDetails->user_id;
					$dataTS->season_id 								= $CRDetails->season_id;
					$dataTS->season_year 							= $CRDetails->season_year;
					
					$dataTS->paid_amount 							= $pricePerStudent;
					
					$dataTS->applicable_for_discount 				= $applicableForDiscount;
					$dataTS->percent_discount_applied 				= $percentDiscountApplied;
					$dataTS->amount_discount_applied 				= $amountDiscountApplied;
					$dataTS->final_paid_amount 						= $finalPaidAmount;
					
					$dataTS->status 								= $transactionStatus;
					$dataTS->created 								= date('Y-m-d H:i:s');

					$resultTS = $this->Transactionstudents->save($dataTS);
					
					$cntrPendingStudents++;
				}
			}
			
			// Step 3:: Add multiple records into transactionteachers
			// to get list of total teachers registered
			$condAllTeachersReg = array();
			$condAllTeachersReg[] = "(Conventionregistrationteachers.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			$allTeachersReg = $this->Conventionregistrationteachers->find()->where($condAllTeachersReg)->all();
			
			$cntrPendingTeachers = 0;
			foreach($allTeachersReg as $alltea)
			{
				// to check if amount paid for this teacher or not
				$checkTeacherF = $this->Transactionteachers->find()->where(['Transactionteachers.conventionregistration_id' => $sess_selected_convention_registration_id,'Transactionteachers.teacher_id' => $alltea->teacher_id])->first();
				if(!$checkTeacherF)
				{
					// add new record to Transactionteachers table
					$transactionteachers = $this->Transactionteachers->newEntity();
					$dataTT = $this->Transactionteachers->patchEntity($transactionteachers, $this->request->data);
					
					$dataTT->transaction_id							= $transaction_id;
					$dataTT->conventionregistration_id				= $sess_selected_convention_registration_id;
					$dataTT->conventionregistrationteacher_id		= $alltea->id;
					$dataTT->teacher_id								= $alltea->teacher_id;
					$dataTT->user_id								= $CRDetails->user_id;
					$dataTT->season_id 								= $CRDetails->season_id;
					$dataTT->season_year 							= $CRDetails->season_year;
					
					$dataTT->paid_amount 							= $pricePerTeacher;
					$dataTT->status 								= $transactionStatus;
					$dataTT->created 								= date('Y-m-d H:i:s');

					$resultTT = $this->Transactionteachers->save($dataTT);
					
					$cntrPendingTeachers++;
				}
			}
			
			
			
			// to get all students who already
			if($cntrPendingStudents>0 || $cntrPendingTeachers>0)
			{
				// to check payment type
				if($payType == "online")
				{
					$this->redirect(['controller' => 'transactions', 'action' => 'paymentprocess',$transaction_slug]);
				}
				else
				if($payType == "invoice")
				{
					$this->redirect(['controller' => 'transactions', 'action' => 'invoiceprocess',$transaction_slug]);
				}
				
				$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
			}
			else
			{
				$this->Flash->error('Invalid payment amount.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
			}
			
		}
		
    } // end function
	
	public function paymentprocess($transaction_slug = null) {		
		
		$this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		// to get admin details
		$settingsInfo = $this->getSettingsInfo();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $transactionInfo    =   $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug, 'Transactions.conventionregistration_id' => $sess_selected_convention_registration_id])->contain(['Conventions','Seasons','Users'])->first();
		
		//$this->prx($bookingInfo);
		
        if(empty($transactionInfo))
		{
            $this->Flash->error('Invalid transaction information.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
        }
		else
		{
            // to get details for convention registration
			
			$totalAmount 	= $transactionInfo->final_amount_paid;
            $transactionId 	= $transactionInfo->id;
			
			/* As we discussed on 12-May-2023, when a user is approaching for online payment, 
			need to send an email to accounts and events team */
			$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
				
			$emailId = $settingsD->accounts_team_email;
						
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '10'])->first();

			$toRepArray = array('[!school_name!]','[!customer_code!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]');
			$fromRepArray = array($transactionInfo->Users['first_name'],$transactionInfo->Users['customer_code'],$transactionInfo->Conventions['name'],$transactionInfo->season_year,CURR,number_format($transactionInfo->final_amount_paid,2));

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
			$email = new Email();
			$email->template('default', 'admintemplate')
				->emailFormat('html')
				->to($emailId)
				->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
				->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
				->subject($subjectToSend)
				->viewVars(['content_for_layout' => $messageToSend])
				->send();
			
			
			
			
			// to count number of students for which this amount paid for
			$total_students_paid_for = $this->Transactionstudents->find()->where(['Transactionstudents.transaction_id' => $transactionId])->count();
			
			if(PAYPAL_MODE == "Sandbox")
				$paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			else
				$paypalURL = 'https://www.paypal.com/cgi-bin/webscr';
			
			//echo $paypalURL;exit;
			
			$itemName 	= SITE_TITLE." Payment for ".$total_students_paid_for." student(s) for convention [".$transactionInfo->Conventions['name']."] and season [".$transactionInfo->Seasons['season_year']."] by school [".$userDetails->first_name."] Customer Code: [".$userDetails->customer_code."]";
			$itemNumber = $transactionInfo->slug;
            
            ?>
            <form action="<?php echo $paypalURL; ?>" method="post" target="_top" id="paymentform">
                <input type='hidden' name='business' value='<?php echo $settingsInfo->paypal_email ?>'>
                <input type='hidden' name='item_name' value='<?php echo $itemName;?>'>
                <input type='hidden' name='item_number' value='<?php echo $itemNumber;?>'>
                <input type='hidden' name='amount' value='<?php echo $totalAmount;?>'>
                <input type='hidden' name='no_shipping' value='1'>
                <input type='hidden' name='currency_code' value='AUD'>
                <input type='hidden' name='notify_url' value='<?php echo HTTP_PATH;?>/transactions/inpnotify/<?php echo $transaction_slug;?>' >
                <input type='hidden' name='cancel_return' value='<?php echo HTTP_PATH;?>/transactions/cancelbooking/<?php echo $transaction_slug;?>'>
                <input type='hidden' name='return' value='<?php echo HTTP_PATH;?>/transactions/paymentsuccess/<?php echo $transaction_slug;?>'>
                <input type="hidden" name="rm" value="2">
                <input type="hidden" name="image_url" value="https://convention.accelerateministries.com.au/acp/img/front/main-logo-120px.png">
                <input type="hidden" name="display" value="1">
                <input type="hidden" name="cmd" value="_xclick">
                <img style="position: fixed;  top: 50%; left: 50%;" src="<?php echo HTTP_PATH;?>/img/loader_large_blue.gif">
            </form>

            <script>
                document.getElementById("paymentform").submit();
            </script>

            <?php
			
			//echo $paypalURL;exit;
        }
       exit;		
	} // end function
	
	public function paymentsuccess($transaction_slug=null){
		
		//echo $transaction_slug;
		//$this->prx($_REQUEST);
		
		if($transaction_slug)
		{
			$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->contain(["Conventions","Users","Seasons"])->first();
			if($transactionD)
			{
				$paymentStatusReceived = $_REQUEST['payment_status'];
				$item_number = $_REQUEST['item_number'];
				
				// compare transaction slug and item number that we sent
				if($transaction_slug == $item_number)
				{
					// update in data
					$this->Transactions->updateAll(['transaction_data' => json_decode($_REQUEST)],["slug" => $transaction_slug]);
					$this->Flash->success("Your payment confirmed successfully. You will receive confirmation email shortly.");
				}
				else
				{
					$this->Flash->error("Transaction mismatch.");
				}
			}
			else
			{
				$this->Flash->error("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->error("Invalid information received");
		}
		
		/* For Testing - starts */
		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - paymentsuccess - To test live payment '.time();
		$messageToSend = 'Request data = '.json_encode($_REQUEST);
		
		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();
		/* For Testing - ends */

		$this->redirect(['controller'=>'users', 'action' => 'dashboard']);
	}
	
	public function transactioninfo() {
		
		$this->Crstudentevents->updateAll(['conventionseason_id' => 0],
			["id > " => 0]);
			
		exit;
		
	}
	
	public function inpnotify(){
		
		// Set this to 0 once you go live or don't require logging.
        define("DEBUG", 1);
        // Set to 0 once you're ready to go live
        define("USE_SANDBOX", 1);
        define("LOG_FILE", "ipn.log");
        // Read POST data
        // reading posted data directly from $_POST causes serialization
        // issues with array data in POST. Reading raw POST data from input stream instead.
        $raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		
		
		
		/* For Testing - starts */
		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - inpnotify - 1 - To test live payment '.time();
		$messageToSend = 'raw_post_array = '.json_encode($raw_post_array);
		$messageToSend .= '<br><br>Post array = '.json_encode($_POST);
		$messageToSend .= '<br><br>Request array = '.json_encode($_REQUEST);
		
		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();
		/* For Testing - ends */

        
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
	        $keyval = explode ('=', $keyval);
	        if (count($keyval) == 2)
		        $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
	        $value = urlencode($value);
	        $req .= "&$key=$value";
        }
        // Post IPN data back to PayPal to validate the IPN data is genuine
        // Without this step anyone can fake IPN data
        if(USE_SANDBOX == true) {
	        $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
	        $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
        }
        $ch = curl_init($paypal_url);
        if ($ch == FALSE) {
	        return FALSE;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        if(DEBUG == true) {
	        curl_setopt($ch, CURLOPT_HEADER, 1);
	        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }
        // CONFIG: Optional proxy configuration
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below. Ensure the file is readable by the webserver.
        // This is mandatory for some environments.
        //$cert = __DIR__ . "./cacert.pem";
        //curl_setopt($ch, CURLOPT_CAINFO, $cert);
        $res = curl_exec($ch);
        if (curl_errno($ch) != 0) // cURL error
	        {
	        if(DEBUG == true) {	
		        error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
	        }
	        curl_close($ch);
	        exit;
        } else {
		        // Log the entire HTTP response if debug is switched on.
		        if(DEBUG == true) {
			        error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
			        error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
		        }
		        curl_close($ch);
        }
        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));
		
		/* For Testing - starts */
		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - inpnotify - 2 - To test live payment '.time();
		$messageToSend = 'res = '.json_encode($res);
		$messageToSend .= '<br><br>ch = '.json_encode($ch);
		$messageToSend .= '<br><br>tokens = '.json_encode($tokens);
		
		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();
		/* For Testing - ends */
		
		
        if (strcmp ($res, "VERIFIED") == 0) {
	        $payment_status = $_POST['payment_status'];
	        $txn_id = $_POST['txn_id'];
            $transaction_slug = $_POST['item_number'];
	        // check whether the payment_status is Completed
	        $isPaymentCompleted = false;
	        if($payment_status == "Completed")
			{
		        /* For Testing - starts */
				$emailId = 'voizacinc@gmail.com';
				$subjectToSend = 'Function - inpnotify - 3 - Inside strcmp VERIFIED - To test live payment '.time();
				$messageToSend = 'payment_status = '.json_encode($payment_status);
				$messageToSend = 'res = '.json_encode($res);
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(HEADERS_CC)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
				/* For Testing - ends */
				
				
				$isPaymentCompleted = true;

				$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->contain(['Conventions','Users'])->first();
				$this->Transactions->updateAll(['status' => '1', 'modified' => date("Y-m-d H:i:s"), 'transaction_id_received' => "$txn_id", 'transaction_data' => "$raw_post_data"], ["id" => $transactionD->id]);
				
				$this->Transactionstudents->updateAll(['status' => '1', 'modified' => date("Y-m-d H:i:s")], ["transaction_id" => $transactionD->id]);
				$this->Transactionteachers->updateAll(['status' => '1', 'modified' => date("Y-m-d H:i:s")], ["transaction_id" => $transactionD->id]);
				
				/* EMAIL CODE STARTED */
				
				/* 1. Send payment confirmation email to school admin */
				$emailId = $transactionD->Users['email_address'];
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '5'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->total_amount,2),$transactionD->Users['customer_code']);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(HEADERS_CC)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
					
				
				/* 2. Send payment confirmation email to accounts team */
				$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
				
				$emailId = $settingsD->accounts_team_email;
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '6'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->total_amount,2),$transactionD->Users['customer_code']);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
				
				/* EMAIL CODE ends */
			
			}
            
        } else if (strcmp ($res, "INVALID") == 0) {
	        // log for manual investigation
	        // Add business logic here which deals with invalid IPN messages
	        if(DEBUG == true) {
		        error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
	        }
        }
        exit;
    }
	
	public function cancelbooking($transaction_slug=null){
		
		if($transaction_slug)
		{
			$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->first();
			
			if($transactionD)
			{
				$this->Transactions->deleteAll(["slug" => $transaction_slug]);
				$this->Transactionstudents->deleteAll(["transaction_id" => $transactionD->id]);
				$this->Transactionteachers->deleteAll(["transaction_id" => $transactionD->id]);
				$this->Flash->error("Your transaction has been cancelled.");
			}
			else
			{
				$this->Flash->success("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->success("Invalid transaction information.");
		}
		
		$this->redirect(['controller'=>'conventionregistrations', 'action' => 'students']);
	}
	
	public function invoiceprocess($transaction_slug=null){
		
		if($transaction_slug)
		{
			$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->contain(["Conventions","Users","Seasons"])->first();
			if($transactionD)
			{
				//$this->prx($transactionD);
				
				/* EMAIL CODE STARTED */
				
				/* 1. Send invoice request received confirmation email to school admin */
				$emailId = $transactionD->Users['email_address'];
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '7'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->final_amount_paid,2),$transactionD->Users['customer_code']);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(HEADERS_CC)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
					
				
				/* 2. Send invoice request received email to accounts team */
				$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
				
				$emailId = $settingsD->accounts_team_email;
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '8'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->final_amount_paid,2),$transactionD->Users['customer_code']);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
				
				/* EMAIL CODE ends */
				
				$this->Flash->success("We have successfully received your invoice request. We will review and send you an email with invoice.");
			}
			else
			{
				$this->Flash->error("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->error("Invalid information received");
		}

		$this->redirect(['controller'=>'users', 'action' => 'dashboard']);
	}
	
	public function mytransactions() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Transactions List" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_transactions','active');
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		$condition[] = "(Transactions.user_id = '".$user_id."')";

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
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

            if (isset($this->request->data['Transactions']['keyword']) && $this->request->data['Transactions']['keyword'] != '') {
                $keyword = trim($this->request->data['Transactions']['keyword']);
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
            $condition[] = "(Transactions.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Users'], 'conditions' => $condition, 'limit' => 50, 'order' => ['Transactions.id' => 'DESC']];
        $this->set('transactions', $this->paginate($this->Transactions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Transactions');
            $this->render('mytransactions');
        }
    }
	
	public function viewdetails($slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Transaction Details " . TITLE_FOR_PAGES);
		
		$this->set('active_transactions','active');
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if ($slug)
		{
            $transactionD = $this->Transactions->find()->where(['Transactions.slug' => $slug])->contain(['Conventions','Users'])->first();
			$this->set('transactionD', $transactionD);
            
			if($transactionD)
			{
				// to get the students list of this transaction
				$transactionStudents = $this->Transactionstudents->find()->where(['Transactionstudents.transaction_id' => $transactionD->id])->order(["Transactionstudents.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionStudents', $transactionStudents);
				
				// to get the teachers list of this transaction
				$transactionTeachers = $this->Transactionteachers->find()->where(['Transactionteachers.transaction_id' => $transactionD->id])->order(["Transactionteachers.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionTeachers', $transactionTeachers);
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
