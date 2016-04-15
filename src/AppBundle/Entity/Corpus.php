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
     * @ORM\ManyToMany(targetEntity="Text")
     */
    protected $texts;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $statistics_outdated;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numberTypes;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numberTokens;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pairs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->statistics_outdated = true;
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

    /**
     * Add text
     *
     * @param \AppBundle\Entity\Text $text
     *
     * @return Corpus
     */
    public function addText(\AppBundle\Entity\Text $text)
    {
        $this->texts[] = $text;
        $this->statistics_outdated = 1;

        return $this;
    }

    /**
     * Remove text
     *
     * @param \AppBundle\Entity\Text $text
     */
    public function removeText(\AppBundle\Entity\Text $text)
    {
        $this->texts->removeElement($text);
        $text->removeCorpora($this);
        $this->statistics_outdated = 1;
    }

    /**
     * Get texts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTexts()
    {
        return $this->texts;
    }

    /**
     * Set statisticsOutdated
     *
     * @param integer $statisticsOutdated
     *
     * @return Corpus
     */
    public function setStatisticsOutdated($statisticsOutdated)
    {
        $this->statistics_outdated = $statisticsOutdated;

        return $this;
    }

    /**
     * Get statisticsOutdated
     *
     * @return integer
     */
    public function getStatisticsOutdated()
    {
        return $this->statistics_outdated;
    }

    /**
     * Set numberTypes
     *
     * @param integer $numberTypes
     *
     * @return Corpus
     */
    public function setNumberTypes($numberTypes)
    {
        $this->numberTypes = $numberTypes;

        return $this;
    }

    /**
     * Get numberTypes
     *
     * @return integer
     */
    public function getNumberTypes()
    {
        return $this->numberTypes;
    }

    /**
     * Set numberTokens
     *
     * @param integer $numberTokens
     *
     * @return Corpus
     */
    public function setNumberTokens($numberTokens)
    {
        $this->numberTokens = $numberTokens;

        return $this;
    }

    /**
     * Get numberTokens
     *
     * @return integer
     */
    public function getNumberTokens()
    {
        return $this->numberTokens;
    }
}
