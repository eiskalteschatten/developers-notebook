<?php

namespace AppBundle\Services;


class Helper
{
    public static function createPagePreview($content) {
        $explodedContent = explode("\n", $content);
        return substr($explodedContent[0], 0, 35);
    }

    public static function cropBookmarkUrl($url) {
        $explodedContent = explode("\n", $url);
        return substr($explodedContent[0], 0, 50);
    }

    public static function createTodosHtmlLinks($todos, $todosUrl) {
        $i = 0;
        $length = count($todos);

        $html = "";

        foreach($todos as $todo) {
            $html .= '<a href="' . $todosUrl . '?selectedItem=' . $todo . '">';
            $html .= '#' . $todo;
            $html .= '</a>';

            if ($i != $length - 1) {
                $html .= ",&nbsp;";
            }

            $i++;
        }

        return $html;
    }

    public static function createIssuesHtmlLinks($issues) {
        $i = 0;
        $length = count($issues);

        $html = "";

        foreach($issues as $issue) {
            $html .= '<a href="' . $issue['url'] . '">';
            $html .= '#' . $issue['id'];
            $html .= '</a>';

            if ($i != $length - 1) {
                $html .= ",&nbsp;";
            }

            $i++;
        }

        return $html;
    }
}