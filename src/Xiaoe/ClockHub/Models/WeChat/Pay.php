<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package clockhub-sdk
 * @version : Pay.php 2019-07-17 15:00 $
 */

namespace Xiaoe\ClockHub\Models\WeChat;

use Xiaoe\ClockHub\Models\Model;

/**
 * @property $mchid
 * @property $mchkey
 * @property $use_collection
 * @property $access_pays
 * @property $pay_directory_verified
 *
 * Class Pay
 * @package Xiaoe\ClockHub\Models\WeChat
 */
class Pay extends Model
{
    protected $fillable = [
        'shop_id',
        'mchid',
        'mchkey',
        'use_collection',
        'access_pays',
        'pay_directory_verified',
    ];
}