<?php

namespace AppBundle\Services;

use AppBundle\Entity\Labels;


class LabelsService
{
    public function createPagePreview($content) {
        $explodedContent = explode("\n", $content);
        return substr($explodedContent[0], 0, 35);
    }
}