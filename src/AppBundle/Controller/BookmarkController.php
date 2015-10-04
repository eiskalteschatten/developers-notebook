<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Bookmark;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Project;
use AppBundle\Services\Helper;

class BookmarkController extends Controller
{
    private $standardArea = "bookmarks";
	private $currentPage = "bookmarks";
	private $repository = "AppBundle:Bookmark";

    /**
     * @Route("/notebook/bookmarks/", name="bookmarks")
     */
    public function indexAction(Request $request)
    {
		$helper = $this->get('app.services.helper');

        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

	    // GET BOOKMARKS
	    
		$bookmarksResult = $this->getDoctrine()
        ->getRepository('AppBundle:Bookmark')
        ->findBy(
			array('userId' => $userId),
			array('dateModified' => 'DESC')
		);

		$bookmarks = array();
		
		foreach ($bookmarksResult as $bookmark) {
			$bookmarks[] = array(
				'id' => $bookmark->getId(),
				'name' => $bookmark->getName(),
				'url' => $bookmark->getUrl(),
				'croppedUrl' => $helper->cropBookmarkUrl($bookmark->getUrl()),
				'notes' => $bookmark->getNotes(),
				'folder' => $bookmark->getFolder(),
				'project' => $bookmark->getProject(),
				'date' => $bookmark->getDateModified()->format($dateTimeFormat)
			);
		}

		// ADD A BLANK HIDDEN ROW FOR CLONING WHEN CREATING A NEW BOOKMARK

		$bookmarks[] = array(
			'id' => '-1',
			'name' => 'dGhpcyByb3cgc2hvdWxkIGJlIGNsb25lZA==',  // BASE64 ENCODED "this row should be cloned"
			'url' => '',
			'croppedUrl' => '',
			'notes' => '',
			'folder' => '',
			'project' => '',
			'date' => ''
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

        return $this->render('default/bookmarks.html.twig', array(
			'bookmarks' => $bookmarks,
			'folders' => $folders,
			'projects' => $projects,
            'standardArea' => $this->standardArea,
			'currentPage' => $this->currentPage
        ));
    }

	/**
	 * @Route("/notebook/bookmarks/createBookmark/", name="bookmarksCreateBookmark")
	 * @Method("POST")
	 */
	public function createBookmarkAction(Request $request)
	{
		$project = $request->request->get('project');
		$folder = $request->request->get('folder');

		$date = new \DateTime("now");

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$bookmark = new Bookmark();
		$bookmark->setUserId($userId);
		$bookmark->setDateCreated($date);
		$bookmark->setDateModified($date);
		$bookmark->setName("");
		$bookmark->setUrl("");
		$bookmark->setNotes("");
		$bookmark->setProject($project);
		$bookmark->setFolder($folder);

		$em = $this->getDoctrine()->getManager();

		$em->persist($bookmark);
		$em->flush();

		$response = new JsonResponse(array('id' => $bookmark->getId(), 'project' => $bookmark->getProject(), 'folder' => $bookmark->getFolder()));

		return $response;
	}

	/**
	 * @Route("/notebook/bookmarks/saveBookmark/", name="bookmarksSaveBookmark")
	 * @Method("POST")
	 */
	public function saveBookmarkAction(Request $request)
	{
		$helper = $this->get('app.services.helper');

		$id = $request->request->get('id');
		$name = $request->request->get('name');
		$url = $request->request->get('url');
		$notes = $request->request->get('notes');

		$date = new \DateTime("now");

		$em = $this->getDoctrine()->getManager();
		$bookmark = $em->getRepository('AppBundle:Bookmark')->find($id);

		if (!$bookmark) {
			throw $this->createNotFoundException('No bookmark found for id '.$id);
		}

		$bookmark->setDateModified($date);
		$bookmark->setName($name);
		$bookmark->setUrl($url);
		$bookmark->setNotes($notes);

		$em->flush();

		$response = new JsonResponse(array('id' => $bookmark->getId(), 'name' => $bookmark->getName(), 'url' => $bookmark->getUrl(), 'croppedUrl' => $helper->cropBookmarkUrl($bookmark->getUrl()), 'notes' => $bookmark->getNotes()));

		return $response;
	}

	/**
	 * @Route("/notebook/bookmarks/remove/", name="bookmarksRemove")
	 * @Method("POST")
	 */
	public function removeAction(Request $request)
	{
		$id = $request->request->get('id');

		$em = $this->getDoctrine()->getManager();
		$bookmark = $em->getRepository('AppBundle:Bookmark')->find($id);

		if (!$bookmark) {
			throw $this->createNotFoundException('No bookmark found for id '.$id);
		}

		$em->remove($bookmark);
		$em->flush();

		return new Response('success');
	}
	
	/**
	 * @Route("/notebook/bookmarks/movePageToFolder/", name="bookmarksMovePageToFolder")
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
	 * @Route("/notebook/bookmarks/removePageFromFolders/", name="bookmarksRemovePageFromFolders")
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
	 * @Route("/notebook/bookmarks/movePageToProject/", name="bookmarksMovePageToProject")
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
