<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class RoomallocationRoomsTable extends Table {

    public function initialize(array $config)
    {
        $this->belongsTo('Roomallocations', [
            'className' => 'Roomallocations',
            'foreignKey' => 'roomallocation_id',
            'propertyName' => 'Roomallocations'
        ]);
        $this->belongsTo('Conventionrooms', [
            'className' => 'Conventionrooms',
            'foreignKey' => 'conventionroom_id',
            'propertyName' => 'Conventionrooms'
        ]);
    }

}
