<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
     * @Route("/post/{id}", requirements={"id":"\d+"})
     */
    public function postAction(Request $request, $id)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
    
    /**
     * @Route("/post/create", name="create_post")
     */
    public function createPostAction(Request $request)
    {
        $post = new Post();
        
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('summary', TextareaType::class)
            ->add('content', TextareaType::class)
            ->add('tags', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Post'))
            ->getForm();
        $form->handleRequest($request);
                
        if ($form->isSubmitted() && $form->isValid()) {
            
            //get the author id
            $author_id = $user = $this->get('security.token_storage')->getToken()->getUser()->getId();

            //look for the author in the db based on his id
            $rep = $this->getDoctrine()->getRepository('AppBundle:User');
            $author = $rep->find($author_id);
            
            //set the author
            $post->setAuthor($author);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            
            $this->addFlash(
                'notice',
                'Succesfully added a new post'
            );
            return $this->redirectToRoute('homepage');
        }
        
        return $this->render('post.html.twig', array(
            'title' => 'Create a new Post',
            'form' => $form->createView()
            ));
    }
    
    /**
     * @Route("/post/{id}/edit", name="edit_post", requirements={"id":"\d+"})
     */
    public function editPostAction(Request $request, $id)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
    
    /**
     * @Route("/post/{id}/delete", name="delete_post", requirements={"id":"\d+"})
     */
    public function deletePostAction(Request $request, $id)
    {
        // replace this example code with whatever you need
        return $this->render('default/default.html.twig');
    }
}