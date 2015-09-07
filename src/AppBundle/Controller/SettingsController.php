<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\EditorSettings;

class SettingsController extends Controller
{
    /**
     * @Route("/notebook/editor-settings/save/", name="editorSaveSettings")
     * @Method("POST")
     */
    public function saveEditorSettingsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $defaultTheme = $request->request->get('defaultTheme');
        $defaultSyntax = $request->request->get('defaultSyntax');
        $highlightActiveLine= ($request->request->get('highlightActiveLine') === 'true');
        $wrapSearch = ($request->request->get('wrapSearch') === 'true');
        $caseSensitive = ($request->request->get('caseSensitive') === 'true');
        $wholeWord = ($request->request->get('wholeWord') === 'true');
        $regExp = ($request->request->get('regExp') === 'true');
        $skipCurrent = ($request->request->get('skipCurrent') === 'true');
        $area = $request->request->get('area');

        $em = $this->getDoctrine()->getManager();
        $es = $em->getRepository('AppBundle:EditorSettings')
            ->findOneBy(array('userId' => $userId));

        $isNew = false;

        if (!$es) {
            $isNew = true;
            $es = new EditorSettings();
            $es->setUserId($userId);
        }

        $es->setDefaultTheme($defaultTheme);
        $es->setHighlightActiveLine($highlightActiveLine);
        $es->setWrapSearch($wrapSearch);
        $es->setCaseSensitiveSearch($caseSensitive);
        $es->setMatchWholeWordsSearch($wholeWord);
        $es->setIsRegexSearch($regExp);
        $es->setSkipCurrentLineSearch($skipCurrent);

        switch ($area) {
            case "code":
                $es->setDefaultSyntaxModeCode($defaultSyntax);
                break;
            case "notebook":
                $es->setDefaultSyntaxModeNotebook($defaultSyntax);
                break;
            case "journal":
                $es->setDefaultSyntaxModeJournal($defaultSyntax);
                break;
            default:
				$es->setDefaultSyntaxModeNotebook($defaultSyntax);
                break;
        }

        if ($isNew == true) {
            $em->persist($es);
        }

        $em->flush();

        return new Response('success');
    }
}