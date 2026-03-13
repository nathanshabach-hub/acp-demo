<?php
namespace App\Controller\Admin;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class PagesController extends AppController{

    public $paginate = ['limit' => 50,'order' => ['Pages.name' => 'asc']];
    var $components = array('RequestHandler', 'PImage', 'PImageTest');
    //public $helpers = array('Javascript', 'Ajax');
   
    public function initialize(){
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->params['action'];
        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                 $this->redirect(['controller'=>'admins', 'action' => 'login']);
            }
        }
    }
    
    public function index() {
        $this->set('title', ADMIN_TITLE. 'Manage Content');
        $this->viewBuilder()->layout('admin');
        $this->set('staticPages', '1');
        $this->set('pageList', '1');
        
        $separator = array();
        $condition = array();
        
        $separator = implode("/", $separator);
        $this->set('separator',$separator); 
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Pages.static_page_title' => 'ASC']];
        $this->set('pages', $this->paginate($this->Pages));
        if($this->request->is("ajax")){
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Pages');
            $this->render('index');
        }
    }
  
   public function edit($slug=null){  
		$this->set('title', ADMIN_TITLE. 'Edit Page');
		$this->viewBuilder()->layout('admin');
       
		$this->set('staticPages', '1');
        $this->set('pageList', '1');
	   
		if($slug){
            $pages1 = $this->Pages->find()->where(['Pages.slug' => $slug])->first();
            $uid = $pages1->id;
		}
		$pages = $this->Pages->get($uid);
		if ($this->request->is(['post', 'put'])) {
            $data = $this->Pages->patchEntity($pages, $this->request->data, ['validate' => 'edit']);
            if(count($data->errors()) == 0){
                if ($this->Pages->save($data)) {
                    $this->Flash->success('Page details updated successfully.');
                    $this->redirect(['controller'=>'pages', 'action' => 'index']);
                }
            }else{
               // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('pages', $pages); 
    }
 
    public function pageimages($slug=null){  
        $imageArray = $_FILES['upload'];
        $returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_PAGES_IMAGE_PATH);
        echo "<span style='font-size: 16px;  font-weight: bold;'>Copy below URL and Paste in next screen:</span> <span style='float: left; font-size: 14px; margin: 7px 0 0; width: 100%;'>" . DISPLAY_PAGES_IMAGE_PATH . $returnedUploadImageArray[0].'</span>'; exit;
        if (file_exists(UPLOAD_PAGES_IMAGE_PATH . $_FILES["upload"]["name"])){
         echo $_FILES["upload"]["name"] . " already exists. ";
        }else{
         move_uploaded_file($_FILES["upload"]["tmp_name"],
         UPLOAD_PAGES_IMAGE_PATH . $_FILES["upload"]["name"]);
         echo "Coty below URL and Paste in next screen: " . DISPLAY_PAGES_IMAGE_PATH . $_FILES["upload"]["name"];
        }
        exit;
    }
    
}




?>