<?php declare(strict_types=1);

use FpvJp\Domain\User;

return [
    'user' => function ($rootValue, $args, $context) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        return $user->jsonSerialize();
    },
    'allUsers' => function ($rootValue, $args, $context) {
        $token = $context['token'];
        error_log(print_r($token, true));

        $users = $this->em->getRepository(User::class)->findAll();
        $userArray = [];
        foreach ($users as $user) {
            $userArray[] = $user->jsonSerialize();
        }
        return $userArray;
    },
    'createUser' => function ($rootValue, $args, $context) {
        // $newUser = new User($args['email'], $args['password']);
        $newRandomUser = new User($this->faker->email(), $this->faker->password());
        $this->em->persist($newRandomUser);
        $this->em->flush();
        return $newRandomUser->jsonSerialize();
    },
    'updateUser' => function ($rootValue, $args, $context) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        $user->updateParameters($args);
        $this->em->flush();
        return $user->jsonSerialize();
    },
    'deleteUser' => function ($rootValue, $args, $context) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        $this->em->remove($user);
        $this->em->flush();
        return $user->jsonSerialize();
    }
];