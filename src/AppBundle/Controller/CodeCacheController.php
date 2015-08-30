<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\CodeCacheEntry;

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
        //$name = $request->request->get('name');
        $date = new \DateTime("now");

        $codeCacheEntry = new CodeCacheEntry();
        $codeCacheEntry->setUserId(0);
        $codeCacheEntry->setCode("");
        $codeCacheEntry->setDateCreated($date);
        $codeCacheEntry->setDateModified($date);
        $codeCacheEntry->setFolder(0);
        $codeCacheEntry->setProject(0);
        $codeCacheEntry->setSyntax('javascript');

        $em = $this->getDoctrine()->getManager();

        $em->persist($codeCacheEntry);
        $em->flush();

        $response = array('id' => $codeCacheEntry->getId());

        return new Response($response);
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
