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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of UserType
 *
 * @author dinel
 */
class UserType extends AbstractType {
    private $in_edit_mode = false;
    private $current_user = false;
    private $is_admin = false;
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->in_edit_mode = $options['in_edit_mode'];
        $this->current_user = $options['current_user'];
        $this->is_admin = $options['is_admin'];
        
        $builder
            ->add('username', TextType::class, array(
                    'label' => 'Login name',
                    'disabled' => $this->in_edit_mode,
            ))
            ->add('full_name', TextType::class, array(
                    'label' => 'Name of user',
            ))
            ->add('email')
            ->add('plain_password', PasswordType::class, array(
                    'label' => 'Password',
                    'mapped' => false,
                    'required' => $this->in_edit_mode ? false : true,
            ))
            ->add('repeat_plain_password', PasswordType::class, array(
                    'label' => 'Repeat password',
                    'mapped' => false,
                    'required' => $this->in_edit_mode ? false : true,
            ))
            ->add('is_administrator', CheckboxType::class, array(
                    'label' => 'Is administrator?',
                    'mapped' => false,
                    'required' => false,
                    'disabled' => $this->current_user,
                    'attr' => $this->is_admin ? array( 'checked' => 'checked') : array(),
            ))
            ->add('change_password', CheckboxType::class, array(
                    'label' => 'Change password at next login?',
                    'mapped' => false,
                    'required' => false,
                    'disabled' => $this->current_user,
                    'attr' => $this->is_admin ? array( 'checked' => 'checked') : array(),
            ))
            ->add('save', SubmitType::class, array(
                'label' => $this->in_edit_mode ? 'Edit user' : 'Add user'));
    }
    
    /**
     * Sets the default options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
                'in_edit_mode' => false,
                'current_user' => false,
                'is_admin' => false,
        ]);
    }
}
