<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of User
 *
 * @author dinel
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=256)
     */
    protected $full_name;
    
    /**
     * Indicates that the user should change their password on the next login
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $change_password;

    public function __construct()
    {
        parent::__construct();
    }
    
    public function isAdmin() {
        return $this->hasRole('ROLE_ADMIN');
    }
    
    public function setFullName($full_name) {
        $this->full_name = $full_name;
        
        return $this->full_name;
    }
    
    public function getFullName() {
        return $this->full_name;
    }
    
    public function getChangePassword() {
        return $this->change_password;
    }
    
    public function setChangePassword($change) {
        $this->change_password = $change;
    }
}
