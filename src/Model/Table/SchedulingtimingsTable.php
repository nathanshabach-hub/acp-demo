<?php

namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SchedulingtimingsTable extends Table {

    public function initialize(array $config)
    {
		$this->belongsTo('Conventionseasons', [
            'className' => 'Conventionseasons',
            'foreignKey' => 'conventionseasons_id',
            'propertyName' => 'Conventionseasons'
        ]);
		$this->belongsTo('Conventions', [
            'className' => 'Conventions',
            'foreignKey' => 'convention_id',
            'propertyName' => 'Conventions'
        ]);
		
		$this->belongsTo('Seasons', [
            'className' => 'Seasons',
            'foreignKey' => 'season_id',
            'propertyName' => 'Seasons'
        ]);
		
		$this->belongsTo('Conventionregistrations', [
            'className' => 'Conventionregistrations',
            'foreignKey' => 'conventionregistration_id',
            'propertyName' => 'Conventionregistrations'
        ]);
		
		$this->belongsTo('Events', [
            'className' => 'Events',
            'foreignKey' => 'event_id',
            'propertyName' => 'Events'
        ]);
		
		$this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
            'propertyName' => 'Users'
        ]);
		
		$this->belongsTo('Conventionrooms', [
            'className' => 'Conventionrooms',
            'foreignKey' => 'room_id',
            'propertyName' => 'Conventionrooms'
        ]);
		
		$this->belongsTo('Opponentuser', [
            'className' => 'Users',
            'foreignKey' => 'user_id_opponent',
            'propertyName' => 'Opponentuser'
        ]);
    }

    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->has('is_bye') && $entity->is_bye === null) {
            $entity->is_bye = 0;
        }

        if (!$entity->has('is_bye')) {
            $entity->is_bye = 0;
        }
    }
	
	      

}

?>