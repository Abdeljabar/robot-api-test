<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Robot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RobotController
 * @package ApiBundle\Controller
 * @Route("/robots")
 */
class RobotController extends Controller
{
    /**
     * @Route("/", name="api_robots_index")
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request) {
        // Initiate the Doctrine Entity Manager
        $em = $this->getDoctrine()->getManager();

        $params = [];

        // Get all robots
        if (!empty($request->query->get('status')))
            $status = $request->query->get('status');
        else
            $status = '';

        if (!empty($request->query->get('type')))
            $type = $request->query->get('type');
        else
            $type = '';

        $robots = $em->getRepository("AppBundle:Robot")->findByParams($status, $type);

        // Check if there is no problem with the Database
        if (null === $robots) {
            // Failure. Return a database query failure.
            return new JsonResponse([
               [
                   'success' => 0,
                   'message' => 'Database query failure.'
               ]
            ], 500);
        }

        // Initiate an empty array of resources
        $data = [];

        // Fill in the array by robots found
        /** @var \AppBundle\Entity\Robot $robot */
        foreach ($robots as $robot) {
            $data[$robot->getId()] = [
                'name'      => $robot->getName(),
                'status'    => $robot->getStatus(),
                'year'      => $robot->getYear(),
                'uri'       => $this->generateUrl('api_robots_show', array('robot' => $robot->getId())),
                'search'       => $this->generateUrl('api_robots_search', array('q' => $robot->getName())),
                'type'      => [
                    'id'    => $robot->getType()->getId(),
                    'name'  => $robot->getType()->getName(),
                ]
            ];
        }


        if (!empty($data)) {
            // Success. Found some robots.
            return new JsonResponse(
                [
                    'success' => 1,
                    'message' => 'Found some robots.',
                    $data
                ], 200
            );

        } else {
            // Failure. Did not find any robots.
            return new JsonResponse(
                [
                    'success' => 0,
                    'message' => 'Did not find any robots.',
                    $data
                ], 200
            );
        }
    }

    /**
     * @Route("/{robot}", name="api_robots_show",  requirements={"robot": "\d+"})
     * @Method("GET")
     * @param Robot $robot
     * @return JsonResponse
     */
    public function showAction(Robot $robot=null) {
        // Check if there is no problem with the Database
        if (null === $robot) {
            // Failure. Return "robot not found".
            return new JsonResponse([
                'success' => 0,
                'message' => 'Robot not found.'
            ], 404);
        }

        // Initiate an empty array of resources
        $playload = [
            'name'      => $robot->getName(),
            'status'    => $robot->getStatus(),
            'year'      => $robot->getYear(),
            'uri'       => $this->generateUrl('api_robots_show', array('robot' => $robot->getId())),
            'type'      => [
                'id'    => $robot->getType()->getId(),
                'name'  => $robot->getType()->getName(),
            ]
        ];

        return new JsonResponse([
            'success' => 1,
            'message' => 'Robot found.',
            $playload
        ], 200);
    }

    /**
     * @Route("/search", name="api_robots_search")
     * @Method("GET")
     * @return JsonResponse
     */
    public function searchAction(Request $request) {
        // Initiate the Doctrine Entity Manager
        $em = $this->getDoctrine()->getManager();

        // Get all robots
        $searchQuery = $request->query->get('q');

        $robots = $em->getRepository("AppBundle:Robot")->searchByName($searchQuery);

        // Check if there is no problem with the Database
        if (null === $robots) {
            // Failure. Return a database query failure.
            return new JsonResponse([
                [
                    'success' => 0,
                    'message' => 'Database query failure.'
                ]
            ], 500);
        }

        // Initiate an empty array of resources
        $playload = [];

        // Fill in the array by the found robots
        /** @var \AppBundle\Entity\Robot $robot */
        foreach ($robots as $robot) {
            $playload[$robot->getId()] = [
                'name'      => $robot->getName(),
                'status'    => $robot->getStatus(),
                'year'      => $robot->getYear(),
                'uri'       => $this->generateUrl('api_robots_show', array('robot' => $robot->getId())),
                'type'      => [
                    'id'    => $robot->getType()->getId(),
                    'name'  => $robot->getType()->getName(),
                ]
            ];
        }


        if (!empty($playload)) {
            // Success. Found some robots.
            return new JsonResponse(
                [
                    'success' => 1,
                    'message' => 'Found some robots.',
                    $playload
                ], 200
            );

        } else {
            // Failure. Did not find any robots.
            return new JsonResponse(
                [
                    'success' => 0,
                    'message' => 'Did not find any robots.',
                    $playload
                ], 200
            );
        }
    }

    /**
     * @Route("/filter/{filter}", name="api_robots_filter")
     * @Method("GET")
     */
    public function filterAction($filter) {
        // Filter by Type & Status
        // Url filtered by status: api/robots/filter/status/[STATUS]
        // Url filtered by type api/robots/filter/type/[TYPE]
    }
}
