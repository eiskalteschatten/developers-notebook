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
use AppBundle\Entity\EditorSettings;

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
            
            $userId = $user->getId();
            $defaultTheme = $this->container->getParameter('AppBundle.editorDefaultMode');
            $standardSyntaxCode = $this->container->getParameter('AppBundle.defaultSyntaxModeCode');
            $standardSyntaxNotebook = $this->container->getParameter('AppBundle.defaultSyntaxModeNotebook');
            $standardSyntaxJournal = $this->container->getParameter('AppBundle.defaultSyntaxModeJournal');
            
            $es = new EditorSettings();
            $es->setUserId($userId);
	        $es->setDefaultTheme($defaultTheme);
	        $es->setHighlightActiveLine(0);
	        $es->setWrapSearch(0);
	        $es->setCaseSensitiveSearch(0);
	        $es->setMatchWholeWordsSearch(0);
	        $es->setIsRegexSearch(0);
	        $es->setSkipCurrentLineSearch(0);
	        $es->setDefaultSyntaxModeCode($standardSyntaxCode);
	        $es->setDefaultSyntaxModeNotebook($standardSyntaxNotebook);
            $es->setDefaultSyntaxModeJournal($standardSyntaxJournal);
            $em->persist($es);
            
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