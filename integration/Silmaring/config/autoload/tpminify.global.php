<?php
return array(
    'TpMinify' => array(
        'cachePath' => __DIR__ . '/../../data/cache/minify/',
        'errorLogger' => __DIR__ . '/../../data/log/tpminify.log',
        'serveOptions' => array(
            'minifiers' => array(
                    'text/css' => array(
                        'Minify_CSSmin',
                        'minify'
                    )
            ),
            'minApp' => array(
                'groups' => array(
                    'css' => array(
                        __DIR__ . '/../../public/css/bootstrap.min.css',
                        __DIR__ . '/../../public/style.css',
                        __DIR__ . '/../../public/extras.css',
                    ),
                    'js' => array(
                        __DIR__ . '/../../public/js/jquery.min.js',
                        __DIR__ . '/../../public/js/jquery.ui.min.js',
                        __DIR__ . '/../../public/js/bootstrap.min.js',
                        __DIR__ . '/../../public/js/plugins/datatables/jquery.dataTables.js',
                        __DIR__ . '/../../public/js/jquery.form.js',
                        __DIR__ . '/../../public/js/silmaring.js',
                        __DIR__ . '/../../public/js/silmaring.test.js',
                        __DIR__ . '/../../public/js/scripts.js',
                    )
                )
            )
        )
    )
);