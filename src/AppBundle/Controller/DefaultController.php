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
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $dateFormat = $this->container->getParameter('AppBundle.dateFormat');

        $sevenDaysAgo = new \DateTime('-7 day');//$now->sub(new \DateInterval("P7D"));
        $sevenDaysFromNow = new \DateTime('+7 day');//$now->add(new \DateInterval("P7D"));

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();
        $userName = $user->getUsername();

        $em = $this->getDoctrine()->getManager();

        // GET TO DOS

        $query = $em->createQuery("SELECT t.id, t.todo, t.datePlanned, t.dateDue, t.priority FROM AppBundle:Todo t WHERE (t.dateDue BETWEEN :sevenDaysAgo AND :sevenDaysFromNow OR t.datePlanned BETWEEN :sevenDaysAgo AND :sevenDaysFromNow) AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateDue, t.datePlanned")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults(5);
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


        // GET RECENTLY ADDED

        $query = $em->createQuery("SELECT b FROM AppBundle:Bookmark b WHERE b.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND b.userId = :userId ORDER BY b.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults(5);
        $recentResult = $query->getResult();

        $recentlyAdded = array();

        foreach ($recentResult as $recent) {
            $dateModified = $recent->getDateModified();
            if ($dateModified) {
                $dateModified = $dateModified->format($dateFormat);
            }

            $recentlyAdded[] = array(
                'id' => $recent->getId(),
                'name' => $recent->getName(),
                'dateModified' => $dateModified
            );
        }


        // GET PROJECTS

        $query = $em->createQuery("SELECT t.id, t.name, t.dateModified FROM AppBundle:Project t WHERE t.dateModified BETWEEN :sevenDaysAgo AND :sevenDaysFromNow AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('userId', $userId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('sevenDaysFromNow', $sevenDaysFromNow)
            ->setMaxResults(5);
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
            'todos' => $todos,
            'recentlyAdded' => $recentlyAdded,
            'projects' => $projects,
            'userName' => $userName,
            'currentPage' => $this->currentPage
        ));
    }
}
