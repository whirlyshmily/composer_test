<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : JsonEncodingException.php 2019-07-03 11:40 $
 */

namespace Xiaoe\ClockHub;

use RuntimeException;

class JsonEncodingException extends RuntimeException
{
    /**
     * Create a new JSON encoding exception for the model.
     *
     * @param mixed $model
     * @param string $message
     * @return JsonEncodingException
     */
    public static function forModel($model, $message)
    {
        return new static('Error encoding model [' . get_class($model) . '] with ID [' . $model->getKey() . '] to JSON: ' . $message);
    }

    /**
     * Create a new JSON encoding exception for an attribute.
     *
     * @param mixed $model
     * @param mixed $key
     * @param string $message
     * @return static
     */
    public static function forAttribute($model, $key, $message)
    {
        $class = get_class($model);

        return new static("Unable to encode attribute [{$key}] for model [{$class}] to JSON: {$message}.");
    }
}