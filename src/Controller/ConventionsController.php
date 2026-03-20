<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\I18n;

class ConventionsController extends AppController {

    public function initialize() {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        // Load Files model
		 
		$this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		

        // Set the layout
        // $this->layout = 'frontend';
    } 

}

?>
