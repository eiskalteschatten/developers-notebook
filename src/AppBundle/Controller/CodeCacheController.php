<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CodeCacheController extends Controller
{
    /**
     * @Route("/notebook/code-cache/", name="codeCache")
     */
    public function indexAction(Request $request)
    {

        return $this->render('default/code-cache.html.twig');
    }

    /**
     * @Route("/notebook/code-cache/create/", name="codeCacheCreate")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $name = $request->request->get('name');

        return new Response('success');
    }

    /**
     * @Route("/notebook/code-cache/save/", name="codeCacheSave")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {

        return new Response('success');
    }
}
