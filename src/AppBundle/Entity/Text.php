<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Text
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="text")
 */
class Text {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=256)
     */
    protected $title;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $description;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $the_text;
    
    /**
     * @ORM\OneToMany(targetEntity="Token", mappedBy="document")
     */
    protected $tokens;
    
    /**
     * @ORM\ManyToMany(targetEntity="Corpus")
     */
    protected $corpora;
    
    /**
     * @ORM\ManyToMany(targetEntity="Domain")
     * @ORM\JoinTable(name="texts_domains",
     *      joinColumns={@ORM\JoinColumn(name="doc_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="domain_id", referencedColumnName="id")}
     *      )
     */
    protected $domains;
     
    /**
     * The constructor
     */
    public function __construct() {
        $this->tokens = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Text
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Text
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
     * Set theText
     *
     * @param string $theText
     *
     * @return Text
     */
    public function setTheText($theText)
    {
        $this->the_text = $theText;

        return $this;
    }

    /**
     * Get theText
     *
     * @return string
     */
    public function getTheText()
    {
        return $this->the_text;
    }

    /**
     * Add token
     *
     * @param \AppBundle\Entity\Token $token
     *
     * @return Text
     */
    public function addToken(\AppBundle\Entity\Token $token)
    {
        $this->tokens[] = $token;
        $token->setDocument($this);

        return $this;
    }

    /**
     * Remove token
     *
     * @param \AppBundle\Entity\Token $token
     */
    public function removeToken(\AppBundle\Entity\Token $token)
    {
        $this->tokens->removeElement($token);
    }

    /**
     * Get tokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Add domain
     *
     * @param \AppBundle\Entity\Domain $domain
     *
     * @return Text
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

    /**
     * Add corpora
     *
     * @param \AppBundle\Entity\Corpus $corpora
     *
     * @return Text
     */
    public function addCorpora(\AppBundle\Entity\Corpus $corpus)
    {
        $this->corpora[] = $corpus;
        $corpus->addText($this);

        return $this;
    }

    /**
     * Remove corpora
     *
     * @param \AppBundle\Entity\Corpus $corpus
     */
    public function removeCorpora(\AppBundle\Entity\Corpus $corpus)
    {
        $this->corpora->removeElement($corpus);
    }

    /**
     * Get corpora
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCorpora()
    {
        return $this->corpora;
    }
}
