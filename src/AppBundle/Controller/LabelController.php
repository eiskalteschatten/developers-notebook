<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class LabelController extends Controller
{
    private $currentPage = "label";

    /**
     * @Route("/notebook/labels/", name="labelsPage")
     */
    public function indexAction(Request $request)
    {
        $labelsService = $this->get('app.services.labels');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $labelsResult = $this->getDoctrine()
            ->getRepository('AppBundle:Labels')
            ->findBy(
                array('userId' => $userId),
                array('name' => 'ASC')
            );

        $labels = array();

        foreach ($labelsResult as $label) {
            $labels[] = array(
                'id' => $label->getId(),
                'name' => $label->getName(),
                'color' => $label->getColor(),
                'labelColorHtml' => $labelsService->createLabelHtml(array($label)),
                'isCompleted' => $label->getIsCompleted()
            );
        }

        return $this->render('default/labels.html.twig', array(
            'labels' => $labels,
            'currentPage' => $this->currentPage
        ));
    }


    /**
     * @Route("/notebook/labels/getListOfLabels/", name="labelsGetListOfLabels")
     * @Method("GET")
     */
    public function getListOfLabelsAction(Request $request)
    {
        $term = $request->query->get('term');
        $searchTerm = "%" . $term . "%";

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t.name FROM AppBundle:Labels t WHERE t.name LIKE :searchTerm AND t.userId = :userId AND t.isCompleted = false")->setParameter('searchTerm', $searchTerm)->setParameter('userId', $userId);
        $labelsResult = $query->getResult();

        $labels = array();

        foreach ($labelsResult as $label) {
            $labels[] = array(
                'label' => $label['name'],
                'value' => $label['name']
            );
        }

        $response = new JsonResponse($labels);

        return $response;
    }

    /**
     * @Route("/notebook/labels/changeColor/", name="labelsChangeColor")
     * @Method("POST")
     */
    public function changeColorAction(Request $request)
    {
        $id = $request->request->get('id');
        $color = $request->request->get('color');

        $em = $this->getDoctrine()->getManager();
        $label = $em->getRepository('AppBundle:Labels')->find($id);

        if (!$label) {
            throw $this->createNotFoundException('No label found for id '.$id);
        }

        $label->setColor($color);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route("/notebook/labels/{name}/", name="singleLabel")
     */
    public function singleLabelAction(Request $request, $name)
    {
        $helper = $this->get('app.services.helper');

        $dateTimeFormat = $this->container->getParameter('AppBundle.dateTimeFormat');
        $dateFormat = $this->container->getParameter('AppBundle.dateFormat');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $decodedName = urldecode($name);

        $labelsResult = $this->getDoctrine()
            ->getRepository('AppBundle:Labels')
            ->findOneBy(
                array('name' => $decodedName, 'userId' => $userId)
            );

        if (!$labelsResult) {
            return $this->render('default/labels-single.html.twig', array(
                'fail' => true,
                'currentPage' => $this->currentPage
            ));
        }

        $em = $this->getDoctrine()->getManager();


        // GET BOOKMARKS

        $query = $em->createQuery("SELECT t FROM AppBundle:Bookmark t WHERE find_in_set(:label, replace(t.labels, ', ', ',')) != 0 AND t.userId = :userId ORDER BY t.dateModified")
            ->setParameter('label', $decodedName)
            ->setParameter('userId', $userId);
        $bookmarksResult = $query->getResult();

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

        $query = $em->createQuery("SELECT t FROM AppBundle:Todo t WHERE find_in_set(:label, replace(t.labels, ', ', ',')) != 0 AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('label', $decodedName)
            ->setParameter('userId', $userId);
        $todosResult = $query->getResult();

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
				'itemId' => $todo->getUserSpecificId(),
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

        $query = $em->createQuery("SELECT t FROM AppBundle:Issue t WHERE find_in_set(:label, replace(t.labels, ', ', ',')) != 0 AND t.userId = :userId AND t.isCompleted = false ORDER BY t.dateModified")
            ->setParameter('label', $decodedName)
            ->setParameter('userId', $userId);
        $issuesResult = $query->getResult();

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
                'itemId' => $issue->getUserSpecificId(),
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

        return $this->render('default/labels-single.html.twig', array(
            'label' => $labelsResult,
            'bookmarks' => $bookmarks,
            'todos' => $todos,
            'issues' => $issues,
            'currentPage' => $this->currentPage
        ));
    }
}
