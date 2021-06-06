<?php

namespace Xiaoe\ClockHub\Models;

use Xiaoe\ClockHub\Models\Diary;

/**
 *
 * @property Diary $diary;
 *
 * @method Diary getDiary();
 *
 *
 * @method $this diary();
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package Clockhub-sdk
 * @version $Id: ClockHub.php 2019-07-01 16:41 $
 */
class Clock extends Model
{
    protected $endpoint = 'clock';

    protected $fillable = [
        'diary',
    ];

}