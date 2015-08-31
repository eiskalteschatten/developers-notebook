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

class CodeCacheController extends Controller
{
	private $standardSyntax = "javascript";
	private $standardArea = "code";
	private $dateTimeFormat = 'Y-m-d H:i:s';
	
    /**
     * @Route("/notebook/code-cache/", name="codeCache")
     */
    public function indexAction(Request $request)
    {
	    // GET PAGES
	    
		$pagesResult = $this->getDoctrine()
        ->getRepository('AppBundle:Pages')
        ->findBy(
		    array('area' => $this->standardArea)//,
		    //array('user_id' => 0)
		);
		
		$pages = array();
		
		foreach ($pagesResult as $page) {
			$content = $page->getContent();
			$explodedContent = explode("\n", $content);
			$previewContent = $explodedContent[0];	
			
			$pages[] = array(
				'id' => $page->getId(),
				'content' => $content,
				'previewContent' => $previewContent,
				'syntax' => $page->getSyntax(),
				'folder' => $page->getFolder(),
				'project' => $page->getProject(),
				'date' => $page->getDateModified()->format($this->dateTimeFormat)
			);
		}
		
		 // GET FOLDERS
		
		$foldersResult = $this->getDoctrine()
        ->getRepository('AppBundle:Folders')
        ->findBy(
		    array('area' => $this->standardArea)//,
		    //array('user_id' => 0)
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
        ->getRepository('AppBundle:Projects')
        ->findAll();
/*
        ->findBy(
		    array('user_id' => 0)
		);
*/
		
		$projects = array();
		
		foreach ($projectsResult as $project) {
			$projects[] = array(
				'id' => $project->getId(),
				'name' => $project->getName()
			);
		}
		
        return $this->render('default/code-cache.html.twig', array(
	        'pages' => $pages,
	        'folders' => $folders,
	        'projects' => $projects
        ));
    }

    /**
     * @Route("/notebook/code-cache/create/", name="codeCacheCreate")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $date = new \DateTime("now");

        $pages = new Pages();
        $pages->setUserId(0);
        $pages->setContent("");
        $pages->setDateCreated($date);
        $pages->setDateModified($date);
        $pages->setFolder(0);
        $pages->setProject(0);
        $pages->setSyntax($this->standardSyntax);
        $pages->setArea($this->standardArea);

        $em = $this->getDoctrine()->getManager();

        $em->persist($pages);
        $em->flush();

        $response = new JsonResponse(array('id' => $pages->getId(), 'syntax' => $pages->getSyntax(), 'folder' => $pages->getFolder(), 'project' => $pages->getProject(), 'date' => $pages->getDateModified()->format($this->dateTimeFormat)));

        return $response;
    }

    /**
     * @Route("/notebook/code-cache/save/", name="codeCacheSave")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {
		//$name = $request->request->get('name');

        return new Response('success');
    }
}
