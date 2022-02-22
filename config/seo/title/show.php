<?php

return [
    [
        'property' => 'og:url',
        'content' => '{{url.title}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{title.name}} ({{title.year}}) - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{title.description}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
    [
        'property' => 'og:type',
        'content' => 'video.movie',
    ],
    [
        'property' => 'og:image',
        'content' => '{{title.poster}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '300',
    ],
    [
        'property' => 'og:image:height',
        'content' => '450',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            '@context' => 'http://schema.org',
            '@type' => 'Movie',
            '@id' => '{{url.title}}',
            'url' => '{{url.title}}',
            'name' => '{{title.name}}',
            'image' => '{{title.poster}}',
            'description' => '{{title.description}}',
            'genre' => [
                '_type' => 'loop',
                'dataSelector' => 'title.genres',
                'template' => '{{tag.name}}',
            ],
            'contentRating' => '{{title.certification}}',
            'actor' => [
                '_type' => 'loop',
                'dataSelector' => 'title.credits',
                'limit' => 10,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'cast',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{url.person}}',
                    'name' => '{{person.name}}',
                ],
            ],
            'director' => [
                '_type' => 'loop',
                'dataSelector' => 'title.credits',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'directing',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{url.person}}',
                    'name' => '{{person.name}}',
                ],
            ],
            'creator' => [
                '_type' => 'loop',
                'dataSelector' => 'title.credits',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'creators',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{url.person}}',
                    'name' => '{{person.name}}',
                ],
            ],
            'datePublished' => '{{title.release_date}}',
            'keywords' => [
                '_type' => 'loop',
                'dataSelector' => 'title.keywords',
                'template' => '{{tag.name}}',
            ],
            'aggregateRating' => [
                '_ifNotNull' => 'title.rating',
                '@type' => 'AggregateRating',
                'ratingCount' => '{{title.vote_count}}',
                'bestRating' => '10.0',
                'worstRating' => '1.0',
                'ratingValue' => '{{title.rating}}',
            ],
            'duration' => '{{title.runtime}}',
        ],
    ],
];
