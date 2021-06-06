<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : ClockHubInterface.php 2019-07-01 16:46 $
 */

namespace Xiaoe\ClockHub;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * $service = app('confhub');
 * $conn = $service->shop($appId)
 *  ->base()
 *  ->status()
 * -> where('wx_app_type', 1)
 * $data = $conn->get();
 *
 *
 * $base = new Base();
 * $base->logo = "xx";
 *
 * $conn = $service->shop($appId)
 *          ->setBase($base)
 *          ->where()
 *          ->save();
 *
 *
 * Interface ClockHubInterface
 * @package Xiaoe\ClockHub
 */
interface ClockHubInterface
{
    public function __construct(array $config, $client, LoggerInterface $logger);

    public function shop($shopId);

    public function platform();
}