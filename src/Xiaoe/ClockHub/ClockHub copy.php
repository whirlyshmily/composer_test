<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : ClockHub.php 2019-07-02 10:13 $
 */

namespace Xiaoe\ClockHub;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Xiaoe\ClockHub\Models\Merchant;
use Xiaoe\ClockHub\Models\Model;
use Xiaoe\ClockHub\Models\Platform;
use Xiaoe\ClockHub\Models\Shop;
use Xiaoe\ClockHub\Models\WeChat;

class ClockHub implements ClockHubInterface
{
    private $config = [
        'url' => '',
        'endpoints' => [
            'shop' => [
                'get' => '/api/xe.shop.config.module.get/2.0.0',
                'save' => '/api/xe.shop.config.module.set/2.0.0',
                'create' => '/api/xe.shop.config.module.new/2.0.0',
                'multi.get' => '/api/xe.shop.config.module.multi.get/2.0.0',
            ],
            'platform' => [
                'get' => '/api/xe.platform.config.module.get/2.0.0',
                'save' => '/api/xe.platform.config.module.set/2.0.0',
                'multi.get' => '/api/xe.platform.config.module.get/2.0.0',
            ],
            'merchant' => [
                'get' => '/api/xe.merchant.config.shop.get/2.0.0',
            ],
            'wechat' => [
                'get' => '/api/xe.wechat.config.module.get/2.0.0',
                'save' => '/api/xe.wechat.config.module.set/2.0.0',
                'multi.get' => '/api/xe.wechat.config.module.multi.get/2.0.0',
            ],
        ],
        'timeout' => 5,
        'log' => [
            'file' => [
                'level' => 'info',
                'maxFiles' => 15,
            ],
        ]
    ];

    private $logger;
    private $client;


    public function __construct(array $config = [], $client = null, LoggerInterface $logger = null)
    {
        $this->config = array_merge($this->config, $config);
        $this->client = $client ? $client : $this->defaultClient();
        $this->logger = $logger ? $logger : $this->defaultLogger();

        Model::setClient($this->client);
        Model::setLogger($this->logger);
        Model::setEndpoints($this->config['endpoints']);
    }

    private function defaultClient()
    {
        if (empty($this->config['url'])){
            throw new ArgumentInvalidException('url must be configured.');
        }
        return new Client([
            'base_uri' => $this->config['url'],
            'timeout' => $this->config['timeout'],
        ]);
    }

    private function defaultLogger()
    {
        $logName = 'confhub';
        $logger = new Logger($logName);
        $logPath = function_exists('storage_path') ? storage_path('logs') : __DIR__;

        $handler = new Handler\RotatingFileHandler(
            join('/', [$logPath, $logName . '.log']),
            $this->config['log']['file']['maxFiles'],
            $this->config['log']['file']['level']
        );

        $handler->setFilenameFormat('{filename}_{date}', 'Y-m-d');

        $handler->setFormatter(new LineFormatter(null, null,
            true, true));

        $handler->pushProcessor(new PsrLogMessageProcessor());
        //$handler->pushProcessor(new ProcessIdProcessor());

        $logger->pushHandler($handler);

        return $logger;
    }

    public function shop($shopId = null)
    {
        $shop = new Shop();

        if (!empty($shopId)){
            $shop->where('shop_id', $shopId);
        }

        return $shop;
    }

    public function platform()
    {
        return new Platform();
    }

    public function merchant()
    {
        return new Merchant();
    }

    public function wechat()
    {
        return new WeChat();
    }
}