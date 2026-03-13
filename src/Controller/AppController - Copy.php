<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use Cake\Mailer\Email;
use Cake\Controller\Component\FlashComponent;
use Cake\Datasource\ConnectionManager;


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller{
    
    public function initialize(){
    parent::initialize();
		$this->loadModel('Timezones');
		$this->loadModel('Eventtypes');
		$this->loadModel('Companies');
		$this->loadModel('Users');
		$this->loadModel('Admins');
    }
	
	public function beforeRender(Event $event) {
        parent::beforeRender($event);
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        $this->set('adminInfo', $adminInfo);
		//$this->prx($adminInfo);
		
        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		
		// to check if school admin is logged in, then show header dropdown
		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		
		if($user_id>0 && $user_type == "School")
		{
			// to get lists of registered conventions for this season
			$season_id = $this->getCurrentSeason();
			$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
			
			$conventionIDSHeader = array();
			$conventionIDSHeader[] 	= 0;
			
			$userConvHeaderDD = array();
			
			$conventionregistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id,'Conventionregistrations.season_year' => $seasonD->season_year])->order(['Conventionregistrations.id' => 'ASC'])->all();
			foreach($conventionregistrations as $convreg)
			{
				if(!in_array($convreg->convention_id,(array)$conventionIDSHeader))
				{
					$conventionIDSHeader[] 	= $convreg->convention_id;
				}
			}
			//$this->prx($conventionIDSHeader);
			
			$conventionIDSHeaderImploded = implode(",",$conventionIDSHeader);
			
			// to get conventions
			$condConventionH = array();
			$condConventionH[] = "(Conventions.id IN ($conventionIDSHeaderImploded))";
			//$condConventionH[] = "(Conventions.status  = '1')";
			$userConvHeaderDD = $this->Conventions->find()->where($condConventionH)->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
			$this->set('userConvHeaderDD', $userConvHeaderDD);
		}

	}
    
//  public function beforeFilter(Event $event) {
//        $this->set('loggedIn', $this->Auth->loggedIn());
//    }
    
    public function getAdminInfo() {
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        return $adminInfo;
	}
	
	public function getCurrentSeason(){
        $currYear = date("Y");
		$seasonD = $this->Seasons->find()->where(['Seasons.season_year' => $currYear])->first();
		return $seasonD->id;
    }
	
	public function isAuthorized($user){
        // Admin can access every action
        if (isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }
        return false;
    }
	
	public function verifyRecatpcha($aData)
	{
		//echo 'ddddddddd<pre>';pr($aData);exit;
		if(!$aData)
		{
			return false;
		} 
		if(isset($aData['g-recaptcha-response']) && !empty($aData['g-recaptcha-response']))
		{
			$recaptcha_secret = SECRETKEY;
			$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha_secret."&response=".$aData['g-recaptcha-response']; 
			$response = json_decode(@file_get_contents($url));   

			if($response->success == true)
			{
				return true;
			}
			else
			{
				return false; 
			} 
		}
		else
		{
			return false;
		}
	}
    
    
	// general login check for user
	function userLoginCheck() {
//        $returnUrl = $this->request->params->url;
        $returnUrl = $this->request->url;
        $userid =$this->request->session()->read("user_id");
        $this->loadModel("Users");
        $isExists = $this->Users->find()->where(['Users.id' => $userid, 'Users.activation_status' => 1, 'Users.status' => 1])->select(['id'])->first();
        if (empty($isExists)) {
            $msgString = "Please Login"; 
            $this->request->session()->delete('user_id');
            $this->request->session()->delete('email_address');
            $this->request->session()->delete('user_type');
            $this->request->session()->delete('last_login');
			
            $this->Flash->error($msgString);
            $this->request->session()->write("returnUrl", $returnUrl);
            $this->redirect('/users/login');
        }
    }
	
	// to check subscribers type login
	function schoolAdminLoginCheck() {  
		if($this->request->session()->read("user_type") != "School")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	// to check individuals user type login
	function teacherLoginCheck() {  
		if($this->request->session()->read("user_type") != "Teacher_Parent")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	function multiLoginCheck($usersTypesList=null) { 
        $user_type =$this->request->session()->read("user_type");
		//echo $user_type;exit;
        if (!in_array($user_type,(array)$usersTypesList)) {
            $msgString = "Unauthorize access !!!"; 
            $this->Flash->error($msgString);
            $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
        }
    }
	
	public function getDateRangeInTwoDates($first, $last){
		
        $step = '+1 day';
		$output_format = 'Y-m-d';
		
		$dates = array();
		$current = strtotime($first);
		$last = strtotime($last);

		while( $current <= $last ) {

			$dates[] = date($output_format, $current);
			$current = strtotime($step, $current);
		}

		return $dates;
    }
	
	public function generateInvoiceNumberWithFormat($credit_id = null) {
        $invoiceNumberG = '';
		
		if($credit_id)
		{
			$invoiceNumberG = str_pad($credit_id, 5, '0', STR_PAD_LEFT);
		}
		
		return "LXL-X-".$invoiceNumberG;
    }
    
    public function getSlug($str, $table='Admins'){
        $slug = Inflector::slug($str);
        $slug = strtolower($slug);
        //$slug = 'dinesh-dhaker';
        $isRecord =  $this->$table->find()->where([$table . '.slug like' => $slug . '%'])->order([$table.'.id'=>'DESC'])->first();
        
        if($isRecord){
            $oldslug = explode('-', $isRecord->slug);
            $last = array_pop($oldslug);
            $slug = $last;
            if(is_numeric($last)){
                $last = $last + 1;
                $slug = $slug.'-'.$last;
            }else{
               $slug = $slug.'-'.$last.'-1'; 
            }
            
            return $slug.time();
        }else{
            return $slug;
        }
    }
	
	public function getLatLng($location = "") {
        //$address = urldecode($address);
        //$address = str_replace(" ", "+", $address);
        $address = urlencode($location);
        // echo $address;exit;
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&sensor=false&charset=utf-8&key=".GOOGLE_MAP_KEY;
		
		//echo $url;exit;

		$latLngArr = array();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        $jsonData = json_decode($output, true);
		
		//$this->prx($jsonData);

        $status = $jsonData['status'];


        if ($status == "OK") {
            $latLngArr[] = $jsonData['results']['0']['geometry']['location']['lat'];
            $latLngArr[] = $jsonData['results']['0']['geometry']['location']['lng'];
            
        }
		
		// set static
		//$latLngArr = array('50.9962609','-114.064511');
		
		return $latLngArr;
        //  pr($status);exit;
    }
	
	// for apis
	public function requestAuthentication($mth='GET', $checkToken=0) {
        $reqMethod = $_SERVER["REQUEST_METHOD"];
        if($reqMethod != $mth){
            echo $this->errorOutputResult('bad request.');
            exit;
        }
        
        $headers = apache_request_headers();
        $apiKey = $headers['key'];
        if(!$apiKey){
            $apiKey = $headers['Key'];
        }
        
        if ($apiKey != API_KEY) {
            echo $this->errorOutputResult('Unauthorized Access. Access key does not match.');
            exit;
        }
        
        if($checkToken == 1){
            $token = $headers['token'];
            $tokenData = $this->verifyToken($token);
            $tokenData = (array)$tokenData;
            
            if(isset($tokenData['error']) && $tokenData['error'] == 1){
                echo $this->errorOutputResult($tokenData['msg']);
                exit;
            }
            return $tokenData;
        }
        if($checkToken == 0){
            
            if(isset($headers['token'])){
                $token = $headers['token'];
                $tokenData = $this->verifyToken($token);
                $tokenData = (array)$tokenData;

                if(isset($tokenData['error']) && $tokenData['error'] == 1){
                    echo $this->errorOutputResult($tokenData['msg']);
                    exit;
                }
                return $tokenData;
            }
            
        }
        return;
    }
	
	public function tutorSubjectsList($user_id=null) {
		
		$arrrTutorSubjects = array();
		
		if($user_id>0)
		{
			$tutorSubjects = $this->Tutorsubjects->find()->where(['Tutorsubjects.user_id' => $user_id])->all();
			
			foreach($tutorSubjects as $subject)
			{
				$arrrTutorSubjects[] = $subject->subject_id;
			}
		}
		
		return $arrrTutorSubjects;		
	}
    
    public function checkToken($mth='GET') {
       echo  $reqMethod = $_SERVER["REQUEST_METHOD"]; exit;
        if($reqMethod != $mth){
            echo $this->errorOutputResult('bad request method.');
            exit;
        }
        
        $headers = apache_request_headers();
        echo $headers['token']; 
        echo $headers['key']; exit;
        
        
        echo $reqMethod;exit;
        
//        switch ($reqMethod) {
//            case 'GET':
//              $apiKey = $_GET['key'];break;
//            case 'PUT':
//              $apiKey = $_PUT['key'];break;
//            case 'POST':
//              $apiKey = $_POST['key'];break;
//            case 'DELETE':
//              $apiKey = $_DELETE['key'];break;
//        }
        
        if ($apiKey != API_KEY) {
            echo $this->errorOutputResult('Unauthorized Access.');
            exit;
        }
        return;
    }
    
    
    public function errorOutputResult($errormsg = null) {
        return '{"response_status":"error","response_msg":"' . $errormsg . '","response_data":""}';
    }
    
    public function successOutputResult($successmsg = null, $response_data='') {
        $response_data = str_replace(":null,", ':"",', $response_data);
        $response_data = str_replace(":null", ':""', $response_data);
        return '{"response_status":"success","response_msg":"' . $successmsg . '","response_data":'.$response_data.'}';
    }
    
    public function successOutput($successmsg = null) {
        return '{"response_status":"success","response_msg":"' . $successmsg . '","response_data":""}';
    }
    
    
    public function errorOutput($errormsg = null) {
        return '{"message":"error","description":"' . $errormsg . '"}';
    }
    
    public function deActivateOutput($errormsg = null) {
        return '{"message":"Deactivated","description":"' . $errormsg . '"}';
    }

    

    public function output($output = null) {
        $output = str_replace(":null,", ':"",', $output);
        return '{"message":"success","description":' . $output . '}';
    }
	
	function prx($arrV = NULL)
	{
		echo '<pre>';
		print_r($arrV);
		echo '</pre>';
		exit;
	}
    
    
            
    
}
