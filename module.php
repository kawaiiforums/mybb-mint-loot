<?php

namespace mint\modules\loot;

// core files
require_once __DIR__ . '/data.php';

// hook files
require_once __DIR__ . '/hooks_acp.php';
require_once __DIR__ . '/hooks_frontend.php';

// hooks
\mint\addHooksNamespace('mint\modules\loot\Hooks');

// init
\mint\loadModuleLanguageFile('loot', 'loot');

\mint\registerSettings([
    'loot_interval' => [
        'title' => 'Loot: Interval (seconds)',
        'description' => 'Choose the amount of time that has to elapse between consecutive loots. Set 0 to disable the requirement.',
        'optionscode' => 'numeric
min=0',
        'value' => '86400',
    ],
    'loot_min_items' => [
        'title' => 'Loot: Minimum Items to Create',
        'description' => 'Choose how many items should be created at the least.',
        'optionscode' => 'numeric
min=0',
        'value' => '1',
    ],
    'loot_max_items' => [
        'title' => 'Loot: Maximum Items to Create',
        'description' => 'Choose how many items should be created at the most.',
        'optionscode' => 'numeric
min=0',
        'value' => '1',
    ],
]);

\mint\registerItemTerminationPoints([
    'loot',
]);
