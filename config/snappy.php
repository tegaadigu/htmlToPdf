<?php

return [
    'pdf'   => [
        'enabled' => true,
        'binary'  => 'xvfb-run /usr/local/bin/wkhtmltopdf --load-error-handling ignore',
        'timeout' => false,
        'options' => [
            'orientation'   => 'portrait',
            'no-background' => false,
            'lowquality'    => false,
            'no-outline'    => true,
            'outline'       => false,
            'page-width'    => 500,
            'zoom'          => 3,
        ],
        'env'     => [],
    ],
    'image' => [
        'enabled' => false,
        'binary'  => '/usr/local/bin/wkhtmltoimage',
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],


];
