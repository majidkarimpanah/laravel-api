<?php

use App\Title;

return [
    Title::class => [
        'stopWords' => ['the', 'a', 'an'],
        'rankingRules' => [
            'desc(popularity)',
            'typo',
            'words',
            'proximity',
            'attribute',
            'wordsPosition',
            'exactness',

            //            'proximity',
            //            'words',
            //            'attribute',
            //            'desc(popularity)',
            //            'typo',
            //            'exactness',
            //            'wordsPosition',
            //            'desc(release_date)',
        ],
    ],
];
