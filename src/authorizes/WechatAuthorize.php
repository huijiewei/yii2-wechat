<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 14:46
 */

namespace huijiewei\wechat\authorizes;

use EasyWeChat\OfficialAccount\Application;
use huijiewei\wechat\exceptions\AuthorizeFailedException;
use huijiewei\wechat\models\WechatUser;
use Overtrue\Socialite\AccessTokenInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\helpers\Url;

class WechatAuthorize extends Component
{
    const SNSAPI_BASE = 'snsapi_base';
    const SNSAPI_USERINFO = 'snsapi_userinfo';

    /* @var $wechat Application */
    public $wechat;
    public $sessionKey = '';

    private $_appId = false;
    private $_wechatUser = null;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->wechat == null) {
            throw new InvalidConfigException('请设置 wechat 属性');
        }

        $this->sessionKey = \Yii::$app->id . '_WX_' . $this->getAppId() . '_ID';
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        if ($this->_appId === false) {
            $this->_appId = $this->wechat->config->get('app_id');
        }

        return $this->_appId;
    }

    /**
     * @return null|WechatUser
     */
    public function getWechatUser()
    {
        return $this->_wechatUser;
    }

    /**
     * @return bool|\yii\web\Response
     * @throws AuthorizeFailedException
     */
    public function authorizeRequired()
    {
        $code = \Yii::$app->getRequest()->get('code', '');
        $scope = \Yii::$app->getRequest()->get('scope', '');

        if (!empty($scope) && !empty($code)) {
            return $this->authorizeProcess($code, $scope);
        }

        $wechatOpenId = \Yii::$app->getSession()->get($this->sessionKey, '');

        if (empty($wechatOpenId)) {
            return $this->redirectToBaseScope();
        }

        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $wechatOpenId);

        if ($wechatUser == null || $wechatUser->getRefreshTokenIsExpired()) {
            \Yii::$app->getSession()->remove($this->sessionKey);

            return $this->redirectToUserInfoScope();
        }

        $this->_wechatUser = $wechatUser;

        return true;
    }

    /**
     * @param $code
     * @param $scope
     *
     * @return \yii\web\Response
     * @throws AuthorizeFailedException
     */
    private function authorizeProcess($code, $scope)
    {
        try {
            $accessToken = $this->wechat->oauth->getAccessToken($code);
        } catch (\Exception $ex) {
            throw new AuthorizeFailedException('微信授权失败，请重新打开页面。');
        }

        if ($scope != $accessToken['scope']) {
            return $this->redirectToUserInfoScope();
        }

        if ($scope == static::SNSAPI_BASE) {
            return $this->processBase($accessToken);
        } else {
            return $this->processUserInfo($accessToken);
        }
    }

    /**
     * @return \yii\web\Response
     */
    private function redirectToUserInfoScope()
    {
        return $this->redirectToScope(static::SNSAPI_USERINFO);
    }

    /**
     * @param $scope
     *
     * @return \yii\web\Response
     */
    private function redirectToScope($scope)
    {
        return \Yii::$app->getResponse()->redirect($this->wechat->oauth->scopes([$scope])->redirect($this->getReturnUrl($scope))->getTargetUrl());
    }

    /**
     * @param null|string $scope
     *
     * @return string
     */
    private function getReturnUrl($scope = null)
    {
        return Url::current(['scope' => $scope, 'code' => null, 'state' => null], true);
    }

    /**
     * @param $accessToken AccessTokenInterface
     *
     * @return \yii\web\Response
     */
    private function processBase($accessToken)
    {
        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $accessToken['openid']);

        if ($wechatUser == null || $wechatUser->getRefreshTokenIsExpired()) {
            return $this->redirectToUserInfoScope();
        }

        \Yii::$app->getSession()->set($this->sessionKey, $accessToken['openid']);

        return $this->redirectToReturnUrl();
    }

    /**
     * @return \yii\web\Response
     */
    private function redirectToReturnUrl()
    {
        return \Yii::$app->getResponse()->redirect($this->getReturnUrl());
    }

    /**
     * @param $accessToken AccessTokenInterface
     *
     * @return \yii\web\Response
     * @throws AuthorizeFailedException
     */
    private function processUserInfo($accessToken)
    {
        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $accessToken['openid']);

        if ($wechatUser == null) {
            $wechatUser = new WechatUser();
            $wechatUser->appId = $this->getAppId();
            $wechatUser->openId = $accessToken['openid'];
        }

        $wechatUser->accessToken = $accessToken['access_token'];
        $wechatUser->refreshToken = $accessToken['refresh_token'];
        $wechatUser->accessTokenExpiredAt = \Yii::$app->getFormatter()->asDatetime('+' . ($accessToken['expires_in'] - 200) . ' seconds');
        $wechatUser->refreshTokenExpiredAt = \Yii::$app->getFormatter()->asDatetime('+20 days');

        $wechatUser->save(false);

        try {
            $wechatUserInfo = $this->wechat->oauth->user($accessToken);
        } catch (\Exception $ex) {
            throw new AuthorizeFailedException('获取用户资料失败，请重新打开页面。');
        }

        \Yii::$app->getSession()->set($this->sessionKey, $accessToken['openid']);

        $wechatUser->nickname = $wechatUserInfo->getNickname();
        $wechatUser->avatar = $wechatUserInfo->getAvatar();
        $wechatUser->details = Json::encode($wechatUserInfo->toArray());

        $wechatUser->save(false);

        return $this->redirectToReturnUrl();
    }

    private function redirectToBaseScope()
    {
        return $this->redirectToScope(static::SNSAPI_BASE);
    }
}
