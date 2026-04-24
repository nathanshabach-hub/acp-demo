<?php
namespace App\Model\Table;
use Cake\ORM\Table;

class ConferenceregistrationsTable extends Table
{
    public function initialize(array $config)
    {
        $this->setTable('conference_registrations');

        $this->belongsTo('Conferenceyears', [
            'foreignKey' => 'conference_year_id',
            'propertyName' => 'Conferenceyears'
        ]);

        $this->belongsTo('Schools', [
            'className' => 'Users',
            'foreignKey' => 'school_id',
            'propertyName' => 'Schools'
        ]);

        $this->belongsTo('Supervisors', [
            'className' => 'Users',
            'foreignKey' => 'supervisor_id',
            'propertyName' => 'Supervisors'
        ]);

        $this->belongsTo('RegisteredBy', [
            'className' => 'Users',
            'foreignKey' => 'registered_by',
            'propertyName' => 'RegisteredBy'
        ]);
    }
}
