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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Cache;

/**
 * SelectionController implements a variety of actions linked to selection. 
 * Necessary to keep the other controllers of manageable size
 *
 * @author dinel
 */
class SelectionController extends Controller {
    
    /**
     * Returns a view which allows users to select a mark
     * @Route("/document/select-mark/{id}", name="select_mark")
     */
    public function selectMarkAction($id) {
        $cat_tree = $this->getCategoriesTree();        
        $stats = $this->getMarkFrequencyFromCache($id);
        
        return $this->render('Annotator/select_mark.html.twig', array(
                'categories' => $cat_tree,
                'stats' => $stats,
                'id' => $id,
            ));        
    }
    
    /**
     * @Route("/document/select-category/{id}", name="select_category")
     */
    public function selectCategoryAction($id) {
        $cat_tree = $this->getCategoriesTree();        
        $stats = $this->getMarkFrequencyFromCache($id);        
        
        return $this->render('Annotator/select_category.html.twig', array(
                'categories' => $cat_tree,
                'stats' => $stats,
                'id' => $id,
            ));        
    }
    
    /**********************************************************************
     * 
     * Private methods here
     * 
     **********************************************************************/
    
    /**
     * Returns an array which contains all the categories
     * @return array with categories
     */
    private function getCategoriesTree() {
        $cat_tree = array();
        
        $categories = $this->getDoctrine()
                           ->getRepository("AppBundle:Category")
                           ->findAll();        
        
        foreach($categories as $category) {
            if($category->getName() == "No parent category") {
                continue;
            }
            
            if($category->getParent()) {
                $cat_tree[$category->getParent()->getName()][] = $category;                
            } else {
                $cat_tree[$category->getName()] = array();
                $cat_tree[$category->getName()][] = $category;                
            }
        }
        
        return $cat_tree;        
    }
    
    /**
     * Returns an associative array with the markers and their frequencies
     * from cache
     * @param int $id the ID of the document
     * @return array the array of pairs
     */
    private function getMarkFrequencyFromCache($id) {
        $stats = array();
        
        $pairs = $this->getDoctrine()
                       ->getRepository('AppBundle:Cache')
                       ->createQueryBuilder('t')
                       ->where('t.link = :id AND t.type = :type')
                       ->setParameter('id', $id)
                       ->setParameter('type', Cache::COUNT_MARK)
                       ->getQuery()
                       ->iterate();
        
        while (($row = $pairs->next()) !== false) {          
            $pair = $row[0];
            $stats[$pair->getKey()] = $pair->getValue();
        }
        
        return $stats;
    }
}
