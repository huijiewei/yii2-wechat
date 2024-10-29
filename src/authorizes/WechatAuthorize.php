<?php

namespace huijiewei\wechat\authorizes;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\OfficialAccount\Application;
use huijiewei\wechat\exceptions\AuthorizeFailedException;
use huijiewei\wechat\models\WechatUser;
use Overtrue\Socialite\Contracts\UserInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Response;

class WechatAuthorize extends Component
{
    const SNSAPI_BASE = 'snsapi_base';
    const SNSAPI_USERINFO = 'snsapi_userinfo';

    public string|Application $wechat = 'wechat';
    public string $sessionKey = '';
    private ?Application $_wechat = null;
    private bool|string $_appId = false;
    private ?WechatUser $_wechatUser = null;
    private ?string $_wechatOpenId = null;
    private ?string $_authorizeScope = null;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->wechat)) {
            throw new InvalidConfigException('wechat 属性不能为空');
        }

        $this->sessionKey = Yii::$app->id . '_WX_' . $this->getAppId() . '_ID';
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function getAppId(): string
    {
        if ($this->_appId === false) {
            $this->_appId = $this->getWechat()->getConfig()->get('app_id');
        }

        return $this->_appId;
    }

    /**
     * @return Application
     * @throws InvalidConfigException
     */
    public function getWechat(): Application
    {
        if ($this->_wechat == null) {
            $this->_wechat = $this->wechat instanceof Application ? $this->wechat : Yii::$app->get($this->wechat)->getApp();
        }

        return $this->_wechat;
    }

    /**
     * @return null|WechatUser
     */
    public function getWechatUser(): ?WechatUser
    {
        return $this->_wechatUser;
    }

    /**
     * @return string|null
     */
    public function getWechatOpenId(): ?string
    {
        return $this->_wechatOpenId;
    }

    public function isScopeBase(): bool
    {
        if ($this->_wechatOpenId != null) {
            return true;
        }

        $wechatOpenId = Yii::$app->getSession()->get($this->sessionKey, '');

        if (empty($wechatOpenId)) {
            $this->_authorizeScope = static::SNSAPI_BASE;

            return false;
        }

        $this->_wechatOpenId = $wechatOpenId;

        return true;
    }

    /**
     * @throws InvalidConfigException
     */
    public function isAuthorized(): bool
    {
        return $this->isScopeUserInfo();
    }

    /**
     * @throws InvalidConfigException
     */
    public function isScopeUserInfo(): bool
    {
        if ($this->_wechatUser != null) {
            return true;
        }

        $wechatOpenId = Yii::$app->getSession()->get($this->sessionKey, '');

        if (empty($wechatOpenId)) {
            $this->_authorizeScope = static::SNSAPI_BASE;

            return false;
        }

        $this->_wechatOpenId = $wechatOpenId;

        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $wechatOpenId);

        if ($wechatUser == null || $wechatUser->getRefreshTokenIsExpired()) {
            Yii::$app->getSession()->remove($this->sessionKey);

            $this->_authorizeScope = static::SNSAPI_USERINFO;

            return false;
        }

        $this->_wechatUser = $wechatUser;

        return true;
    }

    /**
     * @return bool|Response
     * @throws AuthorizeFailedException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     */
    public function authorizeRequired(): Response|bool
    {
        $code = Yii::$app->getRequest()->get('code', '');
        $scope = Yii::$app->getRequest()->get('scope', '');

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
     * @return Response
     * @throws AuthorizeFailedException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws InvalidRouteException|Exception
     */
    private function authorizeProcess($code, $scope)
    {
        try {
            $user = $this->getWechat()->getOAuth()->userFromCode($code);
        } catch (\Exception) {
            throw new AuthorizeFailedException('微信授权失败，请重新打开页面。');
        }

        if ($scope == static::SNSAPI_BASE) {
            return $this->processBase($user);
        } else {
            return $this->processUserInfo($user);
        }
    }

    /**
     * @param $scope string
     *
     * @return Response
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     * @throws InvalidRouteException
     */
    private function redirectToScope(string $scope): Response
    {
        $redirectUrl = $this->getWechat()
            ->getOAuth()
            ->scopes([$scope])
            ->redirect($this->getReturnUrl($scope));

        return Yii::$app->getResponse()->redirect($redirectUrl);
    }

    /**
     * @param string|null $scope
     *
     * @return string
     */
    private function getReturnUrl(string $scope = null): string
    {
        return Url::current(['scope' => $scope, 'code' => null, 'state' => null], true);
    }

    /**
     * @param $user UserInterface
     *
     * @return Response
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     * @throws InvalidArgumentException
     */
    private function processBase(UserInterface $user): Response
    {
        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $user->getId());

        if ($wechatUser == null || $wechatUser->getRefreshTokenIsExpired()) {
            return $this->redirectToScope(static::SNSAPI_USERINFO);
        }

        Yii::$app->getSession()->set($this->sessionKey, $user->getId());

        return $this->redirectToReturnUrl();
    }

    /**
     * @return Response
     * @throws InvalidRouteException
     */
    private function redirectToReturnUrl(): Response
    {
        return Yii::$app->getResponse()->redirect($this->getReturnUrl());
    }

    /**
     * @param $user UserInterface
     *
     * @return Response
     * @throws AuthorizeFailedException
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     * @throws Exception
     */
    private function processUserInfo(UserInterface $user): Response
    {
        try {
            $wechatUserInfo = $this->getWechat()->getOAuth()->userFromToken($user->getAccessToken());
        } catch (\Exception) {
            throw new AuthorizeFailedException('获取用户资料失败，请重新打开页面。');
        }

        $wechatUser = WechatUser::getWechatUserByOpenId($this->getAppId(), $user->getId());

        if ($wechatUser == null) {
            $wechatUser = new WechatUser();
            $wechatUser->appId = $this->getAppId();
            $wechatUser->openId = $user->getId();
        }

        $wechatUser->accessToken = $user->getAccessToken();
        $wechatUser->refreshToken = $user->getRefreshToken();
        $wechatUser->accessTokenExpiredAt = Yii::$app->getFormatter()->asDatetime('+' . ($user->getExpiresIn() - 200) . ' seconds');
        $wechatUser->refreshTokenExpiredAt = Yii::$app->getFormatter()->asDatetime('+20 days');

        $wechatUser->nickname = $wechatUserInfo->getNickname();
        $wechatUser->avatar = $wechatUserInfo->getAvatar();
        $wechatUser->details = Json::encode($wechatUserInfo->toArray());

        $wechatUser->save(false);

        Yii::$app->getSession()->set($this->sessionKey, $user->getId());

        return $this->redirectToReturnUrl();
    }
}
