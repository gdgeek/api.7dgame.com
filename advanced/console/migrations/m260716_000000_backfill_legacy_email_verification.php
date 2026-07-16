<?php

use yii\db\Migration;

/**
 * Marks email addresses from before the verification feature as verified.
 *
 * Before email_verified_at was introduced, an email was persisted only after
 * the user followed the binding link, completed signup mail delivery, or came
 * from an authenticated identity provider. The column migration left all of
 * those existing addresses NULL, which made password recovery unavailable to
 * every legacy account.
 */
class m260716_000000_backfill_legacy_email_verification extends Migration
{
    /** 2026-01-21 11:03:28 +08:00, when email_verified_at was introduced. */
    private const VERIFICATION_FEATURE_RELEASED_AT = 1768964608;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            '{{%user}}',
            ['email_verified_at' => self::VERIFICATION_FEATURE_RELEASED_AT],
            [
                'and',
                ['email_verified_at' => null],
                ['not', ['email' => null]],
                ['<>', 'email', ''],
                ['<', 'created_at', self::VERIFICATION_FEATURE_RELEASED_AT],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            '{{%user}}',
            ['email_verified_at' => null],
            [
                'and',
                ['email_verified_at' => self::VERIFICATION_FEATURE_RELEASED_AT],
                ['<', 'created_at', self::VERIFICATION_FEATURE_RELEASED_AT],
            ]
        );
    }
}
