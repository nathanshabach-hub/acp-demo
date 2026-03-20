<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class RoomsTable extends Table{
    
    public function initialize(array $config)
    {
        $this->setTable('rooms');
        // Global rooms don't need relationships initially
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('name', 'Room name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $isRecord =  $this->find()->where(['Rooms.name' => $name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Room name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('name', 'Room name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Rooms.name' => $name, 'Rooms.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Room name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
}
?>
