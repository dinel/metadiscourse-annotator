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

/**
 * Description of AdminMarkerController
 *
 * @author dinel
 */
class AdminMarkerController extends Controller {
    
    /**
     * @Route("/admin", name="admin_page")
     */
    public function indexAction() {            
        $grouped_marks = $this->getGroupedMarks();
        
        return $this->render('Admin/index.html.twig', array(
                'groupped_markers' => $grouped_marks,
            ));
    }
    
    /**
     * @Route("/admin/maker/summary", name="admin_marker_summary")
     */
    public function makerSummaryAction() {            
        $grouped_marks = $this->getGroupedMarks();
        
        return $this->render('Admin/marker/marker_report.html.twig', array(
                'groupped_markers' => $grouped_marks,
            ));
    }
    
    /**********************************************************************
     * 
     * Private methods
     * 
     **********************************************************************/
    
    /**
     * Returns an array with the markers grouped on the basis of their initial
     */
    private function getGroupedMarks() {
        $marks = $this->getDoctrine()
                      ->getRepository("AppBundle:Markable")
                      ->createQueryBuilder('m')
                      ->orderBy('m.text')
                      ->getQuery()->getResult();
        
        $grouped_marks = array();
        foreach($marks as $mark) {
            if(ctype_alpha($mark->getText()[0])) {
                $grouped_marks[strtoupper($mark->getText()[0])][] = $mark;
            } else {
                $grouped_marks[" Punctuation"][] = $mark;
            }
        }

        ksort($grouped_marks);
        
        return $grouped_marks;
    }
}
