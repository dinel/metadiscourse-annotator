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
 * Description of AdminController
 *
 * @author dinel
 */


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

use AppBundle\Entity\Cache;
use AppBundle\Utils\SharedFunctions;

class AdminController extends Controller 
{
    /**
     * @Route("/admin/misc/empty-cache", name="admin_misc_emtpy_cache")
     */
    public function emptyCacheAction() {
        $connection = $this->getDoctrine()->getManager()->getConnection();
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('cache'));

        return $this->redirectToRoute("admin_page");        
    }

    /**
     * @Route("/admin/text/add", name="admin_text_add")
     */
    public function newTextAction(\Symfony\Component\HttpFoundation\Request $request) {
        $text = new \AppBundle\Entity\Text();
        
        $form = $this->createFormBuilder($text)
                ->add('title', 'text')
                ->add('description', 'text')
                ->add('button', 'choice', array(
                        'label' => "Method",
                        'mapped' => False,
                        'placeholder' => 'Choose an option',
                        'choices' => array(                            
                            'Upload a text file' => 1,
                            //'Copy/paste the text'=> 2,
                            //'Upload annotated text (experimental)' => 3,
                        ),                        
                        'choices_as_values' => true,
                    ))
                ->add('upload_text', 'file', array(
                        'label' => false,
                        'mapped' => false,      
                        'required' => false,
                    ))
                ->add('the_text', 'textarea', array(
                        'attr' => array(
                            'rows' => '10',
                        ),
                        'label' => false,
                        'label_attr' => array(
                            'id' => 'copy-paste-label'
                        ),
                        'required' => false,
                    ))
                ->add('upload_xml', 'text', array(
                        'mapped' => false, 
                        'data' => "This is an experimental feature which is currently disabled",
                        'disabled' => true,
                        'label' => false,
                        'required' => false,
                    ))
                ->add('save', 'submit', array('label' => 'Add text'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {            
            if($form["upload_text"]->isValid()) {
                $tmpfname = tempnam("/tmp", "INP");
                $form["upload_text"]->getData()->move(dirname($tmpfname), basename($tmpfname));
                $handle = fopen($tmpfname, "r");
                
                if ($handle) {
                    $input_text = "";
                    while(TRUE) {
                        if(($line = fgets($handle)) !== false) {
                            $input_text .= $line . "\n";
                        } else {
                            break;
                        }
                    }
                    $text->setTheText($input_text);
                }
                fclose($handle);
                unlink($tmpfname);
            }
            
            $session = $request->getSession();
            if(null != $session->get('corpus')) {
                $corpus_id = $session->get('corpus');
                $corpus = $this->getDoctrine()
                    ->getRepository('AppBundle:Corpus')
                    ->find($corpus_id);                
                $text->addCorpora($corpus);
            }
            
            $em = $this->getDoctrine()->getManager();
            $this->processText($text, $em);            
            $em->persist($text);
            $em->flush();
            $em->clear();
            
            if(isset($corpus_id)) {
                $request->getSession()->remove('corpus');
                return $this->redirectToRoute('edit_corpus', array('id' => $corpus_id));
            } else {
                return $this->redirectToRoute("admin_page");
            }
        }
        
        return $this->render('Admin/new_text.html.twig', array(
                'form' => $form->createView(),
        ));  
    }
    
    /** 
     * @Route("/admin/text/add_parallel", name="admin_parallel_text_add")
     */
    public function newParallelTextAction(\Symfony\Component\HttpFoundation\Request $request) {
        $text = new \AppBundle\Entity\Text();
        
        $form = $this->createFormBuilder($text)
                ->add('title', 'text')
                ->add('description', 'text')
                ->add('button', 'choice', array(
                        'label' => "Method",
                        'mapped' => False,
                        'placeholder' => 'Choose an option',
                        'choices' => array(                            
                            'Upload a text file' => 1,
                            'Upload parallel text' => 4,
                        ),                        
                        'choices_as_values' => true,
                    ))
                ->add('upload_text', 'file', array(
                        'label' => false,
                        'mapped' => false,      
                        'required' => false,
                    ))
                ->add('upload_translation', 'file', array(
                        'label' => false,
                        'mapped' => false,      
                        'required' => false,
                ))
                ->add('save', 'submit', array('label' => 'Add parallel texts'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {            
            if($form["upload_text"]->isValid() && $form["upload_translation"]->isValid()) {
                $tmpfsource = tempnam("/tmp", "INP");
                $form["upload_text"]->getData()->move(dirname($tmpfsource), basename($tmpfsource));
                $handle_src = fopen($tmpfsource, "r");
                
                $tmpftarget = tempnam("/tmp", "PAR");
                $form["upload_translation"]->getData()->move(dirname($tmpftarget), basename($tmpftarget));
                $handle_trg = fopen($tmpftarget, "r");
                
                if ($handle_src && $handle_trg) {
                    $input_text_src = "";
                    $input_text_trg = "";
                    while(TRUE) {
                        if((($line_src = fgets($handle_src)) !== false) &&
                           (($line_trg = fgets($handle_trg)) !== false)) {                            
                            $input_text_src .= $line_src . "\n";
                            $input_text_trg .= $line_trg . "\n";
                        } else {
                            break;
                        }
                    }
                    $text->setTheText($input_text_src);
                }
                fclose($handle_src);
                fclose($handle_trg);
                
                unlink($tmpfsource);
                unlink($tmpftarget);
            }
            
            $session = $request->getSession();
            if(null != $session->get('corpus')) {
                $corpus_id = $session->get('corpus');
                $corpus = $this->getDoctrine()
                    ->getRepository('AppBundle:Corpus')
                    ->find($corpus_id);                
                $text->addCorpora($corpus);
            }
            
            $em = $this->getDoctrine()->getManager();
            $this->processText($text, $em, $input_text_trg);
            $em->persist($text);
            $em->flush();
            $em->clear();
            
            if(isset($corpus_id)) {
                $request->getSession()->remove('corpus');
                return $this->redirectToRoute('edit_corpus', array('id' => $corpus_id));
            } else {
                return $this->redirectToRoute("admin_page");
            }
        }
        
        return $this->render('Admin/new_text.html.twig', array(
                'form' => $form->createView(),
        ));  
    }
                    
    /**
     * @Route("/admin/corpus/edit/{id}", name="edit_corpus")
     */
    public function editCorpusAction($id) {
        
        $session = $this->getRequest()->getSession();
        $session->set('corpus', $id);
        
        $characteristics = $this->getDoctrine()
                    ->getRepository('AppBundle:CorpusCharacteristic')
                    ->findAll();
        
        $corpus = $this->getDoctrine()
                    ->getRepository('AppBundle:Corpus')
                    ->find($id);
        
        $sel_vals = $this->getDoctrine()
                    ->getRepository('AppBundle:CharacteristicValuePairs')
                    ->findBy(array('corpus' => $corpus->getId()));
        
        $selected_vals = array_map(function($value) {
                        return $value->getValue()->getId();
                }, $sel_vals);
                

        $repository = $this->getDoctrine()
                    ->getRepository('AppBundle:Text');                
                
        $texts = $repository->createQueryBuilder('t')
                ->innerJoin('t.corpora', 'c')
                ->where('c.id = :corpus_id')
                ->setParameter('corpus_id', $id)
                ->getQuery()->getResult();        
        
        return $this->render('Admin/edit_corpus.html.twig', array(
                    'corpus' => $corpus,
                    'chars' => $characteristics,
                    'selected_vals' => $selected_vals,
                    'texts' => $texts,
        ));
    }
    
    /**
     * @Route("/admin/corpus/category/{corpus_id}/{value_id}")
     */
    public function editCorpusCategory(\Symfony\Component\HttpFoundation\Request $request, 
            $corpus_id, $value_id) {
        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            
            $value = $this->getDoctrine()
                    ->getRepository('AppBundle:CharacteristicValuePairs')
                    ->findBy(array('corpus' => $corpus_id, 'value' => $value_id));
            
            if(count($value)) {
                $em->remove($value[0]);
            } else {            
                $corpus = $this->getDoctrine()
                        ->getRepository('AppBundle:Corpus')
                        ->find($corpus_id);

                $value = $this->getDoctrine()
                        ->getRepository('AppBundle:CorpusCharacteristicValue')
                        ->find($value_id);

                $pair = new \AppBundle\Entity\CharacteristicValuePairs();
                $pair->setValue($value);
                $pair->setCorpus($corpus);

                $em->persist($pair);                
            }
            $em->flush();
            
            return new JsonResponse("Success");  
        } else {
            return $this->redirectToRoute("admin_page");
        }        
    }
    
    /**
     * @Route("/admin/corpus/remove_text/{corpus_id}/{text_id}")
     */
    public function removeTextFromCorpusAction(\Symfony\Component\HttpFoundation\Request $request, 
            $corpus_id, $text_id) {
        
        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            
            $corpus = $this->getDoctrine()
                    ->getRepository('AppBundle:Corpus')
                    ->find($corpus_id);
            
            $text = $this->getDoctrine()
                    ->getRepository('AppBundle:Text')
                    ->find(substr($text_id, 1));
            
            if($text && $corpus) {
                $corpus->removeText($text);
                $em->persist($corpus);
                $em->flush();
                return new JsonResponse("Success");
            } else {
                return new JsonResponse("Failed");
            }
        } else {
            return $this->redirectToRoute("admin_page");
        }                   
    }
    
    /**
     * @Route("/admin/corpus/list_texts/{corpus_id}/{hint}")
     */
    public function returnListOfTextsAction(\Symfony\Component\HttpFoundation\Request $request, 
            $corpus_id, $hint) {
        
        /*$repository = $this->getDoctrine()
                    ->getRepository('AppBundle:Text'); 
        
        $texts = $repository->createQueryBuilder('t')
                ->innerJoin('t.corpora', 'c')
                ->where('c.id != :corpus_id')
                ->setParameter('corpus_id', $corpus_id)
                ->getQuery()->getResult(); 
         * 
         */
        
        $texts = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->findAll();
        
        $corpus = $this->getDoctrine()
                ->getRepository('AppBundle:Corpus')
                ->find($corpus_id);
        
        $a_texts = array_map(function($value) {
                                return array($value->getId(), $value->getTitle());
                            }, array_values(array_filter($texts, function($value) use($hint, $corpus) {
                                                                    if($corpus->getTexts()->contains($value)) return FALSE;
                                                                    return strstr($value->getTitle(), $hint);
                                                                 })));
        
        return new JsonResponse(array('texts' => $a_texts));        
    }

    /**
     * @Route("/admin/corpus/add_existing/{corpus_id}/{text_id}")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param type $corpus_id
     * @param type $text_id
     */
    public function addExistingText2Corpus(\Symfony\Component\HttpFoundation\Request $request, 
            $corpus_id, $text_id) {
        
        $text = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->find($text_id);
        
        $corpus = $this->getDoctrine()
                ->getRepository('AppBundle:Corpus')
                ->find($corpus_id);
        
        $text->addCorpora($corpus);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($text);
        $em->flush();
        
        return $this->redirectToRoute("edit_corpus", array('id' => $corpus_id));
    }
    
    /**
     * @Route("/admin/reannotate/{id}/{corpus_id}", name="reannotate")
     * @param \AppBundle\Controller\Request $request
     * @param type $id
     */
    public function reannotate(Request $request, $id, $corpus_id = null) {
        
        $text = $this->getDoctrine()
                ->getRepository('AppBundle:Text')
                ->find($id);
        $em = $this->getDoctrine()->getManager();
        $this->annotateTextInDatabase($text, $em);
        $em->flush(); 
        $em->clear();
        
        if($corpus_id) {        
            return $this->redirectToRoute("corpus_annotate", 
                array('id' => $corpus_id));
        } else {
            return $this->redirectToRoute("document_show", 
                array('id' => $id));
        }
        
    }

    /**
     * @Route("/install", name="install")
     */
    public function installAction() {                        
        // install dummy category which is the base for all the categories
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Category");
        $categories = $repository->findBy(array("name" => "No parent category"));        
        if(count($categories) == 0) {
            // insert and remove a category to make sure the first category 
            // does not have counter 0
            $cat = new \AppBundle\Entity\Category();
            $cat->setName("Dummy");
            $em = $this->getDoctrine()->getManager();
            $em->persist($cat);
            $em->flush();
            
            $categories = $repository->findBy(array("name" => "Dummy"));
            $em->remove($categories[0]);
            $em->flush();
            
            $cat = new \AppBundle\Entity\Category();
            $cat->setName("No parent category");
            $em = $this->getDoctrine()->getManager();
            $em->persist($cat);
            $em->flush();            
        }
                
        return $this->redirectToRoute("admin_page");
        
    }
    
    /**
     * Finds markables in an array of tokens
     * @param array $tokens the array of tokens
     * @param int $pos the position from which the search starts
     * @param array $marks_array an array with the markables
     * @return array the longest markable that starts at position pos
     */
    private function findMarkable($tokens, $pos, $marks_array) {
        $best_match = null;
        $best_match_len = 0;
        
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        foreach($marks_array as $mark) {
            $forms_to_check = array_merge([$mark->getText()], explode("##", $mark->getAlternatives()));
            
            foreach($forms_to_check as $form) {
                $a_text = $tokenizer->tokenize($form);
                $match = True;
                for($i = 0; $i < count($a_text); $i++) {
                    if(($pos + $i >= count($tokens)) ||
                       (($pos + $i < count($tokens)) 
                        && !SharedFunctions::sameWord($a_text[$i], $tokens[$pos + $i]))) {
                        $match = False;
                        break;
                    }
                }

                if($match) {
                    if($best_match_len < count($a_text)) {
                        $best_match = $mark;
                        $best_match_len = count($a_text);
                    }
                }
            }            
        }
        
        if($best_match_len) return array($best_match, $best_match_len);
        else return null;
    }
    
    /**
     * Finds markables in an array of tokens
     * @param array $tokens the array of tokens
     * @param int $pos the position from which the search starts
     * @param array $marks_array an array with the markables
     * @return array the longest markable that starts at position pos
     * TODO: findMarkable is very similar. have only one of them. 
     */
    private function findMarkableInDatabase($tokens, $pos, $marks_array, $ignore_annotated = true) {
        $best_match = null;
        $best_match_len = 0;
        
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        foreach($marks_array as $mark) {
            $forms_to_check = array_merge([$mark->getText()], explode("##", $mark->getAlternatives()));
            
            foreach($forms_to_check as $form) {
                $a_text = $tokenizer->tokenize($form);
                $match = True;
                for($i = 0; $i < count($a_text); $i++) {
                    if(($pos + $i >= count($tokens)) ||
                       ($ignore_annotated && $tokens[$pos + $i]->getMarkable()) ||
                        !SharedFunctions::sameWord($a_text[$i], $tokens[$pos + $i]->getContent())) {
                        $match = False;
                        break;
                    }
                }

                if($match) {
                    if($best_match_len < count($a_text)) {
                        $best_match = $mark;
                        $best_match_len = count($a_text);
                    }
                }            
            }
        }
        
        if($best_match_len) return array($best_match, $best_match_len);
        else return null;
    }    
    

    private function processText(\AppBundle\Entity\Text $text, $em, $target = null) {
        // get the tokens in the text
        $lines = explode("\n", $text->getTheText());
        if($target) {
            $lines_target = explode("\n", $target);
        }
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        
        // load all the markers
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Markable");
        $marks = $repository->findBy(array(), array('text' => 'ASC'));
        $marks_array = array();
        foreach($marks as $mark) {
            $marks_array[$mark->getText()] = $mark;
        }
        
        $segment_counter = 0;
        foreach($lines as $line) {
            $tokens = $tokenizer->tokenize($line);
            $first = true;
            
            if($target) {
                $segment_src = new \AppBundle\Entity\Segment();
                $segment_src->setSegment($line);                

                $segment_trg = new \AppBundle\Entity\Segment();
                $segment_trg->setSegment($lines_target[$segment_counter++]);
                $segment_src->setAlignment($segment_trg);
                
                $em->persist($segment_src);
                $em->persist($segment_trg);
            }
            
            $pos = 0;
            while($pos < count($tokens)) {                
                $match = $this->findMarkable($tokens, $pos, $marks_array);
                if($match) {
                    $token = "";
                    for($j = 0; $j < $match[1]; $j++) {
                        $token = $token . " " . $tokens[$pos++];
                    }
                    $t = new \AppBundle\Entity\Token($token);
                    $t->setMarkable($match[0]);
                } else {
                    $t = new \AppBundle\Entity\Token($tokens[$pos++]);
                }
                
                if($first) $t->setNewLineBefore (1);
                $first = false;
                $em->persist($t);
                $text->addToken($t);
                
                if($target) {
                    $segment_src->addToken($t);
                }
            }
        }
    }
    
    /**
     * Function which stores the frequency of different markers for each 
     * document in cache for fast retrieval
     * @param integer $doc the id of the document for which statistics are stored
     */
    private function updateMarkersCache($doc) {
        $stats = array();
        $tokens = $this->getDoctrine()
                       ->getRepository('AppBundle:Token')
                       ->createQueryBuilder('t')
                       ->where('t.document = :id')
                       ->setParameter('id', $doc)
                       ->getQuery()
                       ->iterate();
            
        while (($row = $tokens->next()) !== false) {          
            $token = $row[0];
            if($token->getMarkable()) {
                if(array_key_exists(strtolower($token->getMarkable()->getText()), $stats)) {
                    $stats[strtolower($token->getMarkable()->getText())]++;
                } else {
                    $stats[strtolower($token->getMarkable()->getText())] = 1;
                }
            }
        }
        
        if(count($stats) > 0) {
            $categories = $this->getDoctrine()
                               ->getRepository("AppBundle:Category")
                               ->findAll();
            foreach($categories as $category) {
                if($category->getName() == "No parent category") {
                    continue;
                }
                
                foreach($category->getMarkables() as $markable) {
                    if(! array_key_exists(strtolower($markable->getText()), $stats))
                            $stats[strtolower($markable->getText())] = 0;
                }
            }            
        }
        
        // delete all the records related to this caching from database
        $this->getDoctrine()
             ->getRepository('AppBundle:Cache')
             ->createQueryBuilder('c')
             ->delete()
             ->where('c.link = :id AND c.type = :type')
             ->setParameter('id', $doc)
             ->setParameter('type', Cache::COUNT_MARK)
             ->getQuery()
             ->getResult();
        
        $em = $this->getDoctrine()->getManager();
        foreach($stats as $key => $value) {
            $store = new Cache();
            $store->setType(Cache::COUNT_MARK);
            $store->setLink($doc);
            $store->setKey($key);
            $store->setValue(strval($value));
            $em->persist($store);
        }
        $em->flush();                       
    }

    private function annotateTextInDatabase(\AppBundle\Entity\Text $text, $em) {       
        set_time_limit(0);
        
        // TODO: get this from database/configuration
        $ignore_annotated = false;
        
        // load all the markers
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Markable");
        $marks = $repository->findBy(array(), array('text' => 'ASC'));
        $marks_array = array();
        foreach($marks as $mark) {
            $marks_array[$mark->getText()] = $mark;
        }
        
        $tokens = $text->getTokens()->toArray();
        $pos = 0;
        while($pos < count($tokens)) {                
            $match = $this->findMarkableInDatabase($tokens, $pos, $marks_array, 
                        $ignore_annotated);
            if($match) {
                $token = "";
                $pos_saved = $pos;
                for($j = 0; $j < $match[1]; $j++) {
                    $token = $token . " " . $tokens[$pos]->getContent();
                                        
                    if($j > 0) {
                        if($tokens[$pos]->getMarkable()) {
                            // is there annotation?
                            $annotations = $this->getDoctrine()
                                    ->getRepository('AppBundle:Annotation')
                                    ->createQueryBuilder('a')
                                    ->where('a.token = :id')
                                    ->setParameter('id', $tokens[$pos]->getId())
                                    ->getQuery()
                                    ->iterate();
                            
                            while (($row = $annotations->next()) !== false) {
                                $annotation = $row[0];
                                $em->remove($annotation);
                            }
                        }
                        $tokens[$pos]->setMarkable(null);
                        $text->removeToken($tokens[$pos]);
                        $tokens[$pos]->setDocument(null);
                        if(! $em->contains($tokens[$pos])) {
                            $tokens[$pos] = $em->merge($tokens[$pos]);
                        }
                        
                        $em->remove($tokens[$pos]);                        
                    }
                    $pos++;
                }
                $tokens[$pos_saved]->setContent($token);
                $tokens[$pos_saved]->setMarkable($match[0]);
                if($em->contains($tokens[$pos_saved])) {
                    $em->persist($tokens[$pos_saved]);
                } else {
                    $em->merge($tokens[$pos_saved]);
                }
            } else {
                $pos++;
            }
        }
        $em->flush();
        
        $this->updateMarkersCache($text->getId());
    }    
}
