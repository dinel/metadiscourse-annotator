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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\Type\UserType;
use AppBundle\Utils\UserUtils;

/**
 * Controller which implements User related actions
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
                'links' => array("admin_page", "admin_cat_list", "admin_user_list", "admin_page"),
            ));
    }
    
    /**     
     * Check whether the user has to change their password
     * @Route("/profile/check_change_password", name="admin_check_password_change")
     */
    public function checkForcePasswordChangeAction() {
        if(UserUtils::getCurrentUser($this)->getChangePassword() == 0) {
            $key = '_security.main.target_path';
            if ($this->container->get('session')->has($key)) {
                $url = $this->container->get('session')->get($key);
                $this->container->get('session')->remove($key);
                return new RedirectResponse($url);
            } else {
                return $this->redirectToRoute("homepage");
            }
            
        } else {
            $userManager = $this->get('fos_user.user_manager');
            $user = UserUtils::getCurrentUser($this);
            $user->setChangePassword(0);
            $userManager->updateUser($user, true);
            return $this->redirectToRoute("fos_user_change_password");
        }
    }

    /**
     * @Route("/admin/user/add", name="admin_user_add")
     */
    public function addUserAction(Request $request) {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        if($form->isValid()) {
            $user->setPlainPassword($form->get("plain_password")->getData());
            if($form->get("is_administrator")->getData() == true) {
                $user->addRole("ROLE_ADMIN");
            }
            $user->setChangePassword(1);
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
            $editing_current_user = ($username === UserUtils::getCurrentUserName($this));
            $form = $this->createForm(UserType::class, $user, [
                            'in_edit_mode' => true,
                            'current_user' => $editing_current_user,
                            'is_admin' => $user->isAdmin(),
                        ]);
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
                $user->addRole("ROLE_USER");
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
