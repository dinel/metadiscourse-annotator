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
 * Description of CorpusType
 *
 * @author dinel
 */
class CorpusType extends AbstractType {
    
    /** 
     * The id of the corpus to be edited. Currently the value is not used
     * only if it is null or not
     * @var type 
     */
    private $id;


    public function __construct($id) {
        $this->id = $id;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text')
            ->add('description', 'textarea');
        
        if($this->id) {
            $builder->add('save', 'submit', array('label' => 'Edit corpus'));
        } else {
            $builder->add('save', 'submit', array('label' => 'Add corpus'));
        }
    }
        
    public function getName() {
        return "corpus";
    }
}
