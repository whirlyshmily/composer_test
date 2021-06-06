<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : PageRequest.php 2019-07-08 11:02 $
 */

namespace Xiaoe\ClockHub\Models;

/**
 * @property int $page;
 * @property int $page_size;
 *
 * Class PageRequest
 * @package Xiaoe\ClockHub\Models
 */
class PageRequest extends Model
{
    protected $fillable = [
        'page',
        'page_size',
    ];

    public static function of ($page, $size)
    {
        $pageReq = new self();
        $pageReq->page = $page;
        $pageReq->page_size = $size;

        return $pageReq;
    }
}