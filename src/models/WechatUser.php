<?php

namespace huijiewei\wechat\models;

use yii\db\ActiveRecord;

/**
 * Class WechatUser
 *
 * @property integer $id
 * @property string $appId
 * @property string $openId
 * @property string $unionId
 * @property string $nickname
 * @property string $avatar
 * @property string $details
 * @property string $accessToken
 * @property string $refreshToken
 * @property string $accessTokenExpiredAt
 * @property string $refreshTokenExpiredAt
 * @property string $createdAt
 * @property string $updatedAt
 *
 * @package huijiewei\wechat\models
 */
class WechatUser extends ActiveRecord
{
    /**
     * @param $appId
     * @param $openId
     *
     * @return WechatUser|null
     */
    public static function getWechatUserByOpenId($appId, $openId): ?WechatUser
    {
        /* @var $wechatUser WechatUser|null */
        $wechatUser = WechatUser::find()
            ->where(['appId' => $appId, 'openId' => $openId])
            ->one();

        return $wechatUser;
    }

    /**
     * @return bool
     */
    public function getAccessTokenIsExpired(): bool
    {
        return strtotime($this->accessTokenExpiredAt) <= strtotime('now');
    }

    /**
     * @return bool
     */
    public function getRefreshTokenIsExpired(): bool
    {
        return strtotime($this->refreshTokenExpiredAt) <= strtotime('now');
    }
}
