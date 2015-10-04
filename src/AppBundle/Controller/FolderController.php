<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Folders;

class FolderController extends Controller
{
	/**
	 * @Route("/notebook/folder/createFolder/", name="folderCreateFolder")
	 * @Method("POST")
	 */
	public function createFolderAction(Request $request)
	{
		$standardArea = $request->request->get('standardArea');
		$name = $request->request->get('name');

		$date = new \DateTime("now");

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$folders = new Folders();
		$folders->setUserId($userId);
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
	 * @Route("/notebook/folder/removeFolder/", name="folderRemoveFolder")
	 * @Method("POST")
	 */
	public function removeFolderAction(Request $request)
	{
		$id = $request->request->get('id');

		$em = $this->getDoctrine()->getManager();
		$folders = $em->getRepository('AppBundle:Folders')->find($id);

		$em->remove($folders);

		$pagesResult = $this->getDoctrine()
			->getRepository('AppBundle:Bookmark')
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
