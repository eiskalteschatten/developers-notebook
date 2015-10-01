<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends Controller
{
    /**
     * @Route("/", name="frontendHomepage")
     */
    public function indexAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/index.html.twig',
            array(
                'last_username' => $lastUsername,
                'error' => $error
            ));
    }

    /**
     * @Route("/about", name="aboutLink")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('frontend/static/about.html.twig');
    }
}
