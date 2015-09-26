<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Todo;
use AppBundle\Entity\Issue;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Project;
use AppBundle\Services\Helper;

class IssuesController extends Controller
{
    private $standardArea = "issues";
	private $currentPage = "issues";

    /**
     * @Route("/notebook/issues/", name="issues")
     */
    public function indexAction(Request $request)
    {
		$helper = $this->get('app.services.helper');

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

			$issues[] = array(
				'id' => $issue->getId(),
				'name' => $issue->getTitle(),
				'description' => $issue->getDescription(),
				'isCompleted' => $issue->getIsCompleted(),
				'dateCompleted' => $dateCompleted,
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue,
				'labels' => $issue->getLabels(),
				'todos' => $issue->getTodos(),
				'folder' => $issue->getFolder(),
				'project' => $issue->getProject(),
				'date' => $issue->getDateModified()->format($dateTimeFormat)
			);
		}

		// ADD A BLANK HIDDEN ROW FOR CLONING WHEN CREATING A NEW TO DO

		$issues[] = array(
			'id' => '-1',
			'name' => 'dGhpcyByb3cgc2hvdWxkIGJlIGNsb25lZA==',  // BASE64 ENCODED "this row should be cloned"
			'description' => '',
			'isCompleted' => '',
			'dateCompleted' => '',
			'datePlanned' => '',
			'dateDue' => '',
			'labels' => '',
			'todos' => '',
			'folder' => '',
			'project' => '',
			'date' => '',
		);

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

        return $this->render('default/issues.html.twig', array(
			'issues' => $issues,
			'folders' => $folders,
			'projects' => $projects,
            'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
        ));
    }

	/**
	 * @Route("/notebook/issues/movePageToFolder/", name="issuesMovePageToFolder")
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
	 * @Route("/notebook/issues/removePageFromFolders/", name="issuesRemovePageFromFolders")
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
	 * @Route("/notebook/issues/createFolder/", name="issuesCreateFolder")
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
	 * @Route("/notebook/issues/removeFolder/", name="issuesRemoveFolder")
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
	 * @Route("/notebook/issues/createTodo/", name="issuesCreateIssue")
	 * @Method("POST")
	 */
	public function createIssueAction(Request $request)
	{
		$project = $request->request->get('project');
		$folder = $request->request->get('folder');

		$date = new \DateTime("now");

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$issue = new Issue();
		$issue->setUserId($userId);
		$issue->setDateCreated($date);
		$issue->setDateModified($date);
		$issue->setTodo("");
		$issue->setNotes("");
		$issue->setIsCompleted(false);
		$issue->setPriority(1);
		$issue->setProject($project);
		$issue->setFolder($folder);

		$em = $this->getDoctrine()->getManager();

		$em->persist($issue);
		$em->flush();

		$response = new JsonResponse(array('id' => $issue->getId(), 'project' => $issue->getProject(), 'folder' => $issue->getFolder(), 'priority' => $issue->getPriority(), 'isCompleted' => $issue->getIsCompleted()));

		return $response;
	}

	/**
	 * @Route("/notebook/issues/saveTodo/", name="issuesSaveIssue")
	 * @Method("POST")
	 */
	public function saveIssueAction(Request $request)
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
		$issue = $em->getRepository('AppBundle:Issue')->find($id);

		if (!$issue) {
			throw $this->createNotFoundException('No to do found for id '.$id);
		}

		$issue->setDateModified($date);
		$issue->setTodo($name);
		$issue->setPriority($priority);
		$issue->setDatePlanned($datePlanned);
		$issue->setDateDue($dateDue);
		$issue->setNotes($notes);

		$em->flush();

		$datePlannedResponse = $issue->getDatePlanned();
		if ($datePlannedResponse) {
			$datePlannedResponse = $datePlannedResponse->format($dateFormat);
		}

		$dateDueResponse = $issue->getDateDue();
		if ($dateDueResponse) {
			$dateDueResponse = $dateDueResponse->format($dateFormat);
		}

		$response = new JsonResponse(array('id' => $issue->getId(), 'name' => $issue->getTodo(), 'priority' => $issue->getPriority(), 'datePlanned' => $datePlannedResponse, 'dateDue' => $dateDueResponse, 'notes' => $issue->getNotes()));

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
		$issue = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$issue) {
			throw $this->createNotFoundException('No to do found for id '.$id);
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
		$issue = $em->getRepository('AppBundle:Todo')->find($id);

		if (!$issue) {
			throw $this->createNotFoundException('No to do found for id '.$id);
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
	 * @Route("/notebook/issues/movePageToProject/", name="issuesMovePageToProject")
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
