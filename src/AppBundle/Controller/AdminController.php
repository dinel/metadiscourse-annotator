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
        
        return $this->render('Admin/index.html.twig', array(
                'domains' => $domains,
                'texts' => $texts,
            ));
    }
    
    /**
     * @Route("/admin/domanin/add", name="admin_domain_add")
     */
    public function newDomainAction(\Symfony\Component\HttpFoundation\Request $request) {
        $domain = new \AppBundle\Entity\Domain();
        
        $form = $this->createFormBuilder($domain)
                ->add('name', 'text')
                ->add('description', 'text')
                ->add('save', 'submit', array('label' => 'Add domain'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($domain);
            $em->flush();
            
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
    
    private function processText(\AppBundle\Entity\Text $text, $em) {
        $tokenizer = new WhitespaceAndPunctuationTokenizer();
        $tokens = $tokenizer->tokenize($text->getTheText());
        foreach($tokens as $token) {
            $t = new \AppBundle\Entity\Token($token);
            $em->persist($t);
            $text->addToken($t);
        }
    }
}
