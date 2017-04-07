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
     * @param Request $request
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
     * @Route("/", name="api_robots_new")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $robotName = $request->get('robot_name');
        $robotType = $em->getRepository('AppBundle:Type')->find($request->get('robot_type'));
        $robotStatus = $request->get('robot_status');
        $robotYear = $request->get('robot_year');

        $robot = new Robot();
        $robot->setName($robotName)
            ->setStatus($robotStatus)
            ->setType($robotType)
            ->setYear($robotYear);


        $em->persist($robot);
        $em->flush();

        if ($robot->getId()) {
            $response = [
                'status'    => 1,
                'data'      => [
                    'name'      => $robot->getName(),
                    'status'    => $robot->getStatus(),
                    'year'      => $robot->getYear(),
                    'uri'       => $this->generateUrl('api_robots_show', array('robot' => $robot->getId())),
                    'type'      => [
                        'id'    => $robot->getType()->getId(),
                        'name'  => $robot->getType()->getName(),
                    ]
                ]
            ];
            $code = 201;
        } else {
            $response = [
                'status'    => 0,
                'message'   => 'Could not create a new robot.'
            ];
            $code = 400;
        }

        return new JsonResponse($response, $code);
    }

    /**
     * @Route("/{robot}", name="api_robots_edit")
     * @Method("PUT")
     * @param Request $request
     * @param Robot $robot
     * @return JsonResponse
     */
    public function editAction(Request $request, Robot $robot) {
        $em = $this->getDoctrine()->getManager();

        if (!empty($request->get('robot_name'))) {
            $robotName = $request->get('robot_name');
            $robot->setName($robotName);
        }

        if (!empty($request->get('robot_type'))) {
            $robotType = $em->getRepository('AppBundle:Type')->find($request->get('robot_type'));
            $robot->setType($robotType);
        }

        if ($request->get('robot_status')) {
            $robotStatus = $request->get('robot_status');
            $robot->setStatus($robotStatus);
        }
        if ($request->get('robot_year')) {
            $robotYear = $request->get('robot_year');
            $robot->setYear($robotYear);
        }

        $em->persist($robot);
        $em->flush();

        if ($robot->getId()) {
            $response = [
                'status'    => 1,
                'data'      => [
                    'name'      => $robot->getName(),
                    'status'    => $robot->getStatus(),
                    'year'      => $robot->getYear(),
                    'uri'       => $this->generateUrl('api_robots_show', array('robot' => $robot->getId())),
                    'type'      => [
                        'id'    => $robot->getType()->getId(),
                        'name'  => $robot->getType()->getName(),
                    ]
                ]
            ];
            $code = 200;
        } else {
            $response = [
                'status'    => 0,
                'message'   => 'Could not create a new robot.'
            ];
            $code = 400;
        }

        return new JsonResponse($response, $code);
    }

    /**
     * @Route("/{robot}", name="api_robots_delete")
     * @Method("DELETE")
     * @param Robot $robot
     * @return JsonResponse
     */
    public function deleteAction(Robot $robot) {
        $em = $this->getDoctrine()->getManager();

        $em->remove($robot);
        $em->flush();

        if (!$robot->getId()) {
            $response = [
                'status'    => 1,
                'message'   => 'Robot deleted.'
            ];
            $code = 200;
        } else {
            $response = [
                'status'    => 0,
                'message'   => 'Could not delete robot.'
            ];
            $code = 400;
        }

        return new JsonResponse($response, $code);
    }
}
