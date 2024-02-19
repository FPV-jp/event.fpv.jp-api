<?php declare(strict_types=1);

use FpvJp\Domain\FlightPoint;

return [
    'flightPoint' => function ($rootValue, $args, $context) {
        $flightPoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        return $flightPoint->jsonSerialize();
    },
    'allFlightPoints' => function ($rootValue, $args, $context) {
        $flightPoints = $this->em->getRepository(FlightPoint::class)->findAll();
        $flightPointArray = [];
        foreach ($flightPoints as $flightPoint) {
            $flightPointArray[] = $flightPoint->jsonSerialize();
        }
        return $flightPointArray;
    },
    'createFlightPoint' => function ($rootValue, $args, $context) {
        $newFlightPoint = new FlightPoint($args['createFlightPointInput'], $context['token']);
        $this->em->persist($newFlightPoint);
        $this->em->flush();
        return $newFlightPoint->jsonSerialize();
    },
    'updateFlightPoint' => function ($rootValue, $args, $context) {
        $flightPoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        $flightPoint->updateParameters($args);
        $this->em->flush();
        return $flightPoint->jsonSerialize();
    },
    'deleteFlightPoint' => function ($rootValue, $args, $context) {
        $flightPoint = $this->em->getRepository(FlightPoint::class)->find($args['id']);
        $this->em->remove($flightPoint);
        $this->em->flush();
        return $flightPoint->jsonSerialize();
    }
];