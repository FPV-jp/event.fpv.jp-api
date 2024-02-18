<?php declare(strict_types=1);

use FpvJp\Domain\FlightPoint;

return [
    'flightpoint' => function ($rootValue, $args, $context) {
        $flightpoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        return $flightpoint->jsonSerialize();
    },
    'allFlightPoints' => function ($rootValue, $args, $context) {
        $token = $context['token'];
        error_log(print_r($token, true));

        $flightpoints = $this->em->getRepository(FlightPoint::class)->findAll();
        $flightpointArray = [];
        foreach ($flightpoints as $flightpoint) {
            $flightpointArray[] = $flightpoint->jsonSerialize();
        }
        return $flightpointArray;
    },
    'createFlightPoint' => function ($rootValue, $args, $context) {
        $flightPoint = $args['flightPoint'];
        $newFlightPoint = new FlightPoint(
            $flightPoint['latitude'],
            $flightPoint['longitude'],
            $flightPoint['title'],
            $flightPoint['create_user'],
            $flightPoint['marker_image']
        );
        $this->em->persist($newFlightPoint);
        $this->em->flush();
        return $newFlightPoint->jsonSerialize();
    },
    'updateFlightPoint' => function ($rootValue, $args, $context) {
        $flightpoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        $flightpoint->updateParameters($args);
        $this->em->flush();
        return $flightpoint->jsonSerialize();
    },
    'deleteFlightPoint' => function ($rootValue, $args, $context) {
        $flightpoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        $this->em->remove($flightpoint);
        $this->em->flush();
        return $flightpoint->jsonSerialize();
    }
];