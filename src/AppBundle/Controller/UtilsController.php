<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UtilsController extends Controller
{
    /**
     * @Route("/notebook/utils/upgrade/", name="utilsUpgradeVersion")
     */
    public function upgradeAction(Request $request)
    {
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();

	    // UPDATE TODOS: ADD user_specific_id
	    
		$todosResult = $em
	        ->getRepository('AppBundle:Todo')
	        ->findBy(
				array('userId' => $userId)
			);
		
		foreach($todosResult as $item) {
			$id = $item->getId();
			$itemId = $item->getUserSpecificId();
			
			if ($itemId == 0) {
				$item->setUserSpecificId($id);
			}
		}
		
		
	    // UPDATE ISSUES: ADD user_specific_id
		
		$issuesResult = $em
	        ->getRepository('AppBundle:Issue')
	        ->findBy(
				array('userId' => $userId)
			);
		
		foreach($issuesResult as $item) {
			$id = $item->getId();
			$itemId = $item->getUserSpecificId();
			
			if ($itemId == 0) {
				$item->setUserSpecificId($id);
			}
		}
		
		
		$em->flush();

        return new Response('OK');
    }
}