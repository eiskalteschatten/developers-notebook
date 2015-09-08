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
use AppBundle\Entity\Project;
use AppBundle\Entity\EditorSettings;
use AppBundle\Services\Helper;

class CodeCacheController extends Controller
{
    private $standardArea = "code";
	private $currentPage = "codeCache";

    /**
     * @Route("/notebook/code-cache/", name="codeCache")
     */
    public function indexAction(Request $request)
    {
		$helper = $this->get('app.services.helper');

        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

		$user = $this->get('security.token_storage')->getToken()->getUser();
		$userId = $user->getId();

	    // GET PAGES
	    
		$pagesResult = $this->getDoctrine()
        ->getRepository('AppBundle:Pages')
        ->findBy(
			array('userId' => $userId, 'area' => $this->standardArea),
			array('dateModified' => 'DESC')
		);
		
		$pages = array();
		
		foreach ($pagesResult as $page) {
			$content = $page->getContent();
			$previewContent = $helper->createPagePreview($content);

			$year = $page->getDateCreated()->format('Y');
			
			$pages[] = array(
				'id' => $page->getId(),
				'content' => $content,
				'previewContent' => $previewContent,
				'syntax' => $page->getSyntax(),
				'folder' => $page->getFolder(),
				'project' => $page->getProject(),
				'date' => $page->getDateModified()->format($dateTimeFormat),
				'year' => $year
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
			array('name' => 'ASC'),
			array('isCompleted' => false)
		);
		
		$projects = array();
		
		foreach ($projectsResult as $project) {
			$projects[] = array(
				'id' => $project->getId(),
				'name' => $project->getName()
			);
		}
		
		// GET EDITOR SETTINGS

		$settingsResult = $this->getDoctrine()
        ->getRepository('AppBundle:EditorSettings')
        ->findOneBy(
			array('userId' => $userId)
		);

		$standardSyntax = $settingsResult->getDefaultSyntaxModeCode();
		
        return $this->render('default/code-cache.html.twig', array(
            'standardArea' => $this->standardArea,
	        'pages' => $pages,
	        'folders' => $folders,
	        'projects' => $projects,
	        'editorSettings' => $settingsResult,
			'standardSyntax' => $standardSyntax,
			'syntaxOptions' => $this->container->getParameter('AppBundle.syntaxOptions'),
			'editorThemes' => $this->container->getParameter('AppBundle.editorThemes'),
			'currentPage' => $this->currentPage
        ));
    }
}
