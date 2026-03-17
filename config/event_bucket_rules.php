<?php
return [
    'group_order' => [
        'Academics',
        'Music Combined',
        'Music Instrumental',
        'Music Vocal',
        'Platform',
        'Scripture',
        'Sports',
    ],
    'name_rules' => [
        ['bucket' => 'Scripture', 'pattern' => '/scripture|bible memory|bible bowl|group bible speaking|bible speaking|bible/i'],
        ['bucket' => 'Platform', 'pattern' => '/preaching|oratory|platform|dramatic|declamation|persuasive|poetry|mime|one-act|monologue|narration|narrative|storytelling|speech|puppet|marionette|radio\s*reading|expressive\s*reading|extemp|clown|dialogue/i'],
        ['bucket' => 'Academics', 'pattern' => '/checkers|chess|spelling|math|mathematics|science|history|geography|grammar|essay|handwriting/i'],
        ['bucket' => 'Music Combined', 'pattern' => '/self\s+accompanied|combined\s+ensemble/i'],
        ['bucket' => 'Music Vocal', 'pattern' => '/\b(male|female|mixed)\s+(solo|duet|trio|quartet)\b|music\s+vocal|vocal\s+ensemble|choir|chorale|singing|song/i'],
        ['bucket' => 'Music Vocal', 'pattern' => '/vocal|choir|chorale|singing|song/i'],
        ['bucket' => 'Music Instrumental', 'pattern' => '/instrumental|instrum\.?|music\s*instr\.?|piano|woodwind|brass|string|guitar|percussion|drum|violin|cello|ensemble|tambourine/i'],
        ['bucket' => 'Music Instrumental', 'pattern' => '/^7/'],
        ['bucket' => 'Sports', 'pattern' => '/basketball|futsal|volleyball|table tennis|tennis|soccer|football|badminton|athletics/i'],
    ],
    'category_contains_rules' => [
        ['bucket' => 'Scripture', 'contains' => 'scripture'],
        ['bucket' => 'Platform', 'contains' => 'platform'],
        ['bucket' => 'Academics', 'contains' => 'academics'],
        ['bucket' => 'Sports', 'contains' => 'physical education'],
        ['bucket' => 'Sports', 'contains' => 'sports'],
        ['bucket' => 'Music Combined', 'contains' => 'music'],
    ],
    'default_bucket' => 'Academics',
];
