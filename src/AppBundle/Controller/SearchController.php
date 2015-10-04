<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends Controller
{
    /**
     * @Route("/notebook/search/", name="searchUrl")
     */
    public function indexAction(Request $request)
    {
        $q = $request->query->get('q');

        return $this->render('default/search.html.twig', array(
            'query' => $q
        ));
    }
    
	/**
	 * @Route("/notebook/search/pages/", name="searchPages")
	 * @Method("POST")
	 */
	public function searchPagesAction(Request $request)
	{
		$q = $request->request->get('q');
		$searchTerm = "%" . $q . "%";
		
		$helper = $this->get('app.services.helper');
		
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $numberOfItems = $this->container->getParameter('AppBundle.searchNumberOfResults');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery("SELECT t.id, t.area, t.content, t.dateModified FROM AppBundle:Pages t WHERE t.content LIKE :searchTerm AND t.userId = :userId")
			->setParameter('searchTerm', $searchTerm)
			->setParameter('userId', $userId)
			->setMaxResults($numberOfItems * 3);
		$searchResult = $query->getResult();

		$results = array();

		foreach ($searchResult as $result) {
			$content = $result['content'];
			$previewContent = $helper->createPagePreview($content);

			$results[] = array(
				'id' => $result['id'],
				'previewContent' => $previewContent,
				'area' => $result['area'],
				'date' => $result['dateModified']->format($dateTimeFormat)
			);
		}

		$response = new JsonResponse($results);

		return $response;
	}
    
	/**
	 * @Route("/notebook/search/todosIssuesBookmarks/", name="searchTodosIssuesBookmarks")
	 * @Method("POST")
	 */
	public function searchTodosIssuesBookmarksAction(Request $request)
	{
		$q = $request->request->get('q');
		$searchTerm = "%" . $q . "%";
		
		$helper = $this->get('app.services.helper');
		
        $dateFormat = $this->container->getParameter('AppBundle.dateFormat');
        $numberOfItems = $this->container->getParameter('AppBundle.searchNumberOfResults');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		
		$results = array();
		
		// GET TO DOS
		
		$query = $em->createQuery("SELECT t.id, t.userSpecificId, t.todo, t.priority, t.datePlanned, t.dateDue FROM AppBundle:Todo t WHERE (t.todo LIKE :searchTerm OR t.notes LIKE :searchTerm) AND t.userId = :userId AND t.isCompleted = false")
			->setParameter('searchTerm', $searchTerm)
			->setParameter('userId', $userId)
			->setMaxResults($numberOfItems);
		$searchResult = $query->getResult();

		foreach ($searchResult as $result) {
			$datePlanned = $result['datePlanned'];
			if ($datePlanned) {
				$datePlanned = $datePlanned->format($dateFormat);
			}

			$dateDue = $result['dateDue'];
			if ($dateDue) {
				$dateDue = $dateDue->format($dateFormat);
			}
			
			$results[] = array(
				'id' => $result['id'],
				'itemId' => $result['userSpecificId'],
				'name' => $result['todo'],
				'priority' => $result['priority'],
				'area' => 'todo',
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue	
			);
		}
		
		
		// GET ISSUES
		
		$query = $em->createQuery("SELECT t.id, t.userSpecificId, t.title, t.description, t.datePlanned, t.dateDue FROM AppBundle:Issue t WHERE (t.title LIKE :searchTerm OR t.description LIKE :searchTerm) AND t.userId = :userId AND t.isCompleted = false")
			->setParameter('searchTerm', $searchTerm)
			->setParameter('userId', $userId)
			->setMaxResults($numberOfItems);
		$searchResult = $query->getResult();

		foreach ($searchResult as $result) {
			$datePlanned = $result['datePlanned'];
			if ($datePlanned) {
				$datePlanned = $datePlanned->format($dateFormat);
			}

			$dateDue = $result['dateDue'];
			if ($dateDue) {
				$dateDue = $dateDue->format($dateFormat);
			}
			
			$results[] = array(
				'id' => $result['id'],
				'itemId' => $result['userSpecificId'],
				'name' => $result['title'],
				'description' => $result['description'],
				'area' => 'issues',
				'datePlanned' => $datePlanned,
				'dateDue' => $dateDue		
			);
		}
		
		
		// GET BOOKMARKS
		
		$query = $em->createQuery("SELECT t.id, t.name, t.url FROM AppBundle:Bookmark t WHERE (t.name LIKE :searchTerm OR t.url LIKE :searchTerm OR t.notes LIKE :searchTerm) AND t.userId = :userId")
			->setParameter('searchTerm', $searchTerm)
			->setParameter('userId', $userId)
			->setMaxResults($numberOfItems);
		$searchResult = $query->getResult();

		foreach ($searchResult as $result) {
			$results[] = array(
				'id' => $result['id'],
				'name' => $result['name'],
				'url' => $result['url'],
                'croppedUrl' => $helper->cropBookmarkUrl($result["url"]),
				'area' => 'bookmarks'
			);
		}


		$response = new JsonResponse($results);

		return $response;
	}
	
	/**
	 * @Route("/notebook/search/projects/", name="searchProjects")
	 * @Method("POST")
	 */
	public function searchProjectsAction(Request $request)
	{
		$q = $request->request->get('q');
		$searchTerm = "%" . $q . "%";
		
		$helper = $this->get('app.services.helper');
		
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $numberOfItems = $this->container->getParameter('AppBundle.searchNumberOfResults');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		
		$results = array();
		
		$query = $em->createQuery("SELECT t.id, t.name, t.dateModified FROM AppBundle:Project t WHERE t.name LIKE :searchTerm AND t.userId = :userId AND t.isCompleted = false")
			->setParameter('searchTerm', $searchTerm)
			->setParameter('userId', $userId)
			->setMaxResults($numberOfItems);
		$searchResult = $query->getResult();

		foreach ($searchResult as $result) {
			$results[] = array(
				'id' => $result['id'],
				'name' => $result['name'],
				'date' => $result['dateModified']->format($dateTimeFormat)	
			);
		}


		$response = new JsonResponse($results);

		return $response;
	}
}