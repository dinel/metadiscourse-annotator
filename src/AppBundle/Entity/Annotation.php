<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Annotation
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="annotation")
 */
class Annotation {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Token")
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id")
     **/
    protected $token;
    
    /**
     * @ORM\ManyToOne(targetEntity="Sense")
     * @ORM\JoinColumn(name="sense_id", referencedColumnName="id")
     **/
    protected $sense;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $comments;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $userName;
    
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;
    
    /**
     * @ORM\Column(type="integer", nullable = true)
     */
    protected $polarity;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $uncertain;

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
     * Set token
     *
     * @param \AppBundle\Entity\Token $token
     *
     * @return Annotation
     */
    public function setToken(\AppBundle\Entity\Token $token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return \AppBundle\Entity\Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set sense
     *
     * @param \AppBundle\Entity\Sense $sense
     *
     * @return Annotation
     */
    public function setSense(\AppBundle\Entity\Sense $sense = null)
    {
        $this->sense = $sense;

        return $this;
    }

    /**
     * Get sense
     *
     * @return \AppBundle\Entity\Sense
     */
    public function getSense()
    {
        return $this->sense;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Annotation
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return Annotation
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Annotation
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * Get category as a formatted string
     * 
     */
    public function getCategoryName() {
        if($this->getCategory() && $this->getCategory()->getParent()) {
            return $this->category->getParent()->getName() . "/" . $this->getCategory()->getName();
        } elseif ($this->getCategory()) {
            return $this->getCategory()->getName();
        } else {
            return "";
        }
    }

    /**
     * Set polarity
     *
     * @param integer $polarity
     *
     * @return Annotation
     */
    public function setPolarity($polarity)
    {
        $this->polarity = $polarity;

        return $this;
    }

    /**
     * Get polarity
     *
     * @return integer
     */
    public function getPolarity()
    {
        return $this->polarity;
    }

    /**
     * Set uncertain
     *
     * @param boolean $uncertain
     *
     * @return Annotation
     */
    public function setUncertain($uncertain)
    {
        $this->uncertain = $uncertain;

        return $this;
    }

    /**
     * Get uncertain
     *
     * @return boolean
     */
    public function getUncertain()
    {
        return $this->uncertain;
    }
}
