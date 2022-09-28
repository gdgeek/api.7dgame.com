<?php

namespace api\modules\v1\models;

use linslin\yii2\curl;

/**
 * This is the model class for table "token".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $token
 * @property string|null $updated_at
 * @property string|null $overdue_at
 */
class Token extends \yii\db\ActiveRecord
{
    public static function jsapiToken()
    {

        $token = Token::findOne(['name' => 'jsapi_ticket']);
        if ($token == null) {
            $token = new Token();
            $token->name = 'jsapi_ticket';

        } else {
            if ((strtotime($token->overdue_at) - time()) > 600) {
                return $token;
            }
        }
        $accessToken = Token::accessToken();
        $time = time();
        $token->updated_at = date('Y-m-d H:i:s', $time);

        $curl = new curl\Curl();

        $response = $curl->get('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken->token . '&type=jsapi', false);

        if (!isset($response['expires_in']) || !isset($response['ticket'])) {

            throw new \Exception(json_encode($response));
        }

        $token->overdue_at = date('Y-m-d H:i:s', $time + $response['expires_in']);
        $token->token = $response['ticket'];
        $token->save();
        return $token;

    }
    public static function accessToken()
    {

        $token = Token::findOne(['name' => 'access_token']);
        if ($token == null) {
            $token = new Token();
            $token->name = 'access_token';

        } else {
            if ((strtotime($token->overdue_at) - time()) > 600) {
                return $token;
            }
        }
        $wechat = \Yii::$app->wechat;
        $time = time();
        $token->updated_at = date('Y-m-d H:i:s', $time);

        $curl = new curl\Curl();
        $response = $curl->setGetParams([
            'appid' => $wechat->app_id,
            'secret' => $wechat->secret,
            'grant_type' => 'client_credential',
        ])->get('https://api.weixin.qq.com/cgi-bin/token', false);

        if (!isset($response['expires_in']) || !isset($response['access_token'])) {

            throw new \Exception(json_encode($response));
        }
        $token->overdue_at = date('Y-m-d H:i:s', $time + $response['expires_in']);
        $token->token = $response['access_token'];
        $token->save();

        return $token;

    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token'], 'string'],
            [['updated_at', 'overdue_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'token' => 'Token',
            'updated_at' => 'Updated At',
            'overdue_at' => 'Overdue At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TokenQuery(get_called_class());
    }
}
