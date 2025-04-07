<?php
namespace api\modules\v1;
class RefreshToken extends \yii\redis\ActiveRecord
{
    /**
     * @return array 此记录的属性列表
     */
    public function attributes()
    {
        return ['id', 'user_id', 'key'];
    }

}
