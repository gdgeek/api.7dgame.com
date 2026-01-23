<?php

namespace api\modules\v1\models\data;

use api\modules\v1\models\Meta;
use api\modules\v1\models\Code;

use api\modules\v1\models\MetaCode;

use yii\base\Model;


class MetaCodeTool extends Model
{
    private $_meta;
    public $blockly;
    public $js;
    public $lua;


    public function getMeta()
    {
        return $this->_meta;
    }
    public function __construct($meta_id, array $config = [])
    {

        $this->_meta = Meta::find()->where(['id' => $meta_id])->one();

        if (! $this->_meta) {
            throw new \yii\web\NotFoundHttpException("Meta not found");
        }
        parent::__construct($config);
    }
    public function save()
    {
        $metaCode = $this->meta->metaCode;
        if ($metaCode === null) {
            $metaCode = new MetaCode();
            $metaCode->meta_id = $this->meta->id;
        }


        $metaCode->blockly = $this->blockly;
        $metaCode->lua = $this->lua;
        $metaCode->js = $this->js;

        if ($metaCode->validate()) {
            $metaCode->save();
        } else {
            throw new \yii\web\BadRequestHttpException("MetaCode validation failed: " . json_encode($metaCode->errors));
        }
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blockly'], 'string'],
            [['js', 'lua'], 'string'],
        ];
    }
}
