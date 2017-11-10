<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    /**
     * @Route("/blog")
     */
    public function blogAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
    
    /**
     * @Route("/post/{id}")
     */
    public function postAction(Request $request, $id)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
}