<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Pages;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Projects;

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

        $pages = new Pages();
        $pages->setUserId(0);
        $pages->setContent("");
        $pages->setDateCreated($date);
        $pages->setDateModified($date);
        $pages->setFolder(0);
        $pages->setProject(0);
        $pages->setSyntax('javascript');
        $pages->setArea('code');

        $em = $this->getDoctrine()->getManager();

        $em->persist($pages);
        $em->flush();

        $response = new JsonResponse(array('id' => $pages->getId()));

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
