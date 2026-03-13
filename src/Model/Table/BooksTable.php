<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class BooksTable extends Table {

    public function initialize(array $config)
    {	
		/* $this->belongsTo('Schools', [
            'className' => 'Users',
            'foreignKey' => 'school_id',
            'propertyName' => 'Schools'
        ]);
		
		*/
    }
	
	public function validationAdd(Validator $validator) {
        $validator
                //->notEmpty('first_name', 'First name is required')
                //->notEmpty('last_name', 'Last name is required')
				
				//->notEmpty('username', 'Username is required')
                /* ->add('username', 'custom', [
                    'rule' => function($value, $context) {
                        $username = $context['data']['username'];
                        $isRecord = $this->find()->where(['Users.username' => $username])->first();
                        if ($isRecord) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'message' => 'Username already exist, please try with other username',
                ]) */
                
                //->notEmpty('season_year', 'Season year is required')
                ->notEmpty('book_name', 'Book name is required')
                ->add('book_name', 'custom', [
                    'rule' => function($value, $context) {
                        $book_name = $context['data']['book_name'];
                        $isRecord = $this->find()->where(['Books.book_name' => $book_name])->first();
                        if ($isRecord) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'message' => 'Book name address already exist, please try with other book name.',
                ])
                
                ;
                return $validator;
            }

            /* public function validationEdit(Validator $validator) {
                $validator
                        //->notEmpty('first_name', 'First name is required')
                        //->notEmpty('last_name', 'Last name is required')
                        ->notEmpty('email_address', 'Email address is required')
                        ->add('email_address', 'valid', ['rule' => 'email', 'message' => 'Please enter valid email address.'])
                        ->allowEmpty('password')
                        ->allowEmpty('confirm_password')
                        ->add('confirm_password', [
                            'compare' => [
                                'rule' => ['compareWith', 'password'],
                                'message' => 'Password and confirm password must be same'
                            ]
                        ])
                ;
                return $validator;
            } */
			

                            
                            

                             

}

?>