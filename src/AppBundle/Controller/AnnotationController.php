<?php

/* 
 * Copyright 2020 dinel.
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

class AnnotationController extends Controller {
    /**
     * @Route("/annotation/remove/{id}", name="remove_annotation") 
     * Removes an annotation
     * @param int $id the ID of the annotation to be deleted
     */
    public function removeAnnotationAction(int $id) {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $annotation = $doctrine->getRepository('AppBundle:Annotation')
                               ->find($id);
        
        if($annotation) {
            $em->remove($annotation);
            $em->flush();
            $em->clear();
            
            return new JsonResponse(['success' => True]);
        } else {
            return new JsonResponse(['success' => False]);
        }
    }    
}