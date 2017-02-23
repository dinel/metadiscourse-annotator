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
     * @ORM\Column(type="text", nullable = true)
     */
    protected $explanation;
    
    /**
     * @ORM\ManyToOne(targetEntity="Markable", inversedBy="senses", cascade={"persist"})
     * @ORM\JoinColumn(name="sense_id", referencedColumnName="id")
     */
    protected $markable;
    
    /**
     * @ORM\Column(type="text", length=10)
     */
    protected $fgColor;

    /**
     * @ORM\Column(type="text", length=10)
     */
    protected $bgColor;
    
    /**
     * @ORM\Column(type="integer", nullable = true)
     */
    protected $score;
    
    /*
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $defCategory;
    
    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="senses")
     * @ORM\JoinTable(name="senses_categories")
     */
    protected $categories;

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

    /**
     * Set fgColor
     *
     * @param string $fgColor
     *
     * @return Sense
     */
    public function setFgColor($fgColor)
    {
        $this->fgColor = $fgColor;

        return $this;
    }

    /**
     * Get fgColor
     *
     * @return string
     */
    public function getFgColor()
    {
        return $this->fgColor;
    }

    /**
     * Set bgColor
     *
     * @param string $bgColor
     *
     * @return Sense
     */
    public function setBgColor($bgColor)
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    /**
     * Get bgColor
     *
     * @return string
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * Set score
     *
     * @param integer $score
     *
     * @return Sense
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set explanation
     *
     * @param string $explanation
     *
     * @return Sense
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Sense
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
}
