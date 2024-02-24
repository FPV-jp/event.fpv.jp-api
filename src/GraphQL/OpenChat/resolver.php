<?php declare(strict_types=1);

use FpvJp\Domain\OpenChat;

return [
    'openchat' => function ($rootValue, $args, $context) {
        $openchat = $this->em->getRepository(OpenChat::class)->find($args['id']);
        return $openchat->jsonSerialize();
    },
    'allOpenChats' => function ($rootValue, $args, $context) {
        $token = $context['token'];
        // error_log(print_r($token, true));

        $openchats = $this->em->getRepository(OpenChat::class)->findAll();
        $openchatArray = [];
        foreach ($openchats as $openchat) {
            $openchatArray[] = $openchat->jsonSerialize();
        }
        return $openchatArray;
    },
    'createOpenChat' => function ($rootValue, $args, $context) {
        // $newOpenChat = new OpenChat($args['email'], $args['password']);
        $newRandomOpenChat = new OpenChat($this->faker->email(), $this->faker->password());
        $this->em->persist($newRandomOpenChat);
        $this->em->flush();
        return $newRandomOpenChat->jsonSerialize();
    },
    'updateOpenChat' => function ($rootValue, $args, $context) {
        $openchat = $this->em->getRepository(OpenChat::class)->find($args['id']);
        $openchat->updateParameters($args);
        $this->em->flush();
        return $openchat->jsonSerialize();
    },
    'deleteOpenChat' => function ($rootValue, $args, $context) {
        $openchat = $this->em->getRepository(OpenChat::class)->find($args['id']);
        $this->em->remove($openchat);
        $this->em->flush();
        return $openchat->jsonSerialize();
    }
];