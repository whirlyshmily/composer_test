<?php

namespace Xiaoe\ClockHub\Models\WeChat;

use Xiaoe\ClockHub\Models\Model;

/**
 * @property $qr_url;
 * @property $shop_id;
 * @property $wx_access_token;
 * @property $wx_access_token_refresh_at;
 * @property $wx_app_avatar;
 * @property $wx_app_id;
 * @property $wx_app_name;
 * @property $wx_business_info;
 * @property $wx_func_info;
 * @property $wx_js_ticket;
 * @property $wx_media_count;
 * @property $wx_media_id;
 * @property $wx_media_refreshed_at;
 * @property $wx_media_updated_at;
 * @property $wx_raw_app_id;
 * @property $wx_secrete_key;
 * @property $wx_service_type_info;
 * @property $wx_verify_type_info;
 * @property $wx_app_type;
 * @property $if_auth;
 * @property $wx_card_ticket;
 * @property $wx_card_ticket_refresh_at;
 * @property $bind_at;
 * @property $wx_open_platform_state;
 * @property $wx_bus_verify_txt;
 *
 * Class MpBind
 * @package Xiaoe\ClockHub\Models\WeChat
 */
class MpBind extends Model
{
    protected $fillable =
        [
            'qr_url',
            'shop_id',
            'wx_access_token',
            'wx_access_token_refresh_at',
            'wx_app_avatar',
            'wx_app_id',
            'wx_app_type',
            'wx_app_name',
            'wx_business_info',
            'wx_func_info',
            'wx_js_ticket',
            'wx_media_count',
            'wx_media_id',
            'wx_media_refreshed_at',
            'wx_media_updated_at',
            'wx_raw_app_id',
            'wx_secrete_key',
            'wx_service_type_info',
            'wx_verify_type_info',
            'if_auth',
            'wx_card_ticket',
            'wx_card_ticket_refresh_at',
            'bind_at',
            'wx_open_platform_state',
            'wx_bus_verify_txt'
        ];


}