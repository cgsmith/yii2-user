<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates two-factor authentication table.
 */
class m250128_000003_create_two_factor_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user_two_factor}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull()->unique(),
            'secret' => $this->string(64)->notNull(),
            'enabled_at' => $this->dateTime()->null(),
            'backup_codes' => $this->json()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_user_two_factor_user',
            '{{%user_two_factor}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%user_two_factor}}');
    }
}
