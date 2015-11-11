<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Domain
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="markable")
 */
class Markable 
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     */
    protected $text;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $description;
    
    /**
     * @ORM\OneToMany(targetEntity="Sense", mappedBy="markable")
     */
    protected $senses;
    
    /**
     * @ORM\ManyToMany(targetEntity="Domain")
     * @ORM\JoinTable(name="markers_domains",
     *      joinColumns={@ORM\JoinColumn(name="mark_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="domain_id", referencedColumnName="id")}
     *      )
     */
    protected $domains;
    
    /**
     * The constructor
     */
    public function _construct() {
        $this->senses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->domains = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Domain
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
     * @return Domain
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
     * Set text
     *
     * @param string $text
     *
     * @return Markable
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->senses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add sense
     *
     * @param \AppBundle\Entity\Sense $sense
     *
     * @return Markable
     */
    public function addSense(\AppBundle\Entity\Sense $sense)
    {
        $this->senses[] = $sense;
        $sense->setMarkable($this);

        return $this;
    }

    /**
     * Remove sense
     *
     * @param \AppBundle\Entity\Sense $sense
     */
    public function removeSense(\AppBundle\Entity\Sense $sense)
    {
        $this->senses->removeElement($sense);
    }

    /**
     * Get senses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSenses()
    {
        return $this->senses;
    }

    /**
     * Add domain
     *
     * @param \AppBundle\Entity\Domain $domain
     *
     * @return Markable
     */
    public function addDomain(\AppBundle\Entity\Domain $domain)
    {
        $this->domains[] = $domain;

        return $this;
    }

    /**
     * Remove domain
     *
     * @param \AppBundle\Entity\Domain $domain
     */
    public function removeDomain(\AppBundle\Entity\Domain $domain)
    {
        $this->domains->removeElement($domain);
    }

    /**
     * Get domains
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomains()
    {
        return $this->domains;
    }
}
