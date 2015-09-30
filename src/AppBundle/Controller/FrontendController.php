<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends Controller
{
    /**
     * @Route("/", name="frontend-homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('frontend/index.html.twig');
    }

    /**
     * @Route("/about", name="aboutLink")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('frontend/static/about.html.twig');
    }
}
