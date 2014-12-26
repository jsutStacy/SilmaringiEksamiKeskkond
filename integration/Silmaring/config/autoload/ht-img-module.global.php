<?php
return array(
    'htimg' => [
        'img_source_path_stack' => [
            'data/tmpuploads/profile/images/',
            'data/uploads/'
        ],
        'cache_path' => 'static',
        'enable_cache' => true,
        'web_root' => 'public',
        'filters' => [
            'profile' => [
                'type' => 'thumbnail',
                'options' => [
                    'width' => 50,
                    'height' => 50,
                    'mode' => 'inset'
                ],
            ],
            'images' => [
                'type' => 'thumbnail',
                'options' => [
                    'width' => 35,
                    'height' => 48,
                    'mode' => 'inset'
                ],
            ],
            'images1024x768' => [
                'type' => 'thumbnail',
                'options' => [
                    'width' => 1024,
                    'height' => 768,
                    'mode' => 'inset'
                ],
            ],
            'images233x164' => [
                'type' => 'thumbnail',
                'options' => [
                    'width' => 233,
                    'height' => 164,
                    'mode' => 'inset'
                ],
            ],
        ],
     ]
);