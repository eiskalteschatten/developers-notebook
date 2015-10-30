<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Issue;
use AppBundle\Entity\ConnectorTodosIssues;

class IssuesController extends Controller
{
    private $standardArea = "issues";
	private $currentPage = "issues";
	private $repository = "AppBundle:Issue";

    /**
     * @Route("/notebook/issues/", name="issues")
     */
    public function indexAction(Request $request)
    {
		$helper = $this->get('app.services.helper');
		$labelsService = $this->get('app.services.labels');

		$dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

	    // GET ISSUES
	    
		$issuesResult = $this->getDoctrine()
        ->getRepository('AppBundle:Issue')
        ->findBy(
			array('userId' => $userId),
			array('dateModified' => 'DESC')
		);

		$issues = array();
		
		foreach ($issuesResult as $issue) {
			$dateCompleted = $issue->getDateCompleted();
			if ($dateCompleted) {
				$dateCompleted = $dateCompleted->format($dateTimeFormat);
			}

			$datePlanned = $issue->getDatePlanned();
			if ($datePlanned) {
				$datePlanned = $datePlanned->format($dateFormat);
			}

			$dateDue = $issue->getDateDue();
			if ($dateDue) {
				$dateDue = $dateDue->format($dateFormat);
			}

			// GET CONNECTED TO DOS

			$todosResult =  $this->getDoctrine()
				->getRepository('AppBundle:ConnectorTodosIssues')
				->findBy(
					array('userId' => $userId, 'issue' => $issue->getUserSpecificId()),
					array('dateCreated' => 'ASC')
				);

			$todos = array();

			foreach ($todosResult as $todo) {
				$todos[] = $todo->getTodo();
			}

			// GET LABELS AND CREATE LINKS

			$labelUrls = array();
			$labels = explode(",", $issue->getLabels());

			foreach ($labels as $label) {
				if (!empty($label)) {
					$labelUrls[] = $this->generateUrl("singleLabel", array('name' => urlencode(trim($label))));
				}
			}

			$issues[] = array(
				'id' => $issue->getId(),
				'itemId' => $issue->getUserSpecificId(),
				'name' => $issue->getTitle(),
				'description' => $issue->getDescription(),
				'isCompleted' => $issue->getIsCompleted(),
				'dateCompleted' => $dateCompleted,
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue,
				'labels' => $issue->getLabels(),
				'labelHtml' => $labelsService->createHtmlLinks($labels, $labelUrls),
				'todos' => $todosResult,
				'todosHtml' => $helper->createTodosHtmlLinks($todos, $this->generateUrl('todos')),
				'folder' => $issue->getFolder(),
				'project' => $issue->getProject(),
				'date' => $issue->getDateModified()->format($dateTimeFormat)
			);
		}

		// ADD A BLANK HIDDEN ROW FOR CLONING WHEN CREATING A NEW TO DO

		$issues[] = array(
			'id' => '-1',
			'itemId' => '',
			'name' => 'dGhpcyByb3cgc2hvdWxkIGJlIGNsb25lZA==',  // BASE64 ENCODED "this row should be cloned"
			'description' => '',
			'isCompleted' => '',
			'dateCompleted' => '',
			'datePlanned' => '',
			'dateDue' => '',
			'labels' => '',
			'labelHtml' => '',
			'todos' => '',
			'todosHtml' => '',
			'folder' => '',
			'project' => '',
			'date' => '',
		);

		// GET FOLDERS AND PROJECTS
		
		$foldersProjects = $this->get('app.services.getFoldersProjects');
		$foldersProjects->init($userId, $this->standardArea);
		
		$folders = $foldersProjects->getFolders();
		$projects = $foldersProjects->getProjects();

        return $this->render('default/issues.html.twig', array(
			'issues' => $issues,
			'folders' => $folders,
			'projects' => $projects,
            'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
        ));
    }

	/**
	 * @Route("/notebook/issues/createIssue/", name="issuesCreateIssue")
	 * @Method("POST")
	 */
	public function createIssueAction(Request $request)
	{
		$project = $request->request->get('project');
		$folder = $request->request->get('folder');

		$date = new \DateTime("now");

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();
		
		$issuesResult = $this->getDoctrine()
        ->getRepository('AppBundle:Issue')
        ->findBy(
			array('userId' => $userId),
			array('userSpecificId' => 'DESC')
		);
		
		if ($issuesResult) {
			$nextSpecificId = $issuesResult[0]->getUserSpecificId() + 1;
		}
		else {
			$nextSpecificId = 1;
		}
		
		$issue = new Issue();
		$issue->setUserId($userId);
		$issue->setUserSpecificId($nextSpecificId);
		$issue->setDateCreated($date);
		$issue->setDateModified($date);
		$issue->setTitle("");
		$issue->setDescription("");
		$issue->setIsCompleted(false);
		$issue->setLabels("");
		$issue->setProject($project);
		$issue->setFolder($folder);

		$em = $this->getDoctrine()->getManager();

		$em->persist($issue);
		$em->flush();

		$viewUrl = $this->generateUrl("singleIssue", array('id' => $issue->getId()));

		$response = new JsonResponse(array('id' => $issue->getId(), 'itemId' => $issue->getUserSpecificId(), 'project' => $issue->getProject(), 'folder' => $issue->getFolder(), 'viewUrl' => $viewUrl));

		return $response;
	}

	/**
	 * @Route("/notebook/issues/saveIssue/", name="issuesSaveIssue")
	 * @Method("POST")
	 */
	public function saveIssueAction(Request $request)
	{
		$helper = $this->get('app.services.helper');
		$labelsService = $this->get('app.services.labels');

		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$id = $request->request->get('id');
		$name = $request->request->get('name');
		$labels = rtrim($request->request->get('labels'), ', ');
		$todos = rtrim($request->request->get('todos'), ', ');
		$datePlanned = $request->request->get('datePlanned');
		$dateDue = $request->request->get('dateDue');
		$description = $request->request->get('description');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$issue = $em->getRepository('AppBundle:Issue')->find($id);

		if (!$issue) {
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

		$issue->setDateModified($date);
		$issue->setTitle($name);
		$issue->setLabels($labels);
		$issue->setDatePlanned($datePlanned);
		$issue->setDateDue($dateDue);
		$issue->setDescription($description);

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		// CREATE LABELS AND GENERATE LINKS

		$labelUrls = array();
		$labelsExploded = explode(",", $labels);

		foreach ($labelsExploded as $label) {
			if(!empty($label)) {
				$labelsService->createLabel($label, $userId);
				$labelUrls[] = $this->generateUrl("singleLabel", array('name' => urlencode(trim($label))));
			}
		}

		// CONNECT TODOS AND ISSUES

		$removeConnectors = $em->getRepository('AppBundle:ConnectorTodosIssues')->findBy(
			array('issue' => $issue->getId())
		);

		foreach ($removeConnectors as $removeConnector) {
			$em->remove($removeConnector);
		}

		$todos = str_replace(' ', '', $todos);
		$todosArray = explode(",", $todos);

		foreach ($todosArray as $todo) {
			if (!empty($todo)) {
				$connector = new ConnectorTodosIssues();
				$connector->setUserId($userId);
				$connector->setIssue($issue->getUserSpecificId());
				$connector->setTodo($todo);
				$connector->setDateCreated($date);

				$em->persist($connector);
			}
		}

		$em->flush();

		$datePlannedResponse = $issue->getDatePlanned();
		if ($datePlannedResponse) {
			$datePlannedResponse = $datePlannedResponse->format($dateFormat);
		}
		else {
			$datePlannedResponse = "";
		}

		$dateDueResponse = $issue->getDateDue();
		if ($dateDueResponse) {
			$dateDueResponse = $dateDueResponse->format($dateFormat);
		}
		else {
			$dateDueResponse = "";
		}

		$response = new JsonResponse(array('id' => $issue->getId(), 'itemId' => $issue->getUserSpecificId(), 'name' => $issue->getTitle(), 'labels' => $issue->getLabels(), 'labelHtml' => $labelsService->createHtmlLinks($labelsExploded, $labelUrls), 'todos' => $todosArray, 'todosHtml' => $helper->createTodosHtmlLinks($todosArray, $this->generateUrl('todos')), 'datePlanned' => $datePlannedResponse, 'dateDue' => $dateDueResponse, 'description' => $issue->getDescription()));

		return $response;
	}

	/**
	 * @Route("/notebook/issues/remove/", name="issuesRemove")
	 * @Method("POST")
	 */
	public function removeAction(Request $request)
	{
		$id = $request->request->get('id');

		$em = $this->getDoctrine()->getManager();
		$issue = $em->getRepository('AppBundle:Issue')->find($id);

		if (!$issue) {
			throw $this->createNotFoundException('No issue found for id '.$id);
		}

		$em->remove($issue);
		$em->flush();

		return new Response('success');
	}

	/**
	 * @Route("/notebook/issues/is-complete/", name="issuesIsComplete")
	 * @Method("POST")
	 */
	public function changeIsCompleteAction(Request $request)
	{
		$dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

		$id = $request->request->get('id');
		$isComplete = ($request->request->get('isComplete') === 'true');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$issue = $em->getRepository('AppBundle:Issue')->find($id);

		if (!$issue) {
			throw $this->createNotFoundException('No issue found for id '.$id);
		}

		$issue->setDateModified($date);
		$issue->setIsCompleted($isComplete);

		if ($isComplete == true) {
			$issue->setDateCompleted($date);
			$date = $issue->getDateCompleted()->format($dateTimeFormat);
		}
		else {
			$issue->setDateCompleted(null);
			$date = $issue->getDateModified()->format($dateTimeFormat);
		}

		$em->flush();

		$response = new JsonResponse(array('id' => $issue->getId(), 'isCompleted' => $issue->getIsCompleted(), 'date' => $date));

		return $response;
	}

	/**
	 * @Route("/notebook/issues/movePageToFolder/", name="issuesMovePageToFolder")
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
	 * @Route("/notebook/issues/removePageFromFolders/", name="issuesRemovePageFromFolders")
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
	 * @Route("/notebook/issues/movePageToProject/", name="issuesMovePageToProject")
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
	 * @Route("/notebook/issues/getListOfIssues/", name="issuesGetListOfIssues")
	 * @Method("GET")
	 */
	public function getListOfIssuesAction(Request $request)
	{
		$term = $request->query->get('term');
		$searchTerm = "%" . $term . "%";

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("SELECT t.title, t.id, t.userSpecificId FROM AppBundle:Issue t WHERE (t.title LIKE :searchTerm OR t.userSpecificId LIKE :searchTerm) AND t.userId = :userId AND t.isCompleted = false")->setParameter('searchTerm', $searchTerm)->setParameter('userId', $userId);
		$issuesResult = $query->getResult();

		$issues = array();

		foreach ($issuesResult as $issue) {
			$issues[] = array(
				'label' => "#" . $issue['userSpecificId'] . " " . $issue['title'],
				'value' => $issue['userSpecificId']
			);
		}

		$response = new JsonResponse($issues);

		return $response;
	}
	
	/**
	 * @Route("/notebook/issues/{id}/", name="singleIssue")
	 */
	public function singleIssueAction(Request $request, $id)
	{
		$helper = $this->get('app.services.helper');
		$labelsService = $this->get('app.services.labels');

		$dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
		$dateFormat = $this->container->getParameter('AppBundle.dateFormat');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		// GET ISSUES

		$issuesResult = $this->getDoctrine()
			->getRepository('AppBundle:Issue')
			->findOneBy(
				array('userSpecificId' => $id, 'userId' => $userId)
			);

		if (!$issuesResult) {
			return $this->render('default/issues-single.html.twig', array(
				'fail' => true,
				'currentPage' => $this->currentPage
			));
		}

		$dateCompleted = $issuesResult->getDateCompleted();
		if ($dateCompleted) {
			$dateCompleted = $dateCompleted->format($dateTimeFormat);
		}

		$datePlanned = $issuesResult->getDatePlanned();
		if ($datePlanned) {
			$datePlanned = $datePlanned->format($dateFormat);
		}

		$dateDue = $issuesResult->getDateDue();
		if ($dateDue) {
			$dateDue = $dateDue->format($dateFormat);
		}

		// GET CONNECTED TO DOS

		$todosResult =  $this->getDoctrine()
			->getRepository('AppBundle:ConnectorTodosIssues')
			->findBy(
				array('userId' => $userId, 'issue' => $issuesResult->getId()),
				array('dateCreated' => 'ASC')
			);

		$todos = array();

		foreach ($todosResult as $todo) {
			$todos[] = $todo->getTodo();
		}

		// GET LABELS AND CREATE LINKS

		$labelUrls = array();
		$labels = explode(",", $issuesResult->getLabels());

		foreach ($labels as $label) {
			$labelUrls[] = $this->generateUrl("singleLabel", array('name' => urlencode(trim($label))));
		}

		$issue = array(
			'id' => $issuesResult->getId(),
			'itemId' => $issuesResult->getUserSpecificId(),
			'name' => $issuesResult->getTitle(),
			'descriptionHtml' => nl2br($issuesResult->getDescription()),
			'description' => $issuesResult->getDescription(),
			'isCompleted' => $issuesResult->getIsCompleted(),
			'dateCompleted' => $dateCompleted,
			'datePlanned' => $datePlanned,
			'dateDue' => $dateDue,
			'labels' => $issuesResult->getLabels(),
			'labelHtml' => $labelsService->createHtmlLinks($labels, $labelUrls),
			'todos' => $todos,
			'todosHtml' => $helper->createTodosHtmlLinks($todos, $this->generateUrl('todos')),
			'folder' => $issuesResult->getFolder(),
			'project' => $issuesResult->getProject(),
			'date' => $issuesResult->getDateModified()->format($dateTimeFormat)
		);

		return $this->render('default/issues-single.html.twig', array(
			'issue' => $issue,
			'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
		));
	}
}
