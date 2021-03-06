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
     * @Route("/admin/corpus/progress/{id_corpus}", name="admin_corpus_progress")
     * @Method({"GET"})
     */
    public function corpusProgressReportAction($id_corpus) {
        $corpus = SharedFunctions::getCorpusById($id_corpus, $this->getDoctrine());
        if($corpus) {
            $stats = [];
            foreach($corpus->getTexts() as $text) {
                $val = [];
                $val["text"] = $text;
                $val["no_mark"] = $this->getNoMarkersPerText($text->getId());
                $val["anns"] = $this->getAnnotatorsStatsPerText($text->getId());
                $stats[] = $val;
            }
            
            return $this->render('Report/show_progress_report.html.twig', array(        
                    'stats' => $stats,
                    'id_corpus' => $id_corpus,
                    'usersPerText' => $this->getUsersPerDoneTexts($id_corpus),
                ));  
        }
        
    }
    
    private function getNoMarkersPerText($id_text) {
        $em = $this->getDoctrine()->getManager();        
        $queryBuilder = $em->createQueryBuilder();       
        $queryBuilder->addSelect("count(token.id) as c")
                     ->from("AppBundle:Token", 'token')
                     ->andWhere("token.document=(:param)")
                     ->andWhere("token.markable IS NOT NULL")
                     ->setParameter('param', $id_text);

        return $queryBuilder->getQuery()->getResult()[0]["c"];
    }
    
    private function getAnnotatorsStatsPerText($id_text) {
        $em = $this->getDoctrine()->getManager();        
        $queryBuilder = $em->createQueryBuilder();       
        $queryBuilder->addSelect("count(annotation.id) as c, annotation.userName")
                     ->from("AppBundle:Annotation", 'annotation')
                     ->from("AppBundle:Token", 'token')                
                     ->andWhere("annotation.token = token")
                     ->andWhere("token.document=(:param)")
                     ->setParameter('param', $id_text)
                     ->groupBy('annotation.userName');
        return $queryBuilder->getQuery()->getResult();        
    }
    
    private function getUserName($uid) {
        $user = $this->getDoctrine()
                     ->getRepository('AppBundle:User')
                     ->find($uid);
        if($user) {
            return $user->getFullName();
        } else {
            return null;
        }        
    }
    
    /**
     * Returns a list of text IDs that are marked done by a user
     * @param interger the corpus ID
     * @return array an array of text IDs that have been pinned
     */
    private function getUsersPerDoneTexts($cid) {
        $doneTexts = $this->getDoctrine()
                          ->getRepository("AppBundle:PinnedText")
                          ->findBy(['corpusId' => $cid, 'type' => 'DONE']);
        $usersPerText = [];
        foreach($doneTexts as $text) {
            $name = $this->getUserName($text->getUserId());
            
            // quitely discard deleted users
            if($name) {
                $usersPerText[$text->getTextId()][] = $name;
            }
        }
        return $usersPerText;
    }

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
     * @Route("/report/corpus-markers/{id}", name="get_markers_report")
     * @Method({"GET"})
     */
    public function documentReportMarkersAction($id) {
        ini_set('memory_limit', '-1');
        $markers = array();
               
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
     * @Route("/report/concordances/{id}/{marker}/{sense}/{user}", name="get_concordances")
     * @Method({"GET"})
     */
    public function getConcordancesAction($id, $marker = null, $sense = null, $user = null) {
        ini_set('memory_limit', '-1');
        $concordances = array(); 
        $em = $this->getDoctrine()->getManager();
        set_time_limit(0);
        
        $annotations = $this->getAnnotationsForCorpus($id, $marker, $sense, $user);
        
        $grouped_concordances = [];
        $previous_id = -1;
        foreach($annotations as $annotation) {
            $token = $annotation->getToken();
            
            if($previous_id != $token->getId()) {
                $grouped_concordances[] = [];
                $previous_id = $token->getId();
            }
            $concordances = $grouped_concordances[count($grouped_concordances) - 1];            
            $concordances[] = [
                'concordance' => SharedFunctions::getSentence($token->getId(), $token->getContent(), $em, 80),
                'style' => $annotation->getSense() ? $annotation->getSense()->getId(): "",
                'id' => $annotation->getId(),
            ];
            $grouped_concordances[count($grouped_concordances) - 1] = $concordances;
        }        
                
        $search_scope = "the " . $this->getCorpusById($id)->getName() . " corpus";
        
        return $this->render('Report/show_concordances.html.twig', array(        
                    'grouped_concordances' => $grouped_concordances,
                    'search_scope' => $search_scope,
                    'corpus_id' => $id,
                ));        
    }
    
    /**
     * Helper function which returns all the annotation corresponding to a corpus
     * @param int $corpus_id the ID of the corpus
     * @return the list of annotations
     */
    private function getAnnotationsForCorpus($corpus_id, $marker = null, $sense = null, $user = null) {
        $em = $this->getDoctrine()->getManager();        
        $queryBuilder = $em->createQueryBuilder();       
        $queryBuilder->addSelect("annotation")
                     ->from("AppBundle:Annotation", 'annotation')
                     ->from("AppBundle:Token", 'token')
                     ->andWhere("annotation.token = token")
                     ->andWhere("token.document IN (:param)")
                     ->setParameter('param', explode(",", $this->getListIdTextFromCorpus($corpus_id)))
                     ->orderBy('token.id');
        
        if($marker) {
            $queryBuilder->andWhere("token.markable = (:param_mark)")
                         ->setParameter('param_mark', $marker);
        }
        
        if($sense) {
            if($sense === "none") {
                $queryBuilder->andWhere("annotation.sense is null");
            } else {
                $queryBuilder->andWhere("annotation.sense = (:param_sense)")
                             ->setParameter('param_sense', $sense);
            }
        }
        
        if($user) {
            $queryBuilder->andWhere("annotation.userName = (:param_user)")
                         ->setParameter('param_user', $user);
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
     * Retrieves the corpus based on the ID
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
            $statistics[$tokenContent]["Not a marker"] = [
                    "total" => [], 
                    "sense" => "none"
                ];
            foreach($annotation->getToken()->getMarkable()->getSenses() as $sense) {
                $statistics[$tokenContent][$sense->getDefinition()] = [
                        "total" => [], 
                        "sense"=>$sense->getId()
                    ];
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
