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
				'date' => $result['dateModified']->format($dateTimeFormat),
			);
		}

		$response = new JsonResponse($results);

		return $response;
	}

}