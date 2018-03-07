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
     * Returns the md5sum for the string
     * @param string $string the string for which the hash is needed. Only the 
     * part after / is considered
     * @return string the md5sum for the string
     */
    public static function markableHashFilter($string) {
        $pos = strpos($string, "/");
        if($pos !== false) {
            $string = substr($string, $pos + 1);
        }
        $md5str = md5($string);        
        $md5str_len = strlen($md5str);
        
        $ret = "";
        for($i = 0; $i < $md5str_len; $i++) {
            if(ctype_alpha($md5str[$i])) {
                $ret .= $md5str[$i];
            }
        }
        
        return $ret;
    }
        
    /**
     * Removes a markable from the database and all the annotation associated
     * with it. Takes an optional parameter which indicates which form
     * @param Markable $mark
     * @param type $em
     * @param $doctrine handle to doctrine
     * @param string $form the alternative form to delete
     */
    public static function removeMarkable($mark, $em, $doctrine, $form = null) {
        $tokens = $doctrine->getRepository('\AppBundle\Entity\Token')
                           ->createQueryBuilder('t')
                           ->where('t.markable = :id')
                           ->setParameter('id', $mark->getId())
                           ->getQuery()
                           ->iterate();
            
        while (($row = $tokens->next()) !== false) {            
            $token = $row[0];
            if($form && ! SharedFunctions::sameWord($form, $token->getContent())) {
                continue;
            }
            
            $token->setMarkable(null);
            $em->persist($token);
            
            // tidy up if there is any left annotation with senseid = 0 (not metadiscourse)
            $annotations = $doctrine->getRepository('AppBundle:Annotation')
                                    ->createQueryBuilder('a')
                                    ->where('a.token = :id')
                                    ->setParameter('id', $token->getId())
                                    ->getQuery()
                                    ->iterate();
            while (($row = $annotations->next()) !== false) {
                $annotation = $row[0];
                $em->remove($annotation);
            }
        }
        if(! $em->contains($mark)) {
            $mark = $em->merge($mark);
        }

        $em->flush();
        if(! $form) $em->remove($mark);
        $em->flush();
        $em->clear();
    }
    
    /**
     * Removes a sense
     * @param AppBundle\Entity\Sense $sense
     * @param AppBundle\Entity\Markable $mark
     * @param type $em
     */
    public static function removeSense($sense, $mark, $em, $doctrine) {
        $annotations = $doctrine->getRepository('AppBundle:Annotation')
                                ->createQueryBuilder('a')
                                ->where('a.sense = :id')
                                ->setParameter('id', $sense->getId())
                                ->getQuery()
                                ->iterate();
            
        while (($row = $annotations->next()) !== false) {
            $annotation = $row[0];
            $em->remove($annotation);
        }
        $mark->removeSense($sense);
        if(! $em->contains($sense)) {
            $sense = $em->merge($sense);
        }
        $em->remove($sense);
        $em->flush();
        $em->clear();
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
        
    /**
     * Function that returns a tuple that contain the left and right context 
     * for concordances
     * 
     * @param int $term_id the ID of the term for which the context is retrieved
     * @param string $term the actual term. Probably it will be removed because it
     *                     is not really necessary
     * @param EntityManager $em the entity manager
     * @return array a tuple that contains (left context, term, right context)
     */    
    public static function getSentence($term_id, $term, $em) {
        $str_r = self::getContext(
                "SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id > ?1 ORDER BY t.id", 
                $term_id, 1, $em);
                                
        $str_l = self::getContext("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id < ?1 ORDER BY t.id DESC", 
                $term_id, 2, $em);
        
        return array($str_l, $term, $str_r);
    }
    
    
    /***********************************************************************
     * 
     * Private methods from here
     * 
     ***********************************************************************/      
    
    /**
     * Helper function that retrieves the left or right context for a term
     * @param string $str_query the query that needs to be run to retrieve the 
     * context
     * @param int $term_id the ID of the term
     * @param int $direction indicates whether it is left context (value 1) or 
     * right context (any other value)
     * @param EntityManager $em the entity manager
     * @return string the context
     */
    private function getContext($str_query, $term_id, $direction, $em) {        
        $query = $em->createQuery($str_query);
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            if($direction == 1) {
                $str .= ($token["content"] . " ");
            } else {
                $str = ($token["content"] . " ") . $str;
            }
            
            if (strlen($str) > 40) {
                break;
            }
        }
        
        $em->clear();
        
        return $str;        
    }
}
