<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function indexAction( Request $request )
    {

    	// Add flash message and redirect to home    
    	$request->getSession()->getFlashBag()->add('info', 'Page d\'accueil en construction ...');
    	return $this->redirectToRoute('oc_core_home');
    }
}
