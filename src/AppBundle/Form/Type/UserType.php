<?php

/*
 * Copyright 2017 dinel.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of UserType
 *
 * @author dinel
 */
class UserType extends AbstractType {
    private $in_edit_mode = false;
    private $current_user = false;
    private $is_admin = false;

    public function __construct($in_edit_mode = false, $current_user = false, $is_admin = false) {
        $this->in_edit_mode = $in_edit_mode;
        $this->current_user = $current_user;
        $this->is_admin = $is_admin;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('username', 'text', array(
                    'label' => 'Login name',
                    'disabled' => $this->in_edit_mode,
            ))
            ->add('full_name', 'text', array(
                    'label' => 'Name of user',
            ))
            ->add('email')
            ->add('plain_password', 'password', array(
                    'label' => 'Password',
                    'mapped' => false,
                    'required' => $this->in_edit_mode ? false : true,
            ))
            ->add('repeat_plain_password', 'password', array(
                    'label' => 'Repeat password',
                    'mapped' => false,
                    'required' => $this->in_edit_mode ? false : true,
            ))
            ->add('is_administrator', 'checkbox', array(
                    'label' => 'Is administrator?',
                    'mapped' => false,
                    'required' => false,
                    'disabled' => $this->current_user,
                    'attr' => $this->is_admin ? array( 'checked' => 'checked') : array(),
            ))
            ->add('save', 'submit', array(
                'label' => $this->in_edit_mode ? 'Edit user' : 'Add user'));
    }
    
    public function getName() {
        return "user";
    }
}
