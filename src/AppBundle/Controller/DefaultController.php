<?php

/*
 * Copyright 2015 - 2018 dinel.
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
 * Controller which implements the landing page related actions
 *
 * @author dinel
 */

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
