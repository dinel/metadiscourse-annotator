<?php

/*
 * Copyright 2017 dinel.
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
 * Description of Cache
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="cache")
 */
class Cache {
    const COUNT_MARK = "MARK_FREQ_PER_DOC";
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * field which indicates the type if information 
     * @ORM\Column(type="string", length=256)
     */
    protected $type;
    
    /**
     * field which is used to link to IDs of other columns. Because the link can
     * be to different entities the linking is done this way
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $link;
    
        
    /**
     * field which indicates the type if information 
     * @ORM\Column(name="`key`", type="string", length=256)
     */
    protected $key;
    
    /**
     * field which indicates the type if information 
     * @ORM\Column(type="string", length=256)
     */
    protected $value;

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
     * Set type
     *
     * @param string $type
     *
     * @return Cache
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set link
     *
     * @param integer $link
     *
     * @return Cache
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return integer
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Cache
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set key
     *
     * @param string $key
     *
     * @return Cache
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
