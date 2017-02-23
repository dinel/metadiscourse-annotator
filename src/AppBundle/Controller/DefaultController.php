<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $texts = $this->getDoctrine()
                      ->getRepository('AppBundle:Text')
                       ->findAll();
        /*
        return $this->render('FrontPage/index.html.twig', array(
            'texts' => $texts,
        ));
         * 
         */
        return $this->render('FrontPage/landing_page.html.twig');
    }
    
    /**
     * @Route("/utils/subcategory/{parent}", name="get_subcategory")
     */
    public function getSubcategory(Request $request, $parent) {
        if($request->isXmlHttpRequest()) {
            $repository = $this->getDoctrine()->getRepository("\AppBundle\Entity\Category");
            $categories = $repository->findBy(array('parent' => $parent));
            $a_categories  = array_map(function($value) {
                    return array($value->getId(), $value->getName());
            }, $categories);

            return new JsonResponse(array(
                        'sub_categories' => $a_categories,
                    ));
        } else {
            return $this->redirectToRoute('homepage');
        }                
    }
    
    /**
     * Action which retrieves information associated with a sense
     * @Route("/utils/data-by-sense/{id_sense}")
     */
    public function getDataBySenseAction(Request $request, $id_sense) {
        if($request->isXmlHttpRequest()) {
            $sense = $this->getDoctrine()
                          ->getRepository('AppBundle:Sense')
                          ->find($id_sense);
            
            $polarity = 0;
            $a_categories = array();
            $selected_parent = -1;
            $selected_leaf = -1;
            
            if($sense) {
                $polarity = $sense->getScore();
                $cats = $sense->getCategories();
                if(count($cats) > 0) {
                    $category = $cats[0];
                    if($category->getParent()) {
                        $categories = $this->getDoctrine()
                                           ->getRepository("AppBundle:Category")
                                           ->findBy(array('parent' => $category->getParent()->getId()));
                        $a_categories  = array_map(function($value) {
                                return array($value->getId(), $value->getName());
                        }, $categories);
                        
                        $selected_leaf = $category->getId();
                        $selected_parent = $category->getParent()->getId();
                    } else {
                        $selected_parent = $category->getId();
                    }
                }
            }

            return new JsonResponse(array(
                        'sub_categories' => $a_categories,
                        'polarity' => $polarity,
                        'selected_parent' => $selected_parent,
                        'selected_leaf' => $selected_leaf,
                    ));
        } else {
            return $this->redirectToRoute('homepage');
        }                
    }
}
