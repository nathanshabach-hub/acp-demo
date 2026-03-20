<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class RoomsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Rooms.name' => 'asc']];
    public $components = ['RequestHandler', 'PImage', 'PImageTest'];

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $this->loadModel('Rooms');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Global Rooms');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageRooms', '1');

        $separator = array();
        $condition = array();

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Rooms->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Rooms->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Rooms->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Rooms']['keyword']) && $this->request->getData()['Rooms']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Rooms']['keyword']);
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
            $condition[] = "(Rooms.name LIKE '%".addslashes($keyword)."%' OR Rooms.description LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }

        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Rooms.name' => 'ASC']];
        $this->set('rooms', $this->paginate($this->Rooms));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->templatePath('Element' . DS . 'Admin/Rooms');
            $this->render('index');
        }
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Global Room');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageRooms', '1');

        $rooms = $this->Rooms->newEntity();
        if ($this->request->is('post')) {
            $roomNames = isset($this->request->getData()['room_names']) ? $this->request->getData()['room_names'] : [];
            $roomNames = array_filter(array_map('trim', $roomNames));

            if (empty($roomNames)) {
                $this->Flash->error('Please enter at least one room name.');
            } else {
                $saved = 0;
                $skipped = [];
                foreach ($roomNames as $name) {
                    $exists = $this->Rooms->find()->where(['Rooms.name' => $name])->first();
                    if ($exists) {
                        $skipped[] = $name;
                        continue;
                    }
                    $room = $this->Rooms->newEntity();
                    $room->name = $name;
                    $room->slug = 'room-' . time() . '-' . rand(10, 100000);
                    $room->status = 1;
                    $room->created = date('Y-m-d H:i:s');
                    $room->modified = date('Y-m-d H:i:s');
                    if ($this->Rooms->save($room)) {
                        $saved++;
                    }
                }
                if ($saved > 0) {
                    $msg = $saved . ' room' . ($saved > 1 ? 's' : '') . ' added successfully.';
                    if (!empty($skipped)) {
                        $msg .= ' Skipped (already exist): ' . implode(', ', $skipped) . '.';
                    }
                    $this->Flash->success($msg);
                } else {
                    $this->Flash->error('No rooms were saved. Names may already exist: ' . implode(', ', $skipped) . '.');
                }
                $this->redirect(['controller' => 'rooms', 'action' => 'index']);
            }
        }
        $this->set('rooms', $rooms);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Global Room');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageRooms', '1');

        if ($slug) {
            $roomD = $this->Rooms->find()->where(['Rooms.slug' => $slug])->first();
            $uid = $roomD->id;
        }

        $rooms = $this->Rooms->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Rooms->patchEntity($rooms, $this->request->getData());
            if (count($data->getErrors()) == 0) {
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Rooms->save($data)) {
                    $this->Flash->success('Room updated successfully.');
                    $this->redirect(['controller' => 'rooms', 'action' => 'index']);
                }
            } else {
                $this->Flash->error('Please check errors below.');
            }
        }
        $this->set('rooms', $rooms);
    }

    public function delete($slug = null) {
        if ($slug) {
            $roomD = $this->Rooms->find()->where(['Rooms.slug' => $slug])->first();
            if ($roomD) {
                $this->Rooms->delete($roomD);
                $this->Flash->success('Room deleted successfully.');
            } else {
                $this->Flash->error('Room not found.');
            }
        }
        $this->redirect(['controller' => 'rooms', 'action' => 'index']);
    }

    public function importexcel() {
        $this->set('title', ADMIN_TITLE . 'Import Global Rooms');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageEvents', '1');
        $this->set('manageRooms', '1');

        if ($this->request->is('post')) {
            
            if (empty($this->request->getData()['import_file']['name'])) {
                $this->Flash->error('Please select a file to import.');
                $this->redirect(['controller' => 'rooms', 'action' => 'importexcel']);
                return;
            }
            
            $file = $this->request->getData()['import_file'];
            $fileName = $file['name'];
            $fileTmp = $file['tmp_name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validate file type
            if (!in_array($fileExt, ['csv', 'xlsx', 'xls'])) {
                $this->Flash->error('Invalid file format. Supported formats: CSV, XLSX, XLS');
                $this->redirect(['controller' => 'rooms', 'action' => 'importexcel']);
                return;
            }
            
            $rooms = [];
            
            try {
                if ($fileExt === 'csv') {
                    // Handle CSV
                    $handle = fopen($fileTmp, 'r');
                    $header = fgetcsv($handle);
                    
                    // Find column indices
                    $roomNameCol = -1;
                    $descCol = -1;
                    
                    foreach ($header as $idx => $col) {
                        $col_lower = strtolower(trim($col));
                        if (strpos($col_lower, 'room') !== false && strpos($col_lower, 'name') !== false) {
                            $roomNameCol = $idx;
                        }
                        if (strpos($col_lower, 'desc') !== false || strpos($col_lower, 'description') !== false) {
                            $descCol = $idx;
                        }
                    }
                    
                    if ($roomNameCol === -1) {
                        throw new \Exception('CSV must contain a "Room Name" column');
                    }
                    
                    while (($row = fgetcsv($handle)) !== false) {
                        if (!empty(trim($row[$roomNameCol]))) {
                            $rooms[] = [
                                'name' => trim($row[$roomNameCol]),
                                'description' => ($descCol !== -1 && !empty($row[$descCol])) ? trim($row[$descCol]) : ''
                            ];
                        }
                    }
                    fclose($handle);
                    
                } else {
                    // Handle Excel (XLSX/XLS) using PHPExcel
                    require_once(ROOT . '/vendors/PHPExcel/Classes/PHPExcel.php');
                    
                    $objPHPExcel = \PHPExcel_IOFactory::load($fileTmp);
                    $objWorksheet = $objPHPExcel->getActiveSheet();
                    $highestRow = $objWorksheet->getHighestRow();
                    $highestCol = $objWorksheet->getHighestColumn();
                    
                    // Read header
                    $header = [];
                    for ($col = 'A'; $col !== $highestCol; $col++) {
                        $header[] = strtolower(trim($objWorksheet->getCell($col . '1')->getValue()));
                    }
                    
                    // Find column indices
                    $roomNameCol = -1;
                    $descCol = -1;
                    
                    foreach ($header as $idx => $col) {
                        if (strpos($col, 'room') !== false && strpos($col, 'name') !== false) {
                            $roomNameCol = $idx;
                        }
                        if (strpos($col, 'desc') !== false || strpos($col, 'description') !== false) {
                            $descCol = $idx;
                        }
                    }
                    
                    if ($roomNameCol === -1) {
                        throw new \Exception('Excel must contain a "Room Name" column');
                    }
                    
                    // Read data rows
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $col_array = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                        $roomName = trim($objWorksheet->getCell($col_array[$roomNameCol] . $row)->getValue());
                        
                        if (!empty($roomName)) {
                            $description = '';
                            if ($descCol !== -1) {
                                $description = trim($objWorksheet->getCell($col_array[$descCol] . $row)->getValue());
                            }
                            
                            $rooms[] = [
                                'name' => $roomName,
                                'description' => $description
                            ];
                        }
                    }
                }
                
                // Insert rooms
                $insertedCount = 0;
                $skippedCount = 0;
                $errors = [];
                
                foreach ($rooms as $roomData) {
                    // Check if room already exists
                    $exists = $this->Rooms->find()
                        ->where(['Rooms.name' => $roomData['name']])
                        ->first();
                    
                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }
                    
                    // Create new room
                    $newRoom = $this->Rooms->newEntity();
                    $newRoom->name = $roomData['name'];
                    $newRoom->description = $roomData['description'];
                    $newRoom->slug = 'room-' . time() . '-' . rand(10, 100000);
                    $newRoom->status = 1;
                    $newRoom->created = date('Y-m-d H:i:s');
                    
                    if ($this->Rooms->save($newRoom)) {
                        $insertedCount++;
                    } else {
                        $errors[] = "Failed to insert: " . $roomData['name'];
                    }
                }
                
                if ($insertedCount > 0) {
                    $message = "Successfully imported $insertedCount room(s).";
                    if ($skippedCount > 0) {
                        $message .= " $skippedCount room(s) were skipped (already exist).";
                    }
                    $this->Flash->success($message);
                } elseif ($skippedCount > 0) {
                    $this->Flash->info("All $skippedCount room(s) already exist in the system.");
                } else {
                    $this->Flash->error('No rooms were imported.');
                }
                
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->Flash->error($error);
                    }
                }
                
            } catch (\Exception $e) {
                $this->Flash->error('Error processing file: ' . $e->getMessage());
            }
            
            $this->redirect(['controller' => 'rooms', 'action' => 'index']);
        }
    }
}
?>
