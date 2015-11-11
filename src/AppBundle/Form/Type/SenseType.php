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
 * Description of SenseType
 *
 * @author dinel
 */
class SenseType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('definition', 'text')
            ->add('save', 'submit', array('label' => 'Add sense'));
    }
    
    public function getName() {
        return "sense";
    }
}
