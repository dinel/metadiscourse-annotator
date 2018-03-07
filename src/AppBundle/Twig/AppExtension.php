<?php

/*
 * Copyright 2015 - 2017 dinel.
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

/**
 * Twig extension which returns the hash for a marker
 *
 * @author dinel
 */

namespace AppBundle\Twig;

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
