<?php

return [
    [
        'property' => 'og:url',
        'content' => '{{url.list_model}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{list.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{list.description}}',
    ],
    [
        'property' => 'keywords',
        'content' =>
            'movies, films, movie database, actors, actresses, directors, stars, synopsis, trailers, credits, cast',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            '@context' => 'http://schema.org',
            '@id' => '{{url.list_model}}',
            'url' => '{{url.list_model}}',

            '@type' => 'CreativeWork',
            'dateModified' => '{{list.updated_at}}',
            'name' => '{{list.name}}',
            'about' => [
                '@type' => 'ItemList',
                'itemListElement' => [
                    '_type' => 'loop',
                    'dataSelector' => 'ITEMS',
                    'limit' => 30,
                    'template' => [
                        '@type' => 'ListItem',
                        'position' => '{{title.pivot.order}}',
                        'url' => '{{url.media_item}}',
                    ],
                ],
            ],
        ],
    ],
];
