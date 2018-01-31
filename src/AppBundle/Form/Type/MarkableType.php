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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of MarkableType
 *
 * @author dinel
 */

class MarkableType extends AbstractType {
    private $in_edit_mode = false;


    public function __construct($in_edit_mode = false) {
        $this->in_edit_mode = $in_edit_mode;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('text', 'text', array(
                'label' => 'Metadiscourse marker',
                'disabled' => $this->in_edit_mode,
            ))
            ->add('description', 'text')
            
            ->add('categories', 'entity', array(
                    'class'     => 'AppBundle:Category',
                    'choice_label' => 'Categories',
                    'expanded'  => true,
                    'multiple'  => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('d')
                                      ->where('d.parent IS NOT NULL');
                    },
                ))
            ->add('save', 'submit', array(
                'label' => $this->in_edit_mode ? 'Edit marker' : 'Add marker')
            );
    }

    public function getName() {
        return "markable";
    }

//put your code here
}
