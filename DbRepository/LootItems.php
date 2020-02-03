<?php

namespace mint\modules\loot\DbRepository;

class LootItems extends \mint\DbEntityRepository
{
    public const TABLE_NAME = 'mint_loot_items';
    public const COLUMNS = [
        'id' => [
            'type' => 'integer',
            'primaryKey' => true,
        ],
        'item_type_id' => [
            'type' => 'integer',
            'foreignKeys' => [
                [
                    'table' => 'mint_item_types',
                    'column' => 'id',
                    'onDelete' => 'cascade',
                ],
            ],
        ],
        'probability' => [
            'type' => 'numeric',
            'precision' => 3,
            'scale' => 2,
            'notNull' => true,
        ],
        'usergroups' => [
            'type' => 'text',
        ],
    ];
}
