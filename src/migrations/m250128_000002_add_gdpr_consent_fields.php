<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds enhanced GDPR consent fields to user table.
 */
class m250128_000002_add_gdpr_consent_fields extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%user}}', 'gdpr_consent_version', $this->string(50)->null()->after('gdpr_consent_at'));
        $this->addColumn('{{%user}}', 'gdpr_marketing_consent_at', $this->dateTime()->null()->after('gdpr_consent_version'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%user}}', 'gdpr_marketing_consent_at');
        $this->dropColumn('{{%user}}', 'gdpr_consent_version');
    }
}
