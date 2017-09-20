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

/**
 * Controller for administrating domains
 * @deprecated since summer 2017. It will be removed at some point.
 * @author dinel
 */
class DomainAdminController {
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
}
