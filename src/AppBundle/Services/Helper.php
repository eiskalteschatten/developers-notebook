<?php

namespace AppBundle\Services;


class Helper
{
    public static function createPagePreview($content) {
        $explodedContent = explode("\n", $content);
        return substr($explodedContent[0], 0, 35);
    }

    public static  function cropBookmarkUrl($url) {
        $explodedContent = explode("\n", $url);
        return substr($explodedContent[0], 0, 50);
    }
}