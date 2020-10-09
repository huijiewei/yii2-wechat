<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 11:27
 */

namespace huijiewei\wechat;

use EasyWeChat\Factory;
use huijiewei\wechat\adapters\CacheAdapter;
use huijiewei\wechat\adapters\LogAdapter;
use yii\base\Component;

class Wechat extends Component
{
    public $appConfig = [];
    public $paymentConfig = [];
    public $miniProgramConfig = [];
    public $openPlatformConfig = [];
    public $workConfig = [];
    public $isProd = false;

    private $_app;
    private $_payment;
    private $_miniProgram;
    private $_openPlatform;
    private $_work;
    private $_cache;
    private $_log;

    /**
     * @return bool
     */
    public static function getIsWechatClient()
    {
        return stripos(\Yii::$app->getRequest()->getUserAgent(), 'micromessenger') !== false;
    }

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public function getApp()
    {
        if (!$this->_app instanceof Factory) {
            $this->_app = Factory::officialAccount($this->mergeConfig($this->appConfig));
        }

        return $this->_app;
    }

    /**
     * @param $config
     * @return array
     */
    private function mergeConfig($config)
    {
        $config['cache'] = $this->getCache();
        $config['log']['default'] = $this->isProd ? 'prod' : 'dev';
        $config['log']['channels']['dev']['driver'] = $this->getLog();
        $config['log']['channels']['dev']['level'] = 'debug';
        $config['log']['channels']['prod']['driver'] = $this->getLog();
        $config['log']['channels']['dev']['level'] = 'info';

        return $config;
    }

    /**
     * @return CacheAdapter
     */
    private function getCache()
    {
        if ($this->_cache == null) {
            $this->_cache = new CacheAdapter();
        }

        return $this->_cache;
    }

    /**
     * @return LogAdapter
     */
    private function getLog()
    {
        if ($this->_log == null) {
            $this->_log = new LogAdapter();
        }

        return $this->_log;
    }

    /**
     * @return \EasyWeChat\Payment\Application
     */
    public function getPayment()
    {
        if (!$this->_payment instanceof Factory) {
            $this->_payment = Factory::payment($this->mergeConfig($this->paymentConfig));
        }

        return $this->_payment;
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public function getMiniProgram()
    {
        if (!$this->_miniProgram instanceof Factory) {
            $this->_miniProgram = Factory::miniProgram($this->mergeConfig($this->miniProgramConfig));
        }

        return $this->_miniProgram;
    }

    /**
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public function getOpenPlatform()
    {
        if (!$this->_openPlatform instanceof Factory) {
            $this->_openPlatform = Factory::openPlatform($this->mergeConfig($this->openPlatformConfig));
        }

        return $this->_openPlatform;
    }

    /**
     * @return \EasyWeChat\Work\Application
     */
    public function getWork()
    {
        if (!$this->_work instanceof Factory) {
            $this->_work = Factory::work($this->mergeConfig($this->workConfig));
        }

        return $this->_work;
    }
}
