<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    private $currentPage = "home";

    /**
     * @Route("/notebook/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $helper = $this->get('app.services.helper');

        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $dateFormat = $this->container->getParameter('AppBundle.dateFormat');
        $numberOfItems = $this->container->getParameter('AppBundle.notebookHomeNumberOfItems');
        $numberOfEditedItems = $this->container->getParameter('AppBundle.notebookHomeNumberOfItemsEdited');

        $sevenDaysAgo = new \DateTime('-7 day');//$now->sub(new \DateInterval("P7D"));
        $sevenDaysFromNow = new \DateTime('+7 day');//$now->add(new \DateInterval("P7D"));

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $userName = $user->getUsername();

        $em = $this->getDoctrine()->getManager();

        // GET GENERAL SETTINGS FOR WEATHER

        $generalSettingsResult = $this->getDoctrine()
        ->getRepository('AppBundle:GeneralSettings')
        ->findOneBy(
            array('userId' => $userId)
        );


        // GET TO DOS

        $query = $em->createQuery("SELECT t.id, t.todo, t.datePlanned, t.dateDue, t.priority FROM AppBundle:Todo t WHERE (t.dateDue BETWEEN :sevenDaysAgo AND :sevenDaysFromNow OR t.datePlanned BETWEEN :sevenDaysAgo AND :sevenDaysFromNow) AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateDue, t.datePlanned")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems);
        $todosResult = $query->getResult();

        $todos = array();

        foreach ($todosResult as $todo) {
            $datePlanned = $todo['datePlanned'];
            if ($datePlanned) {
                $datePlanned = $datePlanned->format($dateFormat);
            }

            $dateDue = $todo['dateDue'];
            if ($dateDue) {
                $dateDue = $dateDue->format($dateFormat);
            }

            $todos[] = array(
                'id' => $todo['id'],
                'name' => $todo['todo'],
                'datePlanned' => $datePlanned,
                'dateDue' => $dateDue,
                'priority' => $todo['priority']
            );
        }


        // GET RECENTLY ADDED ITEMS

        $recentlyAdded = array();


        // GET RECENTLY ADDED BOOKMARKS

        $query = $em->createQuery("SELECT t.id, t.name, t.url, t.dateModified FROM AppBundle:Bookmark t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems);
        $bookmarksResult = $query->getResult();

        foreach ($bookmarksResult as $recent) {
            $dateModified = $recent["dateModified"];
            if ($dateModified) {
                $dateModified = $dateModified->format($dateTimeFormat);
            }

            $recentlyAdded[] = array(
                'id' => $recent["id"],
                'name' => $recent["name"],
                'url' => $recent["url"],
                'croppedUrl' => $helper->cropBookmarkUrl($recent["url"]),
                'type' => 'bookmark',
                'dateModified' => $dateModified
            );
        }


        // GET RECENTLY ADDED TODOS

        $query = $em->createQuery("SELECT t.id, t.todo, t.datePlanned, t.dateDue, t.dateModified, t.priority FROM AppBundle:Todo t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems);
        $todosResult = $query->getResult();

        foreach ($todosResult as $recent) {
	        $datePlanned = $recent['datePlanned'];
            if ($datePlanned) {
                $datePlanned = $datePlanned->format($dateFormat);
            }
            
            $dateDue = $recent['dateDue'];
            if ($dateDue) {
                $dateDue = $dateDue->format($dateFormat);
            }
	        
            $dateModified = $recent["dateModified"];
            if ($dateModified) {
                $dateModified = $dateModified->format($dateTimeFormat);
            }

            $recentlyAdded[] = array(
                'id' => $recent['id'],
                'name' => $recent['todo'],
                'datePlanned' => $datePlanned,
                'dateDue' => $dateDue,
                'priority' => $recent['priority'],
                'type' => 'todo',
                'dateModified' => $dateModified
            );
        }


        // GET RECENTLY ADDED ISSUES

        $query = $em->createQuery("SELECT t.id, t.title, t.datePlanned, t.dateDue, t.dateModified FROM AppBundle:Issue t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems);
        $issuesResult = $query->getResult();

        foreach ($issuesResult as $recent) {
	        $datePlanned = $recent['datePlanned'];
            if ($datePlanned) {
                $datePlanned = $datePlanned->format($dateFormat);
            }
            
            $dateDue = $recent['dateDue'];
            if ($dateDue) {
                $dateDue = $dateDue->format($dateFormat);
            }
	        
            $dateModified = $recent["dateModified"];
            if ($dateModified) {
                $dateModified = $dateModified->format($dateTimeFormat);
            }

            $recentlyAdded[] = array(
                'id' => $recent['id'],
                'name' => $recent['title'],
                'datePlanned' => $datePlanned,
                'dateDue' => $dateDue,
                'type' => 'issue',
                'dateModified' => $dateModified
            );
        }


        // GET RECENTLY ADDED PAGES (CODE CACHE, JOURNAL, NOTES)

        $query = $em->createQuery("SELECT t.id, t.content, t.area, t.dateModified FROM AppBundle:Pages t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems * 3);
        $bookmarksResult = $query->getResult();

        foreach ($bookmarksResult as $recent) {
            $dateModified = $recent["dateModified"];
            if ($dateModified) {
                $dateModified = $dateModified->format($dateTimeFormat);
            }

            $recentlyAdded[] = array(
                'id' => $recent['id'],
                'name' => $helper->createPagePreview($recent['content']),
                'area' => $recent['area'],
                'type' => 'page',
                'dateModified' => $dateModified
            );
        }


        // SORT RECENTLY ADDED BY DATE MODIFIED AND CROP IT TO THE DEFAULT NUMBER OF EDITED ITEMS

        usort($recentlyAdded, function($a1, $a2) {
            $v1 = strtotime($a1['dateModified']);
            $v2 = strtotime($a2['dateModified']);
            return $v2 - $v1;
        });

        $recentlyAdded = array_slice($recentlyAdded, 0, $numberOfEditedItems);


        // GET PROJECTS

        $query = $em->createQuery("SELECT t.id, t.name, t.dateModified FROM AppBundle:Project t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults($numberOfItems);
        $projectsResult = $query->getResult();

        $projects = array();

        foreach ($projectsResult as $project) {
            $dateModified = $project['dateModified'];
            if ($dateModified) {
                $dateModified = $dateModified->format($dateFormat);
            }

            $projects[] = array(
                'id' => $project['id'],
                'name' => $project['name'],
                'dateModified' => $dateModified
            );
        }

        return $this->render('default/index.html.twig', array(
            'generalSettings' => $generalSettingsResult,
            'todos' => $todos,
            'recentlyAdded' => $recentlyAdded,
            'projects' => $projects,
            'userName' => $userName,
            'currentPage' => $this->currentPage
        ));
    }
}
