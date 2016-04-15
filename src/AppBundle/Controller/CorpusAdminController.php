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

/**
 * Description of CorpusAdminController
 *
 * @author dinel
 */
class CorpusAdminController extends Controller 
{
    /**
     * Lists all the corpora available
     * @Route("/admin/corpora/", name="corpora_admin_page")
     */
    public function listCorporaAction() {
        $corpora = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->findAll();
        
        return $this->render('Admin/list_corpora.html.twig', array(
                'corpora' => $corpora,
            ));
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
            $corpus = $this->getCorpus($id);
            $totalWords = 0;
            $types = array();
            foreach($corpus->getTexts() as $text) {
                foreach($text->getTokens() as $token) {
                    // TODO: check how multiword expressions are treated
                    $totalWords++;
                    $types[$token->getContent()] = 1;
                }
            }
            $corpus->setNumberTypes(count($types));
            $corpus->setNumberTokens($totalWords);
            $corpus->setStatisticsOutdated(0);
            $this->saveCorpus($corpus);
            
            return new JsonResponse(array(
                    'nowords' => $totalWords,
                    'notypes' => $corpus->getNumberTypes()
                ));
        } else {
            return $this->redirectToRoute("admin_page");
        }
        
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
    
}
