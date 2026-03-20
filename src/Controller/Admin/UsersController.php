<?php
namespace App\Controller\Admin;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class UsersController extends AppController{

    public $paginate = ['limit' => 50];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];
   
    public function initialize(){
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                 $this->redirect(['controller'=>'admins', 'action' => 'login']);
            }
        }
		
		$this->loadModel('Emailtemplates');
		$this->loadModel('Admins');
    }
    
 
    /*School Module*/
	
    public function index() {
        $this->set('title', ADMIN_TITLE. 'Manage Schools/Homeschools');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolList', '1');
        
        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.user_type = 'School')";
        
        if($this->request->is('post')){
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
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
            
                        if(isset($requestData['Users']['keyword']) && $requestData['Users']['keyword']!=''){
                            $keyword = trim($requestData['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0]!=''){
                $searchArr = $this->request->getParam('pass');
//                echo '<pre>';
//                print_r($searchArr);exit;
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.customer_code LIKE '%".addslashes($keyword)."%' OR  Users.first_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
       // $this->paginate = ['contain'=>['Ethnicities','Companies','Educations'], 'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Users');
            $this->render('index');
        }
    }
    
    public function activateuser($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateuser/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivateuser($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateuser/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function archiveuser($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('School details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'index']);
    }
	
	public function restoreuser($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('School details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'index']);
    }
	
	public function add() {
        $this->set('title', ADMIN_TITLE . 'Add School/Homeschool');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolAdd', '1');
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
            $requestData = $this->request->getData();
            $data = $this->Users->patchEntity($users, $requestData, ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

				// to check that this customer code already exixts
				$flagCheck = 1;
				
				$checkCCode = $this->Users->find()->where(['Users.customer_code' => $data->customer_code])->first();
				if($checkCCode)
				{
					$flagCheck = 0;
					$this->Flash->success('Customer code already exists.');
				}
				
				if($flagCheck == 1)
				{
                    $slug = $this->getSlug($requestData['Users']['first_name'] . ' ' . time(), 'Users');
					$data->slug = $slug;
					
					$data->user_type = 'School';
					$data->status = 1;
					$data->activation_status = 0;
					$data->created = date('Y-m-d H:i:s');
					$data->modified = date('Y-m-d H:i:s');
					if ($this->Users->save($data)) {
						$this->Flash->success('School details added successfully. School admin need to verify account from front end.');
						$this->redirect(['controller' => 'users', 'action' => 'index']);
					}
				}
				
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
	
	public function add_custom_query() {
        $this->set('title', ADMIN_TITLE . 'Add School/Homeschool');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolAdd', '1');
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
            $requestData = $this->request->getData();
            $data = $this->Users->patchEntity($users, $requestData, ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

				// to check that this customer code already exixts
				$flagCheck = 1;
				
				$checkCCode = $this->Users->find()->where(['Users.customer_code' => $data->customer_code])->first();
				if($checkCCode)
				{
					$flagCheck = 0;
					$this->Flash->error('Customer code already exists.');
				}
				
				if($flagCheck == 1)
				{
					
					$conn = ConnectionManager::get('default');
					
                    $slug = $this->getSlug($requestData['Users']['first_name'] . ' ' . time(), 'Users');
					
					$queryAdd = "INSERT INTO users 
					(
					`slug`,`user_type`,`customer_code`,
					`first_name`,`middle_name`,`phone`,
					`phone2`,`email_address`,`bill_to_street`,
					`bill_to_block`,`bill_to_city`,`bill_to_zip`,
					`bill_to_country`,`status`,`activation_status`,
					`created`,`modified`
					)
					VALUES
					(
					'".$slug."','School','".$data->customer_code."',
					'".$data->first_name."','".$data->middle_name."','".$data->phone."',
					'".$data->phone2."','".$data->email_address."','".$data->bill_to_street."',
					'".$data->bill_to_block."','".$data->bill_to_city."','".$data->bill_to_zip."',
					'".$data->bill_to_country."','1','0',
					'".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."'
					)
					";
					
					$stmt = $conn->execute($queryAdd);
					$this->Flash->success('School details added successfully. School admin need to verify account from front end.');
					$this->redirect(['controller' => 'users', 'action' => 'index']);
					
					/* if ($this->Users->save($data)) {
						$this->Flash->success('School details added successfully. School admin need to verify account from front end.');
						$this->redirect(['controller' => 'users', 'action' => 'index']);
					} */
				}
				
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
	public function edit($slug=null){
		$this->set('title', ADMIN_TITLE. 'Edit School');
		$this->viewBuilder()->setLayout('admin');
		
		$this->set('manageSchools', '1');
        $this->set('schoolList', '1');
	   
        if($slug){
            $userD = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $userD->id;
			$this->set('userD', $userD);
		}
		$users = $this->Users->get($uid);
		if ($this->request->is(['post', 'put'])) {
            $requestData = $this->request->getData();
            if(empty($requestData['Users']['password'])){
                unset($requestData['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $requestData);
			
			$flagCheck = 1;
			//$this->prx($data);
			if($data->email_address_old != $data->email_address)
			{
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
			}
			
			if(count($data->getErrors()) == 0 && $flagCheck == 1){
               
                if(isset($requestData['Users']['password']) && $requestData['Users']['password'] !=''){
                    $new_password = $requestData['Users']['password'];
                    unset($requestData['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
				
                if ($this->Users->save($data)) {
                    $this->Flash->success('School details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
                }
                
            }else{
               // $this->Flash->error('Please below listed errors.');
                $users->password = '';
            }
        }else{
             $users->password = '';
        }
        $this->set('users', $users);
    }
	
	
	
	/* Teacher Module */
	
    public function teachers() {
        $this->set('title', ADMIN_TITLE. 'Manage Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTeachers', '1');
        $this->set('teacherList', '1');
        
        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.user_type = 'Teacher_Parent')";
        
        if($this->request->is('post')){
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
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
            
                        if(isset($requestData['Users']['keyword']) && $requestData['Users']['keyword']!=''){
                            $keyword = trim($requestData['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0]!=''){
                $searchArr = $this->request->getParam('pass');
//                echo '<pre>';
//                print_r($searchArr);exit;
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
       // $this->paginate = ['contain'=>['Ethnicities','Companies','Educations'], 'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Users');
            $this->render('teachers');
        }
    }
    
    public function activateteacher($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateteacher/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivateteacher($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateteacher/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function archiveteacher($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Supervisors details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
	
	public function restoreteacher($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Supervisors details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
	
	public function addteacher_noneed() {
        $this->set('title', ADMIN_TITLE . 'Add Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTeachers', '1');
        $this->set('teacherAdd', '1');
		
		// to get schools list
		$schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
		$this->set('schoolsDD', $schoolsDD);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
            $requestData = $this->request->getData();
            $data = $this->Users->patchEntity($users, $requestData);
			
			$flagC = 1;
			
			// to check that this email not duplicate for one school
			$checkEmailS = $this->Users->find()->where(['Users.email_address' => $data->email_address,'Users.school_id' => $data->school_id])->first();
			
			// to check that this email already exists in admin
			$checkEmailAdmin = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
			if($checkEmailAdmin || $checkEmailS)
			{
				$flagC = 0;
				$this->Flash->error('Email already exists.');
			}
			
            if (count($data->getErrors()) == 0 && $flagC == 1) {

                $slug = $this->getSlug($requestData['Users']['first_name'] . ' ' . time(), 'Users');
				$data->slug = $slug;
				
				$data->user_type = 'Teacher_Parent';
				$data->status = 1;
				$data->activation_status = 0;
				$data->created = date('Y-m-d H:i:s');
				$data->modified = date('Y-m-d H:i:s');
				if ($this->Users->save($data)) {
					$this->Flash->success('Supervisors details added successfully.');
					$this->redirect(['controller' => 'users', 'action' => 'teachers']);
				}
				
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
	public function editteacher($slug=null){
		$this->set('title', ADMIN_TITLE. 'Edit Supervisors');
		$this->viewBuilder()->setLayout('admin');
		
		$this->set('manageTeachers', '1');
        $this->set('teacherList', '1');
		
		// to get schools list
		$schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
		$this->set('schoolsDD', $schoolsDD);
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
	   
		if($slug){
            $users1 = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $users1->id;
		}
		$users = $this->Users->get($uid);
		if ($this->request->is(['post', 'put'])) {
            $requestData = $this->request->getData();
            if(empty($requestData['Users']['password'])){
                unset($requestData['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $requestData);
			
            if(count($data->getErrors()) == 0){
               
                if(isset($requestData['Users']['password']) && $requestData['Users']['password'] !=''){
                    $new_password = $requestData['Users']['password'];
                    unset($requestData['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
				
                if ($this->Users->save($data)) {
                    $this->Flash->success('Supervisors details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'teachers']);
                }
                
            }else{
               // $this->Flash->error('Please below listed errors.');
                $users->password = '';
            }
        }else{
             $users->password = '';
        }
        $this->set('users', $users);
    }
	
	
	/* Student module starts */
	
	public function students() {
        $this->set('title', ADMIN_TITLE. 'Manage Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageStudents', '1');
        $this->set('studentList', '1');
        
        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.user_type = 'Student')";
        
        if($this->request->is('post')){
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
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
            
                        if(isset($requestData['Users']['keyword']) && $requestData['Users']['keyword']!=''){
                            $keyword = trim($requestData['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0]!=''){
                $searchArr = $this->request->getParam('pass');
//                echo '<pre>';
//                print_r($searchArr);exit;
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.middle_name LIKE '%".addslashes($keyword)."%' OR  Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
       // $this->paginate = ['contain'=>['Ethnicities','Companies','Educations'], 'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Users');
            $this->render('students');
        }
    }
    
    public function activatestudent($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateparent/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivatestudent($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateparent/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function archivestudent($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Student details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
	
	public function restorestudent($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Students details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
    
	public function editstudent($slug=null){
		$this->set('title', ADMIN_TITLE. 'Edit Student');
		$this->viewBuilder()->setLayout('admin');
		
		$this->set('manageStudents', '1');
        $this->set('studentList', '1');
		
		global $genderDD;
		$this->set('genderDD', $genderDD);
		
		global $birthYearDD;
		$this->set('birthYearDD', $birthYearDD);
		
		// to get schools list
		$schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
		$this->set('schoolsDD', $schoolsDD);
	   
		if($slug){
            $users1 = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $users1->id;
		}
		$users = $this->Users->get($uid);
		if ($this->request->is(['post', 'put'])) {
            $requestData = $this->request->getData();
            if(empty($requestData['Users']['password'])){
                unset($requestData['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $requestData, ['validate' => 'edit']);
			
            if(count($data->getErrors()) == 0){
               
                if(isset($requestData['Users']['password']) && $requestData['Users']['password'] !=''){
                    $new_password = $requestData['Users']['password'];
                    unset($requestData['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
				
                if ($this->Users->save($data)) {
                    $this->Flash->success('Student details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'students']);
                }
                
            }else{
               // $this->Flash->error('Please below listed errors.');
                $users->password = '';
            }
        }else{
             $users->password = '';
        }
        $this->set('users', $users);
    }
	
	
	/* CSV Functions */
	public function downloadcsvformat() {
        
		$filename = "schools_standard_csv_format.csv";
		$dataArray = array();
		
		//7732	TEMP001	A B Customer	38815777		mikaelawaqa@accelerate.edu.au 	8-12 Business Drive		Narangba	4504	AU

		$dataArray[] = array('7732','TEMP001','A B Customer','38815777','','mikaelawaqa@accelerate.edu.au','8-12 Business Drive','','Narangba','4504','AU');
		$dataArray[] = array('7733','TEMP002','X Y Customer','34532456','','abc@accelerate.edu.au','12-13 Business Drive','','Narangba','4504','AU');
		
		$delimiter = ",";
		 
		// Create a file pointer 
		$f = fopen('php://memory', 'w'); 
		 
		// Set column headers
		//$fields = array('CUSTOMER CODE','SCHOOL/HSSP NAME','CONTACT PERSON','EMAIL ADDRESS','PASSWORD','PHONE');
		$fields = array('#','BP Code','BP Name','Telephone 1','Telephone 2','E-Mail','Bill-to Street','Bill-to Block','Bill-to City','Bill-to Zip Code','Bill-to Country');
		fputcsv($f, $fields, $delimiter); 
		 
		// Output each row of the data, format line as csv and write to file pointer 
		foreach($dataArray as $datarecord)
		{	
			fputcsv($f, $datarecord, $delimiter);
		} 
		 
		// Move back to beginning of file 
		fseek($f, 0); 
		 
		// Set headers to download file rather than displayed 
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="' . $filename . '";'); 
		 
		//output all remaining data on a file pointer 
		fpassthru($f); 
		
		exit;
    }
	
	public function csvimport() {
        $this->set('title', ADMIN_TITLE . 'Import CSV');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolImport', '1');
		
        $users = $this->Users->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
            $requestData = $this->request->getData();
            $data = $this->Users->patchEntity($users, $requestData);
            if (count($data->getErrors()) == 0) {

				if(!empty($requestData['Users']['csv_file']['name'])){
                    $specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
                    $toReplace = "-";
                    $requestData['Users']['csv_file']['name'] = str_replace($specialCharacters, $toReplace, $requestData['Users']['csv_file']['name']);
                    $imageArray = $requestData['Users']['csv_file'];
                    $returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_SCHOOLS_CSV_PATH);                     
                     
                    $csv_file_system_name 	=  $returnedUploadImageArray[0];
					$csv_original_name 		= 	$requestData['Users']['csv_file']['name'];
					
					$filename = UPLOAD_SCHOOLS_CSV_PATH.$csv_file_system_name;
					
					$cntrTotalRecords 		= 0;
					$cntrCustCodeExists 	= 0;
					$cntrCustEmailExists 	= 0;
					$cntrRecordsImport 		= 0;
					
					$file = fopen($filename, "r");
					while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
					{
						if($cntrTotalRecords>0)
						{	
							//$this->prx($getData);
							
							$flagCheck = 1;
							
							// to check that this customer code already exists
							$checkCCode = $this->Users->find()->where(['Users.customer_code' => $getData[1]])->first();
							if($checkCCode)
							{
								$cntrCustCodeExists++;
								$flagCheck = 0;
							}
							
							if($flagCheck == 1)
							{
								// to check that this email already exists
								$checkEmail = $this->Users->find()->where(['Users.email_address' => $getData[5]])->first();
								if($checkEmail)
								{
									$cntrCustEmailExists++;
									$flagCheck = 0;
								}
							}
							
							if($flagCheck == 1)
							{
								// import records here
								$users = $this->Users->newEntity();
								$dataU = $this->Users->patchEntity($users, array());
								
								$dataU->slug 							= $this->getSlug($getData[2] . ' ' . time(), 'Users');
								$dataU->user_type						= "School";
								$dataU->status							= 1;
								$dataU->activation_status				= 0;
								
								$dataU->customer_hash_from_csv			= $getData[0];
								$dataU->customer_code					= $getData[1];
								$dataU->first_name						= $getData[2];
								$dataU->phone							= $getData[3];
								$dataU->phone2							= $getData[4];
								$dataU->email_address					= $getData[5];
								$dataU->bill_to_street					= $getData[6];
								$dataU->bill_to_block					= $getData[7];
								$dataU->bill_to_city					= $getData[8];
								$dataU->bill_to_zip						= $getData[9];
								$dataU->bill_to_country					= $getData[10];
								
								$user_password = rand(1000,33455678899000);
								$salt = uniqid(mt_rand(), true);
								$dataU->password = crypt($new_password, '$2a$07$' . $salt . '$');
								$dataU->created 				= date('Y-m-d H:i:s');

								$resultU = $this->Users->save($dataU);
								
								$cntrRecordsImport++;
							}
						}
						
						$cntrTotalRecords++;
					}
					
					//echo $csv_file_system_name;exit;
					
					// remove csv
					@unlink($filename);
					
					$this->Flash->success("Total records in csv file = ".($cntrTotalRecords-1));
					if($cntrCustCodeExists > 0)
					{
						$this->Flash->error("Customer code already exists = ".$cntrCustCodeExists);
					}
					
					if($cntrCustEmailExists > 0)
					{
						$this->Flash->error("Email already exists = ".$cntrCustEmailExists);
					}
					
					$this->Flash->success("Total records import = ".$cntrRecordsImport);
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
					 
                }
				else
				{
                    $this->Flash->error('CSV import process failed.');
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
                }
            }
        }
        $this->set('users', $users);
    }
	
	
	/*Judges Module */
	
	public function judges() {
        $this->set('title', ADMIN_TITLE. ' Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageJudges', '1');
        $this->set('activeJudges', '1');
        
        $separator = array();
        $condition = array();
		
		$condition[] = "(Users.activation_status = '1' AND (Users.status = '1' OR Users.status = '2'))";
		$condition[] = "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))";
        
        if($this->request->is('post')){
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
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
            
                        if(isset($requestData['Users']['keyword']) && $requestData['Users']['keyword']!=''){
                            $keyword = trim($requestData['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0]!=''){
                $searchArr = $this->request->getParam('pass');
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Users');
            $this->render('judges');
        }
    }
	
	public function pendingjudges() {
        $this->set('title', ADMIN_TITLE. ' Pending Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageJudges', '1');
        $this->set('pendingJudges', '1');
        
        $separator = array();
        $condition = array();
		
		$condition[] = "( 
			(Users.user_type = 'Judge' AND Users.status = '0' AND Users.activation_status = '1')
			OR 
			(Users.user_type = 'Teacher_Parent' AND Users.is_judge = '2' AND Users.status = '1' AND Users.activation_status = '1') 
			)";
        
        if($this->request->is('post')){
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
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
            
                        if(isset($requestData['Users']['keyword']) && $requestData['Users']['keyword']!=''){
                            $keyword = trim($requestData['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass')[0]) && $this->request->getParam('pass')[0]!=''){
                $searchArr = $this->request->getParam('pass');
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Users');
            $this->render('pendingjudges');
        }
    }
	
	public function approvejudge($slug=null) {
        
		$judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.status' => 0])->first();
		if($judgeD)
		{
			$this->Users->updateAll(['status' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to judge that account is active
			$emailId = $judgeD->email_address;
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '13'])->first();
			
			$LINK = HTTP_PATH."/users/login/";

			$toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!LINK!]');
			$fromRepArray = array(SITE_TITLE,$judgeD->first_name,$LINK);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Judge details approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
	
	public function rejectjudge($slug=null) {
        
		$judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.status' => 0])->first();
		if($judgeD)
		{
			$this->Users->updateAll(['status' => '3','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sending email to judge that account is rejected
			$emailId = $judgeD->email_address;
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '14'])->first();

			$toRepArray = array('[!SITE_TITLE!]','[!first_name!]');
			$fromRepArray = array(SITE_TITLE,$judgeD->first_name);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Judge details approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
	
	public function archivejudge($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Judge details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'judges']);
    }
	
	public function restorejudge($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Judge details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'judges']);
    }
	
	public function approvesupervisorasjudge($slug=null) {
        
		$judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.is_judge' => 2])->first();
		if($judgeD)
		{
			$this->Users->updateAll(['is_judge' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to judge that account is active
			$emailId = $judgeD->email_address;
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '16'])->first();
			
			$LINK = HTTP_PATH."/users/login/";

			$toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!LINK!]');
			$fromRepArray = array(SITE_TITLE,$judgeD->first_name,$LINK);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Supervisor account successfully approvedas judge.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
	
	public function rejectsupervisorasjudge($slug=null) {
        
		$judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.is_judge' => 2])->first();
		if($judgeD)
		{
			$this->Users->updateAll(['is_judge' => '3','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sending email to judge that account is rejected
			$emailId = $judgeD->email_address;
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '17'])->first();

			$toRepArray = array('[!SITE_TITLE!]','[!first_name!]');
			$fromRepArray = array(SITE_TITLE,$judgeD->first_name);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			echo $messageToSend; exit;
			
            $this->sendLegacyHtmlEmail($emailId, $subjectToSend, $messageToSend, [HEADERS_FROM_EMAIL => HEADERS_FROM_NAME], ACCOUNTS_TEAM_ANOTHER_EMAIL);
			
			$this->Flash->success('Judge details approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
	
}




?>