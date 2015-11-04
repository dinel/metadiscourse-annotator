<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Domain
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="sense")
 */
class Sense
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
        
    /**
     * @ORM\Column(type="text")
     */
    protected $definition;
    
    /**
     * @ORM\ManyToOne(targetEntity="Markable", inversedBy="senses")
     * @ORM\JoinColumn(name="sense_id", referencedColumnName="id")
     */
    protected $markable;


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
     * Set definition
     *
     * @param string $definition
     *
     * @return Sense
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get definition
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set markable
     *
     * @param \AppBundle\Entity\Markable $markable
     *
     * @return Sense
     */
    public function setMarkable(\AppBundle\Entity\Markable $markable = null)
    {
        $this->markable = $markable;

        return $this;
    }

    /**
     * Get markable
     *
     * @return \AppBundle\Entity\Markable
     */
    public function getMarkable()
    {
        return $this->markable;
    }
}
