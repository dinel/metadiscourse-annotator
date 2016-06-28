<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchController
 *
 * @author dinel
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends Controller
{
    /**
     * @Route("/search")
     */
    public function indexAction() {
        return $this->render('Search/index.html.twig');
    }

    /**
     * @Route("/search/term/{term}", name="search_term")
     */
    public function searchTermAction($term) {
        
        $statistics = array();

        $tokens = $this->getDoctrine()
                       ->getRepository('\AppBundle\Entity\Token')
                       ->createQueryBuilder('t')
                       ->where('upper(t.content) = upper(:token)')
                       ->setParameter('token', trim($term))
                       ->getQuery()
                       //->execute();
                       ->iterate();
        
        $results = array();
        $message = "";
        $em = $this->getDoctrine()->getManager();
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            //$message .= "+" . ($token->getId()) . "+";
            
            $annotations = $this->getAnnotationsForToken($token->getId());
            
            foreach($annotations as $annotation) {
                $r = array();
                $r[] = $annotation->getId();
                
                if($annotation->getSense()) {
                    $r[] = $annotation->getSense()->getId();
                } else {
                    $r[] = "Not a marker";
                }
                
                $r[] = $this->getSentence($token->getId(), $token->getContent());
                
                if($r) {
                    $results[] = $r;
                }
                
                // the statistics
                if(! array_key_exists($annotation->getUserName(), $statistics)) {
                    $statistics[$annotation->getUserName()] = array();
                }                
                $s_user =& $statistics[$annotation->getUserName()];
                
                if(!array_key_exists($annotation->getToken()->getContent(), $s_user)) {
                        $s_user[$annotation->getToken()->getContent()] = array();
                }
                $a_markable =& $s_user[$annotation->getToken()->getContent()];
                
                
                if($annotation->getSense()) {                         
                    if(!array_key_exists($annotation->getSense()->getDefinition(), $a_markable)) {
                        $a_markable[$annotation->getSense()->getDefinition()] = array();
                    }
                    $a_sense =& $a_markable[$annotation->getSense()->getDefinition()];

                    if(!array_key_exists($annotation->getCategory()->getName(), $a_sense)) {
                        $a_sense[$annotation->getCategory()->getName()] = 0;
                    }
                    $a_sense[$annotation->getCategory()->getName()] += 1;                
                } else {
                    if(!array_key_exists("Not marker", $a_markable)) {
                        $a_markable["Not marker"] = array();
                    }
                    $a_sense =& $a_markable["Not marker"];

                    if(!array_key_exists("   ", $a_sense)) {
                        $a_sense["   "] = 0;
                    }
                    $a_sense["   "] += 1;                
                }
            }
            
            $em->detach($token);
        }
        $em->clear();
        
        return $this->render('Search/search_term.html.twig', array(
                    'term' => $term,
                    'search_results' => $results,
                    'message' => $message,
                    'stats' => $statistics,
                ));
        
    }
    
    /**
     * @Route("/statistics/by-category/{corpus_id}", name="statistics_by_category") 
     */
    public function statisticsByCategoryAction($corpus_id) {
        $corpus = $this->getDoctrine()
                      ->getRepository('\AppBundle\Entity\Corpus')
                      ->find($corpus_id);                
        
        return $this->render('Search/statistics_by_category.html.twig', array(
                    'corpus' => $corpus,
                ));        
    }
    
    /**
     * @Route("/statistics/by-category-intern/{corpus_id}", name="statistics_by_category_intern") 
     */
    public function statisticsByCategoryInternAction($corpus_id) {
        $statistics = array();
        
        $corpus = $this->getDoctrine()
                      ->getRepository('\AppBundle\Entity\Corpus')
                      ->find($corpus_id);
        
        $list_texts = "";
        foreach($corpus->getTexts() as $text) {
            $list_texts .= ("," . $text->getId());
        }
        $list_texts = substr($list_texts, 1);        

        $tokens = $this->getDoctrine()
                       ->getRepository('\AppBundle\Entity\Token')
                       ->createQueryBuilder('t')
                       ->where('t.document IN (:list_texts)')
                       ->setParameter('list_texts', $list_texts)
                       ->getQuery()
                       ->iterate();
        
        $em = $this->getDoctrine()->getManager();
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            
            $annotations = $this->getAnnotationsForToken($token->getId());                    
            
            foreach($annotations as $annotation) {
                $category = $annotation->getCategory();
                if($category) {
                    $this->updateStatistics($statistics, $category->getName());
                    if($category->getParent()) {
                        $this->updateStatistics($statistics, $category->getParent()->getName());
                    }
                }
            }
            $em->detach($token);
        }
        
        $cats = $this->getDoctrine()
                     ->getRepository('AppBundle:Category')
                     ->findAll();
        
        $em->clear();
        
        return $this->render('Search/statistics_by_category_intern.html.twig', array(
                    'stats' => $statistics,
                    'cats' => $cats,
                    'corpus' => $corpus,
                ));        
    }
    
    private function updateStatistics(&$statistics, $category_name) {
        if(! key_exists($category_name, $statistics)) {
            $statistics[$category_name] = 0;
        }
        
        $statistics[$category_name] += 1;
    }

    /**
     * @Route("/search/retrieve_info/{id}")
     */
    public function getAnnotationInformation($id) {
        $annotation = $this->getDoctrine()
                           ->getRepository('AppBundle:Annotation')
                           ->find($id);
        
        return new JsonResponse(array(
                'annotator' => $annotation->getUserName(),
                'sense' => $annotation->getSense() ? $annotation->getSense()->getDefinition() : "Not a marker",
                'comments' => $annotation->getComments(),
                'category' => $annotation->getCategoryName(),
                'polarity' => $annotation->getPolarity(),
                'uncertain' => $annotation->getUncertain(),
                ));
    }
    
    /**
     * @Route("/search/
     */
    
    /**
     * 
     * @param type $term_id
     * @param type $term
     * @return type
     */    
    private function getSentence($term_id, $term) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id > ?1 ORDER BY t.id");
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str_r = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            $str_r .= ($token["content"] . " ");
            if(strlen($str_r) > 40) break;
        }
        
        $query = $em->createQuery("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id < ?1 ORDER BY t.id DESC");
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str_l = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            $str_l = ($token["content"] . " ") . $str_l;
            if(strlen($str_l) > 40) break;
        }
        
        $em->clear();
                
        return array($str_l, $term, $str_r);        
    } 
    
    /**
     * Returns the annotations assigned to a token 
     * @param int $token_id the ID of the token for which the annotation is 
     *            to be returned
     * @return array The list of annotations
     */
    private function getAnnotationsForToken($token_id){
        return $this->getDoctrine()
                    ->getRepository('AppBundle:Annotation')
                    ->createQueryBuilder('a')
                    ->where('a.token = :id')
                    ->setParameter('id', $token_id)
                    ->getQuery()
                    ->execute();
    }
}
