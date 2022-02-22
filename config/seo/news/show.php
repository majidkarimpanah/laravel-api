<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.article}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{article.title}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'The Movie Database ({{site_name}}) is a popular database for movies, TV shows and celebrities.',
    ],
    [
        'property' => 'keywords',
        'content' => 'movies, films, movie database, actors, actresses, directors, stars, synopsis, trailers, credits, cast',
    ],
    [
        'property' => 'og:type',
        'content' => 'video.movie',
    ],
    [
        'property' => 'og:image',
        'content' => '{{article.meta.image}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '270',
    ],
    [
        'property' => 'og:image:height',
        'content' => '400',
    ],
];
