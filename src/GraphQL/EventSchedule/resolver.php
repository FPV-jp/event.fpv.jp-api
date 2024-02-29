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
        $newEventSchedule = new EventSchedule($args['createEventScheduleInput'], $context['token']);
        $this->em->persist($newEventSchedule);
        $this->em->flush();
        return $newEventSchedule->jsonSerialize();
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