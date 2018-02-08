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

namespace AppBundle\Utils;

/**
 * Class which implements a number of static functions that are useful
 * in several controllers
 *
 * @author dinel
 */
class SharedFunctions {
    
    /**
     * Function which returns an array with the categories organised in a tree
     * like structure
     * @param object $doctrine an instance of Doctrine
     * @return array the categories organised in a tree like structure ordered
     * alphabetically, both the parents and the leaves
     */
    public static function getCategoryTree($doctrine) {
        $categories = $doctrine->getRepository("AppBundle:Category")->findBy([], ['name' => 'ASC']);
        $cat_tree = array();       
        
        foreach($categories as $category) {
            if($category->getName() === "No parent category") {
                continue;
            }
            
            if(! $category->getParent()) {
                $cat_tree[$category->getName()] = array();
                $cat_tree[$category->getName()][] = $category;                
            }
        }
        
        foreach($categories as $category) {
            if($category->getName() === "No parent category") {
                continue;
            }
            
            if($category->getParent()) {
                $cat_tree[$category->getParent()->getName()][] = $category;                
            }
        }
        
        ksort($cat_tree, SORT_FLAG_CASE);
        
        return $cat_tree;
    }
    
    /**
     * Function which compares two strings
     * @param string $a the first string
     * @param string $b the second string
     * @return boolean True if the two strings are equal
     */
    public static function sameWord($a, $b) {
        return strtolower($a) == strtolower($b);
    }
}
