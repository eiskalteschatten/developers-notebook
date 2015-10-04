<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Todo;
use AppBundle\Entity\ConnectorTodosIssues;

class TodosController extends Controller
{
    private $standardArea = "todos";
	private $currentPage = "todos";
	private $repository = "AppBundle:Todo";

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

			// GET CONNECTED ISSUES

			$issuesResult = $this->getDoctrine()
				->getRepository('AppBundle:ConnectorTodosIssues')
				->findBy(
					array('userId' => $userId, 'todo' => $todo->getUserSpecificId()),
					array('dateCreated' => 'ASC')
				);

			$issues = array();

			foreach ($issuesResult as $issue) {
				$issues[] = array(
					'id' => $issue->getIssue(),
					'url' => $this->generateUrl("singleIssue", array('id' => $issue->getIssue()))
				);
			}

			$todos[] = array(
				'id' => $todo->getId(),
				'itemId' => $todo->getUserSpecificId(),
				'name' => $todo->getTodo(),
				'notes' => $todo->getNotes(),
				'isCompleted' => $todo->getIsCompleted(),
				'dateCompleted' => $dateCompleted,
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue,
				'priority' => $todo->getPriority(),
				'folder' => $todo->getFolder(),
				'project' => $todo->getProject(),
				'date' => $todo->getDateModified()->format($dateTimeFormat),
				'issues' => $issues,
				'issuesHtml' => $helper->createIssuesHtmlLinks($issues)
			);
		}

		// ADD A BLANK HIDDEN ROW FOR CLONING WHEN CREATING A NEW TO DO

		$todos[] = array(
			'id' => '-1',
			'itemId' => '',
			'name' => 'dGhpcyByb3cgc2hvdWxkIGJlIGNsb25lZA==',  // BASE64 ENCODED "this row should be cloned"
			'notes' => '',
			'isCompleted' => '',
			'dateCompleted' => '',
			'datePlanned' => '',
			'dateDue' => '',
			'priority' => '',
			'folder' => '',
			'project' => '',
			'date' => '',
			'issues' => '',
			'issuesHtml' => ''
		);


		// GET FOLDERS AND PROJECTS
		
		$foldersProjects = $this->get('app.services.getFoldersProjects');
		$foldersProjects->init($userId, $this->standardArea);
		
		$folders = $foldersProjects->getFolders();
		$projects = $foldersProjects->getProjects();
		
        return $this->render('default/todos.html.twig', array(
			'todos' => $todos,
			'folders' => $folders,
			'projects' => $projects,
            'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
        ));
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
		
		$todosResult = $this->getDoctrine()
        ->getRepository('AppBundle:Todo')
        ->findBy(
			array('userId' => $userId),
			array('userSpecificId' => 'DESC')
		);
		
		if ($todosResult) {
			$nextSpecificId = $todosResult[0]->getUserSpecificId() + 1;
		}
		else {
			$nextSpecificId = 1;
		}


		$todo = new Todo();
		$todo->setUserId($userId);
		$todo->setUserSpecificId($nextSpecificId);
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

		$response = new JsonResponse(array('id' => $todo->getId(), 'itemId' => $todo->getUserSpecificId(), 'project' => $todo->getProject(), 'folder' => $todo->getFolder(), 'priority' => $todo->getPriority()));

		return $response;
	}

	/**
	 * @Route("/notebook/todos/saveTodo/", name="todosSaveTodo")
	 * @Method("POST")
	 */
	public function saveTodoAction(Request $request)
	{
		$helper = $this->get('app.services.helper');

		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$id = $request->request->get('id');
		$name = $request->request->get('name');
		$priority = $request->request->get('priority');
		$issuesGet = rtrim($request->request->get('issues'), ', ');
		$datePlanned = $request->request->get('datePlanned');
		$dateDue = $request->request->get('dateDue');
		$notes = $request->request->get('notes');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$todo = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$todo) {
			throw $this->createNotFoundException('No to do found for id '.$id);
		}
		
		if (!empty($datePlanned)) {
			$datePlanned = new \DateTime($datePlanned);
		}
		else {
			$datePlanned = null;
		}
		
		if (!empty($dateDue)) {
			$dateDue = new \DateTime($dateDue);
		}
		else {
			$dateDue = null;
		}

		$todo->setDateModified($date);
		$todo->setTodo($name);
		$todo->setPriority($priority);
		$todo->setDatePlanned($datePlanned);
		$todo->setDateDue($dateDue);
		$todo->setNotes($notes);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$removeConnectors = $em->getRepository('AppBundle:ConnectorTodosIssues')->findBy(
			array('todo' => $todo->getId())
		);

		foreach ($removeConnectors as $removeConnector) {
			$em->remove($removeConnector);
		}

		$issues = str_replace(' ', '', $issuesGet);
		$issuesArray = explode(",", $issues);
		$issuesHtmlArray = array();

		foreach ($issuesArray as $issue) {
			if (!empty($issue)) {
				$connector = new ConnectorTodosIssues();
				$connector->setUserId($userId);
				$connector->setIssue($issue);
				$connector->setTodo($todo->getUserSpecificId());
				$connector->setDateCreated($date);

				$em->persist($connector);

				$issuesHtmlArray[] = array(
					'id' => $issue,
					'url' => $this->generateUrl("singleIssue", array('id' => $issue))
				);
			}
		}

		$em->flush();

		$datePlannedResponse = $todo->getDatePlanned();
		if ($datePlannedResponse) {
			$datePlannedResponse = $datePlannedResponse->format($dateFormat);
		}
		else {
			$datePlannedResponse = "";
		}

		$dateDueResponse = $todo->getDateDue();
		if ($dateDueResponse) {
			$dateDueResponse = $dateDueResponse->format($dateFormat);
		}
		else {
			$dateDueResponse = "";
		}

		$response = new JsonResponse(array('id' => $todo->getId(), 'itemId' => $todo->getUserSpecificId(), 'name' => $todo->getTodo(), 'issues' => $issuesArray, 'issuesHtml' => $helper->createIssuesHtmlLinks($issuesHtmlArray), 'priority' => $todo->getPriority(), 'datePlanned' => $datePlannedResponse, 'dateDue' => $dateDueResponse, 'notes' => $todo->getNotes()));

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
	 * @Route("/notebook/todos/movePageToFolder/", name="todosMovePageToFolder")
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
	 * @Route("/notebook/todos/removePageFromFolders/", name="todosRemovePageFromFolders")
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
	 * @Route("/notebook/todos/movePageToProject/", name="todosMovePageToProject")
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
	
	/**
	 * @Route("/notebook/todos/getListOfTodos/", name="todosGetListOfTodos")
	 * @Method("GET")
	 */
	public function getListOfTodosAction(Request $request)
	{
		$term = $request->query->get('term');
		$searchTerm = "%" . $term . "%";

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("SELECT t.todo, t.id, t.userSpecificId FROM AppBundle:Todo t WHERE (t.todo LIKE :searchTerm OR t.userSpecificId LIKE :searchTerm) AND t.userId = :userId AND t.isCompleted = false")->setParameter('searchTerm', $searchTerm)->setParameter('userId', $userId);
		$todosResult = $query->getResult();

		$todos = array();

		foreach ($todosResult as $todo) {
			$todos[] = array(
				'label' => "#" . $todo['userSpecificId'] . " " . $todo['todo'],
				'value' => $todo['userSpecificId']
			);
		}

		$response = new JsonResponse($todos);

		return $response;
	}
}
