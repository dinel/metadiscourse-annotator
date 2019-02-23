<?php

/*
 * Copyright 2018 dinel.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
     * @ORM\ManyToOne(targetEntity="Text", inversedBy="tokens", cascade={"persist"})
     * @ORM\JoinColumn(name="text_id", referencedColumnName="id")
     */
    protected $document;
    
    /**
     * @ORM\ManyToOne(targetEntity="Segment", inversedBy="tokens")
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id")
     */
    protected $segment;
    
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
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $xml_before;
    
    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $xml_after;
    
    /**
     * String that will be used to compare values. Usually this will be lemma
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $comparator;        
    
    /**
     * The constructor
     */
    public function __construct($content) {
        $this->content = trim($content);
        $this->markable = null;
        $this->newLineBefore = 0;
        $this->xml_before = "";
        $this->xml_after = "";
        $this->comparator = "";
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
        return trim($this->content);
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

    /**
     * Set segment
     *
     * @param \AppBundle\Entity\Segment $segment
     *
     * @return Token
     */
    public function setSegment(\AppBundle\Entity\Segment $segment = null)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get segment
     *
     * @return \AppBundle\Entity\Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }
    
    public function get_xml_before() {
        return $this->xml_before;
    }

    public function get_xml_after() {
        return $this->xml_after;
    }

    public function get_comparator() {
        return $this->comparator;
    }

    public function set_xml_before($xml_before) {
        $this->xml_before = $xml_before;
        return $this;
    }

    public function set_xml_after($xml_after) {
        $this->xml_after = $xml_after;
        return $this;
    }

    public function set_comparator($comparator) {
        $this->comparator = $comparator;
        return $this;
    }


}
