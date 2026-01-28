<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates session tracking table.
 */
class m250128_000001_create_session_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user_session}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'session_id' => $this->string(128)->notNull()->unique(),
            'ip' => $this->string(45)->null(),
            'user_agent' => $this->text()->null(),
            'device_name' => $this->string(255)->null(),
            'last_activity_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_user_session_user',
            '{{%user_session}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_user_session_user_id', '{{%user_session}}', 'user_id');
        $this->createIndex('idx_user_session_last_activity', '{{%user_session}}', 'last_activity_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%user_session}}');
    }
}
