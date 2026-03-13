<?php

namespace App\Model\Table;

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
	
	      

}

?>