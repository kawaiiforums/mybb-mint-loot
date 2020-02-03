<?php

namespace mint\modules\loot\Hooks;

use mint\AcpEntityManagementController;
use mint\DbRepository\ItemTypes;
use mint\modules\loot\DbRepository\LootItems;
use function mint\getIntegerCsv;

function mint_activate(): void
{
    global $db;

    if (!$db->field_exists('mint_last_loot', 'users')) {
        \mint\createColumns([
            'users' => [
                'mint_last_loot' => 'integer NOT NULL DEFAULT 0',
            ],
        ], false);
    }

    \mint\createTables([
        LootItems::class,
    ]);
}

function mint_deactivate(): void
{
    global $mybb;

    if ($mybb->get_input('uninstall') == 1) {
        \mint\dropTables([
            LootItems::class,
        ], true, true);

        \mint\dropColumns([
            'users' => [
                'mint_last_loot',
            ]
        ]);
    }
}

function mint_admin_config_mint_tabs(array &$tabs): void
{
    $tabs[] = 'loot_items';
}

function mint_admin_config_mint_begin(): void
{
    global $mybb, $db, $cache, $lang;

    if ($mybb->input['action'] == 'loot_items') {
        $itemTypes = \mint\queryResultAsArray(ItemTypes::with($db)->get(), 'id', 'title');

        $controller = new AcpEntityManagementController('loot_items', LootItems::class);

        $controller->setColumns([
            'item_type_id' => [
                'listed' => false,
                'formElement' => function (\Form $form, array $entity) use ($itemTypes) {
                    return $form->generate_select_box(
                        'item_type_id',
                        $itemTypes,
                        $entity['item_type_id'] ?? 0
                    );
                },
                'validator' => function (?string $value) use ($lang, $itemTypes): array {
                    $errors = [];

                    if (!array_key_exists($value, $itemTypes)) {
                        $errors['item_type_invalid'] = [];
                    }

                    return $errors;
                },
            ],
            'item_type' => [
                'customizable' => false,
                'dataColumn' => 'item_type_title',
            ],
            'probability' => [
                'formElement' => function (\Form $form, array $entity) {
                    return $form->generate_numeric_field(
                        'probability',
                        $entity['probability'] ?? 0.1,
                        [
                            'min' => 0,
                            'max' => 1,
                            'step' => 0.1,
                        ]
                    );
                },
            ],
            'usergroups' => [
                'presenter' => function (?string $value) use ($cache) {
                    $groupTitles = \mint\getArraySubset(
                        array_column($cache->read('usergroups'), 'title', 'gid'),
                        explode(',', $value)
                    );

                    return implode(', ', $groupTitles);
                },
                'encoder' => function ($value) {
                    return \mint\getIntegerCsv(is_array($value) ? $value : []);
                },
                'formElement' => function (\Form $form, array $entity) {
                    return $form->generate_group_select(
                        'usergroups[]',
                        explode(',', $entity['usergroups']),
                        [
                            'multiple' => true,
                        ]
                    );
                },
            ],
        ]);
        $controller->addForeignKeyData([
            'mint_item_types' => [
                'title',
            ],
        ]);
        $controller->addEntityOptions([
            'update' => [],
            'delete' => [],
        ]);

        $controller->run();
    }
}
