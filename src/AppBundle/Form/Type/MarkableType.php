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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of MarkableType
 *
 * @author dinel
 */

class MarkableType extends AbstractType {
    private $in_edit_mode = false;

    /**
     * Build the form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->in_edit_mode = $options['in_edit_mode'];
        
        $builder
            ->add('text', TextType::class, array(
                'label' => 'Metadiscourse marker',
                'disabled' => $this->in_edit_mode,
            ))
            ->add('description', TextareaType::class)            
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
            ->add('save', SubmitType::class, array(
                'label' => $this->in_edit_mode ? 'Edit marker' : 'Add marker')
            );
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
