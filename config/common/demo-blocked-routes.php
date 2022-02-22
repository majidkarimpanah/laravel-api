<?php

return [
    // sitemap
    ['method' => 'POST', 'name' => 'admin/sitemap/generate'],

    // titles
    ['method' => 'POST', 'name' => 'titles'],
    ['method' => 'POST', 'name' => 'titles/credits'],
    ['method' => 'POST', 'name' => 'titles/credits/reorder'],
    ['method' => 'PUT', 'name' => 'titles/credits/{id}'],
    ['method' => 'DELETE', 'name' => 'titles/credits/{id}'],
    ['method' => 'PUT', 'name' => 'titles/{id}'],
    ['method' => 'DELETE', 'name' => 'titles'],

    // seasons
    ['method' => 'POST', 'name' =>' titles/{titleId}/seasons'],
    ['method' => 'DELETE', 'name' => 'seasons/{seasonId}'],

    // episodes
    ['method' => 'POST', 'name' => 'seasons/{seasonId}/episodes'],
    ['method' => 'PUT', 'name' => 'episodes/{id}'],
    ['method' => 'DELETE', 'name' => 'episodes/{id}'],

    // people
    ['method' => 'POST', 'name' => 'people'],
    ['method' => 'PUT', 'name' => 'people/{id}'],
    ['method' => 'DELETE', 'name' => 'people'],

    // images
    ['method' => 'DELETE', 'name' => 'images'],
    ['method' => 'POST', 'name' => 'images'],

    // reviews
    ['method' => 'DELETE', 'name' => 'reviews', 'origin' => 'admin'],
    ['method' => 'PUT', 'name' => 'reviews/{id}', 'origin' => 'admin'],

    // lists
    ['method' => 'DELETE', 'name' => 'lists'],

    // news
    ['method' => 'POST', 'name' => 'news'],
    ['method' => 'PUT', 'name' => 'news/{id}'],
    ['method' => 'DELETE', 'name' => 'news'],

    // videos
    ['method' => 'POST', 'name' => 'videos'],
    ['method' => 'PUT', 'name' => 'videos/{id}'],
    ['method' => 'DELETE', 'name' => 'videos'],
    ['method' => 'POST', 'name' => 'titles/{id}/videos/change-order'],
    ['method' => 'POST', 'name' => 'videos/{video}/approve'],
    ['method' => 'POST', 'name' => 'videos/{video}/disapprove'],

    // title tags
    ['method' => 'POST', 'name' => 'titles/{titleId}/tags'],
    ['method' => 'DELETE', 'name' => 'titles/{titleId}/tags/{type}/{tagId}'],

    // images
    ['method' => 'POST', 'name' => 'uploads/images', 'origin' => 'admin'],

    // import
    ['method' => 'POST', 'name' => 'media/import'],
    ['method' => 'GET', 'name' => 'tmdb/import'],
];