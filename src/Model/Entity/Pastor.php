<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Pastor extends Entity
{
    protected $_accessible = [
        'first_name' => true,
        'last_name' => true,
        'contact_details' => true,
        'country' => true,
        'description' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
    ];

    protected function _getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}