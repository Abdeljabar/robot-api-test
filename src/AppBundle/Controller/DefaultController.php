<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function overviewAction(Request $request)
    {
        
    }
}
