<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\Type\CorpusType;
use AppBundle\Utils\SharedFunctions;

/**
 * Description of CorpusAdminController
 *
 * @author dinel
 */
class CorpusAdminController extends Controller 
{
    /**
     * Lists all the corpora available
     * @Route("/select-corpus/{path}", name="select-corpus")
     * @Route("/corpora/", name="corpora_admin_page")
     */
    public function listCorporaAction($path = null) {
        $corpora = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->findAll();
        
        $chars = $this->getDoctrine()
                    ->getRepository("AppBundle:CorpusCharacteristic")
                    ->findAll();
        
        return $this->render('Admin/list_corpora.html.twig', array(
                'corpora' => $corpora,
                'chars' => $chars,
                'path' => $path,
            ));
    }
    
    /**
     * @Route("/corpus-info/{id}", name="corpus_info")
     */
    public function displayCorpusInfoAction($id) {
        $corpus = $this->getDoctrine()
                        ->getRepository("AppBundle:Corpus")
                        ->find($id);
        
        if($corpus) {
            return $this->render('Admin/corpus_display_detailed.html.twig', 
                    ['corpus' => $corpus,]);
        }
    }             

    /**
     * Creates a new corpus or edits an existing one
     * @Route("/admin/corpus/new/{id}", name="admin_new_corpus")
     */
    public function newCorpusAction(Request $request, $id = null) {
        if($id) {
            $corpus = $this->getCorpus($id);
        } else {
            $corpus = new \AppBundle\Entity\Corpus();
        }
        
        $form = $this->createForm(new CorpusType($id), $corpus);        
        $form->handleRequest($request);
                        
        if($form->isValid()) {           
            $this->saveCorpus($corpus);            
            return $this->redirectToRoute("edit_corpus", array('id' => $corpus->getId()));
        }
        
        return $this->render('Admin/new_corpus.html.twig', array(
                'form' => $form->createView(),
        ));
    }    
    
    /**
     * Updates the statistics for a corpus
     * @Route("/admin/corpus/stats/{id}", name="corpus_update_stats")
     */
    public function corpusUpdateStats(Request $request, $id) {
        if($request->isXmlHttpRequest()) {
            // just in case but on a decent server this should not be needed
            set_time_limit(0);
            $doctrine = $this->getDoctrine();
            $corpus = SharedFunctions::getCorpusById($id, $doctrine);
            $list_texts = SharedFunctions::getListIdTextFromCorpus($id, $doctrine);

            $query = $doctrine->getManager()
                              ->createQuery("SELECT t.content, COUNT(t.content) AS freq " 
                                            . "FROM AppBundle\Entity\Token t "
                                            . "WHERE t.document in (:param) "
                                            . "GROUP BY t.content ORDER BY freq DESC")
                              ->setParameters(['param' => explode(",", $list_texts)]);;
            $rows = $query->execute();

            $totalWords = 0;
            foreach($rows as $row) {
                $types[$row["content"]] = 1;
                $totalWords += $row["freq"];
            }        
            
            $corpus->setNumberTypes(count($types));
            $corpus->setNumberTokens($totalWords);
            $corpus->setStatisticsOutdated(0);
            $this->saveCorpus($corpus);
            
            // update statistics per text
            $em = $this->getDoctrine()->getManager();
            foreach($corpus->getTexts() as $text) {
                $text->calculateStatistics($this->getDoctrine());
                $em->persist($text);
            }
            $em->flush();
            
            return new JsonResponse(array(
                    'nowords' => $totalWords,
                    'notypes' => $corpus->getNumberTypes()
                ));
        } else {
            return $this->redirectToRoute("admin_page");
        }
    }
        
    /**
     * @Route("/admin/corpus/filter_all/{filter}")
     * @param Request $request
     * @param type $filter
     */
    public function corpusFilterAll(Request $request, $filter = null) {
        if($request->isXmlHttpRequest()) {
            $rep = $this->getDoctrine()->getRepository("AppBundle:CorpusCharacteristicValue");
            $corpora_ids_tmp = array();
            $corpora_ids = array();
            $seen_corpora = array();
            $counter = 0;
            
            if($filter) {
                $values = explode("c", $filter);
                foreach($values as $id) {
                    $value = $rep->find($id);
                    if($value) {
                        $counter++;
                        $pairs = $value->getPairs();
                        foreach($pairs as $pair) {                            
                            if($pair->getCorpus()) {
                                if(! array_key_exists($pair->getCorpus()->getId(), $seen_corpora)) {
                                    $corpora_ids_tmp[] = $this->getCorpusArray($pair->getCorpus());
                                    $seen_corpora[$pair->getCorpus()->getId()] = 1;
                                } else {
                                    $seen_corpora[$pair->getCorpus()->getId()] += 1;
                                }
                            }
                        }                
                    }
                }
                               
                foreach($corpora_ids_tmp as $tmp) {
                    if($seen_corpora[$tmp[0]] == $counter) {
                        $corpora_ids[] = $tmp;
                    }
                }
            } else {
                $corpora = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->findAll();
                foreach($corpora as $corpus) {
                    $corpora_ids[] = $this->getCorpusArray($corpus);
                }
            }
            
            return new JsonResponse(array('corpora' => $corpora_ids));
        } else {
            return $this->redirectToRoute("admin_page");
        }
    }

    /**
     * @Route("/admin/corpus/filter_any/{filter}")
     * @param Request $request
     * @param type $filter
     */
    public function corpusFilterAny(Request $request, $filter = null) {
        if($request->isXmlHttpRequest()) {
            $rep = $this->getDoctrine()->getRepository("AppBundle:CorpusCharacteristicValue");
            $corpora_ids = array();
            $seen_corpora = array();
            
            if($filter) {
                $values = explode("c", $filter);
                foreach($values as $id) {
                    $value = $rep->find($id);
                    if($value) {
                        $pairs = $value->getPairs();
                        foreach($pairs as $pair) {
                            if($pair->getCorpus()) {
                                if(! array_key_exists($pair->getCorpus()->getId(), $seen_corpora)) {
                                    $corpora_ids[] = $this->getCorpusArray($pair->getCorpus());
                                    $seen_corpora[$pair->getCorpus()->getId()] = 1;
                                }
                            }
                        }                
                    }
                }
            } else {
                $corpora = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->findAll();
                foreach($corpora as $corpus) {
                    $corpora_ids[] = $this->getCorpusArray($corpus);
                }
            }
            
            return new JsonResponse(array('corpora' => $corpora_ids));
        } else {
            return $this->redirectToRoute("admin_page");
        }
    }    
    
    /**
     * @Route("/admin/corpus/remove/{id}", name="admin_corpus_delete")
     * @param Request $request
     * @param type $id
     */
    public function corpusRemove(Request $request, $id) {
        $corpus = $this->getCorpus($id);
        if($corpus) {
            $em = $this->getDoctrine()->getManager();
            foreach($corpus->getTexts() as $text) {
                $corpus->removeText($text);
            }
            
            foreach($corpus->getPairs() as $pair) {
                $corpus->removePair($pair);
                $em->remove($pair);
            }
                        
            $em->remove($corpus);
            $em->flush();
        }
        
        return $this->redirectToRoute("corpora_admin_page");
    }
    
    /**
     * @Route("/corpus/annotate/{id}", name="corpus_annotate")
     */
    public function corpusAnnotateAction(Request $request, $id) {
        $session = $this->get('session');
        $session->remove("filter-mark-id");
        
        $corpus = $this->getCorpus($id);
        
        return $this->render('Annotator/annotate_corpus.html.twig', array(
                'texts' => $corpus->getTexts(),
        ));
        
    }

    /****************************************************************
     * Utility methods
     ****************************************************************/
    
    /**
     * Returns the corpus with the given ID
     * @param type $id the ID of the corpus
     * @return Corpus the object corresponding to the corpus
     */
    private function getCorpus($id) {
        // TODO: performs checks on ID
        $corpus = $this->getDoctrine()
                ->getRepository("AppBundle:Corpus")
                ->find($id);
        
        return $corpus;
    }
    
    /**
     * Saves the object to the database
     * @param Corpus $corpus
     */
    private function saveCorpus($corpus) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($corpus);
        $em->flush();
    }
    
    /**
     * Returns an array which contains the necessary information about a corpus
     * @param type $corpus
     * @return type
     */
    private function getCorpusArray($corpus) {
        return array($corpus->getId(),
                     $corpus->getName(),
                     $corpus->getDescription(),
                     count($corpus->getTexts()),
                    );
    }
    
}
