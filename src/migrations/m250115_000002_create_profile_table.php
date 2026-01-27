<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Create user_profile table.
 */
class m250115_000002_create_profile_table extends Migration
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

        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255),
            'bio' => $this->text(),
            'location' => $this->string(255),
            'website' => $this->string(255),
            'timezone' => $this->string(40),
            'avatar_path' => $this->string(255),
            'gravatar_email' => $this->string(255),
            'use_gravatar' => $this->boolean()->defaultValue(true),
            'public_email' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addForeignKey(
            'fk_user_profile_user',
            '{{%user_profile}}',
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
        $this->dropForeignKey('fk_user_profile_user', '{{%user_profile}}');
        $this->dropTable('{{%user_profile}}');
    }
}
