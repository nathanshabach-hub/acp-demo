<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;

class RoomallocationsController extends AppController {

    public $paginate = ['limit' => 100, 'order' => ['Roomallocations.name' => 'asc']];

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

        $this->loadModel('Roomallocations');
        $this->loadModel('RoomallocationRooms');
        $this->loadModel('Conventionrooms');
        $this->loadModel('Conventionseasons');
    }

    /**
     * List all room allocations for a convention season
     */
    public function index($slug_convention_season = null) {
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('slug_convention_season', $slug_convention_season);

        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $this->set('conventionSD', $conventionSD);
        }
        if (!$conventionSD) {
            $this->Flash->error('Convention season not found.');
            return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
        }

        $this->set('title', ADMIN_TITLE . 'Room Allocations > ' . $conventionSD->Conventions['name']);

        $condition = ['Roomallocations.convention_id' => $conventionSD->convention_id];
        $this->paginate = ['conditions' => $condition, 'limit' => 1000000, 'order' => ['Roomallocations.name' => 'ASC']];
        $allocations = $this->paginate($this->Roomallocations);

        // Attach rooms to each allocation
        $allocationList = [];
        foreach ($allocations as $alloc) {
            $rooms = $this->RoomallocationRooms->find()
                ->where(['RoomallocationRooms.roomallocation_id' => $alloc->id])
                ->contain(['Conventionrooms'])
                ->all();
            $allocationList[] = ['allocation' => $alloc, 'rooms' => $rooms];
        }
        $this->set('allocationList', $allocationList);
    }

    /**
     * Add a new room allocation
     */
    public function add($slug_convention_season = null) {
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('slug_convention_season', $slug_convention_season);

        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $this->set('conventionSD', $conventionSD);
        }
        if (!$conventionSD) {
            $this->Flash->error('Convention season not found.');
            return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
        }

        $this->set('title', ADMIN_TITLE . 'Add Room Allocation > ' . $conventionSD->Conventions['name']);

        if ($this->request->is('post')) {
            $name = trim($this->request->data['Roomallocations']['name']);
            $description = trim($this->request->data['Roomallocations']['description']);

            if (empty($name)) {
                $this->Flash->error('Allocation name is required.');
            } else {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . time();
                $newAlloc = $this->Roomallocations->newEntity();
                $newAlloc->slug = $slug;
                $newAlloc->convention_id = $conventionSD->convention_id;
                $newAlloc->name = $name;
                $newAlloc->description = $description;
                $newAlloc->created = date('Y-m-d H:i:s');
                $newAlloc->modified = date('Y-m-d H:i:s');

                if ($this->Roomallocations->save($newAlloc)) {
                    $this->Flash->success('Room Allocation "' . h($name) . '" created successfully.');
                    return $this->redirect(['controller' => 'roomallocations', 'action' => 'view', $slug_convention_season, $newAlloc->id]);
                } else {
                    $this->Flash->error('Could not save Room Allocation. Please try again.');
                }
            }
        }
    }

    /**
     * View a room allocation and manage its rooms
     */
    public function view($slug_convention_season = null, $allocation_id = null) {
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('slug_convention_season', $slug_convention_season);

        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $this->set('conventionSD', $conventionSD);
        }
        if (!$conventionSD) {
            $this->Flash->error('Convention season not found.');
            return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
        }

        $allocation = $this->Roomallocations->find()->where(['Roomallocations.id' => $allocation_id, 'Roomallocations.convention_id' => $conventionSD->convention_id])->first();
        if (!$allocation) {
            $this->Flash->error('Room Allocation not found.');
            return $this->redirect(['controller' => 'roomallocations', 'action' => 'index', $slug_convention_season]);
        }

        $this->set('title', ADMIN_TITLE . 'Room Allocation: ' . $allocation->name);
        $this->set('allocation', $allocation);

        // Rooms already in this allocation
        $assignedRooms = $this->RoomallocationRooms->find()
            ->where(['RoomallocationRooms.roomallocation_id' => $allocation_id])
            ->contain(['Conventionrooms'])
            ->all();
        $this->set('assignedRooms', $assignedRooms);

        // IDs already assigned (to exclude from dropdown)
        $assignedRoomIds = [0];
        foreach ($assignedRooms as $ar) {
            $assignedRoomIds[] = $ar->conventionroom_id;
        }

        // Available rooms for this convention (not yet in this allocation)
        $availableRooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id, 'Conventionrooms.id NOT IN' => $assignedRoomIds])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->combine('id', 'room_name')
            ->toArray();
        $this->set('availableRooms', $availableRooms);

        // Handle add room POST
        if ($this->request->is('post')) {
            $room_id = $this->request->data['RoomallocationRooms']['conventionroom_id'];
            if (!empty($room_id)) {
                $newEntry = $this->RoomallocationRooms->newEntity();
                $newEntry->roomallocation_id = $allocation_id;
                $newEntry->conventionroom_id = $room_id;
                $newEntry->created = date('Y-m-d H:i:s');
                if ($this->RoomallocationRooms->save($newEntry)) {
                    $this->Flash->success('Room added to allocation.');
                } else {
                    $this->Flash->error('Could not add room. Please try again.');
                }
            } else {
                $this->Flash->error('Please select a room to add.');
            }
            return $this->redirect(['controller' => 'roomallocations', 'action' => 'view', $slug_convention_season, $allocation_id]);
        }
    }

    /**
     * Edit allocation name/description
     */
    public function edit($slug_convention_season = null, $allocation_id = null) {
        $this->viewBuilder()->layout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('slug_convention_season', $slug_convention_season);

        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $this->set('conventionSD', $conventionSD);
        }
        if (!$conventionSD) {
            $this->Flash->error('Convention season not found.');
            return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
        }

        $allocation = $this->Roomallocations->find()->where(['Roomallocations.id' => $allocation_id, 'Roomallocations.convention_id' => $conventionSD->convention_id])->first();
        if (!$allocation) {
            $this->Flash->error('Room Allocation not found.');
            return $this->redirect(['controller' => 'roomallocations', 'action' => 'index', $slug_convention_season]);
        }

        $this->set('title', ADMIN_TITLE . 'Edit Room Allocation');
        $this->set('allocation', $allocation);

        if ($this->request->is('post')) {
            $name = trim($this->request->data['Roomallocations']['name']);
            $description = trim($this->request->data['Roomallocations']['description']);
            if (empty($name)) {
                $this->Flash->error('Allocation name is required.');
            } else {
                $allocation->name = $name;
                $allocation->description = $description;
                $allocation->modified = date('Y-m-d H:i:s');
                if ($this->Roomallocations->save($allocation)) {
                    $this->Flash->success('Room Allocation updated.');
                    return $this->redirect(['controller' => 'roomallocations', 'action' => 'view', $slug_convention_season, $allocation_id]);
                } else {
                    $this->Flash->error('Could not update. Please try again.');
                }
            }
        }
    }

    /**
     * Remove a room from an allocation
     */
    public function removeroom($slug_convention_season = null, $entry_id = null) {
        $this->request->allowMethod(['post']);
        $entry = $this->RoomallocationRooms->find()->where(['RoomallocationRooms.id' => $entry_id])->first();
        $allocation_id = $entry ? $entry->roomallocation_id : null;
        if ($entry) {
            $this->RoomallocationRooms->delete($entry);
            $this->Flash->success('Room removed from allocation.');
        } else {
            $this->Flash->error('Entry not found.');
        }
        return $this->redirect(['controller' => 'roomallocations', 'action' => 'view', $slug_convention_season, $allocation_id]);
    }

    /**
     * Delete an entire allocation and its room entries
     */
    public function delete($slug_convention_season = null, $allocation_id = null) {
        $this->request->allowMethod(['post']);

        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
        }

        $allocation = $this->Roomallocations->find()->where(['Roomallocations.id' => $allocation_id])->first();
        if ($allocation) {
            $this->RoomallocationRooms->deleteAll(['RoomallocationRooms.roomallocation_id' => $allocation_id]);
            $this->Roomallocations->delete($allocation);
            $this->Flash->success('Room Allocation deleted.');
        } else {
            $this->Flash->error('Allocation not found.');
        }
        return $this->redirect(['controller' => 'roomallocations', 'action' => 'index', $slug_convention_season]);
    }
}
