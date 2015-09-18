<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\JavascriptError;
use AppBundle\Services\Helper;

class JavaScriptErrorController extends Controller
{
    /**
     * @Route("/notebook/javascript/reportError/", name="reportJavaScriptError")
     * @Method("POST")
     */
    public function reportError(Request $request)
    {
        $errorMsg = $request->request->get('error');
        $userAgent = $request->request->get('userAgent');
        $url = $request->request->get('url');
        $scriptUrl = $request->request->get('scriptUrl');
        $column = $request->request->get('column');
        $line = $request->request->get('line');

        $date = new \DateTime("now");

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = $user->getId();

        $error = new JavascriptError();
        $error->setUserId($userId);
        $error->setError($errorMsg);
        $error->setUserAgent($userAgent);
        $error->setUrl($url);
        $error->setScriptUrl($scriptUrl);
        $error->setColumnNumber($column);
        $error->setLineNumber($line);
        $error->setDateCreated($date);

        $em = $this->getDoctrine()->getManager();

        $em->persist($error);
        $em->flush();

        return new Response('success');
    }
}