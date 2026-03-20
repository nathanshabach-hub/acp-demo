<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class BooksController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Books.name' => 'asc']];
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
		
		$this->loadModel("Conventionbooks");
		$this->loadModel("Conventionbookevents");
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Books');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageBooks', '1');
        $this->set('bookList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Books.parent_id' => 0);

        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            if (isset($requestData['action'])) {
                $idList = implode(',', $requestData['chkRecordId']);
                $action = $requestData['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Books->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Books->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Books->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($requestData['Books']['keyword']) && $requestData['Books']['keyword'] != '') {
                $keyword = trim($requestData['Books']['keyword']);
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
            $condition[] = "(Books.book_name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Books.id' => 'ASC']];
        $this->set('books', $this->paginate($this->Books));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Books');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Book');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageBooks', '1');
        $this->set('bookAdd', '1');
		
        $books = $this->Books->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$flagC = 1;
			$requestData = $this->request->getData();
			
            $data = $this->Books->patchEntity($books, $requestData, ['validate' => 'add']);
            if (count($data->getErrors()) == 0 && $flagC == 1) {

				$slug = $this->getSlug($requestData['Books']['book_name'] . ' ' . time(), 'Books');
				
                $data->slug = $slug;
                $data->status = 1;
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Books->save($data)) {
                    $this->Flash->success('Book added successfully.');
                    $this->redirect(['controller' => 'books', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('books', $books);
    }

}

?>
