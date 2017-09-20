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

use AppBundle\Form\Type\UserType;

/**
 * Description of UserAdminController
 *
 * @author dinel
 */
class UserAdminController extends Controller {
    /**
     * @Route("/admin/user/list", name="admin_user_list")
     */
    public function listUserAction() {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        
        return $this->render('Admin/users/list_users.html.twig', array(
                'users' => $users,
                'active' => array("", "", "active", ""),
                'links' => array("admin_page", "admin_page", "admin_user_list", "admin_page"),
            ));
    }

        /**
     * @Route("/admin/user/add", name="admin_user_add")
     */
    public function addUserAction(Request $request) {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $user->setPlainPassword($form->get("plain_password")->getData());
            if($form->get("is_administrator")->getData() == true) {
                $user->addRole("ROLE_ADMIN");
            }
            $user->setEnabled(true);
            $userManager->updateUser($user, true);
            
            return $this->redirectToRoute("admin_user_list");
        }
        
        return $this->render('Admin/edit_user.html.twig', array(
                'form' => $form->createView(),
                'in_edit_mode' => 0,               
        ));        
    }
    
    /**
     * @Route("/admin/user/delete/{username}", name="admin_user_delete")
     */
    public function deleteUserAction($username) {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);
        if($user) {
            $userManager->deleteUser($user);
        }
        
        return $this->redirectToRoute("admin_user_list");
    }
    
    /**
     * @Route("/admin/user/edit/{username}", name="admin_user_edit")
     */
    public function editUserAction(Request $request, $username) {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);
        if($user) {
            $editing_current_user = $this->get('security.context')->getToken()->getUser()->getUsername() == $username;
            $form = $this->createForm(new UserType(
                            true, 
                            $editing_current_user,
                            $user->isAdmin()),
                        $user);
            $form->handleRequest($request);
        
            if($form->isValid()) {
                if($form->get("plain_password")->getData() !== "") {
                    $user->setPlainPassword($form->get("plain_password")->getData());
                }
                
                if($form->get("is_administrator")->getData() == true || $editing_current_user) {
                    $user->addRole("ROLE_ADMIN");
                } else {
                    $user->removeRole("ROLE_ADMIN");
                }
                $user->setEnabled(true);
                $userManager->updateUser($user, true);

                return $this->redirectToRoute("admin_user_list");
            }

            return $this->render('Admin/edit_user.html.twig', array(
                    'form' => $form->createView(),
                    'in_edit_mode' => 1, 
            ));
        }
        
        return $this->redirectToRoute("admin_user_list");
    }
}
