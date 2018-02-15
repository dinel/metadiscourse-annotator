<?php

/*
 * Copyright 2017 - 2018 dinel.
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\Type\MarkableType;
use AppBundle\Utils\SharedFunctions;

/**
 * Controller which contains marker related actions
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
     * Displays a summary of all the markers
     * @Route("/admin/maker/summary", name="admin_marker_summary")
     */
    public function makerSummaryAction() {            
        $grouped_marks = $this->getGroupedMarks();
        
        return $this->render('Admin/marker/marker_report.html.twig', array(
                'groupped_markers' => $grouped_marks,
        ));
    }
    
    /**
     * Adds a new marker to the database
     * @Route("/admin/marker/add/{marker_text}", name="admin_marker_add")
     */
    public function addMarkerAction(Request $request, $marker_text = "") {
        $mark = new \AppBundle\Entity\Markable();
        $mark->setText($marker_text);
        $cat_tree = SharedFunctions::getCategoryTree($this->getDoctrine());
        
        $form = $this->createForm(MarkableType::class, $mark);
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mark);
            $em->flush();
            
            return $this->redirectToRoute("admin_sense_add", 
                    array('id_marker' => $mark->getId()));
        }
        
        return $this->render('Admin/new_mark.html.twig', array(
                'form' => $form->createView(),
                'in_edit_mode' => 0,
                'mark_id' => $mark->getId(),
                'cat_tree' => $cat_tree,
        ));
    }
    
    /**
     * Action which deletes a marker
     * @param integer $id_marker the ID of the marker to be deleted
     * @Route("/admin/marker/delete/{id_marker}", name="admin_mark_delete")
     */
    public function deleteMarkerAction($id_marker) {
        $doctrine = $this->getDoctrine();
        $mark = $doctrine->getRepository('AppBundle:Markable')
                         ->find($id_marker);
        $em = $doctrine->getManager();
        
        if($mark) {
            // Step 1: delete all the senses associated with this marker
            foreach($mark->getSenses() as  $sense) {
                SharedFunctions::removeSense($sense, $mark, $em, $doctrine);
            }
            
            // Step 2: find all the tokens that have a markable remove the markable
            SharedFunctions::removeMarkable($mark, $em, $doctrine);
        }
        
        return $this->redirectToRoute("admin_page");
    }
    
    /**
     * Action which edits an existing marker 
     * @Route("/admin/marker/edit/{id}", name="admin_marker_edit")
     */
    public function editMarkerAction(Request $request, $id) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id);        
        $cat_tree = SharedFunctions::getCategoryTree($this->getDoctrine());
        
        $form = $this->createForm(MarkableType::class, $mark, [
            'in_edit_mode' => true,
        ]);
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mark);
            $em->flush();
            
            return $this->redirectToRoute("admin_sense_add", 
                    array('id_marker' => $mark->getId()));
        }
        
        return $this->render('Admin/new_mark.html.twig', array(
                'form' => $form->createView(),
                'in_edit_mode' => 1,
                'mark_id' => $mark->getId(),
                'cat_tree' => $cat_tree,
        ));
    }    
    
    /**
     * @Route("/admin/marker/add-alternative/{id}/{alternative}")
     */
    public function addAlternativeAction($id, $alternative) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id);
        
        if($mark) {
            $msg = $this->wereAlternativeUsed($alternative);
            if($msg) {
                return new JsonResponse($msg);
            } else {
                $mark->addAlternative($alternative);
                $em = $this->getDoctrine()->getManager();
                $em->persist($mark);
                $em->flush();
            }
        }
        
        return new JsonResponse("OK");
    }
    
    /**
     * @Route("/admin/marker/remove-alternative/{id}/{alternative}")
     */
    public function removeAlternativeAction($id, $alternative) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id);
        
        if($mark) {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            SharedFunctions::removeMarkable($mark, $em, $doctrine, $alternative);            
            
            if(! $em->contains($mark)) {
                $mark = $em->merge($mark);
            }
            $mark->deleteAlternative($alternative);
            
            $em->flush();
        }
        
        return new JsonResponse();        
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
    
    /**
     * Checks whether an alternative form was used before somewhere.
     */
    private function wereAlternativeUsed($alternative) {
        $marks = $this->getDoctrine()
                      ->getRepository("AppBundle:Markable")
                      ->findAll();
        
        foreach($marks as $mark) {
            if(SharedFunctions::sameWord($mark->getText(), $alternative)) {
                return "The alternative form you are trying to add has already been used as a marker <b>" . $mark->getText() . "</b>!";
            }
            
            foreach(explode("##", $mark->getAlternatives()) as $alt) {
                if(SharedFunctions::sameWord($alt, $alternative)) {
                    return "The alternative form you are trying to add has already been used as an alternative form for marker <b>" . $mark->getText() . "</b>!";
                }
            }            
        }
        
        return false;
    }
}
