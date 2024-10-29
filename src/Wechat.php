<?php

namespace huijiewei\wechat;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\OfficialAccount\Application;
use EasyWeChat\Pay\Application as PayApplication;
use EasyWeChat\MiniApp\Application as MiniApplication;
use EasyWeChat\OpenPlatform\Application as OpenApplication;
use EasyWeChat\Work\Application as WorkApplication;
use yii\base\Component;

class Wechat extends Component
{
    public array $appConfig = [];
    public array $paymentConfig = [];
    public array $miniProgramConfig = [];
    public array $openPlatformConfig = [];
    public array $workConfig = [];

    private Application|null $_app;
    private PayApplication|null $_payment;
    private MiniApplication|null $_miniProgram;
    private OpenApplication|null $_openPlatform;
    private WorkApplication|null $_work;

    /**
     * @return bool
     */
    public static function getIsWechatClient(): bool
    {
        return stripos(\Yii::$app->getRequest()->getUserAgent(), 'micromessenger') !== false;
    }

    /**
     * @return Application
     * @throws InvalidArgumentException
     */
    public function getApp(): Application
    {
        if (!$this->_app instanceof Application) {
            $this->_app = new Application($this->mergeConfig($this->appConfig));
        }

        return $this->_app;
    }

    /**
     * @param $config
     * @return array
     */
    private function mergeConfig($config): array
    {
        return $config;
    }

    /**
     * @return PayApplication
     * @throws InvalidArgumentException
     */
    public function getPayment(): PayApplication
    {
        if (!$this->_payment instanceof PayApplication) {
            $this->_payment = new PayApplication($this->mergeConfig($this->paymentConfig));
        }

        return $this->_payment;
    }

    /**
     * @return MiniApplication
     * @throws InvalidArgumentException
     */
    public function getMiniProgram(): MiniApplication
    {
        if (!$this->_miniProgram instanceof MiniApplication) {
            $this->_miniProgram = new MiniApplication($this->mergeConfig($this->miniProgramConfig));
        }

        return $this->_miniProgram;
    }

    /**
     * @return OpenApplication
     * @throws InvalidArgumentException
     */
    public function getOpenPlatform(): OpenApplication
    {
        if (!$this->_openPlatform instanceof OpenApplication) {
            $this->_openPlatform = new OpenApplication($this->mergeConfig($this->openPlatformConfig));
        }

        return $this->_openPlatform;
    }

    /**
     * @return WorkApplication
     * @throws InvalidArgumentException
     */
    public function getWork(): WorkApplication
    {
        if (!$this->_work instanceof WorkApplication) {
            $this->_work = new WorkApplication($this->mergeConfig($this->workConfig));
        }

        return $this->_work;
    }
}
