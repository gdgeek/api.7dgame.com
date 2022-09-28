<?php

namespace common\rbac\rules;

use yii\rbac\Rule;

/**
 * Description of UserRule
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */
class UserRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'user_rule';

    /**
     * @inheritdoc
     */
    public function execute($userId, $item, $params)
    {
      ////echo json_encode($item);//$user;
      //echo $item->description;
      return !\Yii::$app->user->isGuest;
    }
}
