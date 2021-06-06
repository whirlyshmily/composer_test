<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : ClockHub.php 2019-07-02 10:13 $
 */

namespace Xiaoe\ClockHub;

class ClockHub
{
    public $config = [
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

    public function __construct(array $config = [], $client = null)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function hello($param){
        var_dump($param);
    }

   
}