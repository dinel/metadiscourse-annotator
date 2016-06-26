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
        return $this->render('FrontPage/index.html.twig');
    }

    /**
     * @Route("/search/term/{term}", name="search_term")
     */
    public function searchTermAction($term) {

        $tokens = $this->getDoctrine()
                       ->getRepository('\AppBundle\Entity\Token')
                       ->createQueryBuilder('t')
                       ->where('upper(t.content) = upper(:token)')
                       ->setParameter('token', trim($term))
                       ->getQuery()
                       //->execute();
                       ->iterate();
        
        $results = array();
        $message = "";
        $em = $this->getDoctrine()->getManager();
        
        while (($row = $tokens->next()) !== false) {
            $token = $row[0];
            //$message .= "+" . ($token->getId()) . "+";
            
            $annotations = $this->getDoctrine()
                                ->getRepository('AppBundle:Annotation')
                                ->createQueryBuilder('a')
                                ->where('a.token = :id')
                                ->setParameter('id', $token->getId())
                                ->getQuery()
                                ->execute();
            
            foreach($annotations as $annotation) {
                $r = array();
                $r[] = $annotation->getId();
                
                if($annotation->getSense()) {
                    $r[] = $annotation->getSense()->getId();
                } else {
                    $r[] = "Not a marker";
                }
                
                $r[] = $this->getSentence($token->getId(), $term);
                
                if($r) {
                    $results[] = $r;
                }
            }
            
            $em->detach($token);
        }
        $em->clear();
        
        return $this->render('Search/search_term.html.twig', array(
                    'term' => $term,
                    'search_results' => $results,
                    'message' => $message,
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
                'sense' => $annotation->getSense()->getDefinition(),
                'comments' => $annotation->getComments(),
                'category' => $annotation->getCategoryName(),
                'polarity' => $annotation->getPolarity(),
                'uncertain' => $annotation->getUncertain(),
                ));
    }
    
    /**
     * 
     * @param type $term_id
     * @param type $term
     * @return type
     */    
    private function getSentence($term_id, $term) {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id > ?1 ORDER BY t.id");
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str_r = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            $str_r .= ($token["content"] . " ");
            if(strlen($str_r) > 40) break;
        }
        
        $query = $em->createQuery("SELECT t.content FROM AppBundle\Entity\Token t WHERE t.id < ?1 ORDER BY t.id DESC");
        $query->setParameter(1, $term_id);
        $query->setMaxResults(15);
                
        $str_l = "";
        $tokens = $query->execute();
        foreach($tokens as $token) {
            $str_l = ($token["content"] . " ") . $str_l;
            if(strlen($str_l) > 40) break;
        }
        
        $em->clear();
                
        return array($str_l, $term, $str_r);        
    } 
}
