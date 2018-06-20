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

use AppBundle\Utils\SharedFunctions;

/**
 * Implements actions linked to producing of reports
 *
 * @author dinel
 */
class ReportController extends Controller {
    /**
     * @Route("/report/corpus/{id}/{marker}", name="report_corpus")
     * @Method({"GET"})
     */
    public function documentReportAction($id, $marker = null) {
        ini_set('memory_limit', '-1');
        $statistics = array();               
        set_time_limit(0);
        
        $annotations = $this->getAnnotationsForCorpus($id, $marker);
        
        foreach($annotations as $annotation) {            
            $this->updateStatisticsForSenses($statistics, $annotation);
        }
        
        foreach($statistics as $key => $dummy) {
            $statistics[$key]["total"] = count($statistics[$key]["total"]);
            foreach($statistics[$key] as $sense => $dummy) {
                if($sense != "total" && $sense != "id") {
                    $statistics[$key][$sense]["total"] = count($statistics[$key][$sense]["total"]);
                }
            }
        }
        ksort($statistics, SORT_NATURAL | SORT_FLAG_CASE);
        
        $search_scope = "the " . $this->getCorpusById($id)->getName() . " corpus";
        
        return $this->render('Report/annotation_statistics.html.twig', array(        
                    'stats' => $statistics,
                    'search_scope' => $search_scope,
                    'corpus_id' => $id,
                ));        
    }
    
    /**
     * Produces the list of markers in a corpus with their frequencies. It's the
     * starting point for digging down in the data
     * @Route("/report/corpus-markers/{id}")
     * @Method({"GET"})
     */
    public function documentReportMarkersAction($id) {
        ini_set('memory_limit', '-1');
        $markers = array();
        $em = $this->getDoctrine()->getManager();
               
        set_time_limit(0);
        
        $annotations = $this->getAnnotationsForCorpus($id);
        
        foreach($annotations as $annotation) {            
            if(!array_key_exists(strtolower($annotation->getToken()->getContent()), $markers)) {
                $markers[strtolower($annotation->getToken()->getContent())] = [0, $annotation->getToken()->getMarkable()->getId()];
            }
            $markers[strtolower($annotation->getToken()->getContent())][0]++;
        }
        
        ksort($markers, SORT_NATURAL | SORT_FLAG_CASE);
        $sections = array();
        $sections["Punctuation"] = array();
        
        foreach($markers as $marker => $info) {
            if(ctype_alpha($marker[0])) {
                if(!array_key_exists(strtoupper($marker[0]), $sections)) {
                    $sections[strtoupper($marker[0])] = array();
                }
                $sections[strtoupper($marker[0])][] = [$marker, $info[0], $info[1]];
            } else {
                $sections["Punctuation"][] = [$marker, $info[0], $info[1]];
            }
        }
        
        $search_scope = "the " . $this->getCorpusById($id)->getName() . " corpus";
        
        return $this->render('Report/annotation_markers_statistics.html.twig', array(        
                    'sections' => $sections,
                    'search_scope' => $search_scope,
                    'corpus_id' => $id,
                ));        
    }
    
    /**
     * @Route("/report/concordances/{id}/{marker}", name="get_concordances")
     * @Method({"GET"})
     */
    public function getConcordancesAction($id, $marker = null) {
        ini_set('memory_limit', '-1');
        $concordances = array(); 
        $em = $this->getDoctrine()->getManager();
        set_time_limit(0);
        
        $annotations = $this->getAnnotationsForCorpus($id, $marker);
        
        foreach($annotations as $annotation) {
            $token = $annotation->getToken();
            $concordances[] = [
                'concordance' => SharedFunctions::getSentence($token->getId(), $token->getContent(), $em, 80),
                'style' => $annotation->getSense() ? $annotation->getSense()->getId(): "",
            ];
        }
                
        $search_scope = "the " . $this->getCorpusById($id)->getName() . " corpus";
        
        return $this->render('Report/show_concordances.html.twig', array(        
                    'concordances' => $concordances,
                    'search_scope' => $search_scope,
                    'corpus_id' => $id,
                ));        
    }
    
    /**
     * Helper function which returns all the annotation corresponding to a corpus
     * @param int $corpus_id the ID of the corpus
     * @return the list of annotations
     */
    private function getAnnotationsForCorpus($corpus_id, $marker = null) {
        $em = $this->getDoctrine()->getManager();        
        $queryBuilder = $em->createQueryBuilder();       
        $queryBuilder->addSelect("annotation")
                     ->from("AppBundle:Annotation", 'annotation')
                     ->from("AppBundle:Token", 'token')
                     ->andWhere("annotation.token = token")
                     ->andWhere("token.document IN (:param)")
                     ->setParameter('param', explode(",", $this->getListIdTextFromCorpus($corpus_id)));
        
        if($marker) {
            $queryBuilder->andWhere("token.markable = (:param_mark)")
                         ->setParameter('param_mark', $marker);
        }

        return $queryBuilder->getQuery()->getResult();
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
     * @deprecated since 6 April
     * Retrives the corpus based on the ID
     * @param int $corpus_id the ID of the corpus
     * @return the corpus
     */
    private function getCorpusById($corpus_id) {
        return SharedFunctions::getCorpusById($corpus_id, $this->getDoctrine());
    }   
        
    /**
     * Update the statistics about senses on the basis of an annotation
     * 
     * @param array $statistics an associative array that is passed by reference.
     *                          It contains the statistics
     * @param type $annotation the annotation
     */
    private function updateStatisticsForSenses(&$statistics, $annotation) {
        $tokenContent = strtolower($annotation->getToken()->getContent());
        
        if(!array_key_exists($tokenContent, $statistics)) {
            $statistics[$tokenContent] = array();
            $statistics[$tokenContent]["total"] = array();
            $statistics[$tokenContent]["id"] = $annotation->getToken()->getMarkable()->getId();
            $statistics[$tokenContent]["Not a marker"] = array("total" => []);
            foreach($annotation->getToken()->getMarkable()->getSenses() as $sense) {
                $statistics[$tokenContent][$sense->getDefinition()] = array("total" => []);
            }
        }
        
        if(!in_array($annotation->getToken()->getId(), $statistics[$tokenContent]["total"])) {
            $statistics[$tokenContent]["total"][] = $annotation->getToken()->getId();
        }
        
        if($annotation->getSense()) {                         
            $a_sense =& $statistics[$tokenContent][$annotation->getSense()->getDefinition()];
        } else {
            $a_sense =& $statistics[$tokenContent]["Not a marker"];
        }
        
        if(!array_key_exists($annotation->getUserName(), $a_sense)) {
            $a_sense[$annotation->getUserName()] = 0;
        }
        $a_sense[$annotation->getUserName()] += 1;                
        
        $a_sense["total"][] = $annotation->getToken()->getId();
    }
}
