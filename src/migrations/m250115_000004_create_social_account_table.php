<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Create user_social_account table (placeholder for v2.0 social login).
 */
class m250115_000004_create_social_account_table extends Migration
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

        $this->createTable('{{%user_social_account}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'provider' => $this->string(50)->notNull(),
            'provider_id' => $this->string(255)->notNull(),
            'data' => $this->json(),
            'email' => $this->string(255),
            'username' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_user_social_provider', '{{%user_social_account}}', ['provider', 'provider_id'], true);
        $this->createIndex('idx_user_social_user', '{{%user_social_account}}', 'user_id');

        $this->addForeignKey(
            'fk_user_social_account_user',
            '{{%user_social_account}}',
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
        $this->dropForeignKey('fk_user_social_account_user', '{{%user_social_account}}');
        $this->dropTable('{{%user_social_account}}');
    }
}
