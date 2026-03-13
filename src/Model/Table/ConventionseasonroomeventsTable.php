<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ConventionseasonroomeventsTable extends Table{
    
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
		
		$this->belongsTo('Conventionrooms', [
            'className' => 'Conventionrooms',
            'foreignKey' => 'room_id',
            'propertyName' => 'Conventionrooms'
        ]);
    }
    
     
    
    

}
?>