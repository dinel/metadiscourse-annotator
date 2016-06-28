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
        $results = array();
        $em = $this->getDoctrine()->getManager();
        
        $tokens = $this->retrieveTokensWithCondition(
                'upper(t.content) = upper(:param)', trim($term));
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            
            $annotations = $this->getAnnotationsForToken($token->getId());
            
            foreach($annotations as $annotation) {
                $r = array();
                $r[] = $annotation->getId();
                $r[] = $annotation->getSense() ? 
                            $annotation->getSense()->getId() :
                            "Not a marker";
                $r[] = $this->getSentence($token->getId(), $token->getContent());
                $results[] = $r;
                
                $this->updateStatisticsForSenses($statistics, $annotation);
            }
            
            $em->detach($token);
        }
        $em->clear();
        
        return $this->render('Search/search_term.html.twig', array(
                    'term' => $term,
                    'search_results' => $results,
                    'stats' => $statistics,
                ));        
    }
    
    /**
     * @Route("/search/category/{category_id}", name="search_category")
     */
    public function searchCategoryAction($category_id) {
        $statistics = array();
        $results = array();
        $em = $this->getDoctrine()->getManager();
        
        $tokens = $this->retrieveTokensWithCondition(null, null);
        
        set_time_limit(0);
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            
            $annotations = $this->getAnnotationsForToken($token->getId());
            
            foreach($annotations as $annotation) {
                $category = $annotation->getCategory();
                if($category && 
                  (($category->getId() == $category_id) ||
                   ($category->getParent() && $category->getParent()->getId() == $category_id))) {
                    $r = array();
                    $r[] = $annotation->getId();
                    $r[] = $annotation->getSense() ? 
                                $annotation->getSense()->getId() :
                                "Not a marker";
                    $r[] = $this->getSentence($token->getId(), $token->getContent());
                    $results[] = $r;

                    $this->updateStatisticsForSenses($statistics, $annotation);
                }
                $em->detach($annotation);
            }
            
            $em->detach($token);
            $em->clear();
        }        
        
        return $this->render('Search/search_term.html.twig', array(
                    'term' => "",
                    'search_results' => $results,
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
        
        $tokens = $this->retrieveTokensWithCondition(
                't.document IN (:param)', $list_texts);

        $em = $this->getDoctrine()->getManager();
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            
            $annotations = $this->getAnnotationsForToken($token->getId());                    
            
            foreach($annotations as $annotation) {
                $category = $annotation->getCategory();
                if($category) {
                    $this->updateStatisticsForCategories($statistics, $category->getName());
                    if($category->getParent()) {
                        $this->updateStatisticsForCategories($statistics, $category->getParent()->getName());
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
    
    /***********************************************************************
     * 
     * Private methods from here
     * 
     ***********************************************************************/    
    
    /**
     * Helper function that retrieves the left or right context for a term
     * @param string $str_query the query that needs to be run to retrieve the 
     * context
     * @param int $term_id the ID of the term
     * @param int $direction indicates whether it is left context (value 1) or 
     * right context (any other value)
     * @return string the context
     */
    private function getContext($str_query, $term_id, $direction) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery($str_query);
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            if($direction == 1) {
                $str .= ($token["content"] . " ");
            } else {
                $str = ($token["content"] . " ") . $str;
            }
            
            if (strlen($str) > 40) {
                break;
            }
        }
        
        $em->clear();
        
        return $str;        
    }
    
    /**
     * Function that returns a tuple that contain the left and right context 
     * for concordances
     * 
     * @param int $term_id the ID of the term for which the context is retrieved
     * @param string $term the actual term. Probably it will be removed because it
     *                     is not really necessary 
     * @return array a tuple that contains (left context, term, right context)
     */    
    private function getSentence($term_id, $term) {
        $str_r = $this->getContext(
                "SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id > ?1 ORDER BY t.id", 
                $term_id, 1);
                
        $str_l = $this->getContext("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id < ?1 ORDER BY t.id DESC", 
                $term_id, 2);
        
        return array($str_l, $term, $str_r);        
    }

    /**
     * Returns the annotations assigned to a token 
     * 
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
    
    /**
     * Returns an iterator which gives access to tokens that fulfil a certain
     * condition
     * 
     * @param string $condition the condition used in the WHERE statement. If
     *                          the condition is empty no WHERE statement is used.
     * @param string $param parameter for the WHERE statement
     * @return iterator iterator which gives access to tokens
     */
    private function retrieveTokensWithCondition($condition, $param) {
        $query = $this->getDoctrine()
                      ->getRepository('\AppBundle\Entity\Token')
                      ->createQueryBuilder('t');
        
        if($condition) {
            $query = $query->where($condition)
                           ->setParameter('param', $param);
        }
        
        return $query->getQuery()
                     ->iterate();
    }
    
    /**
     * Update the statistics about categories on the basis of the name of category
     * 
     * @param array $statistics the associative array that stores the statistics
     * @param string $category_name the name of category
     */
    private function updateStatisticsForCategories(&$statistics, $category_name) {
        if(! key_exists($category_name, $statistics)) {
            $statistics[$category_name] = 0;
        }
        
        $statistics[$category_name] += 1;
    }
    
    /**
     * Update the statistics about senses on the basis of an annotation
     * 
     * @param array $statistics an associative array that is passed by reference.
     *                          It contains the statistics
     * @param type $annotation the annotation
     */
    private function updateStatisticsForSenses(&$statistics, $annotation) {
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
}
