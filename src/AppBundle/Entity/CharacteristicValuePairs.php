<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of CharacteristicValuePairs
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="characteristicvaluepairs")
 */

class CharacteristicValuePairs {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="CorpusCharacteristicValue")
     */
    protected $value;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Corpus")
     */
    protected $corpus;

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
     * Set value
     *
     * @param \AppBundle\Entity\CorpusCharacteristicValue $value
     *
     * @return CharacteristicValuePairs
     */
    public function setValue(\AppBundle\Entity\CorpusCharacteristicValue $value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return \AppBundle\Entity\CorpusCharacteristicValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set corpus
     *
     * @param \AppBundle\Entity\Corpus $corpus
     *
     * @return CharacteristicValuePairs
     */
    public function setCorpus(\AppBundle\Entity\Corpus $corpus = null)
    {
        $this->corpus = $corpus;

        return $this;
    }

    /**
     * Get corpus
     *
     * @return \AppBundle\Entity\Corpus
     */
    public function getCorpus()
    {
        return $this->corpus;
    }
}
