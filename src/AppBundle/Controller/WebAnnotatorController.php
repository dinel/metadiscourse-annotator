<?php

/*
 * Copyright 2015 - 2017 dinel.
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
 * Description of WebAnnotatorController
 *
 * @author dinel
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Sense;

class WebAnnotatorController extends Controller
{
    /**
     * @Route("/document/{id}/{id_token}", name="document_show", 
     *          requirements={"id": "\d+", "id_token": "\d+"})
     */
    public function indexAction($id, $id_token = null) {
        $doc = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->find($id);
        
        if($doc) {            
            $tokens = $this->getDoctrine()
                       ->getRepository('\AppBundle\Entity\Token')
                       ->createQueryBuilder('t')
                       ->where('t.document = :id')
                       ->setParameter('id', $id)
                       ->getQuery()
                       ->iterate();
            
            $tokens_style = array();
            $markers = array();
            
            $em = $this->getDoctrine()->getManager();
            
            $session = $this->get('session');
            $id_mark = $session->get('filter-mark-id');

            while (($row = $tokens->next()) !== false) {
                $token = $row[0];
                $ann = $this->getAnnotation($token);
                $style = " dsp-" . str_replace(" ", "-", strtolower($token->getContent()));
                if($ann && ($id_mark == null || $id_mark == $token->getMarkable()->getId())) {                    
                    $flag = 1;
                    if($ann[0]->getSense()) {
                        $style .= " meta-marker sense" . $ann[0]->getSense()->getId();
                    } else {
                        $style .= " meta-marker false-pos";
                    }
                } elseif ($token->getMarkable() && ($id_mark == null || $id_mark == $token->getMarkable()->getId())) {
                    $flag = 0;
                    $style .= " meta-marker meta-marker-todo";
                } else {
                    $style = "normal";
                }
                $tokens_style[] = array($token, $style);
                
                if($token->getMarkable() && ($id_mark == null || $id_mark == $token->getMarkable()->getId())) {
                    if(array_key_exists($token->getMarkable()->getId(), $markers) === false) {
                        $markers[$token->getMarkable()->getId()] = array($token->getMarkable(), 1, $flag);
                    } else {
                        $markers[$token->getMarkable()->getId()][1]++;
                        $markers[$token->getMarkable()->getId()][2] += $flag;
                    }                    
                }
                
                $em->detach($token);
            }
            $em->clear();
            
            $token = null;
            if($id_token) {
                $token = $this->getDoctrine()
                              ->getRepository('AppBundle:Token')
                              ->find($id_token);
            }
                        
            return $this->render('Annotator/index.html.twig', array(
                    'text' => $doc,
                    'tokens_style' => $tokens_style,
                    'token' => $token ? $token->getId() : null,
                    'markers' => $markers,
                ));
        }
    }
    
    /**
     * @Route("/document/select-category/{id}", name="select_mark")
     */
    public function selectMarkAction($id) {
        $categories = $this->getDoctrine()
                           ->getRepository("AppBundle:Category")
                           ->findAll();
        $cat_tree = array();
        
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
        
        return $this->render('Annotator/select.html.twig', array(
                'categories' => $cat_tree,
                'id' => $id,
            ));        
    }
    
    /**
     * @Route("/document/set-mark-id/{id_doc}/{id_mark}", name="set_mark")
     */
    public function setMarkIDAction($id_doc, $id_mark) {        
        $session = $this->get('session');
        $session->set("filter-mark-id", $id_mark);
        
        return $this->forward("AppBundle:WebAnnotator:index", 
                array("id" => $id_doc,
        ));
    }

    /**
     * @Route("/document/next/{id_token}")
     */
    public function nextAction($id_token, \Symfony\Component\HttpFoundation\Request $request) {
        $session = $this->get('session');
        $id_mark = $session->get('filter-mark-id');
            
        $token = $this->getDoctrine()
                      ->getRepository('AppBundle:Token')
                      ->find($id_token);
        $toks = $token->getDocument()->getTokens();
        $pos = $toks->indexOf($token) + 1;
        while($pos < $toks->count()) {
            $tok = $toks->get($pos);
            if($tok->getMarkable() && ($id_mark == null || $id_mark == $tok->getMarkable()->getId())) {
                $id_token = $tok->getId();
                break;
            }
            $pos++;
        }
        
        return $this->createAction($id_token, $request);
    }
    
    /**
     * @Route("/document/prev/{id_token}")
     */
    public function prevAction($id_token, \Symfony\Component\HttpFoundation\Request $request) {
        $session = $this->get('session');
        $id_mark = $session->get('filter-mark-id');
        
        $token = $this->getDoctrine()
                      ->getRepository('AppBundle:Token')
                      ->find($id_token);
        $toks = $token->getDocument()->getTokens();
        $pos = $toks->indexOf($token) - 1;
        while($pos >= 0) {
            $tok = $toks->get($pos);
            if($tok->getMarkable() && ($id_mark == null || $id_mark == $tok->getMarkable()->getId())) {
                $id_token = $tok->getId();
                break;
            }
            $pos--;
        }
        
        return $this->createAction($id_token, $request);
    }

    /**
     * @Route( "/document/marker/{id_token}", name="show_annotation" )
     */
    public function createAction($id_token, \Symfony\Component\HttpFoundation\Request $request) {
        //if($request->isXmlHttpRequest()) {
            //TODO: make the submission secure

            $token = $this->getDoctrine()
                          ->getRepository('AppBundle:Token')
                          ->find($id_token);
            $mark = $token->getMarkable();
            $senses = $mark->getSenses();
            
            // get the context
            $toks = $token->getDocument()->getTokens();
            $pos = $toks->indexOf($token);
            $context = join(" ", $toks->slice($pos - 10, 10))
                    . " <strong>" . $token->getContent() . "</strong> "
                    . join(" ", $toks->slice($pos + 1, 10));            

            $a_senses  = $senses->map(function($value) {
                return array($value->getId(), $value->getDefinition(), $value->getExplanation());
            });

            // check whether there is already annotation
            $annotation = $this->getAnnotation($id_token);

            $comment = "";
            $current_sense = "";
            $current_sense_id = 0;
            $polarity = 0;
            $uncertain = FALSE;
            $category = null;

            if($annotation) {
                $comment = $annotation[0]->getComments();
                if($annotation[0]->getSense()) {
                    $current_sense = $annotation[0]->getSense()->getDefinition();
                    $current_sense_id = $annotation[0]->getSense()->getId();
                    $category = $annotation[0]->getCategory();
                } else {
                    $current_sense = "N/M";                    
                }              
                $polarity = $annotation[0]->getPolarity();
                $uncertain = $annotation[0]->getUncertain();
            }
            
            $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Category");
            
            $parent_category_id = 0;
            $sub_category_id = 0;
            
            $parent_categories = $repository->findBy(array('parent' => NULL));
            $a_parent_cats  = array_map(function($value) {
                return array($value->getId(), $value->getName());
            }, $parent_categories);
            
            $a_subcats = array();
            if($category && $category->getParent()) {
                foreach($category->getParent()->getChildren() as $child) {
                    $a_subcats[] = array($child->getId(), $child->getName());
                }
                $parent_category_id = $category->getParent()->getId();
                $sub_category_id = $category->getId();
            } else {
                if($category) {
                    $parent_category_id = $category->getId();
                }
            }

            return new JsonResponse(array(
                    'tok_id' => $id_token, 
                    'mark_id' => $mark->getId(),
                    'mark_text' => $mark->getText(),
                    'senses' => $a_senses->toArray(),
                    'current_sense' => $current_sense,
                    'current_sense_id' => $current_sense_id,
                    'comment' => $comment,
                    'parent_categories' => $a_parent_cats,
                    'sub_categories' => $a_subcats,
                    'parent_category_id' => $parent_category_id,
                    'sub_category_id' => $sub_category_id,
                    'polarity' => $polarity,
                    'uncertain' => $uncertain,
                    'context' => $context,
                ));
        /*} else {
            return $this->redirectToRoute('homepage');
        }*/
  }
  
    /**
     * TODO: check whether it is worth keeping this
     * @Route( "/document/annotation/sense/{id}/{definition}", name="sense_add" )
     */
    public function addSenseAction($id, $definition, \Symfony\Component\HttpFoundation\Request $request) {
        if($request->isXmlHttpRequest()) {
            //TODO: make the submission secure
            $token = $this->getDoctrine()
                        ->getRepository('AppBundle:Token')
                        ->find($id);
            $mark = $token->getMarkable();
            $sense = new Sense();
            $sense->setDefinition($definition);
            $mark->addSense($sense);

            $em = $this->getDoctrine()->getManager();
            $em->persist($sense);
            $em->flush();

            return new JsonResponse("Success");        
        } else {
            return $this->redirectToRoute('homepage');
        }
    }
    
    /**
     * @Route( "/document/annotation/add/{token_id}/{sense_id}/{category_id}/{polarity}/{uncertain}/{comment}", 
     *         name="annotation_add", defaults={"comment"= ""} )
     */
    public function addAnnotationAction($token_id, $sense_id, $category_id, 
            $polarity, $uncertain, $comment, \Symfony\Component\HttpFoundation\Request $request) {
        if($request->isXmlHttpRequest()) {
            $token = $this->getDoctrine()
                        ->getRepository('AppBundle:Token')
                        ->find($token_id);
            $sense = $this->getDoctrine()
                        ->getRepository('AppBundle:Sense')
                        ->find($sense_id);
            $category = $this->getDoctrine()
                        ->getRepository('AppBundle:Category')
                        ->find($category_id);
            
            $annotation = $this->getAnnotation($token_id);
            if($annotation) {
                $annotation = $annotation[0];
            } else {
                $annotation = new \AppBundle\Entity\Annotation();            
                $annotation->setToken($token);
                $annotation->setUserName($this->getUser()->getUserName());
            }
                        
            $annotation->setSense($sense);
            $annotation->setComments($comment);            
            $annotation->setCategory($category);
            $annotation->setPolarity($polarity);
            $annotation->setUncertain($uncertain);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($annotation);
            $em->flush();
            $em->clear();
            
            return new JsonResponse(array(
                "style" => "sense" . $sense_id,
                "current_sense" => $sense ? $sense->getDefinition() : "Not a marker"));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }
    
   /**
     * @Route("/annotation/css", name="annotation_css")
    *  @Method({"GET"})
     */
    public function generateAnnotationCSSAction() {
        $senses = $this->getDoctrine()
                        ->getRepository('AppBundle:Sense')
                        ->findAll();
        
        $response = new Response($this->render('Annotator/annotation.css.twig', array(
            'senses' => $senses,
        )));
        $response->headers->set('Content-Type', 'text/css; charset=utf-8');
        
        return $response;
    }

    
    private function getAnnotation($token) {
        return $this->getDoctrine()
                    ->getRepository('AppBundle:Annotation')
                    ->findBy(array('token' => $token, 
                                   'userName' => $this->getUser()->getUserName()));
    }
}