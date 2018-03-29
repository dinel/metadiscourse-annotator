<?php

/*
 * Copyright 2015 - 2018 dinel.
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
 * Controller which implements the search related actions
 *
 * @author dinel
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Utils\SharedFunctions;

class SearchController extends Controller
{
    /**
     * @Route("/search")
     * @Method({"GET"})
     */
    public function indexAction() {
        return $this->render('Search/index.html.twig');
    }
    
    /**
     * @Route("/search/enter_term/{corpus_id}", name="enter_term")
     * @Method({"GET"})     
     */
    public function enterTermAction($corpus_id) {
        return $this->render("Search/enter_term.html.twig", array(
                    'corpus_id' => $corpus_id,
                    'corpus_name' => $this->getCorpusById($corpus_id)->getName(),
        ));
    }

    /**
     * @Route("/search/term/{corpus_id}/{term}", name="search_term")
     * @Method({"GET"})
     */
    public function searchTermAction($corpus_id, $term) {        
        return $this->render('Search/search_term.html.twig', array(
                    'prefix' => " category ", 
                    'message' => $term,
                    'stats_for' => "term",
                    'corpus_id' => $corpus_id,
                    'parameter_to_controller' => $term,
                ));        
    }
    
    /**
     * @Route("/search/term_intern/{corpus_id}/{term}", name="search_term_intern")
     * @Method({"GET"})
     */
    public function searchTermInternAction($corpus_id, $term) {
        $statistics = array();
        $results = array();
        $em = $this->getDoctrine()->getManager();
        $styles = array();
        
        if($corpus_id === "none") {
            $tokens = $this->retrieveTokensWithCondition(
                    'trim(upper(t.content)) = trim(upper(:param))', trim($term));
            $search_scope = "all the texts available";
        } else {
            $tokens = $this->retrieveTokensWithCondition(
                        'trim(upper(t.content)) = trim(upper(:param))', trim($term),
                        't.document IN (:param2)', explode(",", $this->getListIdTextFromCorpus($corpus_id)));
            $search_scope = "the " . $this->getCorpusById($corpus_id)->getName() . " corpus";
        }
                
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            
            $annotations = $this->getAnnotationsForToken($token->getId());
            
            foreach($annotations as $annotation) {
                $r = array();
                $r[] = $annotation->getId();
                $r[] = $annotation->getSense() ? 
                            $annotation->getSense()->getId() :
                            "Not a marker";
                $r[] = SharedFunctions::getSentence($token->getId(), $token->getContent(), $em);
                
                if($annotation->getSense()) {
                    $styles[$annotation->getSense()->getDefinition()] = $annotation->getSense()->getId();
                } else {
                    $styles["Not a marker"] = "";
                }
                
                $label = SharedFunctions::markableHashFilter($annotation->getUserName());
                $label .= '-';
                $label .= SharedFunctions::markableHashFilter($annotation->getToken()->getContent());
                $label .= '-';
                $label .= SharedFunctions::markableHashFilter($annotation->getSense() ? $annotation->getSense()->getDefinition() : "Not a marker");
                if($annotation->getSense()) {
                    $label .= '-';
                    $label .= SharedFunctions::markableHashFilter($annotation->getCategoryName() ? $annotation->getCategoryName() : "No category");
                }
                $r[] = $label;
                
                $results[] = $r;
                
                $this->updateStatisticsForSenses($statistics, $annotation);
            }
            
            $em->detach($token);
        }
        $em->clear();
        
        return $this->render('Search/search_term_intern.html.twig', array(
                    'search_results' => $results,
                    'stats' => $statistics,
                    'styles' => $styles,
                    'search_scope' => $search_scope,
                ));        
    }
    
    /**
     * @Route("/search/category/{corpus_id}/{category_id}", name="search_category")
     * @Method({"GET"})
     */
    public function searchCategoryAction($corpus_id, $category_id) {
        $cat = $this->getDoctrine()
                    ->getRepository('AppBundle:Category')
                    ->find($category_id);                               
        
        return $this->render('Search/search_term.html.twig', array(
                    'prefix' => " category ", 
                    'message' => $cat->getName(),
                    'stats_for' => "category",
                    'corpus_id' => $corpus_id,
                    'parameter_to_controller' => $category_id,
                ));        
    }
    
    /**
     * @Route("/search/category_intern/{corpus_id}/{category_id}", name="search_category_intern")
     * @Method({"GET"})
     */
    public function searchCategoryInternAction($corpus_id, $category_id) {
        $statistics = array();
        $results = array();
        $styles = array();
        $em = $this->getDoctrine()->getManager();
               
        set_time_limit(0);
        
        $annotations = $this->getAnnotationsForCorpus($corpus_id);
        
        foreach($annotations as $annotation) {
            $category = $annotation->getCategory();
            if($category && $annotation->getSense() &&
              (($category->getId() == $category_id) ||
               ($category->getParent() && $category->getParent()->getId() == $category_id))) {
                $token = $annotation->getToken();

                $r = array();                    
                $r[] = $annotation->getId();
                $r[] = $annotation->getSense()->getId();
                $r[] = SharedFunctions::getSentence($token->getId(), $token->getContent(), $em);

                $label = SharedFunctions::markableHashFilter($annotation->getUserName());
                $label .= '-';
                $label .= SharedFunctions::markableHashFilter($annotation->getToken()->getContent());
                $label .= '-';
                $label .= SharedFunctions::markableHashFilter($annotation->getSense() ? $annotation->getSense()->getDefinition() : "Not a marker");
                if($annotation->getSense()) {
                    $label .= '-';
                    $label .= SharedFunctions::markableHashFilter($annotation->getCategoryName() ? $annotation->getCategoryName() : "No category");
                }
                $r[] = $label;

                $results[] = $r;

                $styles[$annotation->getSense()->getDefinition()] = $annotation->getSense()->getId();

                $this->updateStatisticsForSenses($statistics, $annotation);
            }
        }
        
        $search_scope = "the " . $this->getCorpusById($corpus_id)->getName() . " corpus";
        
        return $this->render('Search/search_term_intern.html.twig', array(        
                    'search_results' => $results,
                    'stats' => $statistics,
                    'styles' => $styles,
                    'search_scope' => $search_scope,
                ));        
    }
       
    /**
     * @Route("/statistics/by-category/{corpus_id}", name="statistics_by_category") 
     * @Method({"GET"})
     */
    public function statisticsByCategoryAction($corpus_id) {
        return $this->render('Search/statistics_by_category.html.twig', array(
                    'corpus' => $this->getCorpusById($corpus_id),
                ));        
    }
    
    /**
     * @Route("/statistics/by-category-intern/{corpus_id}", name="statistics_by_category_intern") 
     * @Method({"GET"})
     */
    public function statisticsByCategoryInternAction($corpus_id) {
        $statistics = array();                
        set_time_limit(0);                
        
        $annotations = $this->getAnnotationsForCorpus($corpus_id);        
        foreach($annotations as $annotation) {
            $category = $annotation->getCategory();
            if($category && $annotation->getSense()) {
                $this->updateStatisticsForCategories($statistics, $category->getName());
                if($category->getParent()) {
                    $this->updateStatisticsForCategories($statistics, $category->getParent()->getName());
                }
            }
        }
        
        $cats = $this->getDoctrine()
                     ->getRepository('AppBundle:Category')
                     ->findAll();
        
        $corpus = $this->getCorpusById($corpus_id);
        
        return $this->render('Search/statistics_by_category_intern.html.twig', array(
                    'stats' => $statistics,
                    'cats' => $cats,
                    'corpus' => $corpus,
                ));        
    }    

    /**
     * @Route("/search/retrieve_info/{id}")
     * @Method({"GET"})
     */
    public function getAnnotationInformation($id) {
        $annotation = $this->getDoctrine()
                           ->getRepository('AppBundle:Annotation')
                           ->find($id);
        
        $target = "";
        $source = "";
        if($annotation->getToken()->getSegment()) {
            $source = $annotation->getToken()->getSegment()->getSegment();
            $target = $annotation->getToken()->getSegment()->getAlignment()->getSegment();
        }
        
        return new JsonResponse(array(
                'annotator' => $annotation->getUserName(),
                'sense' => $annotation->getSense() ? $annotation->getSense()->getDefinition() : "Not a marker",
                'comments' => $annotation->getComments(),
                'category' => $annotation->getCategoryName(),
                'polarity' => $annotation->getPolarity(),
                'uncertain' => $annotation->getUncertain(),
                'source' => $annotation->getToken()->getDocument()->getTitle() . "(" . $annotation->getToken()->getDocument()->getId() . ")",
                'id_token' => $annotation->getToken()->getId(),
                'id_document' => $annotation->getToken()->getDocument()->getId(),
                'source' => $source,
                'target' => $target,
                ));
    }
        
    /***********************************************************************
     * 
     * Private methods from here
     * 
     ***********************************************************************/      
    
    /**
     * Helper function which returns all the annotation corresponding to a corpus
     * @param int $corpus_id the ID of the corpus
     * @return the list of annotations
     */
    private function getAnnotationsForCorpus($corpus_id) {
        $em = $this->getDoctrine()->getManager();        
        $queryBuilder = $em->createQueryBuilder();       
        $queryBuilder->addSelect("annotation")
                     ->from("AppBundle:Annotation", 'annotation')
                     ->from("AppBundle:Token", 'token')
                     ->andWhere("annotation.token = token")
                     ->andWhere("token.document IN (:param)")
                     ->setParameter('param', explode(",", $this->getListIdTextFromCorpus($corpus_id)));

        return $queryBuilder->getQuery()->getResult();
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
    private function retrieveTokensWithCondition($condition, $param, $condition2 = null, $param2 = null) {
        $query = $this->getDoctrine()
                      ->getRepository('\AppBundle\Entity\Token')
                      ->createQueryBuilder('t');
        
        if($condition) {
            $query = $query->where($condition)
                           ->setParameter('param', $param);
        }
        
        if($condition2) {
            $query = $query->andWhere($condition2)
                           ->setParameter('param2', $param2);
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
            
            if(! $annotation->getCategory()) {
                if(!array_key_exists("No category", $a_sense)) {
                    $a_sense["No category"] = 0;
                }
                $a_sense["No category"] += 1;
            } else {
                if(!array_key_exists($annotation->getCategory()->getName(), $a_sense)) {
                    $a_sense[$annotation->getCategory()->getName()] = 0;
                }
                $a_sense[$annotation->getCategory()->getName()] += 1;                
            }
        } else {
            if(!array_key_exists("Not a marker", $a_markable)) {
                $a_markable["Not a marker"] = array();
            }
            $a_sense =& $a_markable["Not a marker"];

            if(!array_key_exists("   ", $a_sense)) {
                $a_sense["   "] = 0;
            }
            $a_sense["   "] += 1;                
        }
    }
               
    /**
     * Function which returns a list with the IDs of texts from a corpus
     * @param int $corpus_id the ID of corpus
     * @return string a string which contains the IDs of texts separated by comma
     */
    private function getListIdTextFromCorpus($corpus_id) {
        $corpus = $this->getCorpusById($corpus_id);
        
        $list_texts = "";
        foreach($corpus->getTexts() as $text) {
            $list_texts .= ("," . $text->getId());
        }
        
        return substr($list_texts, 1);
    }
    
    /**
     * Retrives the corpus based on the ID
     * @param int $corpus_id the ID of the corpus
     * @return the corpus
     */
    private function getCorpusById($corpus_id) {
        return $this->getDoctrine()
                    ->getRepository('\AppBundle\Entity\Corpus')
                    ->find($corpus_id);
    }            
}
