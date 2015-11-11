<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('text', 'text', array(
                'label' => 'Metadiscourse marker'
            ))
            ->add('description', 'text')
            ->add('domains', 'entity', array(
                    'class'     => 'AppBundle:Domain',
                    'choice_label' => 'Domains',
                    'expanded'  => true,
                    'multiple'  => true
                ))
            ->add('save', 'submit', array('label' => 'Add marker'));
    }

    public function getName() {
        return "markable";
    }

//put your code here
}
