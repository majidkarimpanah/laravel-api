<?php

return [
    // branding
    ['name' => 'branding.site_description', 'value' => "MTDb, the world's most popular and authoritative source for movie, TV and celebrity content."],

    // billing
    ['name' => 'billing.enable', 'value' => true],

    // menus
    ['name' => 'menus', 'value' => json_encode([
        ['name' => 'Primary', 'position' => 'primary', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Movies', 'action' => 'browse?type=movie'],
            ['type' => 'route', 'order' => 2, 'label' => 'Series', 'action' => 'browse?type=series'],
            ['type' => 'route', 'order' => 3, 'label' => 'People', 'action' => 'people'],
            ['type' => 'route', 'order' => 4, 'label' => 'News', 'action' => 'news']
        ]],

        ['name' => 'Explore', 'position' => 'footer-1', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Top Movies', 'action' => 'lists/1'],
            ['type' => 'route', 'order' => 2, 'label' => 'Top Shows', 'action' => 'lists/2'],
            ['type' => 'route', 'order' => 3, 'label' => 'Coming Soon', 'action' => 'lists/3'],
            ['type' => 'route', 'order' => 4, 'label' => 'Now Playing', 'action' => 'lists/4'],
            ['type' => 'route', 'order' => 3, 'label' => 'People', 'action' => 'people'],
        ]],

        ['name' => 'Genres', 'position' => 'footer-2', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Action', 'action' => 'browse?genre=action'],
            ['type' => 'route', 'order' => 2, 'label' => 'Comedy', 'action' => 'browse?genre=comedy'],
            ['type' => 'route', 'order' => 2, 'label' => 'Drama', 'action' => 'browse?genre=drama'],
            ['type' => 'route', 'order' => 2, 'label' => 'Crime', 'action' => 'browse?genre=crime'],
            ['type' => 'route', 'order' => 2, 'label' => 'Adventure', 'action' => 'browse?genre=adventure'],
        ]],

        ['name' => 'Pages', 'position' => 'footer-3', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Contact', 'action' => 'contact'],
            ['type' => 'page', 'order' => 2, 'label' => 'Privacy Policy', 'action' => '1/privacy-policy'],
            ['type' => 'page', 'order' => 2, 'label' => 'Terms of Use', 'action' => '2/terms-of-use'],
            ['type' => 'page', 'order' => 2, 'label' => 'About Us', 'action' => '3/about-us'],
        ]],
    ])],

    // uploads
    ['name' => 'uploads.max_size', 'value' => 52428800],
    ['name' => 'uploads.available_space', 'value' => 104857600],
    ['name' => 'uploads.blocked_extensions', 'value' => json_encode(['exe', 'application/x-msdownload', 'x-dosexec'])],

    // content
    ['name' => 'news.auto_update', 'value' => false],
    ['name' => 'tmdb.language', 'value' => 'en'],
    ['name' => 'tmdb.includeAdult', 'value' => false],
    ['name' => 'titles.video_panel_mode', 'value' => 'carousel'],
    ['name' => 'streaming.video_panel_content', 'value' => 'all'],
    ['name' => 'streaming.related_videos_type', 'value' => 'other_titles'],
    ['name' => 'player.show_next_episodes', 'value' => false],
    ['name' => 'titles.enable_reviews', 'value' => true],
    ['name' => 'titles.enable_comments', 'value' => true],
    ['name' => 'homepage.list_items_count', 'value' => 10],
    ['name' => 'homepage.slider_items_count', 'value' => 5],
    ['name' => 'homepage.autoslide', 'value' => true],
    ['name' => 'streaming.default_sort', 'value' => 'order:asc'],
    ['name' => 'streaming.show_captions_panel', 'value' => false],
    ['name' => 'streaming.show_category_select', 'value' => false],
    ['name' => 'streaming.streaming.auto_approve', 'value' => true],
    ['name' => 'streaming.streaming.show_header_play', 'value' => false],
    ['name' => 'content.people_index_min_popularity', 'value' => 0],
    ['name' => 'content.search_provider', 'value' => 'local'],
    ['name' => 'content.title_provider', 'value' => 'local'],
    ['name' => 'content.people_provider', 'value' => 'local'],
    ['name' => 'content.list_provider', 'value' => 'local'],
    ['name' => 'browse.genres', 'value' => json_encode([
        'drama', 'action', 'thriller', 'comedy',
        'science fiction', 'horror', 'mystery', 'romance',
        ])
    ],
    ['name' => 'browse.ageRatings', 'value' => json_encode(
        [
            'g', 'pg', 'pg-13', 'r', 'nc-17'
        ]
    )],
    ['name' => 'browse.year_slider_min', 'value' => 1880],
    ['name' => 'browse.year_slider_max', 'value' => 2023],
    ['name' => 'streaming.qualities', 'value' => json_encode(
        [
            'regular', 'SD', 'HD', '720p', '1080p', '4k'
        ]
    )],

    // HOMEPAGE APPEARANCE
    ['name' => 'landing.appearance', 'value' => json_encode([
        'headerTitle' => 'Watch on Any Device',
        'headerSubtitle' => 'Stream on your phone, tablet, laptop, PC and TV without paying more. First month is free!',
        'headerImage' => 'client/assets/images/landing.jpg',
        'headerOverlayColor' => 'rgba(0,0,0,0.7)',
        'actions' => [
            'cta1' => 'Join Now',
        ],
        'primaryFeatures' => [
            [
                'title' => 'High Quality',
                'subtitle' => 'Never run out of things to watch. Hundreds of movies and TV series available in HD.',
                'image' => 'hd',
            ],
            [
                'title' => 'Multiple User',
                'subtitle' => 'No need for multiple accounts. Friends and family can share the same account.',
                'image' => 'people',
            ],
            [
                'title' => 'Discover',
                'subtitle' => 'Find new things to watch based on your preferences and other user ratings.',
                'image' => 'rate-review',
            ]
        ],
        'secondaryFeatures' => [
            [
                'title' => 'Watch Anytime, Anywhere. From Any Device.',
                'subtitle' => 'COMPLETE FREEDOM',
                'description' => 'Watch TV Shows And Movies on Smart TVs, Consoles, Chromecast, Apple TV, Phone, Tablet or Browser.',
                'image' => 'client/assets/images/landing/endgame.jpg',
            ],
            [
                'title' => 'Cancel Online Anytime.',
                'subtitle' => 'No COMMITMENTS',
                'description' => 'If you decide MTDb isn\'t for you - no problem. No commitment. Cancel online at anytime.',
                'image' => 'client/assets/images/landing/wick.jpg',
            ]
        ]
    ])],
];
