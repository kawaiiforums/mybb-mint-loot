<?php

namespace mint\modules\loot\Hooks;

function mint_economy_hub_items_service_links(array &$links): void
{
    $links['loot'] = [
        'url' => 'misc.php?action=economy_items_loot',
    ];
}

function mint_misc_pages(array &$pages): void
{
    global $mybb;

    $pages['economy_items_loot'] = [
        'parents' => [
            'economy_hub',
        ],
        'permission' => function () use ($mybb): bool {
            return $mybb->user['uid'] != 0;
        },
        'controller' => function (array $globals) {
            extract($globals);

            $pageTitle = $lang->mint_page_economy_items_loot;

            $messages = null;
            $content = null;

            $cooldownEndDate = (
                \mint\modules\loot\getUserLastLootDate($mybb->user['uid']) +
                (int)\mint\getSettingValue('loot_interval')
            );

            if ($cooldownEndDate <= \TIME_NOW) {
                \mint\modules\loot\setUserLastLootDate($mybb->user['uid'], \TIME_NOW);

                $userGroupIds = array_map('intval', explode(',', $mybb->user['additionalgroups']));
                $userGroupIds[] = $mybb->user['usergroup'];

                $itemTypeIds = \mint\modules\loot\getLootItemIdsByUsergroupIds($userGroupIds);

                $result = true;

                foreach ($itemTypeIds as $itemTypeId) {
                    $result &= \mint\createItemsWithTerminationPoint(
                        $itemTypeId,
                        1,
                        $mybb->user['uid'],
                        'loot'
                    );
                }

                $messages .= \mint\getRenderedMessage(
                    $lang->sprintf(
                        $lang->mint_loot_success,
                        count($itemTypeIds)
                    ),
                    'success'
                );

                $content .= \mint\getRenderedInventory(
                    \mint\getUserItemsDetailsByItemTypeAndResolvedAmount(array_count_values($itemTypeIds), $mybb->user['uid'])
                );

                if (!$result) {
                    $content .= \mint\getRenderedMessage($lang->mint_loot_errors_encountered);
                }
            } else {
                $messages .= \mint\getRenderedMessage(
                    $lang->sprintf(
                        $lang->mint_loot_cooldown,
                        \my_date($mybb->settings['dateformat'], $cooldownEndDate),
                        \my_date($mybb->settings['timeformat'], $cooldownEndDate)
                    ),
                    'note'
                );
            }

            $content = $messages . $content;

            eval('$page = "' . \mint\tpl('page') . '";');

            return $page;
        }
    ];
}
