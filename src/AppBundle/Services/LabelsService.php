<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Labels;


class LabelsService
{
    protected $em;
    protected $defaultLabelColor;
    protected $router;
    protected $name;
    protected $color;
    protected $isComplete;

    public function __construct(EntityManager $entityManager, $defaultLabelColor, $router) {
        $this->em = $entityManager;
        $this->defaultLabelColor = $defaultLabelColor;
        $this->router = $router;
    }

    public function createLabel($labels, $userId) {
        $labelsExploded = explode(", ", $labels);

        foreach ($labelsExploded as $label) {
            $name = ltrim($label);
            $this->setName($label);

            $labelResult = $this->em->getRepository('AppBundle\Entity\Labels')->findOneBy(array('name' => $name, 'userId' => $userId));

            if (!$labelResult) {
                $labelResult = new Labels();
                $labelResult->setUserId($userId);
                $labelResult->setName($name);
                $labelResult->setColor($this->defaultLabelColor);
                $labelResult->setIsCompleted(false);

                $this->em->persist($labelResult);
            } else {
                $labelResult->setIsCompleted(false);
            }
        }

        $this->em->flush();

        $response = new JsonResponse(array("id" => $labelResult->getId(), "color" => $labelResult->getColor()));
        return $response;
    }

    public function fetchLabels($labels, $userId) {
        $labelsExploded = explode(", ", $labels);

        $labelsResult = $this->em
            ->getRepository('AppBundle:Labels')
            ->findBy(
                array('userId' => $userId, 'name' => $labelsExploded)
            );

        return $labelsResult;
    }

    public function getLabelUrls($labels) {
        $labelUrls = array();

        foreach ($labels as $label) {
            $labelUrls[] = $this->router->generate("singleLabel", array('name' => urlencode(trim($label->getName()))), "NETWORK_PATH");
        }

        return $labelUrls;
    }

    public function createLabelHtml($labels) {
        $i = 0;
        $length = count($labels);
        $html = "";

        if ($length > 0 && !empty($labels[0])) {
            foreach ($labels as $label) {
                $url = $this->router->generate("singleLabel", array('name' => urlencode(trim($label->getName()))), "NETWORK_PATH");
                $html .= '<a href="' . $url . '" class="label-color" style="background-color: ' . $label->getColor() . '">';
                $html .= $label->getName();
                $html .= '</a>';

                $i++;
            }
        }

        return $html;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getColor() {
        return $this->color;
    }

    public function setIsComplete($isComplete) {
        $this->isComplete = $isComplete;
    }

    public function getIsComplete() {
        return $this->isComplete;
    }
}