<?php

/*
 * Copyright 2017 dinel.
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

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Category;
use AppBundle\Form\Type\CategoryType;

/**
 * Controller which implements the Category related actions
 *
 * @author dinel
 */
class CategoryAdminController extends Controller {
    /**
     * Displays a tree with categories
     * @Route("/admin/category/list", name="admin_cat_list")
     */
    public function indexAction() {                        
        $categories = $this->getDoctrine()
                           ->getRepository("AppBundle:Category")
                           ->findAll();
        $cat_tree = array();       
        
        foreach($categories as $category) {
            if($category->getName() == "No parent category") {
                continue;
            }
            
            if($category->getParent()) {
                $cat_tree[$category->getParent()->getName()][] = $category;                
            } else {
                $cat_tree[$category->getName()] = array();
                $cat_tree[$category->getName()][] = $category;                
            }
        }
                
        return $this->render('Admin/categories/list_categories.html.twig', array(
                'categories' => $cat_tree,
                'active' => array("", "active", "", ""),
                'links' => array("admin_page", "admin_cat_list", "admin_user_list", "admin_page"),
            ));
    }
    
    /**
     * Creates a new category
     * @Route("/admin/category/add", name="admin_category_add")
     */
    public function addCategoryAction(Request $request) {
        $category = new Category();
        
        return $this->editCategory_intern($request, $category, false);
    }    
    
    /**
     * Edits an existing category
     * @Route("/admin/category/edit/{id_category}", name="admin_category_edit")
     */
    public function editCategoryAction(Request $request, $id_category) {
        $category = $this->getDoctrine()
                         ->getRepository('AppBundle:Category')
                         ->find($id_category);
        
        return $this->editCategory_intern($request, $category, true);        
    }  
    
    /***********************************************************************
     * 
     * Private functions
     * 
     ***********************************************************************/
        
    /**
     * Creates or edits an existing category
     * @param Request $request the request
     * @param Category $category an object Category that will be edited
     * @param bool $editing true if it edits an existing category
     * @return type
     */
    private function editCategory_intern($request, $category, $editing) {
        $categoryType = new CategoryType($editing);
        $form = $this->createForm($categoryType, $category);
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            if($category->getParent()->getName() == "No parent category") {
                $category->setParent(null);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            
            return $this->redirectToRoute("admin_cat_list");
        }
        
        return $this->render('Admin/new_category.html.twig', array(
                'form' => $form->createView(),
        ));
    }
}
