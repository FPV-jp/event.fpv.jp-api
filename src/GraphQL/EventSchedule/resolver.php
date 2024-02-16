<?php declare(strict_types=1);

use FpvJp\Domain\EventSchedule;

return [
    'eventschedule' => function ($rootValue, $args, $context) {
        $eventschedule = $this->em->getRepository(EventSchedule::class)->find($args['id']);
        return $eventschedule->jsonSerialize();
    },
    'allEventSchedules' => function ($rootValue, $args, $context) {
        // $token = $context['token'];
        // error_log(print_r($token, true));

        $eventschedules = $this->em->getRepository(EventSchedule::class)->findAll();
        $eventscheduleArray = [];
        foreach ($eventschedules as $eventschedule) {
            $eventscheduleArray[] = $eventschedule->jsonSerialize();
        }
        return $eventscheduleArray;
    },
    'createEventSchedule' => function ($rootValue, $args, $context) {
        // $newEventSchedule = new EventSchedule($args['email'], $args['password']);
        $newRandomEventSchedule = new EventSchedule($this->faker->email(), $this->faker->password());
        $this->em->persist($newRandomEventSchedule);
        $this->em->flush();
        return $newRandomEventSchedule->jsonSerialize();
    },
    'updateEventSchedule' => function ($rootValue, $args, $context) {
        $eventschedule = $this->em->getRepository(EventSchedule::class)->find($args['id']);
        $eventschedule->updateParameters($args);
        $this->em->flush();
        return $eventschedule->jsonSerialize();
    },
    'deleteEventSchedule' => function ($rootValue, $args, $context) {
        $eventschedule = $this->em->getRepository(EventSchedule::class)->find($args['id']);
        $this->em->remove($eventschedule);
        $this->em->flush();
        return $eventschedule->jsonSerialize();
    }
];