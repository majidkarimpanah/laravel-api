<?php

return [
    'roles' => [
        [
            'name' => 'users',
            'extends' => 'users',
            'default' => true,
            'permissions' => [
                'titles.view',
                'videos.view',
                'videos.rate',
                'videos.play',
                'people.view',
                'reviews.view',
                'reviews.create',
                'news.view',
                'lists.create',
                'lists.view',
                'plans.view',
                'comments.view',
                'comments.create',
            ],
        ],
        [
            'name' => 'guests',
            'guests' => true,
            'extends' => 'guests',
            'permissions' => [
                'titles.view',
                'videos.view',
                'videos.play',
                'people.view',
                'reviews.view',
                'news.view',
                'lists.view',
                'plans.view',
                'comments.view',
            ],
        ],
    ],
    'all' => [
        'titles' => [
            [
                'name' => 'titles.view',
                'description' =>
                    'Allow viewing movies, series and episodes on the site.',
            ],
            [
                'name' => 'titles.create',
                'description' =>
                    'Allow user to create new movies, series and episodes from admin area.',
                'advanced' => true,
            ],
            [
                'name' => 'titles.update',
                'description' => 'Allow user to update all titles on the site.',
                'advanced' => true,
            ],
            [
                'name' => 'titles.delete',
                'description' => 'Allow user to delete all titles on the site.',
                'advanced' => true,
            ],
        ],
        'comments' => [
            [
                'name' => 'comments.view',
                'description' => 'Allow viewing comments on the site.',
            ],
            [
                'name' => 'comments.create',
                'description' => 'Allow creating new comments.',
            ],
            [
                'name' => 'comments.update',
                'description' =>
                    'Allow editing of all comments, whether user created that comment or not. User can edit their own comments without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'comments.delete',
                'description' =>
                    'Allow deleting any comment, whether user created that comment or not. User can delete their own comments without this permission.',
                'advanced' => true,
            ],
        ],
        'reviews' => [
            [
                'name' => 'reviews.view',
                'description' =>
                    'Allow user to view reviews left by other users.',
            ],
            [
                'name' => 'reviews.create',
                'description' => 'Allow user to rate movies and series.',
            ],
            [
                'name' => 'reviews.update',
                'description' =>
                    'Allow editing of all reviews on the site, regardless of who created them. User can edit reviews they have created without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'reviews.delete',
                'description' =>
                    'Allow deletion of all reviews on the site, regardless of who created them. User can delete reviews they have created without this permission.',
                'advanced' => true,
            ],
        ],
        'people' => [
            [
                'name' => 'people.view',
                'description' => 'Allow viewing actor pages on the site.',
            ],
            [
                'name' => 'people.create',
                'description' =>
                    'Allow user to create new actors from admin area.',
                'advanced' => true,
            ],
            [
                'name' => 'people.update',
                'description' => 'Allow user to update all actors on the site.',
                'advanced' => true,
            ],
            [
                'name' => 'people.delete',
                'description' => 'Allow user to delete all actors on the site.',
                'advanced' => true,
            ],
        ],
        'news' => [
            [
                'name' => 'news.view',
                'description' =>
                    'Allow viewing of all news articles on the site, regardless of who created them. User can view articles they created without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'news.create',
                'description' => 'Allow users to create news articles.',
            ],
            [
                'name' => 'news.update',
                'description' =>
                    'Allow editing of all news articles on the site, regardless of who created them. User can edit articles they have created without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'news.delete',
                'description' =>
                    'Allow deleting of all news on the site, regardless of who created them. User can delete articles they have created without this permission.',
                'advanced' => true,
            ],
        ],
        'videos' => [
            [
                'name' => 'videos.rate',
                'description' => 'Allow user to rate videos on the site.',
            ],
            [
                'name' => 'videos.view',
                'description' =>
                    'Allow user to view videos on the site. This will only show video thumbnail and title, but not allow video playback.',
            ],
            [
                'name' => 'videos.play',
                'description' => 'Allow user to play videos on the site.',
            ],
            [
                'name' => 'videos.create',
                'description' =>
                    'Allow creating new videos from title/episode page or from admin area.',
            ],
            [
                'name' => 'videos.update',
                'description' =>
                    'Allow editing of all videos on the site, regardless of who created them. User can edit their own videos without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'videos.delete',
                'description' =>
                    'Allow deleting of all videos on the site, regardless of who created them. User can delete their own videos without this permission.',
                'advanced' => true,
            ],
        ],
        'lists' => [
            [
                'name' => 'lists.view',
                'description' =>
                    'Allow viewing of all lists on the site, regardless of who created them. User can view their own lists without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'lists.create',
                'description' => 'Allow users to create lists.',
            ],
            [
                'name' => 'lists.update',
                'description' =>
                    'Allow editing of all lists on the site, regardless of who created them. User can edit their own lists without this permission.',
                'advanced' => true,
            ],
            [
                'name' => 'lists.delete',
                'description' =>
                    'Allow deleting of all lists on the site, regardless of who created them. User can delete their own lists without this permission.',
                'advanced' => true,
            ],
        ],
    ],
];
