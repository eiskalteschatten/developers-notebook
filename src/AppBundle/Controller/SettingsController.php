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
     * @Route("/settings/", name="settingsPage")
     */
    public function settingsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $settingsResult = $this->getDoctrine()
            ->getRepository('AppBundle:EditorSettings')
            ->findOneBy(
                array('userId' => $userId)
            );

        return $this->render('default/settings.html.twig', array(
            'editorSettings' => $settingsResult,
            'syntaxOptions' => $this->container->getParameter('AppBundle.syntaxOptions'),
            'editorThemes' => $this->container->getParameter('AppBundle.editorThemes')
        ));
    }

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

        if (!$es) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.settingsSavedError')));
            return $response;
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
            case "notes":
                $es->setDefaultSyntaxModeNotebook($defaultSyntax);
                break;
            case "journal":
                $es->setDefaultSyntaxModeJournal($defaultSyntax);
                break;
            default:
                $es->setDefaultSyntaxModeNotebook($defaultSyntax);
                break;
        }

        $em->flush();

        $response = new JsonResponse(array('msgType' => 'success', 'message' => $this->container->getParameter('AppBundle.messages.settingsSavedSuccess')));
        return $response;
    }

    /**
     * @Route("/notebook/editor-settings/save-all/", name="editorSaveAllSettings")
     * @Method("POST")
     */
    public function saveAllEditorSettingsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $defaultTheme = $request->request->get('defaultTheme');
        $defaultSyntaxCode = $request->request->get('defaultSyntaxCode');
        $defaultSyntaxJournal = $request->request->get('defaultSyntaxJournal');
        $defaultSyntaxNotebook = $request->request->get('defaultSyntaxNotebook');
        $highlightActiveLine= ($request->request->get('highlightActiveLine') === 'true');
        $wrapSearch = ($request->request->get('wrapSearch') === 'true');
        $caseSensitive = ($request->request->get('caseSensitive') === 'true');
        $wholeWord = ($request->request->get('wholeWord') === 'true');
        $regExp = ($request->request->get('regExp') === 'true');
        $skipCurrent = ($request->request->get('skipCurrent') === 'true');

        $em = $this->getDoctrine()->getManager();
        $es = $em->getRepository('AppBundle:EditorSettings')
            ->findOneBy(array('userId' => $userId));

        if (!$es) {
            $response = new JsonResponse(array('msgType' => 'error', 'message' => $this->container->getParameter('AppBundle.messages.settingsSavedError')));
            return $response;
        }

        $es->setDefaultTheme($defaultTheme);
        $es->setHighlightActiveLine($highlightActiveLine);
        $es->setWrapSearch($wrapSearch);
        $es->setCaseSensitiveSearch($caseSensitive);
        $es->setMatchWholeWordsSearch($wholeWord);
        $es->setIsRegexSearch($regExp);
        $es->setSkipCurrentLineSearch($skipCurrent);
        $es->setDefaultSyntaxModeCode($defaultSyntaxCode);
        $es->setDefaultSyntaxModeNotebook($defaultSyntaxNotebook);
        $es->setDefaultSyntaxModeJournal($defaultSyntaxJournal);

        $em->flush();

        $response = new JsonResponse(array('msgType' => 'success', 'message' => $this->container->getParameter('AppBundle.messages.settingsSavedSuccess')));
        return $response;
    }
}