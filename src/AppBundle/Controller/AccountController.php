<?php
/**
 * Created by PhpStorm.
 * User: alexseifert
 * Date: 31/08/15
 * Time: 21:05
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

class AccountController extends Controller
{
    /**
     * @Route("/register/", name="account_register")
     */
    public function registerAction(Request $request)
    {
        return $this->render(
            'security/register.html.twig'
        );
    }

    /**
     * @Route("/register/create", name="account_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $passwordConfirm = $request->request->get('passwordConfirm');

        if ($username == "" || $email == "" || $password == "" || $passwordConfirm == "") {
            $error = "Please fill out all fields.";

            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $error
                )
            );
        }

        // Check for duplicate usernames
        $entity = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findOneBy(array('username' => $username));

        if ($entity != null) {
            $error = "This username already exists. Please choose a new one.";

            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $error
                )
            );
        }

        // Check for duplicate email addresses
        $entity = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $email));

        if ($entity != null) {
            $error = "This email address already exists. Please choose a new one.";

            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $error
                )
            );
        }

        if ($password == $passwordConfirm) {
            $user = new User();

            $encoder = $this->container->get('security.password_encoder');
            $encryptedPassword = $encoder->encodePassword($user, $password);

            $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
            $date = new \DateTime("now");

            $user->setDateCreated($date);
            $user->setDateModified($date);
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($encryptedPassword);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            return $this->redirectToRoute('default_security_target');
        }

        $error = "The passwords you typed did not match.";

        return $this->render(
            'security/register.html.twig',
            array(
                'error' => $error
            )
        );
    }
}