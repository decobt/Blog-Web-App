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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PostController extends Controller
{
    /**
     * @Route("/posts", name="blog")
     */
    public function blogAction(Request $request)
    {
        // get repository
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');

        // get logged user's id
        $author = $this->get('security.token_storage')->getToken()->getUser()->getId();

        // search all posts based on author
        $posts = $rep ->findBy(
            array('author'=>$author)
        );

        // render view
        return $this->render('home.html.twig', array(
            'posts'=>$posts
        ));
    }

    /**
     * @Route("/post/{id}", name="view_post", requirements={"id":"\d+"})
     */
    public function postAction(Request $request, $id)
    {
        //find all comments for the post
        $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->findBy(array(
            'post'=>$post
        ));

        //create a new comment object
        $comment = new Comment();

        //check if user is authenticated fully, set the author to be that user
        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $author_user = $this->get('security.token_storage')->getToken()->getUser();
            $comment->setAuthor($author_user);
        }

        //set the post parent to the comment and the date
        $comment->setPost($post);
        $comment->setDate(new \DateTime('now'));

        //create the form
        $form = $this->createFormBuilder($comment)
            ->add('comment', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Add Comment'))
            ->getForm();

        //process the form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

        }

        //render the view
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

        //create the form
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('summary', TextareaType::class)
            ->add('content', TextareaType::class)
            ->add('image', FileType::class, array('label' => 'Post Image(jpeg)', 'required' => false))
            ->add('tags', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Post'))
            ->getForm();

        $form->handleRequest($request);

        //process the form submission
        if ($form->isSubmitted() && $form->isValid()) {

            //get the author id
            $author_id = $this->get('security.token_storage')->getToken()->getUser()->getId();

            //look for the author in the db based on his id
            $rep = $this->getDoctrine()->getRepository('AppBundle:User');
            $author = $rep->find($author_id);

            //set the author
            $post->setAuthor($author);

            // $file stores the uploaded file
            $file = $post->getImage();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('post_directory'),
                $fileName
            );

            // Update the 'brochure' property to store the file name
            // instead of its contents
            $post->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            // add a flash message of success
            $this->addFlash(
                'success',
                'Succesfully added a new post'
            );

            //redirect to the dashboard
            return $this->redirectToRoute('dashboard');
        }

        //render the view
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
        //look for the post in DB
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        $post = $rep->find($id);

        //create the form with the data from the db
        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('summary', TextareaType::class)
            ->add('content', TextareaType::class)
            ->add('tags', TextType::class)
            ->add('image', FileType::class, array('data_class' => null, 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Update Post'))
            ->getForm();
        $form->handleRequest($request);

        //process the form submission
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            //get the uploaded document
            // $document stores the uploaded file
            $document = $post->getImage();

            // Generate a unique name for the file before saving it
            $fileName = md5(uniqid()).'.'.$document->guessExtension();

            // Move the file to the directory where brochures are stored
            $document->move(
                $this->getParameter('post_directory'),
                $fileName
            );
            $post->setImage($fileName);

            $em->persist($post);
            $em->flush();

            //add a flash message of success
            $this->addFlash(
                'success',
                'Succesfully updated the post!'
            );
            //redirect to dashboard
            return $this->redirectToRoute('dashboard');
        }

        // render the view
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
        //search for the post in the db
        $rep = $this->getDoctrine()->getRepository('AppBundle:Post');
        $post = $rep->find($id);

        //remove the post from the db
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        // add a flash message of success
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
        //search for the comment in the db
        $rep = $this->getDoctrine()->getRepository('AppBundle:Comment');
        $comment = $rep->find($id);

        //remove the comment
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        //add a flash message of success
        $this->addFlash(
                'info',
                'Succesfully removed the comment!'
            );
        return $this->redirectToRoute('dashboard');
    }
}
