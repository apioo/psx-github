<?php

namespace PSX\Github;

use Doctrine\DBAL\Schema\Schema;

class TableSchema
{
    public static function addSchema(Schema $schema)
    {
        $table = $schema->createTable('apioo_license');
        $table->addColumn('li_id', 'integer', ['autoincrement' => true]);
        $table->addColumn('li_status', 'integer');
        $table->addColumn('li_license', 'string');
        $table->addColumn('li_name', 'string');
        $table->addColumn('li_avatar', 'string');
        $table->addColumn('li_url', 'string');
        $table->addColumn('li_price', 'integer');
        $table->addColumn('li_insert_date', 'datetime');
        $table->setPrimaryKey(['li_id']);
        $table->addUniqueIndex(['li_name']);
    }
}
