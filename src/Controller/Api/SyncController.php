<?php
namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * Sync Controller – provides endpoints for the offline-first PWA layer.
 */
class SyncController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function beforeRender(EventInterface $event)
    {
        // Skip AppController's beforeRender (which loads views/session data)
    }

    /**
     * GET /api/sync/ping
     * Simple connectivity check. Returns server time and session status.
     */
    public function ping()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $userId = $this->request->getSession()->read('user_id');

        $data = [
            'status' => 'ok',
            'online' => true,
            'server_time' => date('c'),
            'authenticated' => !empty($userId),
            'user_id' => $userId
        ];

        $this->response = $this->response->withStringBody(json_encode($data));
        return $this->response;
    }
}
