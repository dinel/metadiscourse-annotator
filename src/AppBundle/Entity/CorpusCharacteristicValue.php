<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of CorpusCharacteristicValue
 *
 * @author dinel
 */


/**
 * @ORM\Entity
 * @ORM\Table(name="corpuscharacteristicvalue")
 */

class CorpusCharacteristicValue {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, unique=false)
     * Name of the value. This is a string like "20th century"
     */
    protected $name;
    
    /**
     * @ORM\ManyToOne(targetEntity="CorpusCharacteristic", inversedBy="values")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * Link to the corpus characteristic
     */
    protected $characteristic;   
    
    /**
     * @ORM\OneToMany(targetEntity="CharacteristicValuePairs", mappedBy="value")
    */
    protected $pairs;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pairs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return CorpusCharacteristicValue
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
     * Set characteristic
     *
     * @param \AppBundle\Entity\CorpusCharacteristic $characteristic
     *
     * @return CorpusCharacteristicValue
     */
    public function setCharacteristic(\AppBundle\Entity\CorpusCharacteristic $characteristic = null)
    {
        $this->characteristic = $characteristic;

        return $this;
    }

    /**
     * Get characteristic
     *
     * @return \AppBundle\Entity\CorpusCharacteristic
     */
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * Add pair
     *
     * @param \AppBundle\Entity\CharacteristicValuePairs $pair
     *
     * @return CorpusCharacteristicValue
     */
    public function addPair(\AppBundle\Entity\CharacteristicValuePairs $pair)
    {
        $this->pairs[] = $pair;

        return $this;
    }

    /**
     * Remove pair
     *
     * @param \AppBundle\Entity\CharacteristicValuePairs $pair
     */
    public function removePair(\AppBundle\Entity\CharacteristicValuePairs $pair)
    {
        $this->pairs->removeElement($pair);
    }

    /**
     * Get pairs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPairs()
    {
        return $this->pairs;
    }
}
