<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of CorpusAdminController
 *
 * @author dinel
 */
class CorpusAdminController extends Controller 
{
    /**
     * @Route("/admin/corpora/", name="corpora_admin_page")
     */
    public function listCorporaAction() {
        $corpora = $this->getDoctrine()
                    ->getRepository("AppBundle:Corpus")
                    ->findAll();
        
        return $this->render('Admin/list_corpora.html.twig', array(
                'corpora' => $corpora,
            ));
    }
    
}
