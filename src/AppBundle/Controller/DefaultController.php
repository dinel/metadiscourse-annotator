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
}
