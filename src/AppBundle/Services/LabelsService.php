<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Labels;


class LabelsService
{
    protected $em;
    protected $defaultLabelColor;
    protected $name;
    protected $color;
    protected $isComplete;

    public function __construct(EntityManager $entityManager, $defaultLabelColor) {
        $this->em = $entityManager;
        $this->defaultLabelColor = $defaultLabelColor;
    }

    public function createLabel($name, $userId) {
        $name = ltrim($name);
        $this->setName($name);

        $label = $this->em->getRepository('AppBundle\Entity\Labels')->findOneBy(array('name' => $name, 'userId' => $userId));

        if (!$label) {
            $label = new Labels();
            $label->setUserId($userId);
            $label->setName($name);
            $label->setColor($this->defaultLabelColor);
            $label->setIsCompleted(false);

            $this->em->persist($label);
        }
        else {
            $label->setIsCompleted(false);
        }

        $this->em->flush();

        $response = new JsonResponse(array("id" => $label->getId(), "color" => $label->getColor()));
        return $response;
    }

    public static function createHtmlLinks($labels, $labelsUrl) {
        $i = 0;
        $length = count($labels);

        $html = "";

        foreach($labels as $label) {
            $html .= '<a href="' . $labelsUrl[$i] . '">';
            $html .= trim($label);
            $html .= '</a>';

            if ($i != $length - 1) {
                $html .= ",&nbsp;";
            }

            $i++;
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