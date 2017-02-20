<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Category
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="category")
 */

class Category 
{
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
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;        
    
    /**
     * @ORM\ManyToMany(targetEntity="Markable", mappedBy="categories")
     */
    private $markables;
    
    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->markables = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Category
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
    
    public function getCategories() {
        return $this->getName();
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Category $child
     *
     * @return Category
     */
    public function addChild(\AppBundle\Entity\Category $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Category $child
     */
    public function removeChild(\AppBundle\Entity\Category $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Category $parent
     *
     * @return Category
     */
    public function setParent(\AppBundle\Entity\Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add markable
     *
     * @param \AppBundle\Entity\Markable $markable
     *
     * @return Category
     */
    public function addMarkable(\AppBundle\Entity\Markable $markable)
    {
        $this->markables[] = $markable;

        return $this;
    }

    /**
     * Remove markable
     *
     * @param \AppBundle\Entity\Markable $markable
     */
    public function removeMarkable(\AppBundle\Entity\Markable $markable)
    {
        $this->markables->removeElement($markable);
    }

    /**
     * Get markables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMarkables()
    {
        return $this->markables;
    }
}
