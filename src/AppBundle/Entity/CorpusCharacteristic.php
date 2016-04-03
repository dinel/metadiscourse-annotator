<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of CorpusCharacteristic
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="corpuscharacteristic")
 */
class CorpusCharacteristic {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $name;    
    
    /**
     * @ORM\OneToMany(targetEntity="CorpusCharacteristicValue", mappedBy="characteristic")
     */
    protected $values;    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CorpusCharacteristic
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add value
     *
     * @param \AppBundle\Entity\CorpusCharacteristicValue $value
     *
     * @return CorpusCharacteristic
     */
    public function addValue(\AppBundle\Entity\CorpusCharacteristicValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param \AppBundle\Entity\CorpusCharacteristicValue $value
     */
    public function removeValue(\AppBundle\Entity\CorpusCharacteristicValue $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getValues()
    {
        return $this->values;
    }
}
