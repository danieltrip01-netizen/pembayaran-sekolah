<?php
return [
    'public_path' => public_path(),
    // 'public_path' => '/home/esck4946/public_html',
    'show_warnings' => false,
    'orientation' => 'portrait',
    'options' => [
        'chroot' => base_path(),
        'isRemoteEnabled' => true,
        'isHtml5ParserEnabled' => true,
    ],
];