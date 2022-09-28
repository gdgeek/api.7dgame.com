<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class InputForm extends Model
{
    public $text;
   


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            ['text', 'required'],
            [['text'], 'string', 'max' => 140],
        ];
    }

    
}
