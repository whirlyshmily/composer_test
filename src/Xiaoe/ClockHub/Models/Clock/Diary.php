<?php

namespace Xiaoe\ClockHub\Models\Diary;

use Xiaoe\ClockHub\Models\Model;

/**
 * @property $app_id;
 * @property $id;
 * @property $activity_id;
 * @property $user_id;
 * @property $common_user_id;
 * @property $clock_date;
 * @property $clock_theme_id;
 * @property $text_content;
 * @property $img_urls;
 * @property $img_compressed_urls;
 * @property $audio_records;
 * @property $video_records;
 * @property $is_private;
 * @property $state;
 * @property $zan_count;
 * @property $comment_count;
 * @property $review_count;
 * @property $is_topic;
 * @property $topic_time;
 * @property $publish_time;
 * @property $type;
 * @property $is_reclock;
 * @property $reclock_status;
 * @property $clock_from;
 * @property $content_type;
 * @property $created_at;
 * @property $updated_at;
 * @property $manager_user_id;
 *
 * Class Diary
 * @package Xiaoe\ClockHub\Models\Diary
 */
class Diary extends Model
{
    protected $fillable =
    [
        'app_id',
        'id',
        'activity_id',
        'user_id',
        'common_user_id',
        'clock_date',
        'clock_theme_id',
        'text_content',
        'img_urls',
        'img_compressed_urls',
        'audio_records',
        'video_records',
        'is_private',
        'state',
        'zan_count',
        'comment_count',
        'review_count',
        'is_topic',
        'topic_time',
        'publish_time',
        'type',
        'is_reclock',
        'reclock_status',
        'clock_from',
        'content_type',
        'created_at',
        'updated_at',
        'manager_user_id'
    ];
}