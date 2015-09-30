<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Project;
use AppBundle\Entity\Pages;
use AppBundle\Entity\Bookmark;
use AppBundle\Entity\Todo;
use AppBundle\Entity\Issue;
use AppBundle\Services\Helper;

class ProjectController extends Controller
{
    private $currentPage = "projects";

    /**
     * @Route("/notebook/projects", name="projects")
     */
    public function indexAction(Request $request)
    {
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $projectsResult = $this->getDoctrine()
            ->getRepository('AppBundle:Project')
            ->findBy(
                array('userId' => $userId),
                array('name' => 'ASC')
            );

        $projects = array();

        foreach ($projectsResult as $project) {
            if ($project->getDateCompleted()) {
                $dateCompleted = $project->getDateCompleted()->format($dateTimeFormat);
            }
            else {
                $dateCompleted = "";
            }

            $projects[] = array(
                'id' => $project->getId(),
                'name' => $project->getName(),
                'dateModified' => $project->getDateModified()->format($dateTimeFormat),
                'isCompleted' => $project->getIsCompleted(),
                'dateCompleted' => $dateCompleted
            );
        }

        return $this->render('default/projects.html.twig', array(
            'projects' => $projects,
            'currentPage' => $this->currentPage
        ));
    }

    /**
     * @Route("/notebook/projects/{id}/", name="singleProject")
     */
    public function singleProjectAction(Request $request, $id)
    {
        $helper = $this->get('app.services.helper');

        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $dateFormat = $this->container->getParameter('AppBundle.dateFormat');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $projectsResult = $this->getDoctrine()
            ->getRepository('AppBundle:Project')
            ->findOneBy(
                array('id' => $id, 'userId' => $userId)
            );

        if (!$projectsResult) {
            return $this->render('default/projects-single.html.twig', array(
                'fail' => true,
                'currentPage' => $this->currentPage
            ));
        }

        // GET PAGES FROM JOURNAL, CODE CACHE, AND NOTES

        $pagesResults = $this->getDoctrine()
            ->getRepository('AppBundle:Pages')
            ->findBy(
                array('userId' => $userId, 'project' => $id),
                array('dateModified' => 'ASC')
            );

        $pages = array();

        foreach ($pagesResults as $page) {
            $pages[] = array(
                'id' => $page->getId(),
                'content' => $helper->createPagePreview($page->getContent()),
                'area' => $page->getArea(),
                'dateModified' => $page->getDateModified()->format($dateTimeFormat)
            );
        }

        // GET BOOKMARKS

        $bookmarksResult = $this->getDoctrine()
            ->getRepository('AppBundle:Bookmark')
            ->findBy(
                array('userId' => $userId, 'project' => $id),
                array('dateModified' => 'DESC')
            );

        $bookmarks = array();

        foreach ($bookmarksResult as $bookmark) {
            $bookmarks[] = array(
                'id' => $bookmark->getId(),
                'name' => $bookmark->getName(),
                'url' => $bookmark->getUrl(),
                'croppedUrl' => $helper->cropBookmarkUrl($bookmark->getUrl()),
                'notes' => $bookmark->getNotes(),
                'folder' => $bookmark->getFolder(),
                'project' => $bookmark->getProject(),
                'date' => $bookmark->getDateModified()->format($dateTimeFormat)
            );
        }


        // GET TO DOS

        $todosResult = $this->getDoctrine()
            ->getRepository('AppBundle:Todo')
            ->findBy(
                array('userId' => $userId, 'project' => $id),
                array('dateModified' => 'DESC')
            );

        $todos = array();

        foreach ($todosResult as $todo) {
            $dateCompleted = $todo->getDateCompleted();
            if ($dateCompleted) {
                $dateCompleted = $dateCompleted->format($dateTimeFormat);
            }

            $datePlanned = $todo->getDatePlanned();
            if ($datePlanned) {
                $datePlanned = $datePlanned->format($dateFormat);
            }

            $dateDue = $todo->getDateDue();
            if ($dateDue) {
                $dateDue = $dateDue->format($dateFormat);
            }

            $issuesTodosResult = $this->getDoctrine()
                ->getRepository('AppBundle:ConnectorTodosIssues')
                ->findBy(
                    array('userId' => $userId, 'todo' => $todo->getId()),
                    array('dateCreated' => 'ASC')
                );

            $issuesTodos = array();
            $issuesTodosHtml = array();

            foreach ($issuesTodosResult as $issue) {
                $issuesTodos[] = $issue->getIssue();

                $issuesTodosHtml[] = array(
                    'id' => $issue->getIssue(),
                    'url' => $this->generateUrl("singleIssue", array('id' => $issue->getIssue()))
                );
            }

            $todos[] = array(
                'id' => $todo->getId(),
                'name' => $todo->getTodo(),
                'notes' => $todo->getNotes(),
                'isCompleted' => $todo->getIsCompleted(),
                'dateCompleted' => $dateCompleted,
                'datePlanned' => $datePlanned,
                'dateDue' => $dateDue,
                'priority' => $todo->getPriority(),
                'folder' => $todo->getFolder(),
                'project' => $todo->getProject(),
                'date' => $todo->getDateModified()->format($dateTimeFormat),
                'issues' => $issuesTodos,
                'issuesHtml' => $helper->createIssuesHtmlLinks($issuesTodosHtml)
            );
        }

        // GET ISSUES

        $issuesResult = $this->getDoctrine()
            ->getRepository('AppBundle:Issue')
            ->findBy(
                array('userId' => $userId, 'project' => $id),
                array('dateModified' => 'DESC')
            );

        $issues = array();

        foreach ($issuesResult as $issue) {
            $dateCompleted = $issue->getDateCompleted();
            if ($dateCompleted) {
                $dateCompleted = $dateCompleted->format($dateTimeFormat);
            }

            $datePlanned = $issue->getDatePlanned();
            if ($datePlanned) {
                $datePlanned = $datePlanned->format($dateFormat);
            }

            $dateDue = $issue->getDateDue();
            if ($dateDue) {
                $dateDue = $dateDue->format($dateFormat);
            }

            // GET CONNECTED TO DOS

            $issuesTodosResult = $this->getDoctrine()
                ->getRepository('AppBundle:ConnectorTodosIssues')
                ->findBy(
                    array('userId' => $userId, 'issue' => $issue->getId()),
                    array('dateCreated' => 'ASC')
                );

            $issuesTodos = array();

            foreach ($issuesTodosResult as $todo) {
                $issuesTodos[] = $todo->getTodo();
            }

            $issues[] = array(
                'id' => $issue->getId(),
                'name' => $issue->getTitle(),
                'description' => $issue->getDescription(),
                'isCompleted' => $issue->getIsCompleted(),
                'dateCompleted' => $dateCompleted,
                'datePlanned' => $datePlanned,
                'dateDue' => $dateDue,
                'labels' => $issue->getLabels(),
                'todos' => $helper->createTodosHtmlLinks($issuesTodos, $this->generateUrl('todos')),
                'folder' => $issue->getFolder(),
                'project' => $issue->getProject(),
                'date' => $issue->getDateModified()->format($dateTimeFormat)
            );
        }

        return $this->render('default/projects-single.html.twig', array(
            'project' => $projectsResult,
            'pages' => $pages,
            'bookmarks' => $bookmarks,
            'todos' => $todos,
            'issues' => $issues,
            'currentPage' => $this->currentPage
        ));
    }

    /**
     * @Route("/notebook/project/create/", name="projectCreate")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

        $name = $request->request->get('name');

        $date = new \DateTime("now");

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $project = new Project();
        $project->setUserId($userId);
        $project->setName($name);
        $project->setDateCreated($date);
        $project->setDateModified($date);
        $project->setIsCompleted(false);

        $em = $this->getDoctrine()->getManager();

        $em->persist($project);
        $em->flush();

        $response = new JsonResponse(array('id' => $project->getId(), 'name' => $project->getName(), 'date' => $project->getDateModified()->format($dateTimeFormat)));

        return $response;
    }


    /**
     * @Route("/notebook/project/is-complete/", name="projectIsComplete")
     * @Method("POST")
     */
    public function changeIsCompleteAction(Request $request)
    {
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

        $id = $request->request->get('id');
        $isComplete = ($request->request->get('isComplete') === 'true');
        $allComplete = ($request->request->get('allComplete') === 'true');

        $date = new \DateTime("now");

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('AppBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('No project found for id '.$id);
        }

        $project->setDateModified($date);
        $project->setIsCompleted($isComplete);

        if ($isComplete == true) {
            $project->setDateCompleted($date);
            $date = $project->getDateCompleted()->format($dateTimeFormat);
        }
        else {
            $project->setDateCompleted(null);
            $date = $project->getDateModified()->format($dateTimeFormat);
        }

        if ($allComplete == true) {
            $issuesResult = $this->getDoctrine()
                ->getRepository('AppBundle:Issue')
                ->findBy(
                    array('project' => $id)
                );

            if ($issuesResult) {
                foreach ($issuesResult as $issue) {
                    $issue->setIsCompleted(true);
                }
            }

            $todosResult = $this->getDoctrine()
                ->getRepository('AppBundle:Todo')
                ->findBy(
                    array('project' => $id)
                );

            if ($todosResult) {
                foreach ($todosResult as $todo) {
                    $todo->setIsCompleted(true);
                }
            }
        }

        $em->flush();

        $response = new JsonResponse(array('id' => $project->getId(), 'isCompleted' => $project->getIsCompleted(), 'date' => $date));

        return $response;
    }

    /**
     * @Route("/notebook/project/delete/", name="projectDelete")
     * @Method("POST")
     */
    public function deleteProjectAction(Request $request)
    {
        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');

        $id = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('AppBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('No project found for id '.$id);
        }

        $em->remove($project);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route("/notebook/project/rename/", name="projectRename")
     * @Method("POST")
     */
    public function renameProjectAction(Request $request)
    {
        $helper = $this->get('app.services.helper');

        $id = $request->request->get('id');
        $name = $request->request->get('name');

        $date = new \DateTime("now");

        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('AppBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('No project found for id '.$id);
        }

        $project->setDateModified($date);
        $project->setName($name);

        $em->flush();

        $response = new JsonResponse(array('id' => $project->getId(), 'name' => $project->getName()));

        return $response;
    }

}
