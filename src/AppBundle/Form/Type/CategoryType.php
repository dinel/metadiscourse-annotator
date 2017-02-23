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
 * Description of CategoryType
 *
 * @author dinel
 */
class CategoryType extends AbstractType {
    public function __construct($in_edit_mode = false) {
        $this->in_edit_mode = $in_edit_mode;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text')
            ->add('parent', 'entity', array(
                    'class'     => 'AppBundle:Category',
                    'choice_label' => 'Name',
                    'expanded'  => false,
                    'multiple'  => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('c')                                      
                                      ->where('c.parent is NULL');
                    },
                ))
            ->add('save', 'submit', array('label' => 
                $this->in_edit_mode ? 'Edit category' : 'Add category'));
    }
        
    public function getName() {
        return "category";
    }
}
