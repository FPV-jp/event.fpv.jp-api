<?php declare(strict_types=1);

return [
    'assets' => function ($rootValue, $args, $context) {
        // $assets = $this->cloudinary->assets()->getArrayCopy();
        // error_log(print_r($assets['next_cursor'], true));
        // error_log(print_r($assets['resources'], true));
        return $this->cloudinary->assets()->getArrayCopy();
    },
];