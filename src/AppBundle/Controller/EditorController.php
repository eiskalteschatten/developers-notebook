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
use AppBundle\Services\Helper;

class EditorController extends Controller
{
    /**
     * @Route("/notebook/editor/create/", name="codeCacheCreate")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

        $standardArea = $request->request->get('standardArea');
        $folder = $request->request->get('folder');
        $project = $request->request->get('project');
        $syntax = $request->request->get('syntax');

        $date = new \DateTime("now");

        $pages = new Pages();
        $pages->setUserId(0);
        $pages->setContent("");
        $pages->setDateCreated($date);
        $pages->setDateModified($date);
        $pages->setFolder($folder);
        $pages->setProject($project);
        $pages->setSyntax($syntax);
        $pages->setArea($standardArea);

        $em = $this->getDoctrine()->getManager();

        $em->persist($pages);
        $em->flush();

        $response = new JsonResponse(array('id' => $pages->getId(), 'syntax' => $pages->getSyntax(), 'folder' => $pages->getFolder(), 'project' => $pages->getProject(), 'date' => $pages->getDateModified()->format($dateTimeFormat)));

        return $response;
    }

    /**
     * @Route("/notebook/editor/save/", name="codeCacheSave")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {
        $helper = $this->get('app.services.helper');

        $id = $request->request->get('id');
        $syntax = $request->request->get('syntax');
        $content = $request->request->get('content');

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($id);

        if (!$pages) {
            throw $this->createNotFoundException('No page found for id '.$id);
        }

        $pages->setSyntax($syntax);
        $pages->setContent($content);
        $em->flush();

        $response = new JsonResponse(array('previewContent' => $helper->createPagePreview($content)));

        return $response;
    }

    /**
     * @Route("/notebook/editor/remove/", name="codeCacheRemove")
     * @Method("POST")
     */
    public function removeAction(Request $request)
    {
        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($id);

        $em->remove($pages);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route("/notebook/editor/movePageToFolder/", name="codeCacheMovePageToFolder")
     * @Method("POST")
     */
    public function movePageToFolderAction(Request $request)
    {
        $folderId = $request->request->get('folderId');
        $pageId = $request->request->get('pageId');

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($pageId);

        $pages->setFolder($folderId);
        $em->flush();

        $response = new JsonResponse(array('folder' => $pages->getFolder()));

        return $response;
    }

    /**
     * @Route("/notebook/editor/removePageFromFolders/", name="codeCacheRemovePageFromFolders")
     * @Method("POST")
     */
    public function removePageFromFoldersAction(Request $request)
    {
        $folderId = -1;
        $pageId = $request->request->get('pageId');

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($pageId);

        $pages->setFolder($folderId);
        $em->flush();

        $response = new JsonResponse(array('folder' => $pages->getFolder()));

        return $response;
    }


    /**
     * @Route("/notebook/editor/createFolder/", name="codeCacheCreateFolder")
     * @Method("POST")
     */
    public function createFolderAction(Request $request)
    {
        $standardArea = $request->request->get('standardArea');
        $name = $request->request->get('name');

        $date = new \DateTime("now");

        $folders = new Folders();
        $folders->setUserId(0);
        $folders->setDateCreated($date);
        $folders->setDateModified($date);
        $folders->setName($name);
        $folders->setArea($standardArea);

        $em = $this->getDoctrine()->getManager();

        $em->persist($folders);
        $em->flush();

        $response = new JsonResponse(array('id' => $folders->getId(), 'name' => $folders->getName()));

        return $response;
    }

    /**
     * @Route("/notebook/editor/removeFolder/", name="codeCacheRemoveFolder")
     * @Method("POST")
     */
    public function removeFolderAction(Request $request)
    {
        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();
        $folders = $em->getRepository('AppBundle:Folders')->find($id);

        $em->remove($folders);

        $pagesResult = $this->getDoctrine()
            ->getRepository('AppBundle:Pages')
            ->findBy(
                array('folder' => $id)
            );

        foreach($pagesResult as $page) {
            $page->setFolder(-1);
        }

        $em->flush();

        return new Response('success');
    }
}