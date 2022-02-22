<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{url.episode}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{episode.title.name}} ({{episode.title.year}}) - {{episode.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{episode.description}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            '@context' => 'http://schema.org',
            '@type' => 'TVEpisode',
            '@id' => '{{url.episode}}',
            'url' => '{{url.episode}}',
            'name' => '{{episode.name}}',
            'image' => '{{episode.poster}}',
            'timeRequired' => '{{episode.title.runtime}}',
            'contentRating' => 'TV-PG',
            'description' => '{{episode.description}}',
            'datePublished' => '{{episode.release_date}}',
            "keywords" => [
                '_type' => 'loop',
                'dataSelector' => 'episode.title.keywords',
                'template' => '{{tag.name}}'
            ],
            'genre' => [
                '_type' => 'loop',
                'dataSelector' => 'episode.title.genres',
                'template' => '{{tag.name}}'
            ],
            'actor' => [
                '_type' => 'loop',
                'dataSelector' => 'episode.credits',
                'limit' => 10,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'cast',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{url.person}}',
                    'name' => '{{person.name}'
                ],
            ],
            'director' => [
                '_type' => 'loop',
                'dataSelector' => 'episode.credits',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'directing',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{url.person}}',
                    'name' => '{{person.name}'
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
                    'name' => '{{person.name}'
                ],
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingCount' => '{{episode.vote_count}}',
                'bestRating' => '10.0',
                'worstRating' => '1.0',
                'ratingValue' => '{{episode.rating}}'
            ],
        ]
    ]
];
