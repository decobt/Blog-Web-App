<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // set repository
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        
        $posts = $rep ->findAll();
        
        return $this->render('home.html.twig', array(
            'posts'=>$posts,
            'search' => ''
        ));
    }
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        // set repository
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        
        // get logged user's id
        $author = $this->get('security.token_storage')->getToken()->getUser()->getId();
        
        // search all posts based on author
        $posts = $rep ->findBy(
            array('author'=>$author)
        );
        
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->findBy(
            array('author'=>$author)
        );
        
        // render the dashboard with all the posts
        return $this->render('dashboard.html.twig', array(
            'posts'=>$posts,
            'comments'=> $comments
        ));
    }
    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
    
    /**
     * @Route("/profile/{id}", name="profile", requirements={"id":"\d+"})
     */
    public function profileAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
    
    /**
     * @Route("/search", name="search")
     * @Method("POST")
     */
    public function searchAction(Request $request)
    {
        $search_term = $request->get('search');
        
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository("AppBundle:Post")->createQueryBuilder('p')
               ->where('p.content LIKE :term')
               ->orwhere('p.summary LIKE :term')
               ->orwhere('p.title LIKE :term')
               ->setParameter('term', '%' . $search_term . '%')
               ->getQuery()
               ->getResult();
        
        // replace this example code with whatever you need
        return $this->render('home.html.twig', array(
            'posts'=>$result,
            'search' => ''
        ));
    }
}
