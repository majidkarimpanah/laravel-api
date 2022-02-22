<?php

return [
    [
        'property' => 'og:url',
        'content' => '{{url.person}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{person.name}} - {{site_name}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{person.description}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'biography, facts, photos, credits',
    ],
    [
        'property' => 'og:type',
        'content' => 'profile',
    ],
    [
        'property' => 'og:image',
        'content' => '{{person.poster}}',
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
            '@type' => 'Person',
            '@id' => '{{url.person}}',
            'url' => '{{url.person}}',
            'name' => '{{person.name}}',
            'image' => '{{person.poster}}',
            'description' => '{{person.description}}',
            'jobTitle' => ['{{person.known_for}}'],
        ],
    ],
];
