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
			$previewContent = $this->createPagePreview($content);
			
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
		$folder = $request->request->get('folder');
		$project = $request->request->get('project');
		$syntax = $request->request->get('syntax');
		
        $date = new \DateTime("now");

        $pages = new Pages();
        $pages->setUserId(0);
        $pages->setContent("");
        $pages->setDateCreated($date);
        $pages->setDateModified($date);
        $pages->setFolder($folder);
        $pages->setProject($project);
        $pages->setSyntax($syntax);
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
		$id = $request->request->get('id');
		$syntax = $request->request->get('syntax');
		$content = $request->request->get('content');
		
		$em = $this->getDoctrine()->getManager();
	    $pages = $em->getRepository('AppBundle:Pages')->find($id);
	
	    if (!$pages) {
	        throw $this->createNotFoundException('No page found for id '.$id);
	    }
	
	    $pages->setSyntax($syntax);
	    $pages->setContent($content);	    
	    $em->flush();

		$response = new JsonResponse(array('previewContent' => $this->createPagePreview($content)));

        return $response;
    }
    
    /**
     * @Route("/notebook/code-cache/remove/", name="codeCacheRemove")
     * @Method("POST")
     */
    public function removeAction(Request $request)
    {
		$id = $request->request->get('id');
		
		$em = $this->getDoctrine()->getManager();
	    $pages = $em->getRepository('AppBundle:Pages')->find($id);
	
		$em->remove($pages);
		$em->flush();
	   
        return new Response('success');
    }
    
    
    private function createPagePreview($content) {
	    $explodedContent = explode("\n", $content);
		return $explodedContent[0];	
    }
    
    
    /**
     * @Route("/notebook/code-cache/createFolder/", name="codeCacheCreateFolder")
     * @Method("POST")
     */
    public function createFolderAction(Request $request)
    {
		$name = $request->request->get('name');
		
        $date = new \DateTime("now");

        $folders = new Folders();
        $folders->setUserId(0);
        $folders->setDateCreated($date);
        $folders->setDateModified($date);
        $folders->setName($name);
        $folders->setArea($this->standardArea);

        $em = $this->getDoctrine()->getManager();

        $em->persist($folders);
        $em->flush();

        $response = new JsonResponse(array('id' => $folders->getId(), 'name' => $folders->getName()));

        return $response;
    }
}
