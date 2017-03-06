<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class which defines a set of preferences for an annotation
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="annotation_preference")
 */

class AnnotationPreference {
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;    
    
    /**
     * @ORM\ManyToMany(targetEntity="Category")
     */
    private $categories;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $notMarkableLabel;                     
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $showPolarity;  
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $showCategories;  
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return AnnotationPreference
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
     * @return AnnotationPreference
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
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return AnnotationPreference
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \AppBundle\Entity\Category $category
     */
    public function removeCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set notMarkableLabel
     *
     * @param string $notMarkableLabel
     *
     * @return AnnotationPreference
     */
    public function setNotMarkableLabel($notMarkableLabel)
    {
        $this->notMarkableLabel = $notMarkableLabel;

        return $this;
    }

    /**
     * Get notMarkableLabel
     *
     * @return string
     */
    public function getNotMarkableLabel()
    {
        return $this->notMarkableLabel;
    }

    /**
     * Set showPolarity
     *
     * @param \int $showPolarity
     *
     * @return AnnotationPreference
     */
    public function setShowPolarity($showPolarity)
    {
        $this->showPolarity = $showPolarity;

        return $this;
    }

    /**
     * Get showPolarity
     *
     * @return \int
     */
    public function getShowPolarity()
    {
        return $this->showPolarity;
    }

    /**
     * Set showCategories
     *
     * @param integer $showCategories
     *
     * @return AnnotationPreference
     */
    public function setShowCategories($showCategories)
    {
        $this->showCategories = $showCategories;

        return $this;
    }

    /**
     * Get showCategories
     *
     * @return integer
     */
    public function getShowCategories()
    {
        return $this->showCategories;
    }
}
