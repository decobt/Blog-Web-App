<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Post;
use AppBundle\Entity\Comment;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PostController extends Controller
{
    /**
     * @Route("/posts", name="blog")
     */
    public function blogAction(Request $request)
    {
        // set repository
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        
        // get logged user's id
        $author = $this->get('security.token_storage')->getToken()->getUser()->getId();
        
        // search all posts based on author
        $posts = $rep ->findBy(
            array('author'=>$author)
        );
        
        // replace this example code with whatever you need
        return $this->render('home.html.twig', array(
            'posts'=>$posts
        ));
    }
    
    /**
     * @Route("/post/{id}", name="view_post", requirements={"id":"\d+"})
     */
    public function postAction(Request $request, $id)
    {
        $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->findBy(array(
            'post'=>$post
        ));
        
        $comment = new Comment();

        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $author_user = $this->get('security.token_storage')->getToken()->getUser();
            $comment->setAuthor($author_user);
        }
        
        $comment->setPost($post);
        $comment->setDate(new \DateTime('now'));
        
        $form = $this->createFormBuilder($comment)
            ->add('comment', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Add Comment'))
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            
        }
        // replace this example code with whatever you need
        return $this->render('page.html.twig', array(
            'post'=>$post,
            'comments'=>$comments,
            'form'=>$form->createView()
        ));
    }
    
    /**
     * @Route("/post/create", name="create_post")
     */
    public function createPostAction(Request $request)
    {
        $post = new Post();
        $post->setDate(new \DateTime('now'));
        
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
            $author_id = $this->get('security.token_storage')->getToken()->getUser()->getId();

            //look for the author in the db based on his id
            $rep = $this->getDoctrine()->getRepository('AppBundle:User');
            $author = $rep->find($author_id);
            
            //set the author
            $post->setAuthor($author);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            
            $this->addFlash(
                'success',
                'Succesfully added a new post'
            );
            return $this->redirectToRoute('dashboard');
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
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        $post = $rep->find($id);
                
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('summary', TextareaType::class)
            ->add('content', TextareaType::class)
            ->add('tags', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Update Post'))
            ->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            
            $this->addFlash(
                'success',
                'Succesfully updated the post!'
            );
            return $this->redirectToRoute('dashboard');
        }
        
        // replace this example code with whatever you need
        return $this->render('post.html.twig', array(
            'title' => 'Update the Post',
            'form' => $form->createView()
            ));
    }
    
    /**
     * @Route("/post/{id}/delete", name="delete_post", requirements={"id":"\d+"})
     */
    public function deletePostAction(Request $request, $id)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        $post = $rep->find($id);
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
            
        $this->addFlash(
                'info',
                'Succesfully removed the post!'
            );
        return $this->redirectToRoute('dashboard');
    }
    
    /**
     * @Route("/comment/{id}/delete", name="delete_comment", requirements={"id":"\d+"})
     */
    public function deleteCommentAction(Request $request, $id)
    {
        $rep = $this->getDoctrine()->getRepository('AppBundle:Comment');
        $comment = $rep->find($id);
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
            
        $this->addFlash(
                'info',
                'Succesfully removed the comment!'
            );
        return $this->redirectToRoute('dashboard');
    }
}