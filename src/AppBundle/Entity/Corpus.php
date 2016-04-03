<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Corpus
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="corpus")
 */

class Corpus {
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
     * @ORM\Column(type="string")
     */
    protected $description;  
    
    /**
     * @ORM\OneToMany(targetEntity="CharacteristicValuePairs", mappedBy="corpus")
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
     * @return Corpus
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
     * Set description
     *
     * @param string $description
     *
     * @return Corpus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add pair
     *
     * @param \AppBundle\Entity\CharacteristicValuePairs $pair
     *
     * @return Corpus
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
