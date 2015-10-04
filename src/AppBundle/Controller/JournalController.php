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

class JournalController extends Controller
{
    private $standardArea = "journal";
	private $currentPage = "journal";

    /**
     * @Route("/notebook/journal/", name="journal")
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
			array('dateCreated' => 'DESC')
		);
		
		$pages = array();
		$years = array();
		
		foreach ($pagesResult as $page) {
			$content = $page->getContent();
			$previewContent = $helper->createPagePreview($content);

			$year = $page->getDateCreated()->format('Y');

			if (!in_array($year, $years)) {
				$years[] = $year;
			}

			$pages[] = array(
				'id' => $page->getId(),
				'content' => $content,
				'previewContent' => $previewContent,
				'syntax' => $page->getSyntax(),
				'folder' => $page->getFolder(),
				'project' => $page->getProject(),
				'date' => $page->getDateCreated()->format($dateTimeFormat),
				'year' => $year
			);
		}

		ksort($years);

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

		$standardSyntax = $settingsResult->getDefaultSyntaxModeJournal();
		
        return $this->render('default/journal.html.twig', array(
            'standardArea' => $this->standardArea,
	        'pages' => $pages,
			'years' => $years,
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
