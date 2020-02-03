<?php

namespace mint\modules\loot;

use \mint\modules\loot\DbRepository\LootItems;

function getUserLastLootDate(int $userId): ?int
{
    global $mybb, $db;

    if ($userId === $mybb->user['uid']) {
        return (int)$mybb->user['mint_last_loot'];
    } else {
        $query = $db->simple_select('users', 'mint_last_loot', 'uid = ' . (int)$userId);

        if ($db->num_rows($query) == 1) {
            return (int)$db->fetch_field($query, 'mint_last_loot');
        } else {
            return null;
        }
    }
}

function setUserLastLootDate(int $userId, int $date): bool
{
    global $mybb, $db;

    $result = $db->update_query('users', [
        'mint_last_loot' => $date,
    ], 'uid = ' . (int)$userId);

    if ($userId === $mybb->user['uid']) {
        $mybb->user['mint_last_loot'] = $date;
    }

    return (bool)$result;
}

function getLootItemsByUsergroupIds(array $usergroupIds): array
{
    global $db;

    $lootItems = \mint\queryResultAsArray(
        LootItems::with($db)->get()
    );

    $applicableLootItems = array_filter($lootItems, function (array $entry) use ($usergroupIds) {
        return !empty(
            array_intersect(
                $usergroupIds,
                explode(',', $entry['usergroups'])
            )
        );
    });

    return $applicableLootItems;
}

function getLootItemIdsByUsergroupIds(array $usergroupIds): array
{
    $itemTypeIds = [];

    $minItems = abs(\mint\getSettingValue('loot_min_items'));
    $maxItems = abs(\mint\getSettingValue('loot_max_items'));

    $applicableLootItems = \mint\modules\loot\getLootItemsByUsergroupIds($usergroupIds);

    $probabilitiesSum = array_sum(
        array_column($applicableLootItems, 'probability')
    );

    for ($i = 1; $i <= $maxItems; $i++) {
        if ($probabilitiesSum < 1 && $i <= $minItems) {
            $randomMax = $probabilitiesSum * 100;
        } else {
            $randomMax = 100;
        }

        $randomNumber = \my_rand(0, $randomMax) / 100;

        $progressiveSum = 0;

        foreach ($applicableLootItems as $entry) {
            $progressiveSum += $entry['probability'];

            if ($randomNumber <= $progressiveSum) {
                $itemTypeIds[] = $entry['item_type_id'];
                break;
            }
        }
    }

    return $itemTypeIds;
}
