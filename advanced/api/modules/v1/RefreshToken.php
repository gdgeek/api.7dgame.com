<?php

namespace api\modules\v1;
class RefreshToken extends \yii\redis\ActiveRecord
{
    
    public function attributes()
    {
        return ['id', 'user_id', 'key'];
    }

}
