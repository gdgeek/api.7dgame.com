<?php

namespace common\models;

/** Read-only row for the existing Yii invitation GridView, with no AR access. */
class IdentityInvitationView extends \yii\base\Model
{
    public $id;
    public $code;
    public $sender_id;
    public $recipient_id;
    public $recipient_username;
    public $used;
    public $auth_item_name;
    public $create_at;

    public function getRecipient()
    {
        return new class($this->recipient_username) {
            public function __construct(private $username) {}
            public function one() { return $this->username === null ? null : (object)['username' => $this->username]; }
        };
    }
}
