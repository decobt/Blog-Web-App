<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $user = new User();

        // create the login form
        $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('login'))
            ->setMethod('POST')
            ->add('username', TextType::class)
            ->add('password', PasswordType::class)
            ->add('save', SubmitType::class, array('label' => 'Login'))
            ->getForm();

        $error = $authUtils->getLastAuthenticationError();

            return $this->render('auth/login.html.twig', array(
                'form' => $form->createView(),
                'error' => $error,
                ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {

    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();

        // create the register form
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, array('label' => 'Register'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the password
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());

            // set the password
            $user->setPassword($password);

            // save the new user
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // add a flash message for success
            $this->addFlash(
                'success',
                'Succesfully created a new profile!'
            );

            //redirect to homepage
            return $this->redirectToRoute('homepage');
        }

        // render the registration form
        return $this->render('auth/register.html.twig', array(
                'form' => $form->createView()
                ));
    }
}
