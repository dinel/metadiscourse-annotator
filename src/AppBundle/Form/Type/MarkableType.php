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
            /*
            ->add('domains', 'entity', array(
                    'class'     => 'AppBundle:Domain',
                    'choice_label' => 'Domains',
                    'expanded'  => true,
                    'multiple'  => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('d')
                                      ->where('d.disabled = 0');
                    },
                ))*/
            ->add('save', 'submit', array(
                'label' => $this->in_edit_mode ? 'Edit marker' : 'Add marker')
            );
    }

    public function getName() {
        return "markable";
    }

//put your code here
}
