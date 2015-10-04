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
	private $repository = "AppBundle:Pages";
	
    /**
     * @Route("/notebook/editor/create/", name="editorCreate")
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

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $pages = new Pages();
        $pages->setUserId($userId);
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

        $response = new JsonResponse(array('id' => $pages->getId(), 'syntax' => $pages->getSyntax(), 'folder' => $pages->getFolder(), 'project' => $pages->getProject(), 'date' => $pages->getDateModified()->format($dateTimeFormat), 'year' => $pages->getDateCreated()->format('Y')));

        return $response;
    }

    /**
     * @Route("/notebook/editor/save/", name="editorSave")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {
        $helper = $this->get('app.services.helper');

        $id = $request->request->get('id');
        $syntax = $request->request->get('syntax');
        $content = $request->request->get('content');

        $date = new \DateTime("now");

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($id);

        if (!$pages) {
            throw $this->createNotFoundException('No page found for id '.$id);
        }

        $pages->setSyntax($syntax);
        $pages->setContent($content);
        $pages->setDateModified($date);
        $em->flush();

        $response = new JsonResponse(array('previewContent' => $helper->createPagePreview($content)));

        return $response;
    }

    /**
     * @Route("/notebook/editor/remove/", name="editorRemove")
     * @Method("POST")
     */
    public function removeAction(Request $request)
    {
        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository('AppBundle:Pages')->find($id);

        if (!$pages) {
            throw $this->createNotFoundException('No page found for id '.$id);
        }

        $em->remove($pages);
        $em->flush();

        return new Response('success');
    } 

	/**
     * @Route("/notebook/editor/movePageToFolder/", name="editorMovePageToFolder")
	 * @Method("POST")
	 */
	public function moveItemToFolderAction(Request $request)
	{
		$folderId = $request->request->get('folderId');
		$pageId = $request->request->get('pageId');

		$foldersProjects = $this->get('app.services.foldersProjects');
		$foldersProjects->init($this->repository, $folderId, $pageId);
		$response = $foldersProjects->moveItemToFolder();

		return $response;
	}

	/**
     * @Route("/notebook/editor/removePageFromFolders/", name="editorRemovePageFromFolders")
	 * @Method("POST")
	 */
	public function removeItemFromFoldersAction(Request $request)
	{
		$folderId = -1;
		$pageId = $request->request->get('pageId');

		$foldersProjects = $this->get('app.services.foldersProjects');
		$foldersProjects->init($this->repository, $folderId, $pageId);
		$response = $foldersProjects->moveItemToFolder();

		return $response;
	}
	
	/**
     * @Route("/notebook/editor/movePageToProject/", name="editorMovePageToProject")
	 * @Method("POST")
	 */
	public function moveItemToProjectAction(Request $request)
	{
		$projectId = $request->request->get('projectId');
		$pageId = $request->request->get('pageId');

		$foldersProjects = $this->get('app.services.foldersProjects');
		$foldersProjects->init($this->repository, $projectId, $pageId);
		$response = $foldersProjects->moveItemToProject();

		return $response;
	}
}