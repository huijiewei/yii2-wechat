<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 14:46
 */

namespace huijiewei\wechat\authorizes;

use EasyWeChat\Factory;
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

    public $wechat = 'wechat';
    public $sessionKey = '';

    /* @var $_wechat Application */
    private $_wechat = null;
    private $_appId = false;
    private $_wechatUser = null;
    private $_authorizeScope = null;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (empty($this->wechat)) {
            throw new InvalidConfigException('wechat 属性不能为空');
        }

        $this->sessionKey = \Yii::$app->id . '_WX_' . $this->getAppId() . '_ID';
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        if ($this->_appId === false) {
            $this->_appId = $this->getWechat()->config->get('app_id');
        }

        return $this->_appId;
    }

    /**
     * @return Application|null
     */
    public function getWechat()
    {
        if ($this->_wechat == null) {
            $this->_wechat = $this->wechat instanceof Factory ? $this->wechat : \Yii::$app->get($this->wechat)->getApp();
        }

        return $this->_wechat;
    }

    /**
     * @return null|WechatUser
     */
    public function getWechatUser()
    {
        return $this->_wechatUser;
    }

    public function isAuthorized()
    {
        if ($this->_wechatUser != null) {
            return true;
        }

        $wechatOpenId = \Yii::$app->getSession()->get($this->sessionKey, '');

        if (empty($wechatOpenId)) {
            $this->_authorizeScope = static::SNSAPI_BASE;

            return false;
        }

        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $wechatOpenId);

        if ($wechatUser == null || $wechatUser->getRefreshTokenIsExpired()) {
            \Yii::$app->getSession()->remove($this->sessionKey);

            $this->_authorizeScope = static::SNSAPI_USERINFO;

            return false;
        }


        $this->_wechatUser = $wechatUser;

        return true;
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

        if ($this->_authorizeScope == null) {
            throw new AuthorizeFailedException('请先运行 isAuthorized');
        }

        return $this->redirectToScope($this->_authorizeScope);
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
            $accessToken = $this->getWechat()->oauth->getAccessToken($code);
        } catch (\Exception $ex) {
            throw new AuthorizeFailedException('微信授权失败，请重新打开页面。');
        }

        if ($scope != $accessToken['scope']) {
            return $this->redirectToScope(static::SNSAPI_USERINFO);
        }

        if ($scope == static::SNSAPI_BASE) {
            return $this->processBase($accessToken);
        } else {
            return $this->processUserInfo($accessToken);
        }
    }

    /**
     * @param $scope
     *
     * @return \yii\web\Response
     */
    private function redirectToScope($scope)
    {
        return \Yii::$app->getResponse()->redirect($this->getWechat()->oauth->scopes([$scope])->redirect($this->getReturnUrl($scope))->getTargetUrl());
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
            return $this->redirectToScope(static::SNSAPI_USERINFO);
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
        try {
            $wechatUserInfo = $this->getWechat()->oauth->user($accessToken);
        } catch (\Exception $ex) {
            throw new AuthorizeFailedException('获取用户资料失败，请重新打开页面。');
        }

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

        $wechatUser->nickname = $wechatUserInfo->getNickname();
        $wechatUser->avatar = $wechatUserInfo->getAvatar();
        $wechatUser->details = Json::encode($wechatUserInfo->toArray());

        $wechatUser->save(false);

        \Yii::$app->getSession()->set($this->sessionKey, $accessToken['openid']);

        return $this->redirectToReturnUrl();
    }
}
