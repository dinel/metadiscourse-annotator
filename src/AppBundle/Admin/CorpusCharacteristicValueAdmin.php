<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

use AppBundle\Entity\CorpusCharacteristicValue;

/**
 * Description of CorpusCharacteristicValueAdmin
 *
 * @author dinel
 */
class CorpusCharacteristicValueAdmin extends Admin 
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('name', 'text')
                ->add('characteristic', 'entity', array(
                    'class' => 'AppBundle\Entity\CorpusCharacteristic',
                    'property' => 'name',
                ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('name')
                ->add('characteristic', null, array(), 'entity', array(
                    'class' => 'AppBundle\Entity\CorpusCharacteristic',
                    'property' => 'name',
                ));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name')
                ->add('characteristic.name');
    }
    
    /**
     * 
     * @param \AppBundle\Admin\BlogPost $object
     * @return type
     */
    public function toString($object)
    {
        return $object instanceof CorpusCharacteristicValue
            ? $object->getName()
            : 'Characteristic'; // shown in the breadcrumb on the create view
    }
}
