<?php

use yii\db\Migration;

/**
 *
 * php yii migrate/up --migrationPath=@vendors/mrssoft/yii2-abtest/migrations
 */
class m160126_082547_abtest extends Migration
{
    public function up()
    {
        $this->createTable('{{%abtest}}', [
            'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => 'VARCHAR(32) NOT NULL',
            'title' => 'VARCHAR(256) NOT NULL',
            'variants' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 2',
            'current' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
            'date' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'public' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'PRIMARY KEY (`id`)',
            'UNIQUE INDEX `name` (`name`)'
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%abtest}}');
    }
}
