<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;


class AdminsTable extends Table
{
    
    public function initialize(array $config) {
        
        //$this->addBehavior('Timestamp');
    }
    
    public function validationDefault(Validator $validator){ 
        $validator
        ->notEmpty('username', 'Username is required') 
        ->notEmpty('password', 'Password is required');
        return $validator;
    }
    
    public function validationForgotPassword(Validator $validator){
        $validator
        ->notEmpty('username', 'Username is required') 
        ->add('email', 'valid' , ['rule'=> 'email',  'message' => 'Please enter valid email address.']);
        return $validator;
    }
    
     public function validationChangeEmail(Validator $validator){
        $validator
        ->notEmpty('new_email', 'New email is required')
        ->add('new_email','custom',[
            'rule'=>  function($value, $context){
                $new_email =  $context['data']['new_email'];
                $old_email =  $context['data']['old_email'];
                if($new_email == $old_email){
                    return false;
                }
                return true;
            },
            'message'=>'You can not change new email same as current email',
        ])  
        ->notEmpty('conf_email', 'Confirm email is required')
        ->add('conf_email', [
            'compare' => [
                'rule' => ['compareWith', 'new_email'],
                'message' => 'New email and confirm email must be same'
            ]
        ]);
        return $validator;
    }
    
    public function validationChangeusername(Validator $validator){
        $validator
        ->notEmpty('new_username', 'New Username is required')
        ->add('new_username','custom',[
            'rule'=>  function($value, $context){
                $new_username =  $context['data']['new_username'];
                $conf_username =  $context['data']['old_username'];
                if($new_username == $conf_username){
                    return false;
                }
                return true;
            },
            'message'=>'You can not change new username same as current username',
        ])  
        ->notEmpty('conf_username', 'Confirm Username is required')
        ->add('conf_username', [
            'compare' => [
                'rule' => ['compareWith', 'new_username'],
                'message' => 'New Username and Confirm Username must be same'
            ]
        ]);
        return $validator;
    }
    public function validationChangePassword(Validator $validator){
        $validator
        ->notEmpty('old_password', 'Current Password is required')
        ->add('old_password','custom',[
            'rule'=>  function($value, $context){
                $old_password =  $context['data']['old_password'];
                $adminId =  $context['data']['id'];
                $adminInfo =  $this->find()->where(['Admins.id' => $adminId])->first();
                if($adminInfo){ 
                    if(!empty($adminInfo) && crypt($old_password, $adminInfo->password) == $adminInfo->password) { 
                        return true;
                    }else{
                        return false;
                    }
                }
                return false;
            },
            'message'=>'Current password is not correct!',
        ])       
        ->notEmpty('new_password', 'New Password is required')
        ->add('new_password','custom',[
            'rule'=>  function($value, $context){
                $old_password =  $context['data']['old_password'];
                $new_password =  $context['data']['new_password'];
                if($old_password == $new_password){
                    return false;
                }
                return true;
            },
            'message'=>'You can not change new password same as current password',
        ])               
        ->notEmpty('conf_password', 'Confirm Password is required')
        ->add('conf_password', [
            'compare' => [
                'rule' => ['compareWith', 'new_password'],
                'message' => 'New Password and Confirm Password must be same'
            ]
        ]);
        return $validator;
    }
}
?>
