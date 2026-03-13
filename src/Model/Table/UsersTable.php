<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table {

    public function initialize(array $config)
    {	
		$this->belongsTo('Schools', [
            'className' => 'Users',
            'foreignKey' => 'school_id',
            'propertyName' => 'Schools'
        ]);
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
                
                ->notEmpty('email_address', 'Email address is required')
                ->add('email_address', 'valid', ['rule' => 'email', 'message' => 'Please enter valid email address.'])
                ->add('email_address', 'custom', [
                    'rule' => function($value, $context) {
                        $email_address = $context['data']['email_address'];
                        $isRecord = $this->find()->where(['Users.email_address' => $email_address])->first();
                        if ($isRecord) {
                            return false;
                        } else {
                            return true;
                        }
                    },
                    'message' => 'Email address already exist, please try with other email address',
                ])
                //->notEmpty('password', 'Password is required')
                //->notEmpty('confirm_password', 'Confirm password is required')
                ->add('confirm_password', [
                    'compare' => [
                        'rule' => ['compareWith', 'password'],
                        'message' => 'Password and confirm password must be same'
                    ]
                ])
                ;
                return $validator;
            }

            public function validationEdit(Validator $validator) {
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
            }
			
			public function validationEditindividual(Validator $validator) {
                $validator
                        ->notEmpty('first_name', 'First name is required')
                        ->notEmpty('last_name', 'Last name is required')
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
            }
             
            
            
            
            public function validationEditProfile(Validator $validator) {
                $validator
                        ->notEmpty('first_name', 'First name is required')
                        ->notEmpty('last_name', 'Last name is required')
                        ->notEmpty('email_address', 'Email address is required')
                        ->add('email_address', 'valid', ['rule' => 'email', 'message' => 'Please enter valid email address.']);
                return $validator;
            }

            public function validationRegister(Validator $validator) {
//                die('testing');
                $validator
                        ->notEmpty('first_name', 'First name is required')
                        ->notEmpty('last_name', 'Last name is required')
                        ->notEmpty('email_address', 'Email address is required')
                        ->add('email_address', 'valid', ['rule' => 'email', 'message' => 'Please enter valid email address.'])
                        ->add('email_address', 'custom', [
                            'rule' => function($value, $context) {
                                $email_address = $context['data']['email_address'];
                                $isRecord = $this->find()->where(['Users.email_address' => $email_address])->first();
                                if ($isRecord) {
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                                    'message' => 'Email address already exist, please try with other email address',
                                ])
                                ->notEmpty('password', 'Password is required')
                                ->notEmpty('confirm_password', 'Confirm password is required')
                                ->add('confirm_password', [
                                    'compare' => [
                                        'rule' => ['compareWith', 'password'],
                                        'message' => 'Password and confirm password must be same'
                                    ]
                        ]);

                        return $validator;
                    }

                    public function validationlogin(Validator $validator) {
                        $validator
                                ->notEmpty('email_address', 'Email is required')
                                ->notEmpty('password', 'Password is required');
                        return $validator;
                    }

                    public function validationforgotPassword(Validator $validator) {
                        $validator
                                ->notEmpty('email_address', 'Email is required')
                                ->add('email_address', 'valid', ['rule' => 'email', 'message' => 'Please enter valid email address.'])
                                ->add('email_address', 'custom', [
                                    'rule' => function($value, $context) {
                                        $email_address = $context['data']['email_address'];
                                        $isRecord = $this->find()->where(['Users.email_address' => $email_address])->first();
                                        if ($isRecord) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    },
                                            'message' => 'Email address you have entered is not found in our database. Please enter correct email address.',
                                ]);
                                return $validator;
                            }

                            public function validationresetPassword(Validator $validator) {
                                $validator
                                        ->notEmpty('password', 'Password is required')
                                        ->notEmpty('confirm_password', 'Confirm password is required')
                                        ->add('confirm_password', [
                                            'compare' => [
                                                'rule' => ['compareWith', 'password'],
                                                'message' => 'Password and confirm password must be same'
                                            ]
                                ]);

                                return $validator;
                            }
                            
                            public function validationchangePassword(Validator $validator) { 
                                $validator
                                        ->notEmpty('old_password', 'Old Password is required field')
                                        ->notEmpty('new_password', 'New Password is required field.')
                                        ->notEmpty('confirm_password', 'Confirm Password is required field.')
                                        ->add('confirm_password', [
                                            'compare' => [
                                                'rule' => ['compareWith', 'new_password'],
                                                'message' => 'Password and confirm password must be same'
                                            ]
                                ]);

                                return $validator;
                            }
                            
                            

                             

                        }

?>