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

        return $this->render('default/projects-single.html.twig', array(
            'project' => $projectsResult,
            'pages' => $pages,
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
}
