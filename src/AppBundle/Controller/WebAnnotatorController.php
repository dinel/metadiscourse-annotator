<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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

use AppBundle\Entity\Sense;

class WebAnnotatorController extends Controller
{
    /**
     * @Route("/document/{id}", name="document_show")
     */
    public function indexAction($id) {
        $doc = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->find($id);
        
        if($doc) {
            $tokens = $doc->getTokens();
            $tokens_style = array();
            foreach($tokens as $token) {
                $ann = $this->getAnnotation($token);
                if($ann) {
                    if($ann[0]->getSense()) {
                        $tokens_style[] = array($token, "meta-marker sense" . $ann[0]->getSense()->getId());
                    } else {
                        $tokens_style[] = array($token, "meta-marker false-pos");
                    }
                } elseif ($token->getMarkable()) {
                    $tokens_style[] = array($token, "meta-marker meta-marker-todo");
                } else {
                    $tokens_style[] = array($token, "normal");
                }
                
            }
                        
            return $this->render('Annotator/index.html.twig', array(
                    'title' => $doc->getTitle(),
                    'tokens_style' => $tokens_style,
                ));
        }
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

            $a_senses  = $senses->map(function($value) {
                return array($value->getId(), $value->getDefinition());
            });

            // check whether there is already annotation
            $annotation = $this->getAnnotation($id_token);

            $comment = "";
            $current_sense = "";
            $current_sense_id = 0;
            $polarity = 0;
            $uncertain = FALSE;

            if($annotation) {
                $comment = $annotation[0]->getComments();
                if($annotation[0]->getSense()) {
                    $current_sense = $annotation[0]->getSense()->getDefinition();
                    $current_sense_id = $annotation[0]->getSense()->getId();
                    $category = $annotation[0]->getCategory();
                } else {
                    $current_sense = "Not a marker";                    
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