<?php

/*
 * Copyright 2015 - 2018 dinel.
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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of SenseType
 *
 * @author dinel
 */

class SenseType extends AbstractType {
    private $in_edit_mode = false;
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->in_edit_mode = $options['in_edit_mode'];
        
        $builder
            ->add('definition', TextType::class, array(
                    'label' => 'Label:',
                ))
            ->add('explanation', TextType::class, array(
                    'label' => 'Explanation:',
                    'required' => False,
                ))
            ->add('score', TextType::class, array(
                    'label' => 'Default score:',
                ))
            ->add('categories', EntityType::class, array(
                    'class'     => 'AppBundle:Category',
                    'choice_label' => 'Categories',
                    'expanded'  => true,
                    'multiple'  => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('d')
                                      ->where('d.parent IS NOT NULL')
                                      ->orderBy('d.name');
                    },
                ))
            ->add('fgColor', TextType::class, array(
                    'label' => 'Text color:',
                    'data' => '#000000',
                ))
            ->add('bgColor', TextType::class, array(
                    'label' => 'Background color:',
                    'data' => '#ffffff',
                ))
            ->add('save', SubmitType::class, array(
                'label' => $this->in_edit_mode ? 'Edit sense' : 'Add sense'));
    }

    /**
     * Sets the default options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
                'in_edit_mode' => false,
        ]);
    }    
}
