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
            ->add('definition', 'text', array(
                    'label' => 'Label:',
                ))
            ->add('explanation', 'text', array(
                    'label' => 'Explanation:',
                    'required' => False,
                ))
            ->add('score', 'text', array(
                    'label' => 'Default score:',
                ))
            ->add('fgColor', 'text', array(
                    'label' => 'Text color:',
                    'data' => '#000000',
                ))
            ->add('bgColor', 'text', array(
                    'label' => 'Background color:',
                    'data' => '#ffffff',
                ))
            ->add('save', 'submit', array('label' => 'Add sense'));
    }
    
    public function getName() {
        return "sense";
    }
}
