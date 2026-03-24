<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class NametagsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventionregistrations.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
		$this->loadModel('Conventions');
		$this->loadModel('Settings');
		$this->loadModel('Seasons');
		$this->loadModel('Emailtemplates');
		$this->loadModel('Conventionregistrationstudents');
		$this->loadModel('Conventionregistrationteachers');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionseasons');
		$this->loadModel('Visitors');
    }

    public function students() {

        $this->set('title', ADMIN_TITLE . 'Name Tags - Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('nameTags', '1');
        $this->set('nameTagsStudents', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Conventionregistrationstudents.convention_id = '".$convSeasD->convention_id."')";
			$condition[] = "(Conventionregistrationstudents.season_id = '".$convSeasD->season_id."')";
			$condition[] = "(Conventionregistrationstudents.season_year = '".$convSeasD->season_year."')";
		}
		else
		{
			$this->Flash->error('Please choose convention season from top navigation bar.');
            $this->redirect(['controller' => 'admins','action' => 'dashboard']);
		}

		$nametags = $this->Conventionregistrationstudents->find()->where($condition)->contain(['Students','Users'])->order(['Conventionregistrationstudents.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }

	public function printnametagsstudents() {

        $this->viewBuilder()->setLayout('');

		$condition = array();

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Conventionregistrationstudents.convention_id = '".$convSeasD->convention_id."')";
			$condition[] = "(Conventionregistrationstudents.season_id = '".$convSeasD->season_id."')";
			$condition[] = "(Conventionregistrationstudents.season_year = '".$convSeasD->season_year."')";
		}


		$nametags = $this->Conventionregistrationstudents->find()->where($condition)->contain(['Students','Users'])->order(['Conventionregistrationstudents.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }


	/* Sponsors */
	public function sponsors() {

        $this->set('title', ADMIN_TITLE . 'Name Tags - Sponsors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('nameTags', '1');
        $this->set('nameTagsSponsors', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Conventionregistrationteachers.convention_id = '".$convSeasD->convention_id."')";
			$condition[] = "(Conventionregistrationteachers.season_id = '".$convSeasD->season_id."')";
			$condition[] = "(Conventionregistrationteachers.season_year = '".$convSeasD->season_year."')";
		}
		else
		{
			$this->Flash->error('Please choose convention season from top navigation bar.');
            $this->redirect(['controller' => 'admins','action' => 'dashboard']);
		}

		$nametags = $this->Conventionregistrationteachers->find()->where($condition)->contain(['Teachers','Users'])->order(['Conventionregistrationteachers.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }

	public function printnametagssponsors() {

        $this->viewBuilder()->setLayout('');

		$condition = array();

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Conventionregistrationteachers.convention_id = '".$convSeasD->convention_id."')";
			$condition[] = "(Conventionregistrationteachers.season_id = '".$convSeasD->season_id."')";
			$condition[] = "(Conventionregistrationteachers.season_year = '".$convSeasD->season_year."')";
		}
		else
		{
			$this->Flash->error('Please choose convention season from top navigation bar.');
            $this->redirect(['controller' => 'admins','action' => 'dashboard']);
		}

		$nametags = $this->Conventionregistrationteachers->find()->where($condition)->contain(['Teachers','Users'])->order(['Conventionregistrationteachers.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }


	/* Visitors */
	public function visitors() {

        $this->set('title', ADMIN_TITLE . 'Name Tags - Visitors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('nameTags', '1');
        $this->set('nameTagsVisitors', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Visitors.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		else
		{
			$this->Flash->error('Please choose convention season from top navigation bar.');
            $this->redirect(['controller' => 'admins','action' => 'dashboard']);
		}

		$nametags = $this->Visitors->find()->where($condition)->order(['Visitors.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }

	public function addvisitor() {
        $this->set('title', ADMIN_TITLE . 'Add New Visitor');
        $this->viewBuilder()->setLayout('admin');

        $this->set('nameTags', '1');
        $this->set('nameTagsVisitors', '1');

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Visitors.conventionseason_id = '".$sess_admin_header_season_id."')";
		}

        $visitors = $this->Visitors->newEntity();
        if ($this->request->is('post')) {

			//$this->prx($this->request->getData());

            $data = $this->Visitors->patchEntity($visitors, $this->request->getData());
            if (count($data->getErrors()) == 0) {

                $data->conventionseason_id 			= $sess_admin_header_season_id;
                $data->convention_id 				= $convSeasD->convention_id;
                $data->season_id 					= $convSeasD->season_id;
                $data->season_year 					= $convSeasD->season_year;

                $data->created 					= date('Y-m-d H:i:s');
                $data->modified 				= NULL;
                if ($this->Visitors->save($data)) {
                    $this->Flash->success('Visitor added successfully.');
                    $this->redirect(['controller' => 'nametags', 'action' => 'visitors']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('visitors', $visitors);
    }

    public function editvisitor($id = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Visitor');
        $this->viewBuilder()->setLayout('admin');

		$this->set('nameTags', '1');
        $this->set('nameTagsVisitors', '1');

        if ($id) {
            $categories1 = $this->Visitors->find()->where(['Visitors.id' => $id])->first();
            $uid = $categories1->id;
        }

        $visitors = $this->Visitors->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Visitors->patchEntity($visitors, $this->request->getData());

            if (count($data->getErrors()) == 0) {

				$data->modified = date("Y-m-d H:i:s");
				//$this->prx($data);
                if ($this->Visitors->save($data)) {
                    $this->Flash->success('Visitor details updated successfully.');
                    $this->redirect(['controller' => 'nametags', 'action' => 'visitors']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('visitors', $visitors);
    }

	public function deletevisitor($id = null) {

        // to chek if visitor exists
		if($id)
		{
			// to get details of division
			$visitorD = $this->Visitors->find()->where(['Visitors.id' => $id])->first();

			if($visitorD)
			{
				$this->Visitors->deleteAll(["id" => $id]);
				$this->Flash->success('Visitor deleted successfully.');
			}
			else
			{
				$this->Flash->error('Visitor not found.');
			}
		}
		else
		{
			$this->Flash->error('Invalid details.');
		}

        $this->redirect(['controller' => 'nametags', 'action' => 'visitors']);
    }

	public function printnametagsvisitors() {

        $this->viewBuilder()->setLayout('');

		$condition = array();

		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// to get details of convention Season
			$convSeasD = $this->Conventionseasons->find()->where(["Conventionseasons.id"=>$sess_admin_header_season_id])->contain(['Conventions'])->first();

			$this->set('convSeasD', $convSeasD);

			$condition[] = "(Visitors.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		else
		{
			$this->Flash->error('Please choose convention season from top navigation bar.');
            $this->redirect(['controller' => 'admins','action' => 'dashboard']);
		}

		$nametags = $this->Visitors->find()->where($condition)->order(['Visitors.id' => 'DESC'])->all();
		$this->set('nametags', $nametags);

		//$this->prx($nametags);
    }



}

?>
