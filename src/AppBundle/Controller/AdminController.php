<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ListManagerController
 *
 * @author dinel
 */


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

use AppBundle\Form\Type\MarkableType;
use AppBundle\Form\Type\SenseType;
use AppBundle\Form\Type\CategoryType;
use AppBundle\Form\Type\CorpusType;

use AppBundle\Entity\Sense;
use AppBundle\Entity\Domain;
use AppBundle\Entity\Corpus;

class AdminController extends Controller 
{
    /**
     * @Route("/admin", name="admin_page")
     */
    public function indexAction() {
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Domain");
        $domains = $repository->findAll();
        
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Text");
        $texts = $repository->findAll();
        
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Markable");
        $marks = $repository->findAll();
        
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Category");
        $categories = $repository->findAll();
        $cat_tree = array();
        
        foreach($categories as $category) {
            if($category->getName() == "No parent category") continue;
            
            if($category->getParent()) {
                $cat_tree[$category->getParent()->getName()][] = $category;                
            } else {
                $cat_tree[$category->getName()] = array();
                $cat_tree[$category->getName()][] = $category;                
            }
        }        
        
        return $this->render('Admin/index.html.twig', array(
                'domains' => $domains,
                'texts' => $texts,
                'markers' => $marks,
                'categories' => $cat_tree,
            ));
    }
    
    /**
     * @Route("/admin/domain/add", name="admin_domain_add")
     */
    public function newDomainAction(\Symfony\Component\HttpFoundation\Request $request) {
        $domain = new \AppBundle\Entity\Domain();
        return $this->editDomainCommon($domain, $request);        
    }
    
    /**
     * @Route("/admin/domain/edit/{id}", name="admin_domain_edit")
     */
    public function editDomainAction($id, \Symfony\Component\HttpFoundation\Request $request) {
        $domain = $this->getDoctrine()
                ->getRepository('AppBundle:Domain')
                ->find($id);
        
        return $this->editDomainCommon($domain, $request, true);
    }
    
    /**
     * Exports the domains to a file
     * @Route("/admin/domain/export", name="admin_domain_export")
     */
    public function exportDomainAction(\Symfony\Component\HttpFoundation\Request $request) {
        $domains = $this->getDoctrine()
                ->getRepository('AppBundle:Domain')
                ->findAll();
        
        $file_contents = "";
        foreach($domains as $domain) {
            $file_contents .= $domain->getName() . "\n";
            $file_contents .= $domain->getDescription() . "\n";
            $file_contents .= ($domain->getDisabled() ? "0":"1") . "\n";
        }
               
        return new Response($file_contents, 200, array(
                'X-Sendfile'          => "domains.txt",
                'Content-type'        => 'application/octet-stream',
                'Content-Disposition' => sprintf('attachment; filename="%s"', "domains.txt")));
    }
    
    /**
     * Imports the domain from a file
     * @Route("/admin/domain/import", name="admin_domain_import")
     */
    public function importDomainAction(\Symfony\Component\HttpFoundation\Request $request) {
        $file = $request->files->get('file');
        $handle = fopen($file, "r");
        if ($handle) {
            while (TRUE) {
                if((($name = fgets($handle)) !== false) &&
                   (($description = fgets($handle)) !== false) &&
                   (($enabled = fgets($handle)) !== false)) {
                    $domain = new Domain();
                    $domain->setName($name);
                    $domain->setDescription($description);
                    $domain->setDisabled($enabled === "0");
                    
                    $domain_name = $this->getDoctrine()
                        ->getRepository('AppBundle:Domain')
                        ->findBy(array('name' => $name));
                    if(count($domain_name) == 0) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($domain);
                        $em->flush();                        
                    }                                        
                } else {
                    break;
                }                
            }

            fclose($handle);
        }
        
        return $this->redirectToRoute("admin_page");        
    }

    
    /**
     * Function which stores the common functionality for creating and editing
     * domains
     * @param type $domain
     * @param type $request
     * @return type
     */
    private function editDomainCommon($domain, $request, $edit = false) {
        if($edit) $label = "Update details";
        else $label = "Add domain";
        
        $form = $this->createFormBuilder($domain)
                ->add('name', 'text')
                ->add('description', 'textarea')
                ->add('disabled', 'checkbox', array('required' => false,))
                ->add('save', 'submit', array('label' => $label))
                ->add('reset', 'submit', array('label' => 'Cancel'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            if($form->get('save')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($domain);
                $em->flush();
            }
            
            return $this->redirectToRoute("admin_page");
        } 
        
        return $this->render('Admin/new_domain.html.twig', array(
                'form' => $form->createView(),
        ));
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
                            'Copy/paste the text'=> 2,
                            'Upload annotated text (experimental)' => 3,
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
                ->add('domains', 'entity', array(
                        'class'     => 'AppBundle:Domain',
                        'choice_label' => 'Domains',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('d')
                                        ->where('d.disabled = 0');
                        },
                        'expanded'  => true,
                        'multiple'  => true
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
     * Action which adds a new marker to the database
     * @Route("/admin/marker/add", name="admin_marker_add")
     */
    public function newMarkerAdd(\Symfony\Component\HttpFoundation\Request $request) {
        $mark = new \AppBundle\Entity\Markable();
        
        $form = $this->createForm(new MarkableType(), $mark);
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($mark);
            $em->flush();
            
            return $this->redirectToRoute("admin_sense_add", 
                    array('id_marker' => $mark->getId()));
        }
        
        return $this->render('Admin/new_mark.html.twig', array(
                'form' => $form->createView(),
        ));
    }
    
    /**
     * Action which adds a sense to a given marker
     * @Route("/admin/sense/add/{id_marker}", name="admin_sense_add")
     */
    public function newSenseAdd($id_marker, \Symfony\Component\HttpFoundation\Request $request) {
        $mark = $this->getDoctrine()
                     ->getRepository('AppBundle:Markable')
                     ->find($id_marker);
        // TODO: what to do if the marker is not found. Assumes it works right now
        if($mark) {
            $sense = new Sense();
            $mark->addSense($sense);
            $form = $this->createForm(new SenseType(), $sense);
            $form->handleRequest($request);
            
            if($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($mark);
                $em->flush();
            }
            
            return $this->render('Admin/new_sense.html.twig', array(
                'mark' => $mark,
                'form' => $form->createView(),
            ));
        }
    }
    
    /**
     * @Route("/admin/category/add", name="admin_category_add")
     */
    public function newCategoryAction(\Symfony\Component\HttpFoundation\Request $request) {
        $category = new \AppBundle\Entity\Category();
        $form = $this->createForm(new CategoryType(), $category);
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            if($category->getParent()->getName() == "No parent category") {
                $category->setParent(null);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            
            return $this->redirectToRoute("admin_page");
        }
        
        return $this->render('Admin/new_category.html.twig', array(
                'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/corpus/new/{id}", name="admin_new_corpus")
     */
    public function newCorpusAction(\Symfony\Component\HttpFoundation\Request $request,
            $id = null) {
        
        if($id) {
            $corpus = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->find($id);
        } else {
            $corpus = new \AppBundle\Entity\Corpus();
        }
                
        $form = $this->createForm(new CorpusType(), $corpus);        
        $form->handleRequest($request);
                        
        if($form->isValid()) {           
            $em = $this->getDoctrine()->getManager();
            $em->persist($corpus);
            $em->flush();
            
            return $this->redirectToRoute("edit_corpus", array('id' => $corpus->getId()));
        }
        
        return $this->render('Admin/new_corpus.html.twig', array(
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
        
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Domain");
        $domain_any = $repository->findBy(array("name" => "Any"));        
        if(count($domain_any) == 0) {
            $domain = new \AppBundle\Entity\Domain();
            $domain->setName("Any");
            $domain->setDescription("A general domain");
            $em = $this->getDoctrine()->getManager();
            $em->persist($domain);
            $em->flush();
        } 
        
        return $this->redirectToRoute("admin_page");
        
    }

        /**
     * 
     * @param type $token
     * @param type $marks_array
     * @return \AppBundle\Entity\Token
     */
    private function checkToken($token, $marks_array) {
        if(array_key_exists($token, $marks_array)) {
            $t = new \AppBundle\Entity\Token($token);
            $t->setMarkable($marks_array[$token]);
            
            return $t;
        } else {
            return null;
        }
    }
    
    private function findMarkable($tokens, $pos, $marks_array) {
        $best_match = null;
        $best_match_len = 0;
        
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        foreach($marks_array as $mark) {
            $a_text = $tokenizer->tokenize($mark->getText());
            $match = True;
            for($i = 0; $i < count($a_text); $i++) {
                if(($pos + $i >= count($tokens)) ||
                   ($pos + $i < count($tokens) && $a_text[$i] != $tokens[$pos + $i])) {
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
        
        if($best_match_len) return array($best_match, $best_match_len);
        else return null;
    }


    private function processText(\AppBundle\Entity\Text $text, $em) {
        // get the tokens in the text
        $lines = explode("\n", $text->getTheText());
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        
        // load all the markers
        // TODO: filter by domain
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Markable");
        $marks = $repository->findBy(array(), array('text' => 'ASC'));
        $marks_array = array();
        foreach($marks as $mark) {
            $marks_array[$mark->getText()] = $mark;
        }
        
        foreach($lines as $line) {
            $tokens = $tokenizer->tokenize($line);
            $first = true;
            
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
            }
        }
    }

    /**
     * 
     * @param \AppBundle\Entity\Text $text
     * @param type $em
     */
    private function processText_1(\AppBundle\Entity\Text $text, $em) {
        // get the tokens in the text
        $lines = explode("\n", $text->getTheText());
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        
        // load all the markers
        // TODO: filter by domain
        $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Markable");
        $marks = $repository->findAll();
        $marks_array = array();
        foreach($marks as $mark) {
            $marks_array[$mark->getText()] = $mark;
        }        
                
        foreach($lines as $line) {
            $tokens = $tokenizer->tokenize($line);
            $first = true;
            $el_1 = $el_2 = $el_3 = null;
            foreach($tokens as $token) {
                $el_3 = $el_2;
                $el_2 = $el_1;
                $el_1 = $token;
                
                if($el_3 != null) {
                    $t = $this->checkToken($el_3 . " " . $el_2 . " " . $el_1, $marks_array);
                    if($t) {
                        $el_1 = $el_2 = $el_3 = null;
                    } else {
                        $t = $this->checkToken($el_3 . " " . $el_2, $marks_array);
                        if($t) {
                            $el_2 = $el_3 = null;
                        } else {
                            $t = $this->checkToken($el_3, $marks_array);
                            if(! $t) {
                                $t = new \AppBundle\Entity\Token($el_3);
                            }
                        }
                    } 
                    
                    if($first) $t->setNewLineBefore (1);
                    $first = false;
                    $em->persist($t);
                    $text->addToken($t);
                }                
            }
            
            $t = $this->checkToken($el_2 . " " . $el_1, $marks_array);
            if($t) {
                $em->persist($t);
                $text->addToken($t);
            } else {
                if($el_2) {
                    $t = $this->checkToken($el_2, $marks_array);
                    if(! $t) {
                        $t = new \AppBundle\Entity\Token($el_2);
                    }
                    $em->persist($t);
                    $text->addToken($t);
                }
                 
                if($el_1) {
                    $t = $this->checkToken($el_1, $marks_array);
                    if(! $t) {
                        $t = new \AppBundle\Entity\Token($el_1);
                    }
                    $em->persist($t);
                    $text->addToken($t);
                }
            }
        }
    }
}
