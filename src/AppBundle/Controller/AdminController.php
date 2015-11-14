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
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;

use AppBundle\Form\Type\MarkableType;
use AppBundle\Form\Type\SenseType;
use AppBundle\Entity\Sense;

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
        
        
        return $this->render('Admin/index.html.twig', array(
                'domains' => $domains,
                'texts' => $texts,
                'markers' => $marks
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
                ->add('the_text', 'textarea')
                ->add('domains', 'entity', array(
                        'class'     => 'AppBundle:Domain',
                        'choice_label' => 'Domains',
                        'expanded'  => true,
                        'multiple'  => true
                     ))
                ->add('save', 'submit', array('label' => 'Add text'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $this->processText($text, $em);            
            $em->persist($text);
            $em->flush();
            
            return $this->redirectToRoute("admin_page");
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
     * 
     * @param \AppBundle\Entity\Text $text
     * @param type $em
     */
    private function processText(\AppBundle\Entity\Text $text, $em) {
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
            foreach($tokens as $token) {
                $t = new \AppBundle\Entity\Token($token);
                if(array_key_exists($token, $marks_array)) {
                    $t->setMarkable($marks_array[$token]);
                }
                if($first) $t->setNewLineBefore (1);
                $first = false;
                $em->persist($t);
                $text->addToken($t);
            }
        }
    }
}
