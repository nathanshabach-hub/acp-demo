<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class BooksController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Books.name' => 'asc']];
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
		
		$this->loadModel("Conventionbooks");
		$this->loadModel("Conventionbookevents");
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Books');
        $this->viewBuilder()->layout('admin');
        $this->set('manageBooks', '1');
        $this->set('bookList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Books.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->data['action'])) {
                $idList = implode(',', $this->request->data['chkRecordId']);
                $action = $this->request->data['action'];
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

            if (isset($this->request->data['Books']['keyword']) && $this->request->data['Books']['keyword'] != '') {
                $keyword = trim($this->request->data['Books']['keyword']);
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
            $condition[] = "(Books.book_name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Books.id' => 'ASC']];
        $this->set('books', $this->paginate($this->Books));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->layout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Books');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Book');
        $this->viewBuilder()->layout('admin');
		
        $this->set('manageBooks', '1');
        $this->set('bookAdd', '1');
		
        $books = $this->Books->newEntity();
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->data);
			
			$flagC = 1;
			
            $data = $this->Books->patchEntity($books, $this->request->data, ['validate' => 'add']);
            if (count($data->errors()) == 0 && $flagC == 1) {

				$slug = $this->getSlug($this->request->data['Books']['book_name'] . ' ' . time(), 'Books');
				
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
