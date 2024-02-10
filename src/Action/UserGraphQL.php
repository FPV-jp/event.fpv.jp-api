<?php declare(strict_types=1);

use FpvJp\Domain\User;

return [
    'user' => function ($rootValue, $args) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        return $user->jsonSerialize();
    },
    'allUsers' => function () {
        $users = $this->em->getRepository(User::class)->findAll();
        $userArray = [];
        foreach ($users as $user) {
            $userArray[] = $user->jsonSerialize();
        }
        return $userArray;
    },
    'createUser' => function ($rootValue, $args) {
        // $newUser = new User($args['email'], $args['password']);
        $newRandomUser = new User($this->faker->email(), $this->faker->password());
        $this->em->persist($newRandomUser);
        $this->em->flush();
        return $newRandomUser->jsonSerialize();
    },
    'updateUser' => function ($rootValue, $args) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        $user->updateParameters($args);
        $this->em->flush();
        return $user->jsonSerialize();
    },
    'deleteUser' => function ($rootValue, $args) {
        $user = $this->em->getRepository(User::class)->find($args['id']);
        $this->em->remove($user);
        $this->em->flush();
        return $user->jsonSerialize();
    }
];