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

    /**
     * @return ActiveQuery 定义一个关联到 Order 的记录（可以在其它数据库中，例如 elasticsearch 或者 sql）
   
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    public static function find()
    {
        return new CustomerQuery(get_called_class());
    }
    */
}
