<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.search}}',
    ],
    [
        'property' => 'og:title',
        'content' => 'Results for "{{query}}" - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'Search results for "{{query}}"',
    ],
    [
        'property' => 'og:type',
        'content' => 'website',
    ],
];
