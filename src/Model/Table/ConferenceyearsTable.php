<?php
namespace App\Model\Table;
use Cake\ORM\Table;

class ConferenceyearsTable extends Table
{
    public function initialize(array $config)
    {
        $this->setTable('conference_years');

        $this->hasMany('Conferenceregistrations', [
            'foreignKey' => 'conference_year_id',
        ]);
    }
}
