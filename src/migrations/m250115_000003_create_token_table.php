<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Create user_token table.
 */
class m250115_000003_create_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_token}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'type' => "ENUM('confirmation', 'recovery', 'email_change') NOT NULL",
            'token' => $this->string(64)->notNull()->unique(),
            'data' => $this->json(),
            'expires_at' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_user_token_user_type', '{{%user_token}}', ['user_id', 'type']);
        $this->createIndex('idx_user_token_expires', '{{%user_token}}', 'expires_at');

        $this->addForeignKey(
            'fk_user_token_user',
            '{{%user_token}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk_user_token_user', '{{%user_token}}');
        $this->dropTable('{{%user_token}}');
    }
}
