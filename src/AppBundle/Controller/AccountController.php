<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;
use AppBundle\Entity\GeneralSettings;
use AppBundle\Entity\EditorSettings;

class AccountController extends Controller
{
    /**
     * @Route("/notebook/account/", name="accountPage")
     */
    public function settingsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $session = $this->container->get('session');

        if ($session->getFlashBag()->get('canGoToMyAccount')) {
            $userResult = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->findOneBy(
                    array('id' => $userId)
                );

            return $this->render('default/account.html.twig', array(
                'userInfo' => $userResult
            ));
        }

        return $this->render('default/account-security.html.twig');
    }

    /**
     * @Route("/notebook/account/verifyPassword/", name="accountVerifyPassword")
     * @Method("POST")
     */
    public function verifyAccountPassword(Request $request)
    {
        $password = $request->request->get('password');

        if (empty($password)) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.allFieldsRequiredError')));
            return $response;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $em = $this->getDoctrine()->getManager();
        $userResults = $em->getRepository('AppBundle:User')
            ->findOneBy(array('id' => $userId));

        if (!$userResults) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountSavedError')));
            return $response;
        }

        $encoder = $this->container->get('security.password_encoder');
        $encryptedPassword = $encoder->encodePassword($userResults, $password);

        if (!$encoder->isPasswordValid($user, $password)) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountPasswordNotValid')));
            return $response;
        }

        $session = $this->container->get('session');
        $session->getFlashBag()->add('canGoToMyAccount', 'true');

        $response = new Response("reload");
        return $response;
    }

    /**
     * @Route("/notebook/account/saveInfo/", name="accountSaveInfo")
     * @Method("POST")
     */
    public function saveAccountInfo(Request $request)
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');

        if (empty($username) || empty($email)) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.allFieldsRequiredError')));
            return $response;
        }

        $date = new \DateTime("now");

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $currentUserName = $user->getUsername();
        $currentEmail = $user->getEmail();

        $em = $this->getDoctrine()->getManager();
        $userResults = $em->getRepository('AppBundle:User')
            ->findOneBy(array('id' => $userId));

        if (!$userResults) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountSavedError')));
            return $response;
        }

        $usernameResults = $em->getRepository('AppBundle:User')
            ->findOneBy(array('username' => $username));

        if ($usernameResults && $usernameResults->getUsername() != $currentUserName) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountUsernameExistsError')));
            return $response;
        }

        $emailResults = $em->getRepository('AppBundle:User')
            ->findOneBy(array('email' => $email));

        if ($emailResults && $emailResults->getEmail() != $currentEmail) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountEmailExistsError')));
            return $response;
        }

        $userResults->setUsername($username);
        $userResults->setEmail($email);
        $userResults->setDateModified($date);

        $em->flush();

        $response = new JsonResponse(array('msgType' => 'success', 'message' => $this->container->getParameter('AppBundle.messages.accountSavedSuccess')));
        return $response;
    }

    /**
     * @Route("/notebook/account/savePassword/", name="accountSavePassword")
     * @Method("POST")
     */
    public function saveAccountPassword(Request $request)
    {
        $password = $request->request->get('password');
        $passwordConfirm = $request->request->get('passwordConfirm');

        if (empty($password) || empty($passwordConfirm)) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.allFieldsRequiredError')));
            return $response;
        }

        if ($password == $passwordConfirm) {
            $date = new \DateTime("now");

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $userId = $user->getId();

            $em = $this->getDoctrine()->getManager();
            $userResults = $em->getRepository('AppBundle:User')
                ->findOneBy(array('id' => $userId));

            if (!$userResults) {
                $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountSavedError')));
                return $response;
            }

            $encoder = $this->container->get('security.password_encoder');
            $encryptedPassword = $encoder->encodePassword($userResults, $password);

            $userResults->setPassword($encryptedPassword);
            $userResults->setDateModified($date);

            $em->flush();

            $response = new JsonResponse(array('msgType' => 'success', 'message' => $this->container->getParameter('AppBundle.messages.accountSavedSuccess')));
            return $response;
        }

        $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.accountPasswordsDontMatch')));
        return $response;
    }

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

        if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $this->container->getParameter('AppBundle.messages.allFieldsRequiredError')
                )
            );
        }

        // Check for duplicate usernames
        $entity = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findOneBy(array('username' => $username));

        if ($entity != null) {
            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $this->container->getParameter('AppBundle.messages.accountUsernameExistsError')
                )
            );
        }

        // Check for duplicate email addresses
        $entity = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findOneBy(array('email' => $email));

        if ($entity != null) {
            return $this->render(
                'security/register.html.twig',
                array(
                    'error' => $this->container->getParameter('AppBundle.messages.accountEmailExistsError')
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

            $defaultShowWeather = ($this->container->getParameter('AppBundle.defaultShowWeather') === 'true');
            $defaultWeatherLocation = $this->container->getParameter('AppBundle.defaultWeatherLocation');
            $defaultWeatherUnit = $this->container->getParameter('AppBundle.defaultWeatherUnit');
            $gs = new GeneralSettings();
            $gs->setUserId($userId);
            $gs->setShowWeather($defaultShowWeather);
            $gs->setWeatherLocation($defaultWeatherLocation);
            $gs->setWeatherUnit($defaultWeatherUnit);
            $em->persist($gs);

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

        return $this->render(
            'security/register.html.twig',
            array(
                'error' => $this->container->getParameter('AppBundle.messages.accountPasswordsDontMatch')
            )
        );
    }
}