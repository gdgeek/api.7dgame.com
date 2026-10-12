<?php

namespace backend\models;

/** Server pagination remains authoritative; never slice a remote page twice. */
class IdentityAdminDataProvider extends \yii\data\BaseDataProvider
{
    public array $rows = [];
    public int $remoteTotal = 0;
    protected function prepareModels() { return $this->rows; }
    protected function prepareKeys($models) { return array_map(static fn($row) => $row->id, $models); }
    protected function prepareTotalCount() { return $this->remoteTotal; }
}
