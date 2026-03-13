<?php

// src/Controller/UserssController.php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
 

class UsersController extends AppController {

    var $components = array('RequestHandler', 'PImage', 'Auth', 'PImageTest');

    
	public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(['login','registration','registerprevdetails','forgotpassword','resetpassword','teachersetpassword','dashboard','editprofile','changepassword','teachers','editteacher','addteacher','archiveteacher','restoreteacher','students','addstudent','editstudent','archivestudent','restorestudent','judgesregistration','logout','judgesconfirmation','judgeeditprofile','applyforjudge','switchprofile','judgeexperience','judgingform','logintotest']);
        
		// Include the FlashComponent
        $this->loadComponent('Flash');

		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Conventionregistrationstudents");
		
		$this->loadModel("Evaluationforms");
		$this->loadModel("Settings");
    }
	
	public function login($convention_slug=null,$season_id=null) {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Login '.TITLE_FOR_PAGES);
		
		$user_id = $this->request->session()->read("user_id");
		
		$this->set('header_menu_login_active', 'active');
		
		global $loginUserTypes;
		$this->set('loginUserTypes', $loginUserTypes);
		
		if ($this->request->is('post'))
		{	
			//$this->prx($this->request->data);
			$email_address	= $this->request->data['Users']['email_address'];
            $password		= $this->request->data['Users']['password'];
            $user_type		= $this->request->data['Users']['user_type'];
            $rememberme		= $this->request->data['Users']['rememberme'];
			
			// Step 1 :: To check in users table
			$userInfo = $this->Users->find()->where(['Users.email_address' => $email_address,'Users.user_type' => $user_type])->first();
			//$this->prx($userInfo);
			if ($userInfo)
			{
				if ($userInfo->activation_status == 0) {
					$this->Flash->error('Your account has not yet verified.');
				}
				elseif ($userInfo->status == 0) {
					$this->Flash->error('Your account got temporary disabled. Please contact events team.');
				}
				elseif ($userInfo->status == 2) {
					$this->Flash->error('Your account is archived. Please contact events team.');
				}	
				elseif ($userInfo->status == 3) {
					$this->Flash->error('Your account is rejected. Please contact events team.');
				}				
				elseif (!empty($userInfo) && crypt($password, $userInfo->password) == $userInfo->password) {
					
					
					//$this->prx($userInfo);

					$this->request->session()->write("user_id", $userInfo->id);
					$this->request->session()->write("email_address", $userInfo->email_address);
					$this->request->session()->write("user_type", $userInfo->user_type);
					$this->request->session()->write("last_login", $userInfo->last_login);
					
					if($userInfo->user_type == "Teacher_Parent" && $userInfo->is_judge == "1")
					{
						$this->request->session()->write("current_session_profile_type", 'Supervisor');
					}
					
					// update last login
					$this->Users->updateAll(['forgot_password_status' => '0','last_login' => date('Y-m-d H:i:s')], ["id" => $userInfo->id]);
					
					
					if($convention_slug && $season_id>0)
					{
						// now check if registration for convention required
						// 1. Check convention exists
						$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
						if($conventionD)
						{
							// to get season details
							$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
							if($seasonD)
							{
								// enter this user record in conventionregistrations table
								$convention_id 	= $conventionD->id;
								$user_id 		= $userInfo->id;
								
								// to check if this record already exists
								$checkRegExists = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
								if(!$checkRegExists)
								{
									// insert new record
									$conventionregistrations = $this->Conventionregistrations->newEntity();
									$dataCR = $this->Conventionregistrations->patchEntity($conventionregistrations, array());

									$dataCR->slug 					= "convention-registration-".$convention_id.'-'.$user_id.'-'.$season_id.'-'.time();
									$dataCR->convention_id			= $convention_id;
									$dataCR->user_id				= $user_id;
									$dataCR->season_id				= $season_id;
									$dataCR->season_year 			= $seasonD->season_year;
									$dataCR->status 				= 1;
									
									$dataCR->created 				= date('Y-m-d H:i:s');
									$dataCR->modified 				= NULL;

									$resultCR 		= $this->Conventionregistrations->save($dataCR);
								}
							}
						}
					}
					
					
					$returnUrl = $this->request->session()->read("returnUrl");
					
					if(isset($returnUrl) && !empty($returnUrl))
					{						
						$this->request->session()->delete("returnUrl");
						$this->redirect('/' . $returnUrl);
					}
					else
					{
						$this->request->session()->delete("returnUrl");
						$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
					}
					
				} else {
					$this->Flash->error('Invalid email or password.');
				}
			}
		
			if(!$userInfo)
			{
				$this->Flash->error('Invalid email. Account not found in system.');
			}
			
		} //end if ($this->request->is('post'))
		
    }
	
	public function registration($conventionregistration_slug=null) {
		
        $this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Registration '.TITLE_FOR_PAGES);
		
        $this->set('conventionregistration_slug', $conventionregistration_slug);
		
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conventionregistration_slug])->first();
		if($conventionRegD)
		{
			$this->set('conventionRegD', $conventionRegD);
		}
		else
		{
			$this->Flash->error('Invalid registration information.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
		
		$user_id = $conventionRegD->user_id;
		
		
		$users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				//$this->prx($this->request->data);
				
				$new_password 	= $data->password;
				$salt 			= uniqid(mt_rand(), true);
				$password 		= crypt($new_password, '$2a$07$' . $salt . '$');
				
				// update details
				$this->Users->updateAll([
				'first_name' => $data->first_name,
				'middle_name' => $data->middle_name,
				'phone' => $data->phone,
				'password' => $password
				], ["id" => $user_id]);
				
				$userInfo = $this->Users->find()->where(['Users.id' => $user_id])->first();
				
				// now create a session for this user
				$this->request->session()->write("user_id", $userInfo->id);
				$this->request->session()->write("email_address", $userInfo->email_address);
				$this->request->session()->write("user_type", $userInfo->user_type);
				$this->request->session()->write("first_name", $userInfo->first_name);
				//$this->request->session()->write("last_login", $userInfo->last_login);
				
				$this->Flash->success('Profile details updated successfully.');
                $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
            }
        }
        $this->set('users', $users);
		
		
    }
	
	public function registerprevdetails($convention_slug=null,$season_id=null) {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Register Using Previous Details '.TITLE_FOR_PAGES);
		
		$this->set('convention_slug',$convention_slug);
		$this->set('season_id',$season_id);
		
		// check convention details
		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
		if($conventionD)
		{
			// to get season details
			$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
		}
		else
		{
			$this->Flash->error('Invalid details.');
			$this->redirect(['controller' => 'homes', 'action' => 'index']);
		}
		
		if ($this->request->is('post'))
		{	
			//$this->prx($this->request->data);
			$customer_code	= $this->request->data['Users']['customer_code'];
            $password		= $this->request->data['Users']['password'];
			
			// Step 1 :: To check in users table
			$userInfo = $this->Users->find()->where(['Users.customer_code' => $customer_code,'Users.user_type' => 'School'])->first();
			//echo crypt($password, $userInfo->password);
			//$this->prx($userInfo);
			if ($userInfo)
			{
				if ($userInfo->activation_status == 0) {
					$this->Flash->error('Your account has not yet active.');
				}
				elseif ($userInfo->status == 0) {
					$this->Flash->error('Your account got temporary disabled.');
				} 
				elseif (!empty($userInfo) && crypt($password, $userInfo->password) == $userInfo->password) {

					$this->request->session()->write("user_id", $userInfo->id);
					$this->request->session()->write("email_address", $userInfo->email_address);
					$this->request->session()->write("user_type", $userInfo->user_type);
					$this->request->session()->write("last_login", $userInfo->last_login);
					
					// update last login
					$this->Users->updateAll(['forgot_password_status' => '0','last_login' => date('Y-m-d H:i:s')], ["id" => $userInfo->id]);
					
					$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
					
					
				} else {
					$this->Flash->error('Invalid customer code or password.');
				}
			}
		
			if(!$userInfo)
			{
				$this->Flash->error('Invalid customer codes. Account not found in system.');
			}
			
		} //end if ($this->request->is('post'))
		
		
    }
	
	public function forgotpassword() {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Forgot Password '.TITLE_FOR_PAGES);
		
		global $loginUserTypes;
		$this->set('loginUserTypes', $loginUserTypes);
		
		if ($this->request->is('post'))
		{			
			$captchaValid=1;
			
			//$captchaValid=1;
			if ($captchaValid)
			{
				$email_address			= $this->request->data['Users']['email_address'];
				$user_type				= $this->request->data['Users']['user_type'];
				
				// Step 1 :: To check in users table
				$userInfo = $this->Users->find()->where(['Users.email_address' => $email_address,'Users.user_type' => $user_type])->first();
				
				//$this->prx($userInfo);
				
				if ($userInfo)
				{
					if ($userInfo->activation_status == 0) {
						$this->Flash->error('Your account has not yet verified.');
					}
					elseif ($userInfo->status == 0) {
						$this->Flash->error('Your account got temporary disabled.');
					}
					elseif ($userInfo) {
						
						// update forgot password status
						$this->Users->updateAll(['forgot_password_status' => 1], ['id' => $userInfo->id]);
						
						$emailId = $userInfo['email_address'];
						
						$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '3'])->first();
						
						$reset_password_link = HTTP_PATH."/users/resetpassword/".$userInfo->email_address."/".md5($userInfo->email_address)."/".$userInfo->id."/".md5($userInfo->id);

						$toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!email_address!]','[!reset_password_link!]');
						$fromRepArray = array(SITE_TITLE,$userInfo->first_name,$userInfo->email_address,$reset_password_link);

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

						$this->Flash->success('We have successfully sent you reset password link. Please click that link and reset your password.');
						$this->redirect(['controller' => 'users', 'action' => 'login']);
						
						
					} else {
						$this->Flash->error('Invalid email entered.');
					}
				}
				
				// if not found anywhere
				if(!$companyInfo && !$userInfo)
				{
					$this->Flash->error('Invalid email. Account not found in system.');
				}
				
			}			
			
			//$this->Flash->error('Invalid email or password.');
			
		} //end if ($this->request->is('post'))
		
    }
	
	public function resetpassword($email_address = NULL, $enc_email = NULL, $userID = NULL, $enc_userID = NULL) {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Reset Password '.TITLE_FOR_PAGES);

		if(md5($email_address) != $enc_email)		
		{
			$this->Flash->error('Invalid link.');
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}
		
		if(md5($userID) != $enc_userID)		
		{
			$this->Flash->error('Invalid reset password link.');
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}
		
		$userInfo = $this->Users->find()->where(['Users.id' => $userID, 'Users.email_address' => $email_address])->first();
		
		if($userInfo->forgot_password_status != 1)		
		{
			$this->Flash->error('Invalid link.');
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}
		
		if ($this->request->is('post'))
		{
			if($userInfo)
			{
				$new_password = $this->request->data['Users']['password'];
				unset($this->request->data['Users']['confirm_password']);
				$salt = uniqid(mt_rand(), true);
				$password = crypt($new_password, '$2a$07$' . $salt . '$');				
				
				$this->Users->updateAll(['password' => $password,'forgot_password_status' => '0'], ['id' => $userID]);
				$this->Flash->success('Your password reset successfully. Please login.');
				$this->redirect(['controller' => 'users', 'action' => 'login']);
			}
			else
			{
				$this->Flash->error('Invalid request.');
				$this->redirect(['controller' => 'users', 'action' => 'login']);
			}
		}
    }
	
	public function teachersetpassword($email_address = NULL, $enc_email = NULL, $userID = NULL, $enc_userID = NULL) {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Teacher Set Password '.TITLE_FOR_PAGES);

		if(md5($email_address) != $enc_email)		
		{
			$this->Flash->error('Invalid link.');
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}
		
		if(md5($userID) != $enc_userID)		
		{
			$this->Flash->error('Invalid reset password link.');
			$this->redirect(['controller' => 'users', 'action' => 'login']);
		}
		
		$userInfo = $this->Users->find()->where(['Users.id' => $userID, 'Users.email_address' => $email_address])->first();
		
		if ($this->request->is('post'))
		{
			if($userInfo)
			{
				$new_password = $this->request->data['Users']['password'];
				unset($this->request->data['Users']['confirm_password']);
				$salt = uniqid(mt_rand(), true);
				$password = crypt($new_password, '$2a$07$' . $salt . '$');				
				
				$this->Users->updateAll(['password' => $password,'forgot_password_status' => '0'], ['id' => $userID]);
				$this->Flash->success('Your password reset successfully. Please login.');
				$this->redirect(['controller' => 'users', 'action' => 'login']);
			}
			else
			{
				$this->Flash->error('Invalid request.');
				$this->redirect(['controller' => 'users', 'action' => 'login']);
			}
		}
		
    }
	
	public function dashboard() {
		//echo 'ddd';exit;
		$this->userLoginCheck();
		
		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Dashboard '.TITLE_FOR_PAGES);
		
		$this->set('active_dashboard', 'active');
		
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		$this->set('userDetails', $userDetails);
		
		$settingsD = $this->Settings->find()->where(['Settings.id' => 1])->first();
		$this->set('settingsD', $settingsD);
		
    }
	
	public function editprofile() {
		
		$this->userLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Edit Profile " . TITLE_FOR_PAGES);
		
		$this->set('active_editprofile','active');
		
        $user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        $this->set('userDetails', $userDetails);
		
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
                    $this->Flash->success('Profile details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function changepassword() {
        
		$this->userLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent','Judge']);
		
        $this->set("title_for_layout", "Change Password" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_changepassword','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $users = $this->Users->newEntity();

        if ($this->request->is('post')) {
            //die;
            $data = $this->Users->patchEntity($users, $this->request->data, ['validate' => 'changePassword']);

            if (count($data->errors()) == 0) {
//               pr($data); die('tsting');
                $old_password = $this->request->data['Users']['old_password'];
                $new_password = $this->request->data['Users']['new_password'];
                if ($userDetails && crypt($old_password, $userDetails->password) != $userDetails->password) {// Checking the OLD password matched aur not
                    $msgString = 'Old Password is not correct.';
                } else {
                    if ($userDetails && crypt($new_password, $userDetails->password) == $userDetails->password) {// Checking the both password matched aur not
                        $msgString .="- You cannot put your old password for the new password.";
                    }
                }


                if (isset($msgString) && $msgString != '') {
                    $this->Flash->error($msgString);
                } else {
                    $new_password = $data["new_password"];
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    /* geting and setting users data */
                    $usersTable = TableRegistry::get("Users");
                    $user = $usersTable->get($user_id); // Return user with id 
                    $user->password = $password;
                    $usersTable->save($user);

                    $this->Flash->success('Your Password has been changed successfully.');
                    return $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            } else {
                $this->Flash->error('Please below listed errors.');
            }
        }


        $this->set('users', $users);
    }
	
	public function teachers() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Supervisors List" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_teachers','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.school_id = '".$user_id."')";
		$condition[] = "(Users.user_type = 'Teacher_Parent')";

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Users->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Users->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Users->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Users']['keyword']) && $this->request->data['Users']['keyword'] != '') {
                $keyword = trim($this->request->data['Users']['keyword']);
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
            $condition[] = "(Users.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 30, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Users');
            $this->render('teachers');
        }
    }
	
	public function editteacher($teacher_slug=null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Edit Supervisor Details " . TITLE_FOR_PAGES);
		
		$this->set('active_teachers','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
		// to get teacher details
		$teacherD = $this->Users->find()->where(['Users.school_id' => $user_id,'Users.slug' => $teacher_slug])->first();
		
        $users = $this->Users->get($teacherD->id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
                    $this->Flash->success('Supervisor details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'teachers']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function addteacher() {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Add Supervisors Details " . TITLE_FOR_PAGES);
		
		$this->set('active_teachers','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$flagCheck = 1;
			
            $data = $this->Users->patchEntity($users, $this->request->data);
			
			// to check that this email not duplicate for one school
			$checkEmailS = $this->Users->find()->where(['Users.email_address' => $data->email_address,'Users.school_id' => $user_id])->first();
			
			// to check if this email exists - in admins table
			$checkEmailA = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
			
			if($checkEmailA || $checkEmailS)
			{
				$flagCheck = 0;
				$this->Flash->error('Email address already exists.');
			}
			
            if (count($data->errors()) == 0 && $flagCheck == 1) {

				$slug = $this->getSlug($this->request->data['Users']['first_name'] . ' ' . time(), 'Users');
				$data->slug = $slug;
				
				$data->user_type = 'Teacher_Parent';
				$data->school_id = $user_id;
				$data->status = 0;
				$data->activation_status = 0;
				$data->created = date('Y-m-d H:i:s');
				$data->modified = date('Y-m-d H:i:s');
				if ($resultUT = $this->Users->save($data)) {
					
					// now send an email to teacher to verify account set password
					$emailId 				= $resultUT->email_address;
					$teacher_name 			= $resultUT->first_name;
					
					$school_name 			= $userDetails->first_name;
					
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '4'])->first();

					$link = HTTP_PATH . "/homes/teacherverifyaccount/" . $resultUT->slug . "/" . md5($resultUT->slug) . "/" . md5($resultUT->id);
					//$sitelink = '<a style="color:#000; text-decoration: underline;" href="mailto:' . MAIL_FROM . '">' . MAIL_FROM . '</a>';
					
					$toRepArray 	= array('[!teacher_name!]','[!school_name!]','[!teacher_email_address!]','[!LINK!]','[!customer_code!]');
					$fromRepArray 	= array($teacher_name,$school_name,$emailId,$link,$userDetails->customer_code);
					
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;
					
					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to($emailId)
						//->cc(HEADERS_CC)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
					
					
					$this->Flash->success('Supervisor details added successfully. Supervisor will receive an email to verify account.');
					$this->redirect(['controller' => 'users', 'action' => 'teachers']);
				}
				
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
	
	public function archiveteacher($teacher_slug=null) {
        
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$school_id 	= $this->request->session()->read("user_id");
		
		// to check if this teacher exists
		$teacherD = $this->Users->find()->where(['Users.slug' => $teacher_slug,'Users.school_id' => $school_id])->first();
		if($teacherD)
		{
			$this->Users->updateAll(['status' => '2'], ["slug"=>$teacher_slug]);
			$this->Flash->success('Supervisors details archived successfully.');
		}
		else
		{
			$this->Flash->error('Supervisors not found.');
		}
		
		
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
	
	public function restoreteacher($teacher_slug=null) {
        
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$school_id 	= $this->request->session()->read("user_id");
		
		// to check if this teacher exists
		$teacherD = $this->Users->find()->where(['Users.slug' => $teacher_slug,'Users.school_id' => $school_id])->first();
		if($teacherD)
		{
			$this->Users->updateAll(['status' => '1'], ["slug"=>$teacher_slug]);
			$this->Flash->success('Supervisor details restored successfully.');
		}
		else
		{
			$this->Flash->error('Supervisor not found.');
		}
		
		
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
	
	public function students() {

        $this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Students List" . TITLE_FOR_PAGES);
        $this->viewbuilder()->layout('home');
        
		$this->set('active_students','active');
		
        $msgString = '';

		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.user_type = 'Student')";
		
		if($this->request->session()->read("user_type") == "School")
		{
			$condition[] = "(Users.school_id = '".$user_id."')";
		}
		else
		if($this->request->session()->read("user_type") == "Teacher_Parent")
		{
			$condition[] = "(Users.school_id = '".$userDetails->school_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Users->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Users->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Users->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->data['Users']['keyword']) && $this->request->data['Users']['keyword'] != '') {
                $keyword = trim($this->request->data['Users']['keyword']);
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
            $condition[] = "(Users.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 30, 'order' => ['Users.first_name' => 'ASC','Users.middle_name' => 'ASC','Users.last_name' => 'ASC']];
        $this->set('users', $this->paginate($this->Users));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Users');
            $this->render('students');
        }
		
		//$this->prx($this->paginate($this->Users));

    }
	
	public function addstudent() {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Add Student Details " . TITLE_FOR_PAGES);
		
		$this->set('active_students','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		global $birthYearDD;
		$this->set('birthYearDD', $birthYearDD);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {

				$slug = $this->getSlug($this->request->data['Users']['first_name'] . ' ' . time(), 'Users');
				$data->slug = $slug;
				
				if($this->request->session()->read("user_type") == "School")
				{
					$data->school_id = $user_id;
				}
				else
				if($this->request->session()->read("user_type") == "Teacher")
				{
					$data->school_id 			= $userDetails->school_id;
				}
				
				$data->user_type = 'Student';
				$data->status = 1;
				$data->activation_status = 1;
				$data->created = date('Y-m-d H:i:s');
				$data->modified = date('Y-m-d H:i:s');
				if ($this->Users->save($data)) {
					$this->Flash->success('Student details added successfully.');
					$this->redirect(['controller' => 'users', 'action' => 'students']);
				}
				
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
	
	public function editstudent($student_slug=null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Edit Student Details " . TITLE_FOR_PAGES);
		
		$this->set('active_students','active');
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		global $birthYearDD;
		$this->set('birthYearDD', $birthYearDD);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		// to get student details
		$studentD = $this->Users->find()->where(['Users.slug' => $student_slug])->first();
		
		
        $users = $this->Users->get($studentD->id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
                    $this->Flash->success('Student details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'students']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function archivestudent($student_slug=null) {
        
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$school_id 	= $this->request->session()->read("user_id");
		
		// to check if this student exists
		$studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
		if($studentD)
		{
			$this->Users->updateAll(['status' => '2'], ["slug"=>$student_slug]);
			$this->Flash->success('Student details archived successfully.');
		}
		else
		{
			$this->Flash->error('Student not found.');
		}
		
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
	
	public function restorestudent($student_slug=null) {
        
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$school_id 	= $this->request->session()->read("user_id");
		
		// to check if this student exists
		$studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
		if($studentD)
		{
			$this->Users->updateAll(['status' => '1'], ["slug"=>$student_slug]);
			$this->Flash->success('Student details restored successfully.');
		}
		else
		{
			$this->Flash->error('Student not found.');
		}
		
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
	
	public function deletestudent_old($student_slug=null) {
        
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		$school_id 	= $this->request->session()->read("user_id");
		
		// to check if this student exists
		$studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
		if($studentD)
		{
			// to check if this student is linked with any convention registration students list
			$checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.student_id' => $studentD->id])->first();
			if($checkCRS)
			{
				$this->Flash->error('Student cannot delete. Student is linked with convention registration teachers list.');
			}
			else
			{
				$this->Users->deleteAll(['Users.school_id' => $school_id,'Users.id' => $studentD->id]);
				$this->Flash->success('Student details deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Student not found.');
		}
		
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
	
	public function judgesregistration() {
		
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Judges Registration '.TITLE_FOR_PAGES);
		
		$this->set('header_menu_judgesreg_active', 'active');
		
		$users = $this->Users->newEntity();
        if ($this->request->is('post')) {
            
			$data = $this->Users->patchEntity($users, $this->request->data);
			
			$flagCheck				=	1;
			
			// to check if its exists in users table
			$checkUE = $this->Users->find()->where(['Users.email_address' => $data->email_address])->first();
			if($checkUE)
			{
				$flagCheck = 0;
				$this->Flash->error('Email address already exists.');
			}
			
			// to check if its exists in admins table
			$checkUA = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
			if($checkUA)
			{
				$flagCheck = 0;
				$this->Flash->error('Email address already exists.');
			}
			
            if (count($data->errors()) == 0 && $flagCheck)
			{
				//$this->prx($this->request->data);
				
                $slug = $this->getSlug($this->request->data['Users']['first_name'].' '.$this->request->data['Users']['last_name'].' '.time(), 'Users');
                $data->slug 				= $slug;
				$data->created 				= date('Y-m-d H:i:s', time());
				$data->modified 			= date('Y-m-d H:i:s', time());
                
				$data->user_type 			= 'Judge';
				$data->is_judge 			= 1;
				
				$data->status 				= 0;
                $data->activation_status 	= 0;
				
                $new_password = $this->request->data['Users']['password'];
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');
                $data->password = $password;
				
                if ($result = $this->Users->save($data)) {
					
					$first_name = $result->first_name;
					$last_name 	= $result->last_name;
                    $emailId 	= $this->request->data['Users']['email_address'];
					
					$uIDA 		= $result->id + 2013;
					
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '11'])->first();

                    $link = HTTP_PATH . "/users/judgesconfirmation/".$result->slug."/". md5($result->slug)."/".urlencode($emailId).'/'.md5($uIDA);
                    
					$toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!last_name!]','[!email_address!]','[!LINK!]');
                    $fromRepArray = array(SITE_TITLE,$first_name,$last_name,$emailId,$link);
                    
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;
					
					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to($emailId)
						->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
					
                    $this->Flash->success('Your account has been successfully created. Please check your email for your activation link. If you do not receive it within a few minutes, please check your spam folder or contact our support team.');
					
                    $this->redirect(['controller' => 'users', 'action' => 'login']);
                }
            } else {
                
            }
        }
        $this->set('users', $users);
		
    }
	
	public function judgesconfirmation($slug = null, $md5slug = null, $email = null, $uIDA = null) {
		
		if (md5($slug) == $md5slug)
		{
            $userCheck = $this->Users->find()->where(['Users.slug' => $slug])->first();
			
			if($userCheck && $uIDA == md5($userCheck->id + 2013))
			{
				if ($userCheck->status == 0 && $userCheck->activation_status == 0)
				{
					$this->Users->updateAll(['activation_status' => '1','modified' => date('Y-m-d H:i:s', time())], ["id" => $userCheck->id]);
					
					// send email to events team
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '12'])->first();
                    
					$toRepArray = array('[!first_name!]','[!last_name!]','[!email_address!]');
                    $fromRepArray = array($userCheck->first_name,$userCheck->last_name,$$userCheck->email_address);
                    
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;
					
					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to(ACCOUNTS_TEAM_ANOTHER_EMAIL)
						//->cc(HEADERS_CC)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
					
					
					$this->Flash->success('Your account has been successfully verified. Admin will review and activate your account.');
					$this->redirect(['controller' => 'users', 'action' => 'login']);
				}
				else
				{
					$this->Flash->error('You have already used this activation link!');
				}
			}
			else
			{
				$this->Flash->error('User not found.');
			}
        }
		else
		{
			$this->Flash->error('Invalid activation link!');
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'login']);
    }
	
	public function judgeeditprofile() {
		
		$this->userLoginCheck();
		$this->judgeLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Edit Profile " . TITLE_FOR_PAGES);
		
		$this->set('active_editprofile','active');
		
        $user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
                    $this->Flash->success('Profile details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function applyforjudge() {
		
		$this->userLoginCheck();
		$this->teacherLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Apply For Judge " . TITLE_FOR_PAGES);
		
		$this->set('active_applyforjudge','active');
		
        $user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        $this->set('userDetails', $userDetails);
		
		/* if($userDetails->is_judge != 0)
		{
			$this->Flash->error('Invalid access.');
            $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		} */
		
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
			$data->is_judge = 2;
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
					
					// no send email to admin that one supervisor applied for judge
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '15'])->first();
                    
					$toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!last_name!]','[!email_address!]');
                    $fromRepArray = array(SITE_TITLE,$userDetails->first_name,$userDetails->last_name,$userDetails->email_address);
                    
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;
					
					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to(ACCOUNTS_TEAM_ANOTHER_EMAIL)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
					
					$this->Flash->success('Your request to apply for judge has been submitted successfully. Please wait while admin review and approve/reject.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function switchprofile($switchprofiletype=null) {

        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
		
		if($switchprofiletype == 'switchtojudge' || $switchprofiletype == 'switchtosupervisor')
		{
			$this->request->session()->delete('sess_selected_convention_registration_id');
			$this->request->session()->delete('sess_selected_convention_id');
			
			if($userDetails->user_type == 'Teacher_Parent' && $userDetails->is_judge == '1')
			{
				if($switchprofiletype == 'switchtojudge')
				{
					$this->request->session()->delete('current_session_profile_type');
					$this->request->session()->write("current_session_profile_type", 'Judge');
					
					$this->Flash->success('Profile successfully switched as judge.');
				}
				
				if($switchprofiletype == 'switchtosupervisor')
				{
					$this->request->session()->delete('current_session_profile_type');
					$this->request->session()->write("current_session_profile_type", 'Supervisor');
					
					$this->Flash->success('Profile successfully switched as supervisor.');
				}
			}
			else
			{
				$this->Flash->error('Invalid user type.');
			}
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
		
		
        $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
    }
	
	public function judgeexperience() {
		
		$this->userLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");
        $this->set("title_for_layout", "Judge Experience " . TITLE_FOR_PAGES);
		
		$this->set('active_judgeexp','active');
		
        $user_id = $this->request->session()->read("user_id");
        
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        $this->set('userDetails', $userDetails);
		
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->data);
			
            if (count($data->errors()) == 0) {
				
				if ($this->Users->save($data)) {
                    $this->Flash->success('Judge experience details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
		//pr($users);exit;
		
    }
	
	public function logout() {

        $this->Flash->success('Logout successfully.');
        $this->request->session()->delete('user_id');
        $this->request->session()->delete('email_address');
        $this->request->session()->delete('user_type');
		
		$this->request->session()->delete('sess_selected_convention_registration_id');
		$this->request->session()->delete('sess_selected_convention_id');
		$this->request->session()->delete('current_session_profile_type');
		
        $this->redirect(['controller' => 'users', 'action' => 'login']);
    }
    
	public function logintotest($user_id=null) {
		
		$userInfo = $this->Users->find()->where(['Users.id' => $user_id])->first();
		
		$this->request->session()->write("user_id", $userInfo->id);
		$this->request->session()->write("email_address", $userInfo->email_address);
		$this->request->session()->write("user_type", $userInfo->user_type);
		$this->request->session()->write("last_login", $userInfo->last_login);
		
		if($userInfo->user_type == "Teacher_Parent" && $userInfo->is_judge == "1")
		{
			$this->request->session()->write("current_session_profile_type", 'Supervisor');
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		
    }
	
	public function judgingform($event_id_number = '055') {
		//echo 'ddd';exit;
		$this->userLoginCheck();
		
		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewbuilder()->layout("home");		
		$this->set('title_for_layout', 'Judging Form '.TITLE_FOR_PAGES);
		
		$this->set('active_dashboard', 'active');
		
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		$this->set('userDetails', $userDetails);
		
		//$event_id_number = '055';
		
		// now fetch the form based on event id number
		$condEvalForm = array();
		$condEvalForm[] = "(Evaluationforms.event_id_numbers LIKE '".$event_id_number."' OR Evaluationforms.event_id_numbers LIKE '".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number."')";
		
		$evalFormD = $this->Evaluationforms->find()->where($condEvalForm)->first();
		//$this->prx($evalFormD);
		
		$this->set('evalFormD', $evalFormD);
		
		
		if ($this->request->is(['post'])) {
			
			$this->prx($this->request->data);
		}
		
		
    }

 

}

?>