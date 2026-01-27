<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Create user table.
 */
class m250115_000001_create_user_table extends Migration
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

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'email' => $this->string(255)->notNull()->unique(),
            'username' => $this->string(255)->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'status' => "ENUM('pending', 'active', 'blocked') NOT NULL DEFAULT 'pending'",
            'email_confirmed_at' => $this->dateTime(),
            'blocked_at' => $this->dateTime(),
            'last_login_at' => $this->dateTime(),
            'last_login_ip' => $this->string(45),
            'registration_ip' => $this->string(45),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'gdpr_consent_at' => $this->dateTime(),
            'gdpr_deleted_at' => $this->dateTime(),
        ], $tableOptions);

        $this->createIndex('idx_user_status', '{{%user}}', 'status');
        $this->createIndex('idx_user_email_confirmed', '{{%user}}', 'email_confirmed_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}
