<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class RoomallocationsTable extends Table {

    public function initialize(array $config)
    {
        $this->belongsTo('Conventions', [
            'className' => 'Conventions',
            'foreignKey' => 'convention_id',
            'propertyName' => 'Conventions'
        ]);
        $this->hasMany('RoomallocationRooms', [
            'className' => 'RoomallocationRooms',
            'foreignKey' => 'roomallocation_id',
            'propertyName' => 'RoomallocationRooms'
        ]);
    }

}
