<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="web_homepage")
     */
    public function indexAction()
    {

    }

    /**
     * @Route("/overview", name="web_overview")
     * @param Request $request
     * @return Response
     */
    public function overviewAction(Request $request)
    {
        $robotsUrl = file_get_contents('http://robot.app/app_dev.php/api/robots/');
        $robotsResponse = json_decode($robotsUrl, true);

        //var_dump($robotsResponse);exit;

        $robots = $robotsResponse['data'];

        foreach ($robots as $k=>$v) {
            
        }

        if ($robotsResponse['success']) {
            return $this->render(
                'overview/index.html.twig',
                ['robots'=>$robots]);
        } else {
            return new Response('There was an error: ' . $robotsResponse['message']);
        }

    }
}
