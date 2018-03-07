<?php

/*
 * Copyright 2015 - 2018 dinel.
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

use AppBundle\Utils\SharedFunctions;

class AppExtension extends \Twig_Extension {
    /**
     * Returns the extensions defined 
     * @return array the filters implemented
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('markHash', array($this, 'markableHashFilter')),
        );
    }
    
    /**
     * Returns the md5sum for the string
     * @param string $string the string for which the hash is needed. Only the 
     * part after / is considered
     * @return string the md5sum for the string
     */
    public function markableHashFilter($string) {
        return SharedFunctions::markableHashFilter($string);
    }
}
