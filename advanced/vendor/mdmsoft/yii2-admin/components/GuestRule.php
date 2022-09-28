<?php

namespace mdm\admin\components;

use yii\rbac\Rule;

/**
 * Description of GuestRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */
class GuestRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'guest_rule';

    /**
     * @inheritdoc
     */
    public function execute($userId, $item, $params)
    {
        return \Yii::$app->user->isGuest;
    }
}
