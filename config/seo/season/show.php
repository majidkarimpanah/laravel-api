<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.season}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{title.name}} ({{title.year}}) - Season {{title.season.number}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'List of episodes for {{title.name}}: Season {{title.season.number}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
];
