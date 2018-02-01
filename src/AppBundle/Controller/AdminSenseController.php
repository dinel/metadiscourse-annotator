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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\Type\SenseType;
use AppBundle\Entity\Sense;

/**
 * Description of AdminSenseController
 *
 * @author dinel
 */
class AdminSenseController extends Controller {
    
    /**
     * Action which adds a sense to a given marker
     * @Route("/admin/sense/add/{id_marker}", name="admin_sense_add")
     */
    public function addSenseAction($id_marker, Request $request) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id_marker);
        // TODO: what to do if the marker is not found. Assumes it works right now
        if($mark) {
            $sense = new Sense();
            $sense->setBgColor('#ffffff');
            $sense->setFgColor('#000000');
            $sense->setScore(0);
            $mark->addSense($sense);
            $form = $this->createForm(new SenseType(), $sense);
            $form->handleRequest($request);
            
            if($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($mark);
                $em->flush();
                
                $sense = new Sense();
                $sense->setBgColor('#ffffff');
                $sense->setFgColor('#000000');
                $sense->setScore(0);
                $form = $this->createForm(SenseType::class, $sense);
            }
            
            return $this->render('Admin/new_sense.html.twig', array(
                'mark' => $mark,
                'form' => $form->createView(),
                'message' => 'Add a new sense',
                'initial_sense' => $sense,
                'delete_button' => 0,
            ));
        }
    }
        
    /**
     * Action which adds a sense to a given marker
     * @Route("/admin/sense/edit/{id_marker}/{id_sense}", name="admin_sense_edit")
     */
    public function editSenseAction($id_marker, $id_sense, Request $request) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id_marker);
        
        $sense = $this->getDoctrine()
                     ->getRepository('AppBundle:Sense')
                     ->find($id_sense);
        // TODO: what to do if the marker is not found. Assumes it works right now
        if($mark && $sense) {
            $form = $this->createForm(SenseType::class, $sense, [
                'in_edit_mode' => true,
            ]);
            $form->handleRequest($request);
            
            if($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($mark);
                $em->flush();
            }
            
            return $this->render('Admin/new_sense.html.twig', array(
                'mark' => $mark,
                'form' => $form->createView(),
                'message' => 'Edit sense',
                'initial_sense' => $sense,
                'delete_button' => 1,
            ));
        }
    }
}
