<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Todo;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Project;
use AppBundle\Services\Helper;

class TodosController extends Controller
{
    private $standardArea = "todos";
	private $currentPage = "todos";

    /**
     * @Route("/notebook/todos/", name="todos")
     */
    public function indexAction(Request $request)
    {
		$helper = $this->get('app.services.helper');

		$dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

	    // GET TO DOS
	    
		$todosResult = $this->getDoctrine()
        ->getRepository('AppBundle:Todo')
        ->findBy(
			array('userId' => $userId),
			array('dateModified' => 'DESC')
		);

		$todos = array();
		
		foreach ($todosResult as $todo) {
			$dateCompleted = $todo->getDateCompleted();
			if ($dateCompleted) {
				$dateCompleted = $dateCompleted->format($dateTimeFormat);
			}

			$datePlanned = $todo->getDatePlanned();
			if ($datePlanned) {
				$datePlanned = $datePlanned->format($dateFormat);
			}

			$dateDue = $todo->getDateDue();
			if ($dateDue) {
				$dateDue = $dateDue->format($dateFormat);
			}

			$todos[] = array(
				'id' => $todo->getId(),
				'name' => $todo->getTodo(),
				'notes' => $todo->getNotes(),
				'isCompleted' => $todo->getIsCompleted(),
				'dateCompleted' => $dateCompleted,
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue,
				'priority' => $todo->getPriority(),
				'folder' => $todo->getFolder(),
				'project' => $todo->getProject(),
				'date' => $todo->getDateModified()->format($dateTimeFormat)
			);
		}

		$noTodos = false;

		if (empty($todos)) {
			$noTodos = true;

			$todos[] = array(
				'id' => '-1',
				'name' => '',
				'notes' => '',
				'isCompleted' => '',
				'dateCompleted' => '',
				'datePlanned' => '',
				'dateDue' => '',
				'priority' => '',
				'folder' => '',
				'project' => '',
				'date' => ''
			);
		}
		
		// GET FOLDERS
		
		$foldersResult = $this->getDoctrine()
        ->getRepository('AppBundle:Folders')
        ->findBy(
			array('userId' => $userId, 'area' => $this->standardArea),
			array('name' => 'ASC')
		);
		
		$folders = array();
		
		foreach ($foldersResult as $folder) {
			$folders[] = array(
				'id' => $folder->getId(),
				'name' => $folder->getName()
			);
		}

		// GET PROJECTS

		$projectsResult = $this->getDoctrine()
        ->getRepository('AppBundle:Project')
        ->findBy(
			array('userId' => $userId),
			array('name' => 'ASC')
		);
		
		$projects = array();
		
		foreach ($projectsResult as $project) {
			if (!$project->getIsCompleted()) {
				$projects[] = array(
					'id' => $project->getId(),
					'name' => $project->getName()
				);
			}
		}

        return $this->render('default/todos.html.twig', array(
			'todos' => $todos,
			'noTodos' => $noTodos,
			'folders' => $folders,
			'projects' => $projects,
            'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
        ));
    }

	/**
	 * @Route("/notebook/todos/movePageToFolder/", name="todosMovePageToFolder")
	 * @Method("POST")
	 */
	public function movePageToFolderAction(Request $request)
	{
		$folderId = $request->request->get('folderId');
		$pageId = $request->request->get('pageId');

		$em = $this->getDoctrine()->getManager();
		$pages = $em->getRepository('AppBundle:Todo')->find($pageId);

		$pages->setFolder($folderId);
		$em->flush();

		$response = new JsonResponse(array('folder' => $pages->getFolder()));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/removePageFromFolders/", name="todosRemovePageFromFolders")
	 * @Method("POST")
	 */
	public function removePageFromFoldersAction(Request $request)
	{
		$folderId = -1;
		$pageId = $request->request->get('pageId');

		$em = $this->getDoctrine()->getManager();
		$pages = $em->getRepository('AppBundle:Todo')->find($pageId);

		$pages->setFolder($folderId);
		$em->flush();

		$response = new JsonResponse(array('folder' => $pages->getFolder()));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/createFolder/", name="todosCreateFolder")
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
	 * @Route("/notebook/todos/removeFolder/", name="todosRemoveFolder")
	 * @Method("POST")
	 */
	public function removeFolderAction(Request $request)
	{
		$id = $request->request->get('id');

		$em = $this->getDoctrine()->getManager();
		$folders = $em->getRepository('AppBundle:Folders')->find($id);

		$em->remove($folders);

		$pagesResult = $this->getDoctrine()
			->getRepository('AppBundle:Todo')
			->findBy(
				array('folder' => $id)
			);

		foreach($pagesResult as $page) {
			$page->setFolder(-1);
		}

		$em->flush();

		return new Response('success');
	}

	/**
	 * @Route("/notebook/todos/createTodo/", name="todosCreateTodo")
	 * @Method("POST")
	 */
	public function createTodoAction(Request $request)
	{
		$project = $request->request->get('project');
		$folder = $request->request->get('folder');

		$date = new \DateTime("now");

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$todo = new Todo();
		$todo->setUserId($userId);
		$todo->setDateCreated($date);
		$todo->setDateModified($date);
		$todo->setTodo("");
		$todo->setNotes("");
		$todo->setIsCompleted(false);
		$todo->setPriority(1);
		$todo->setProject($project);
		$todo->setFolder($folder);

		$em = $this->getDoctrine()->getManager();

		$em->persist($todo);
		$em->flush();

		$response = new JsonResponse(array('id' => $todo->getId(), 'project' => $todo->getProject(), 'folder' => $todo->getFolder(), 'priority' => $todo->getPriority(), 'isCompleted' => $todo->getIsCompleted()));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/saveTodo/", name="todosSaveTodo")
	 * @Method("POST")
	 */
	public function saveTodoAction(Request $request)
	{
		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$id = $request->request->get('id');
		$name = $request->request->get('name');
		$priority = $request->request->get('priority');
		$datePlanned = new \DateTime($request->request->get('datePlanned'));
		$dateDue = new \DateTime($request->request->get('dateDue'));
		$notes = $request->request->get('notes');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$todo = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$todo) {
			throw $this->createNotFoundException('No to do found for id '.$id);
		}

		$todo->setDateModified($date);
		$todo->setTodo($name);
		$todo->setPriority($priority);
		$todo->setDatePlanned($datePlanned);
		$todo->setDateDue($dateDue);
		$todo->setNotes($notes);

		$em->flush();

		$datePlannedResponse = $todo->getDatePlanned();
		if ($datePlannedResponse) {
			$datePlannedResponse = $datePlannedResponse->format($dateFormat);
		}

		$dateDueResponse = $todo->getDateDue();
		if ($dateDueResponse) {
			$dateDueResponse = $dateDueResponse->format($dateFormat);
		}

		$response = new JsonResponse(array('id' => $todo->getId(), 'name' => $todo->getTodo(), 'priority' => $todo->getPriority(), 'datePlanned' => $datePlannedResponse, 'dateDue' => $dateDueResponse, 'notes' => $todo->getNotes()));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/remove/", name="todosRemove")
	 * @Method("POST")
	 */
	public function removeAction(Request $request)
	{
		$id = $request->request->get('id');

		$em = $this->getDoctrine()->getManager();
		$todo = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$todo) {
			throw $this->createNotFoundException('No to do found for id '.$id);
		}

		$em->remove($todo);
		$em->flush();

		return new Response('success');
	}

	/**
	 * @Route("/notebook/todos/is-complete/", name="todosIsComplete")
	 * @Method("POST")
	 */
	public function changeIsCompleteAction(Request $request)
	{
		$dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

		$id = $request->request->get('id');
		$isComplete = ($request->request->get('isComplete') === 'true');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$todo = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$todo) {
			throw $this->createNotFoundException('No to do found for id '.$id);
		}

		$todo->setDateModified($date);
		$todo->setIsCompleted($isComplete);

		if ($isComplete == true) {
			$todo->setDateCompleted($date);
			$date = $todo->getDateCompleted()->format($dateTimeFormat);
		}
		else {
			$todo->setDateCompleted(null);
			$date = $todo->getDateModified()->format($dateTimeFormat);
		}

		$em->flush();

		$response = new JsonResponse(array('id' => $todo->getId(), 'isCompleted' => $todo->getIsCompleted(), 'date' => $date));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/movePageToProject/", name="todosMovePageToProject")
	 * @Method("POST")
	 */
	public function movePageToProjectAction(Request $request)
	{
		$projectId = $request->request->get('projectId');
		$pageId = $request->request->get('pageId');

		$em = $this->getDoctrine()->getManager();
		$pages = $em->getRepository('AppBundle:Todo')->find($pageId);

		$pages->setProject($projectId);
		$em->flush();

		$response = new JsonResponse(array('project' => $pages->getProject()));

		return $response;
	}
}
