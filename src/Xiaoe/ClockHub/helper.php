<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package clockhub-sdk
 * @version $Id: helper.php 2019-07-19 09:57 $
 */

if (! function_exists('clockhub')) {
    /**
     * Get an instance of clockhub service.
     *
     * @return \Xiaoe\ClockHub\ClockHub
     */
    function clockhub()
    {
        return app('clockhub');
    }
}