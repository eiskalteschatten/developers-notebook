<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Project;

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
}
