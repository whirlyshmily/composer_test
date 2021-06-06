<?php

namespace Xiaoe\ClockHub\Models;

use Xiaoe\ClockHub\Models\WeChat\MpBind;
use Xiaoe\ClockHub\Models\WeChat\Pay;

/**
 *
 * @property MpBind $mpBind;
 * @property Pay $pay;
 *
 * @method MpBind getMpBind();
 * @method Pay getPay();
 *
 *
 * @method $this mpBind();
 * @method $this pay();
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version $Id: ClockHub.php 2019-07-01 16:41 $
 */
class WeChat extends Model
{
    protected $endpoint = 'wechat';

    protected $fillable = [
        'mpBind',
        'pay',
    ];

}