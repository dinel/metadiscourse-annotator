<?php

/*
 * Copyright (C) 2016 dinel
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Segment
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="segment")
 */
class Segment {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $segment;
    
    /**
     * @ORM\OneToMany(targetEntity="Token", mappedBy="segment")
     */
    protected $tokens;
    
    /**
     * @ORM\OneToOne(targetEntity="Segment")
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id")
     */
    protected $alignment;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tokens = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set segment
     *
     * @param string $segment
     *
     * @return Segment
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get segment
     *
     * @return string
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * Add token
     *
     * @param \AppBundle\Entity\Token $token
     *
     * @return Segment
     */
    public function addToken(\AppBundle\Entity\Token $token)
    {
        $this->tokens[] = $token;
        $token->setSegment($this);

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
     * Set alignment
     *
     * @param \AppBundle\Entity\Segment $alignment
     *
     * @return Segment
     */
    public function setAlignment(\AppBundle\Entity\Segment $alignment = null)
    {
        $this->alignment = $alignment;

        return $this;
    }

    /**
     * Get alignment
     *
     * @return \AppBundle\Entity\Segment
     */
    public function getAlignment()
    {
        return $this->alignment;
    }
}
