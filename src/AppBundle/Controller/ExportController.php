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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Utils\SharedFunctions;

/**
 * Controller which provides the functionality to expert annotation to XML
 *
 * @author dinel
 */
class ExportController extends Controller {
    
    /**
     * Action which exports a text as XML
     * @Route("/admin/export_document/{id}", name="export_document")
     * @Method({"GET"})
     * @param integer $id the ID of the document to be exported
     */
    public function exportToXMLAction($id) {
        $this->getDoctrine()->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger(null);
        ini_set('memory_limit', '-1');
        
        $annotated_doc = "<doc>";
        $doc = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->find($id);
        
        if($doc) {            
            $tokens = SharedFunctions::getTokensFromDocument($id, 
                    $this->getDoctrine());
            
            while (($row = $tokens->next()) !== false) {
                $token = $row[0];
                $annotations = $this->getAnnotation($token->getId());
                $element = htmlspecialchars($token->getContent());
                 
                if($annotations) {
                    $tags = "<marker id='" . $token->getId() . "'>";
                    foreach($annotations as $annotation) {
                        $tags .= sprintf("<annotation id='%s' annotator='%s' sense='%s'/>",
                                $annotation->getId(), 
                                $annotation->getUserName(),
                                $annotation->getSense() ? $annotation->getSense()->getDefinition() : "NOT");
                    }
                    $element = $tags . $element . "</marker>";
                } else {
                    $element = " " . $element . " ";
                }
                
                if($token->getNewLineBefore()) {
                    $element .= "\n";
                }
                $annotated_doc .= $element;
            }
        }        
        
        $annotated_doc .= "</doc>";
        $response = new Response($annotated_doc);
        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');
        return $response;
    }
    
    /**
     * Returns the annotation for a token
     * @param integer $token
     * @return array an array which contains the annotation assigned to a token
     */
    private function getAnnotation($token) {
        return $this->getDoctrine()
                    ->getRepository('AppBundle:Annotation')
                    ->findBy(array('token' => $token));
    }
}
