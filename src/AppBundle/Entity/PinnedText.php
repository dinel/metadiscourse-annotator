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
 * Description of PinnedText
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="pinned_text")
 */
class PinnedText {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $textId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $corpusId;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $type;
    
    /**
     * @ORM\Column(type="date")     
     */
    protected $date;

    public function getId() {
        return $this->id;
    }

    public function getTextId() {
        return $this->textId;
    }

    public function getCorpusId() {
        return $this->corpusId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTextId($textId) {
        $this->textId = $textId;
        return $this;
    }

    public function setCorpusId($corpusId) {
        $this->corpusId = $corpusId;
        return $this;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function getDate() {
        return $this->date;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setDate($date) {
        $this->date = $date;
        return $this;
    }
}
