<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Twig;

/**
 * Description of AppExtension
 *
 * @author dinel
 */
class AppExtension extends \Twig_Extension {
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('markHash', array($this, 'markableHashFilter')),
        );
    }
    
    public function markableHashFilter($string) {
        $pos = strpos($string, "/");
        if($pos !== false) {
            $string = substr($string, $pos + 1);
        }
        $md5str = md5($string);
        $ret = "";
        for($i = 0; $i < strlen($md5str); $i++) {
            if(ctype_alpha($md5str[$i])) {
                $ret .= $md5str[$i];
            }
        }
        
        return $ret;
    }

    public function getName()
    {
        return 'app_extension';
    }
}





