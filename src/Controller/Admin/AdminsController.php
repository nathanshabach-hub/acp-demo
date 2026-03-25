<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;

class AdminsController extends AppController {

    public $paginate = ['limit' => 1];
    public $components = ['RequestHandler', 'PImage'];

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
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
        $this->loadModel("Crstudentevents");
    }

    public function login() {
        $this->set('title', ADMIN_TITLE . 'Admin Login');
        $this->viewBuilder()->setLayout('admin_login');

        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($loggedAdminId) {
            $this->redirect(['action' => 'dashboard']);
        }

        // echo Configure::version(); exit;

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            $admin = $this->Admins->patchEntity($admin, $requestData);
            if (count($admin->getErrors()) == 0) {
                $userName = $requestData['Admins']['username'];
                $password = $requestData['Admins']['password'];
                $adminInfo = $this->Admins->find()->where(['Admins.username' => $userName])->first();
                if ($adminInfo) {
                    if ($adminInfo->status == 0) {
                        $this->Flash->error('Your account got temporary disabled.');
                    } elseif (!empty($adminInfo) && crypt($password, $adminInfo->password) == $adminInfo->password) {

                        if (isset($requestData['Admins']['remember']) && $requestData['Admins']['remember'] == '1') {
                            setcookie("admin_username", $userName, time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_password", $password, time() + 60 * 60 * 24 * 100, "/");
                        } else {
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                        }
                        $this->request->getSession()->write('admin_id', $adminInfo->id);
                        $this->request->getSession()->write('admin_username', $userName);
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
                $admin = $this->Admins->patchEntity($admin, [
                    'Admins' => [
                        'username' => $_COOKIE["admin_username"],
                        'password' => $_COOKIE["admin_password"],
                        'remember' => 1,
                    ],
                ]);
            }
        }
        $this->set('admin', $admin);
    }

    public function forgotPassword() {
        $this->set('title', ADMIN_TITLE . 'Forgot Password');
        $this->viewBuilder()->setLayout('admin_login');

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            $admin = $this->Admins->patchEntity($admin, $requestData, ['validate' => 'forgotPassword']);
            if (count($admin->getErrors()) == 0) {
                $email = $requestData['Admins']['email'];
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

                    $mailer = new Mailer('default');
                    if (method_exists($mailer, 'setEmailFormat')) {
                        $mailer->setEmailFormat('html');
                    }
                    if (method_exists($mailer, 'setTo')) {
                        $mailer->setTo($emailId);
                    }
                    if (method_exists($mailer, 'setFrom')) {
                        $mailer->setFrom([MAIL_FROM => SITE_TITLE]);
                    }
                    if (method_exists($mailer, 'setSubject')) {
                        $mailer->setSubject($subjectToSend);
                    }

                    if (method_exists($mailer, 'deliver')) {
                        $mailer->deliver($messageToSend);
                    } else {
                        $mailer->send($messageToSend);
                    }

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
		
        //$this->prx($this->request->getData());
        $requestData = $this->request->getData();
		
        $admin_header_season_id = $requestData['admin_header_season_id'];
		
		if($admin_header_season_id>0)
		{
			$convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$admin_header_season_id])->contain(['Conventions'])->first();
			
			if($convSD)
			{
				$this->request->getSession()->write('sess_admin_header_season_id', $admin_header_season_id);
				
				$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convSD->Conventions['slug']]);
			}
		}
		else
		{
			$this->request->getSession()->write('sess_admin_header_season_id', 0);
		}
		
		
		
		$this->redirect(['action' => 'dashboard']);
	}
	
    public function dashboard() {
        $this->set('title', ADMIN_TITLE . 'Admin Dashboard');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
		// to check if convention season selected from header
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
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

			$chartData = $this->getDashboardChartData((int)$convSD->id);
			$this->set('schedCategoryData', json_encode($chartData['schedCategoryData']));
			$this->set('totalScheduled', (int)$chartData['totalScheduled']);
			$this->set('totalUnscheduled', (int)$chartData['totalUnscheduled']);
			$this->set('dayNames', json_encode($chartData['dayNames']));
			$this->set('dayCountData', json_encode($chartData['dayCountData']));
			$this->set('topEventLabels', json_encode($chartData['topEventLabels']));
			$this->set('topEventCounts', json_encode($chartData['topEventCounts']));
			$this->set('unregisteredEventLabels', json_encode($chartData['unregisteredEventLabels']));
			$this->set('unregisteredEventFlags', json_encode($chartData['unregisteredEventFlags']));
			
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

    public function chartview($chartKey = null) {
        $this->set('title', ADMIN_TITLE . 'Dashboard Chart');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $sessAdminHeaderSeasonId = (int)$this->request->getSession()->read("sess_admin_header_season_id");
        $this->set('sess_admin_header_season_id', $sessAdminHeaderSeasonId);
        if ($sessAdminHeaderSeasonId <= 0) {
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" => $sessAdminHeaderSeasonId])->first();
        if (empty($convSD)) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $chartData = $this->getDashboardChartData((int)$convSD->id);
        $participantData = $this->getSeasonParticipantBreakdown($convSD);

        $chartKey = strtolower(trim((string)$chartKey));
        $chartOptions = [];
        $chartTitle = '';
        $chartSubtitle = 'Convention Season: ' . $convSD->slug;
        $emptyMessage = '';

        switch ($chartKey) {
            case 'scheduled-by-category':
                $chartTitle = 'Scheduled Events by Category';
                $chartOptions = [
                    'chart' => ['type' => 'column'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'xAxis' => ['categories' => ['Group Sequential', 'Individual Elimination', 'Group Elimination', 'Individual Sequential']],
                    'yAxis' => ['min' => 0, 'title' => ['text' => 'Scheduled Entries']],
                    'legend' => ['enabled' => false],
                    'series' => [[
                        'name' => 'Entries',
                        'data' => $chartData['schedCategoryData'],
                        'colorByPoint' => true,
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            case 'schedule-status':
                $chartTitle = 'Schedule Status';
                $chartOptions = [
                    'chart' => ['type' => 'pie'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'plotOptions' => ['pie' => ['innerSize' => '55%', 'dataLabels' => ['enabled' => true]]],
                    'series' => [[
                        'name' => 'Entries',
                        'data' => [
                            ['name' => 'Scheduled', 'y' => (int)$chartData['totalScheduled'], 'color' => '#00a65a'],
                            ['name' => 'Unscheduled', 'y' => (int)$chartData['totalUnscheduled'], 'color' => '#dd4b39'],
                        ],
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            case 'participants-breakdown':
                $chartTitle = 'Participants Breakdown';
                $chartOptions = [
                    'chart' => ['type' => 'bar'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'xAxis' => ['categories' => ['Students', 'Supervisors', 'Schools', 'Judges']],
                    'yAxis' => ['min' => 0, 'title' => ['text' => 'Count']],
                    'legend' => ['enabled' => false],
                    'series' => [[
                        'name' => 'Count',
                        'colorByPoint' => true,
                        'data' => [
                            (int)$participantData['total_students'],
                            (int)$participantData['total_teachers_parents'],
                            (int)$participantData['total_schools'],
                            (int)$participantData['total_judges'],
                        ],
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            case 'events-per-day':
                $chartTitle = 'Events per Convention Day';
                $chartOptions = [
                    'chart' => ['type' => 'column'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'xAxis' => ['categories' => $chartData['dayNames']],
                    'yAxis' => ['min' => 0, 'title' => ['text' => 'Scheduled Entries']],
                    'legend' => ['enabled' => false],
                    'series' => [[
                        'name' => 'Entries',
                        'data' => $chartData['dayCountData'],
                        'color' => '#3c8dbc',
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            case 'most-entered-events':
                $chartTitle = 'Most Entered Events';
                if (empty($chartData['topEventLabels'])) {
                    $emptyMessage = 'No event registrations found yet.';
                }
                $chartOptions = [
                    'chart' => ['type' => 'bar'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'xAxis' => ['categories' => $chartData['topEventLabels'], 'title' => ['text' => null]],
                    'yAxis' => ['min' => 0, 'title' => ['text' => 'Registrations']],
                    'legend' => ['enabled' => false],
                    'series' => [[
                        'name' => 'Registrations',
                        'data' => $chartData['topEventCounts'],
                        'color' => '#00a65a',
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            case 'events-with-no-registrations':
                $chartTitle = 'Events With No Registrations';
                if (empty($chartData['unregisteredEventLabels'])) {
                    $emptyMessage = 'All configured events have registrations.';
                }
                $chartOptions = [
                    'chart' => ['type' => 'bar'],
                    'title' => ['text' => $chartTitle],
                    'subtitle' => ['text' => $chartSubtitle],
                    'xAxis' => ['categories' => $chartData['unregisteredEventLabels'], 'title' => ['text' => null]],
                    'yAxis' => ['min' => 0, 'max' => 1, 'tickInterval' => 1, 'title' => ['text' => 'No Registration Flag']],
                    'legend' => ['enabled' => false],
                    'plotOptions' => ['bar' => ['dataLabels' => ['enabled' => true]]],
                    'series' => [[
                        'name' => 'No registrations',
                        'data' => $chartData['unregisteredEventFlags'],
                        'color' => '#dd4b39',
                    ]],
                    'credits' => ['enabled' => false],
                ];
                break;

            default:
                $this->Flash->error('Invalid chart selection.');
                return $this->redirect(['action' => 'dashboard']);
        }

        $this->set('chartKey', $chartKey);
        $this->set('chartTitle', $chartTitle);
        $this->set('chartSubtitle', $chartSubtitle);
        $this->set('emptyMessage', $emptyMessage);
        $this->set('chartOptionsJson', json_encode($chartOptions));
    }

    private function getSeasonParticipantBreakdown($convSD) {
        $totalStudents = $this->Conventionregistrationstudents->find()->where([
            "convention_id" => $convSD->convention_id,
            "season_id" => $convSD->season_id,
            "season_year" => $convSD->season_year,
        ])->count();

        $totalTeachersParents = $this->Conventionregistrationteachers->find()->where([
            "convention_id" => $convSD->convention_id,
            "season_id" => $convSD->season_id,
            "season_year" => $convSD->season_year,
        ])->count();

        $totalSchools = 0;
        $listSchools = $this->Conventionregistrations->find()->where([
            "convention_id" => $convSD->convention_id,
            "season_id" => $convSD->season_id,
            "season_year" => $convSD->season_year,
        ])->contain(['Users'])->all();
        foreach ($listSchools as $schoolcntr) {
            if ($schoolcntr->Users['user_type'] == "School") {
                $totalSchools++;
            }
        }

        $totalJudges = 0;
        $listCR = $this->Conventionregistrations->find()->where([
            "convention_id" => $convSD->convention_id,
            "season_id" => $convSD->season_id,
            "season_year" => $convSD->season_year,
        ])->contain(['Users'])->all();
        foreach ($listCR as $judgcntr) {
            if (($judgcntr->Users['user_type'] == "Judge" || $judgcntr->Users['user_type'] == "Teacher_Parent") && $judgcntr->Users['is_judge'] == 1) {
                $totalJudges++;
            }
        }

        return [
            'total_students' => (int)$totalStudents,
            'total_teachers_parents' => (int)$totalTeachersParents,
            'total_schools' => (int)$totalSchools,
            'total_judges' => (int)$totalJudges,
        ];
    }

    private function getDashboardChartData($conventionSeasonId) {
        $schedCategoryData = [];
        for ($cat = 1; $cat <= 4; $cat++) {
            $schedCategoryData[] = $this->Schedulingtimings->find()->where([
                "conventionseasons_id" => $conventionSeasonId,
                "schedule_category" => $cat,
                "day IS NOT" => null,
            ])->count();
        }

        $totalScheduled = $this->Schedulingtimings->find()->where([
            "conventionseasons_id" => $conventionSeasonId,
            "day IS NOT" => null,
        ])->count();
        $totalUnscheduled = $this->Schedulingtimings->find()->where([
            "conventionseasons_id" => $conventionSeasonId,
            "day" => null,
        ])->count();

        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        $dayCountData = [];
        foreach ($dayNames as $d) {
            $dayCountData[] = $this->Schedulingtimings->find()->where([
                "conventionseasons_id" => $conventionSeasonId,
                "day" => $d,
            ])->count();
        }

        $seasonEventRows = $this->Conventionseasonevents->find()
            ->select(['event_id'])
            ->where(['Conventionseasonevents.conventionseasons_id' => $conventionSeasonId])
            ->all();

        $seasonEventIds = [];
        foreach ($seasonEventRows as $seasonEventRow) {
            $seasonEventIds[] = (int)$seasonEventRow->event_id;
        }
        $seasonEventIds = array_values(array_unique(array_filter($seasonEventIds)));

        $eventNameMap = [];
        $eventCountMap = [];
        if (!empty($seasonEventIds)) {
            $seasonEvents = $this->Events->find()
                ->select(['id', 'event_name', 'event_id_number'])
                ->where(['Events.id IN' => $seasonEventIds])
                ->all();

            foreach ($seasonEvents as $seasonEvent) {
                $eventNameMap[(int)$seasonEvent->id] = (string)$seasonEvent->event_name . ' (' . (string)$seasonEvent->event_id_number . ')';
                $eventCountMap[(int)$seasonEvent->id] = 0;
            }

            $entryRows = $this->Crstudentevents->find()
                ->select(['event_id', 'cnt' => 'COUNT(*)'])
                ->where([
                    'Crstudentevents.conventionseason_id' => $conventionSeasonId,
                    'Crstudentevents.event_id IN' => $seasonEventIds,
                ])
                ->group(['Crstudentevents.event_id'])
                ->all();

            foreach ($entryRows as $entryRow) {
                $eventId = (int)$entryRow->event_id;
                $eventCountMap[$eventId] = (int)$entryRow->cnt;
            }
        }

        arsort($eventCountMap);
        $topEventLabels = [];
        $topEventCounts = [];
        $topLimit = 10;
        $topCounter = 0;
        foreach ($eventCountMap as $eventId => $entryCount) {
            if ($entryCount <= 0) {
                continue;
            }
            $topEventLabels[] = isset($eventNameMap[$eventId]) ? $eventNameMap[$eventId] : ('Event #' . $eventId);
            $topEventCounts[] = (int)$entryCount;
            $topCounter++;
            if ($topCounter >= $topLimit) {
                break;
            }
        }

        $unregisteredEventLabels = [];
        $unregisteredEventFlags = [];
        foreach ($eventCountMap as $eventId => $entryCount) {
            if ((int)$entryCount === 0) {
                $unregisteredEventLabels[] = isset($eventNameMap[$eventId]) ? $eventNameMap[$eventId] : ('Event #' . $eventId);
                $unregisteredEventFlags[] = 1;
            }
        }

        return [
            'schedCategoryData' => $schedCategoryData,
            'totalScheduled' => (int)$totalScheduled,
            'totalUnscheduled' => (int)$totalUnscheduled,
            'dayNames' => $dayNames,
            'dayCountData' => $dayCountData,
            'topEventLabels' => $topEventLabels,
            'topEventCounts' => $topEventCounts,
            'unregisteredEventLabels' => $unregisteredEventLabels,
            'unregisteredEventFlags' => $unregisteredEventFlags,
        ];
    }

    public function changeEmail() {
        $this->set('title', ADMIN_TITLE . 'Change Email Address');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changeEmail', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            $admin = $this->Admins->patchEntity($admin, $requestData, ['validate' => 'changeEmail']);
            if (count($admin->getErrors()) == 0) {
                $new_email = $requestData['Admins']['new_email'];
                $this->Admins->updateAll(['email' => $new_email], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->Flash->success('Admin email updated successfully.');
                $this->redirect(['action' => 'changeEmail']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changeusername() {
        $this->set('title', ADMIN_TITLE . 'Change Username');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConfig', '1');
        $this->set('changeUsername', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {

            $requestData = $this->request->getData();
            $admin = $this->Admins->patchEntity($admin, $requestData, ['validate' => 'changeusername']);
            if (count($admin->getErrors()) == 0) {
                $username = $requestData['Admins']['new_username'];
                $this->Admins->updateAll(['username' => $username], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->request->getSession()->write('admin_username', $username);
                $this->Flash->success('Admin username updated successfully.');
                $this->redirect(['action' => 'changeusername']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changePassword() {
        $this->set('title', ADMIN_TITLE . 'Change Password');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changePassword', '1');
		
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $requestData = $this->request->getData();
            $requestData['Admins']['id'] = $this->request->getSession()->read('admin_id');
            $admin = $this->Admins->patchEntity($admin, $requestData, ['validate' => 'changePassword']);
            if (count($admin->getErrors()) == 0) {
                $new_password = $requestData['Admins']['new_password'];
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');
                $this->Admins->updateAll(['password' => $password], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->Flash->success('Admin password updated successfully.');
                $this->redirect(['action' => 'changePassword']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }
	
	public function resetpassword() {
	
		$adminInfo = $this->Admins->find()->where()->order(['Admins.id' => "ASC"])->first();

		$this->request->getSession()->write('admin_id', $adminInfo->id);
		$this->request->getSession()->write('admin_username', $adminInfo->username);
		$this->redirect(['action' => 'dashboard']);
	
	}

    public function settings() {
        $this->set('title', ADMIN_TITLE . 'Settings');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('settings', '1');
		
        if ($this->request->is('post')) {
                $requestData = $this->request->getData();
				
                $paypal_email 							= $requestData['Settings']['paypal_email'];
                $accounts_team_email 					= $requestData['Settings']['accounts_team_email'];
                $full_registration_price 				= $requestData['Settings']['full_registration_price'];
                $scripture_only_registration_price 		= $requestData['Settings']['scripture_only_registration_price'];
                $scripture_trophy_discount 				= $requestData['Settings']['scripture_trophy_discount'];
				
                $min_events_student 				= $requestData['Settings']['min_events_student'];
                $max_events_student 				= $requestData['Settings']['max_events_student'];
				
                $judges_low_score_saving_pin 				= $requestData['Settings']['judges_low_score_saving_pin'];
				
				
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
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('postinfo', '1');
		
        if ($this->request->is('post')) {
                $requestData = $this->request->getData();
				
                $postinfo 							= $requestData['Settings']['postinfo'];
				
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