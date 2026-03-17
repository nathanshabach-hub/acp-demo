<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;

class AdminsController extends AppController {

    public $paginate = ['limit' => 1];
    var $components = array('RequestHandler', 'PImage');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->params['action'];
        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') { // check admin login session, direct to admin login if session not active
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['action' => 'login']);
            }
        }
		
		$this->loadModel("Emailtemplates");
		$this->loadModel("Users");
		$this->loadModel("Seasons");
		$this->loadModel("Events");
		$this->loadModel("Conventions");
		$this->loadModel("Divisions");
		$this->loadModel("Settings");
		$this->loadModel("Transactions");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Conventionseasonevents");
    }

    public function login() {
        $this->set('title', ADMIN_TITLE . 'Admin Login');
        $this->viewBuilder()->layout('admin_login');

        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($loggedAdminId) {
            $this->redirect(['action' => 'dashboard']);
        }

        // echo Configure::version(); exit;

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->data);
            if (count($admin->errors()) == 0) {
                $userName = $this->request->data['Admins']['username'];
                $password = $this->request->data['Admins']['password'];
                $adminInfo = $this->Admins->find()->where(['Admins.username' => $userName])->first();
                if ($adminInfo) {
                    if ($adminInfo->status == 0) {
                        $this->Flash->error('Your account got temporary disabled.');
                    } elseif (!empty($adminInfo) && crypt($password, $adminInfo->password) == $adminInfo->password) {

                        if (isset($this->request->data['Admins']['remember']) && $this->request->data['Admins']['remember'] == '1') {
                            setcookie("admin_username", $userName, time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_password", $password, time() + 60 * 60 * 24 * 100, "/");
                        } else {
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                        }
                        $this->request->session()->write('admin_id', $adminInfo->id);
                        $this->request->session()->write('admin_username', $userName);
                        $this->redirect(['action' => 'dashboard']);
                    } else {
                        $this->Flash->error('Invalid username or password.');
                    }
                } else {
                    $this->Flash->error('Invalid username or password.');
                }
            } else {
                $this->Flash->error('Please below listed errors.');
            }
        } else {
            if (isset($_COOKIE["admin_username"]) && isset($_COOKIE["admin_password"])) {
                $this->request->data['Admins']['username'] = $_COOKIE["admin_username"];
                $this->request->data['Admins']['password'] = $_COOKIE["admin_password"];
                $this->request->data['Admins']['remember'] = 1;
            }
        }
        $this->set('admin', $admin);
    }

    public function forgotPassword() {
        $this->set('title', ADMIN_TITLE . 'Forgot Password');
        $this->viewBuilder()->layout('admin_login');

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->data, ['validate' => 'forgotPassword']);
            if (count($admin->errors()) == 0) {
                $email = $this->request->data['Admins']['email'];
                $adminInfo = $this->Admins->find()->where(['Admins.email' => $email])->first();
                if ($adminInfo) {
                    $new_password = rand(1000000, 999999999);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $this->Admins->updateAll(['password' => $password], ['id' => $adminInfo->id]);

                    $username = $adminInfo['username'];
                    $emailId = $adminInfo['email'];
                    
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '1'])->first();

                    $toRepArray = array('[!email!]', '[!username!]', '[!password!]', '[!HTTP_PATH!]', '[!SITE_TITLE!]');
                    $fromRepArray = array($emailId, $username, $new_password, HTTP_PATH, SITE_TITLE);

                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;

                    $email = new Email();
                    $email->template('default', 'admintemplate')
                            ->emailFormat('html')
                            ->to($emailId)
                            ->from([MAIL_FROM => SITE_TITLE])
                            ->subject($subjectToSend)
                            ->viewVars(['content_for_layout' => $messageToSend])
                            ->send();

                    $this->Flash->success('New admin password sent to admin email address.');
                    $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('Invalid email address, please enter correct email address.');
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
    }

    public function logout() {
        session_destroy();
        $this->Flash->success('Logout successfully.');
        $this->redirect(['action' => 'login']);
    }

    public function headerchooseconvseas() {
		
		//$this->prx($this->request->data);
		
		$admin_header_season_id = $this->request->data['admin_header_season_id'];
		
		if($admin_header_season_id>0)
		{
			$convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$admin_header_season_id])->contain(['Conventions'])->first();
			
			if($convSD)
			{
				$this->request->session()->write('sess_admin_header_season_id', $admin_header_season_id);
				
				$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convSD->Conventions['slug']]);
			}
		}
		else
		{
			$this->request->session()->write('sess_admin_header_season_id', 0);
		}
		
		
		
		$this->redirect(['action' => 'dashboard']);
	}
	
    public function dashboard() {
        $this->set('title', ADMIN_TITLE . 'Admin Dashboard');
        $this->viewBuilder()->layout('admin');
        $this->set('dashboard', '1');
		
		// to check if convention season selected from header
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$this->set('sess_admin_header_season_id', $sess_admin_header_season_id);
		if($sess_admin_header_season_id>0)
		{
			// to get convention season details
			$convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$sess_admin_header_season_id])->first();
			
			$this->set('conv_season_slug', $convSD->slug);
			
			$total_students = $this->Conventionregistrationstudents->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->count();
			$this->set('total_students', $total_students);
			
			$total_teachers_parents = $this->Conventionregistrationteachers->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->count();
			$this->set('total_teachers_parents', $total_teachers_parents);
			
			// to get total schools, this require process to check
			$cntrSchools = 0;
			$listSchools = $this->Conventionregistrations->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->contain(['Users'])->all();
			foreach($listSchools as $schoolcntr)
			{
				if($schoolcntr->Users['user_type'] == "School")
				{
					$cntrSchools++;
				}
			}
			$this->set('total_schools', $cntrSchools);
			
			// to get total judges, this require process to check
			$cntrJudges = 0;
			$listCR = $this->Conventionregistrations->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->contain(['Users'])->all();
			foreach($listCR as $judgcntr)
			{
				//echo $judgcntr->Users['user_type'];
				//echo $judgcntr->Users['is_judge'];
				//echo '<hr>';
				if(($judgcntr->Users['user_type'] == "Judge" || $judgcntr->Users['user_type'] == "Teacher_Parent") && $judgcntr->Users['is_judge'] == 1)
				{
					$cntrJudges++;
				}
			}
			$this->set('total_judges', $cntrJudges);
			
			$total_conv_seas_events = $this->Conventionseasonevents->find()->where(["conventionseasons_id"=> $convSD->id])->count();
			$this->set('total_conv_seas_events', $total_conv_seas_events);
			
			
			$condTr = array();
			//$condTr[] = "(Transactions.status = '2' OR Transactions.status = '3')";
			$condTr[] = "(Transactions.conventionseason_id = '".$convSD->id."')";
			
			$total_transactions = $this->Transactions->find()->where($condTr)->count();
			$this->set('total_transactions', $total_transactions);

			// Chart data: scheduled events by category
			$schedCategoryData = [];
			for ($cat = 1; $cat <= 4; $cat++) {
				$schedCategoryData[] = $this->Schedulingtimings->find()->where([
					"conventionseasons_id" => $convSD->id,
					"schedule_category" => $cat,
					"day IS NOT" => null,
				])->count();
			}
			$this->set('schedCategoryData', json_encode($schedCategoryData));

			// Chart data: scheduled vs unscheduled
			$totalScheduled = $this->Schedulingtimings->find()->where([
				"conventionseasons_id" => $convSD->id,
				"day IS NOT" => null,
			])->count();
			$totalUnscheduled = $this->Schedulingtimings->find()->where([
				"conventionseasons_id" => $convSD->id,
				"day" => null,
			])->count();
			$this->set('totalScheduled', $totalScheduled);
			$this->set('totalUnscheduled', $totalUnscheduled);

			// Chart data: scheduling by day
			$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
			$dayCountData = [];
			foreach ($dayNames as $d) {
				$dayCountData[] = $this->Schedulingtimings->find()->where([
					"conventionseasons_id" => $convSD->id,
					"day" => $d,
				])->count();
			}
			$this->set('dayNames', json_encode($dayNames));
			$this->set('dayCountData', json_encode($dayCountData));
			
		}
		else
		{
			$total_seasons = $this->Seasons->find()->where(['1 = 1'])->count();
			$this->set('total_seasons', $total_seasons);
			
			$total_events = $this->Events->find()->where(['1 = 1'])->count();
			$this->set('total_events', $total_events);
			
			$total_conventions = $this->Conventions->find()->where(['1 = 1'])->count();
			$this->set('total_conventions', $total_conventions);
			
			$total_divisions = $this->Divisions->find()->where(['1 = 1'])->count();
			$this->set('total_divisions', $total_divisions);
			
			$total_schools = $this->Users->find()->where(["user_type"=> "School"])->count();
			$this->set('total_schools', $total_schools);
			
			$total_teachers_parents = $this->Users->find()->where(["user_type"=> "Teacher_Parent"])->count();
			$this->set('total_teachers_parents', $total_teachers_parents);
			
			$total_students = $this->Users->find()->where(["user_type"=> "Student"])->count();
			$this->set('total_students', $total_students);
			
			$total_registrations = $this->Conventionregistrations->find()->where(['1 = 1'])->count();
			$this->set('total_registrations', $total_registrations);
			
			$total_transactions = $this->Transactions->find()->where(['1 = 1'])->count();
			$this->set('total_transactions', $total_transactions);
			
			$condJ = array();
			$condJ[] = "(Users.activation_status = '1' AND (Users.status = '1' OR Users.status = '2'))";
			$condJ[] = "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))";
			$total_judges = $this->Users->find()->where($condJ)->count();
			$this->set('total_judges', $total_judges);
		
		}

    }

    public function changeEmail() {
        $this->set('title', ADMIN_TITLE . 'Change Email Address');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changeEmail', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->data, ['validate' => 'changeEmail']);
            if (count($admin->errors()) == 0) {
                $new_email = $this->request->data['Admins']['new_email'];
                $this->Admins->updateAll(['email' => $new_email], ['id' => $this->request->session()->read('admin_id')]);
                $this->Flash->success('Admin email updated successfully.');
                $this->redirect(['action' => 'changeEmail']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->session()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changeusername() {
        $this->set('title', ADMIN_TITLE . 'Change Username');
        $this->viewBuilder()->layout('admin');

        $this->set('manageConfig', '1');
        $this->set('changeUsername', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {

            $admin = $this->Admins->patchEntity($admin, $this->request->data, ['validate' => 'changeusername']);
            if (count($admin->errors()) == 0) {
                $username = $this->request->data['Admins']['new_username'];
                $this->Admins->updateAll(['username' => $username], ['id' => $this->request->session()->read('admin_id')]);
                $this->request->session()->write('admin_username', $username);
                $this->Flash->success('Admin username updated successfully.');
                $this->redirect(['action' => 'changeusername']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->session()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changePassword() {
        $this->set('title', ADMIN_TITLE . 'Change Password');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changePassword', '1');
		
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $this->request->data['Admins']['id'] = $this->request->session()->read('admin_id');
            $admin = $this->Admins->patchEntity($admin, $this->request->data, ['validate' => 'changePassword']);
            if (count($admin->errors()) == 0) {
                $new_password = $this->request->data['Admins']['new_password'];
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');
                $this->Admins->updateAll(['password' => $password], ['id' => $this->request->session()->read('admin_id')]);
                $this->Flash->success('Admin password updated successfully.');
                $this->redirect(['action' => 'changePassword']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->session()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }
	
	public function resetpassword() {
	
		$adminInfo = $this->Admins->find()->where()->order(['Admins.id' => "ASC"])->first();

		$this->request->session()->write('admin_id', $adminInfo->id);
		$this->request->session()->write('admin_username', $adminInfo->username);
		$this->redirect(['action' => 'dashboard']);
	
	}

    public function settings() {
        $this->set('title', ADMIN_TITLE . 'Settings');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('settings', '1');
		
        if ($this->request->is('post')) {
				
				$paypal_email 							= $this->request->data['Settings']['paypal_email'];
				$accounts_team_email 					= $this->request->data['Settings']['accounts_team_email'];
				$full_registration_price 				= $this->request->data['Settings']['full_registration_price'];
				$scripture_only_registration_price 		= $this->request->data['Settings']['scripture_only_registration_price'];
				$scripture_trophy_discount 				= $this->request->data['Settings']['scripture_trophy_discount'];
				
				$min_events_student 				= $this->request->data['Settings']['min_events_student'];
				$max_events_student 				= $this->request->data['Settings']['max_events_student'];
				
				$judges_low_score_saving_pin 				= $this->request->data['Settings']['judges_low_score_saving_pin'];
				
				
				//$tax_percent 							= $this->request->data['Settings']['tax_percent'];
				
                $this->Settings->updateAll([
				'paypal_email' 							=> $paypal_email,
				'accounts_team_email' 					=> $accounts_team_email,
				'full_registration_price' 				=> $full_registration_price,
				'scripture_only_registration_price' 	=> $scripture_only_registration_price,
				'scripture_trophy_discount' 			=> $scripture_trophy_discount,
				'min_events_student' 					=> $min_events_student,
				'max_events_student' 					=> $max_events_student,
				'judges_low_score_saving_pin' 			=> $judges_low_score_saving_pin,
				
				
				//'tax_percent' 							=> $tax_percent
				], ['id' => 1]);
                
                $this->Flash->success('Settings updated successfully.');
                $this->redirect(['controller' => 'admins','action' => 'settings']);
             
        }
		
        $settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        $this->set('settingsInfo', $settingsInfo);
    }
	
	public function postinfo() {
        $this->set('title', ADMIN_TITLE . 'Post Information');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('postinfo', '1');
		
        if ($this->request->is('post')) {
				
				$postinfo 							= $this->request->data['Settings']['postinfo'];
				
                $this->Settings->updateAll([
				'postinfo' 							=> $postinfo
				], ['id' => 1]);
                
                $this->Flash->success('Information posted successfully.');
                $this->redirect(['controller' => 'admins','action' => 'postinfo']);
             
        }
		
        $settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        $this->set('settingsInfo', $settingsInfo);
    }

}

?>