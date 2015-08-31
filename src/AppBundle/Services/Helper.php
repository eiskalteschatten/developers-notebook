<?php

namespace AppBundle\Services;


class Helper
{
    public static function createPagePreview($content) {
        $explodedContent = explode("\n", $content);
        return substr($explodedContent[0], 0, 35);
    }
}