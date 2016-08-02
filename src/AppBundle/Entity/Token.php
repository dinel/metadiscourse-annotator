<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Token
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="token")
 */
class Token {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=256)
     */
    protected $content;
            
   /**
     * @ORM\ManyToOne(targetEntity="Text", inversedBy="tokens")
     * @ORM\JoinColumn(name="text_id", referencedColumnName="id")
     */
    protected $document;
    
    /**
     * @ORM\ManyToOne(targetEntity="Markable")
     * @ORM\JoinColumn(name="markable_id", referencedColumnName="id")
     **/
    private $markable;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $newLineBefore;
    
    /**
     * The constructor
     */
    public function __construct($content) {
        $this->content = trim($content);
        $this->markable = null;
        $this->newLineBefore = 0;
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
     * Set content
     *
     * @param string $content
     *
     * @return Token
     */
    public function setContent($content)
    {
        $this->content = trim($content);

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Converts the Token to string
     */
    public function __toString() {
        return $this->getContent();
    }

    /**
     * Set document
     *
     * @param \AppBundle\Entity\Text $document
     *
     * @return Token
     */
    public function setDocument(\AppBundle\Entity\Text $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \AppBundle\Entity\Text
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set markable
     *
     * @param \AppBundle\Entity\Markable $markable
     *
     * @return Token
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
     * Get class
     */
    public function getCSSClass() {
        if(! $this->markable) {
            return "normal_tok";
        } else {
            return "meta-marker";
        }
    }

    /**
     * Set newLineBefore
     *
     * @param integer $newLineBefore
     *
     * @return Token
     */
    public function setNewLineBefore($newLineBefore)
    {
        $this->newLineBefore = $newLineBefore;

        return $this;
    }

    /**
     * Get newLineBefore
     *
     * @return integer
     */
    public function getNewLineBefore()
    {
        return $this->newLineBefore;
    }
}
