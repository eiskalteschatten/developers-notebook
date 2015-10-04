<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Pages;
use AppBundle\Entity\EditorSettings;
use AppBundle\Services\Helper;

class NotebookController extends Controller
{
    private $standardArea = "notes";
	private $currentPage = "notes";

    /**
     * @Route("/notebook/notes/", name="notes")
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
		
		
		// GET FOLDERS AND PROJECTS
		
		$foldersProjects = $this->get('app.services.getFoldersProjects');
		$foldersProjects->init($userId, $this->standardArea);
		
		$folders = $foldersProjects->getFolders();
		$projects = $foldersProjects->getProjects();
		
		
		// GET EDITOR SETTINGS

		$settingsResult = $this->getDoctrine()
        ->getRepository('AppBundle:EditorSettings')
        ->findOneBy(
			array('userId' => $userId)
		);

		$standardSyntax = $settingsResult->getDefaultSyntaxModeNotebook();
		
        return $this->render('default/notebook.html.twig', array(
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
