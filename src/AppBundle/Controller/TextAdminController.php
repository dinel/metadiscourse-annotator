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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

use AppBundle\Entity\PinnedText;

/**
 * Description of TextAdminController
 *
 * @author dinel
 */
class TextAdminController extends Controller {
    /**
     * Action for when a user pins a text.
     * @Route("/corpus/pin_text/{text}")
     * @Method({"POST"})
     */
    public function pinTextAction(Request $request, $text) {
        if($request->isXmlHttpRequest()) {
            $user = $this->getUser()->getId();
            $corpus = $this->get('session')->get("corpus");
            if($user && $corpus && $text) {
                $pt = new PinnedText();
                $pt->setCorpusId($corpus);
                $pt->setTextId($text);
                $pt->setUserId($user);
                $pt->setType("PIN");
                $pt->setDate(new DateTime("now"));
                $em = $this->getDoctrine()->getManager();
                $em->persist($pt);
                $em->flush();

                return new JsonResponse("Success");
            }

            return new JsonResponse("Error");        
        } else {
            return $this->redirectToRoute("homepage");
        }
    }
    
    /**
     * Action for when a user pins a text.
     * @Route("/corpus/unpin_text/{text}")
     * @Method({"POST"})
     */
    public function unpinTextAction(Request $request, $text) {
        if($request->isXmlHttpRequest()) {
            $uid = $this->getUser()->getId();
            $cid = $this->get('session')->get("corpus");
            if($uid && $cid && $text) {
                $pinnedText = $this->getDoctrine()
                                ->getRepository("AppBundle:PinnedText")
                                ->findBy(['corpusId' => $cid, 'userId' => $uid, 
                                          'textId' => $text, 'type' => 'PIN']);
                if($pinnedText) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($pinnedText[0]);
                    $em->flush();
                    $em->clear();
                }

                return new JsonResponse("Success");
            }

            return new JsonResponse("Error");        
        } else {
            return $this->redirectToRoute("homepage");
        }
    }
    
    /**
     * Action for when a user pins a text.
     * @Route("/corpus/text_done/{text}")
     * @Method({"POST"})
     */
    public function markTextDoneAction(Request $request, $text) {
        if($request->isXmlHttpRequest()) {
            $user = $this->getUser()->getId();
            $corpus = $this->get('session')->get("corpus");
            if($user && $corpus && $text) {
                $pt = new PinnedText();
                $pt->setCorpusId($corpus);
                $pt->setTextId($text);
                $pt->setUserId($user);
                $pt->setType("DONE");
                $pt->setDate(new DateTime("now"));
                $em = $this->getDoctrine()->getManager();
                $em->persist($pt);
                $em->flush();

                return new JsonResponse("Success");
            }

            return new JsonResponse("Error");        
        } else {
            return $this->redirectToRoute("homepage");
        }
    }
    
    /**
     * Action for when a user pins a text.
     * @Route("/corpus/text_undone/{text}")
     * @Method({"POST"})
     */
    public function markTextUndoneAction(Request $request, $text) {
        if($request->isXmlHttpRequest()) {
            $uid = $this->getUser()->getId();
            $cid = $this->get('session')->get("corpus");
            if($uid && $cid && $text) {
                $doneText = $this->getDoctrine()
                                ->getRepository("AppBundle:PinnedText")
                                ->findBy(['corpusId' => $cid, 'userId' => $uid, 
                                          'textId' => $text, 'type' => 'DONE']);
                if($doneText) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($doneText[0]);
                    $em->flush();
                    $em->clear();
                }

                return new JsonResponse("Success");
            }

            return new JsonResponse("Error");        
        } else {
            return $this->redirectToRoute("homepage");
        }
    }
    
    /****************************************************************
     * Utility methods
     ****************************************************************/
    
}
