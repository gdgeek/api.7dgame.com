<?php

namespace api\modules\v1\models;

class OrganizationQuery extends \yii\db\ActiveQuery
{
    public function ordered(): self
    {
        return $this->orderBy(['title' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
