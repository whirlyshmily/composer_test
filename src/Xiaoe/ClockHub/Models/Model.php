<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version : Model.php 2019-07-02 11:56 $
 */

namespace Xiaoe\ClockHub\Models;

use ArrayAccess;
use BadMethodCallException;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Xiaoe\ClockHub\ArgumentInvalidException;
use Xiaoe\ClockHub\JsonEncodingException;
use Xiaoe\ClockHub\MassAssignmentException;
use Xiaoe\ClockHub\Models\Concerns\GuardsAttributes;
use Xiaoe\ClockHub\Models\Concerns\HasAttributes;
use Xiaoe\ClockHub\Models\Concerns\HasTimestamps;
use Xiaoe\ClockHub\Models\Concerns\HidesAttributes;

class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    use GuardsAttributes,
        HasAttributes,
        HidesAttributes,
        HasTimestamps;

    protected static $client;
    protected static $logger;
    protected static $endpoints;

    private $wheres = [];
    private $orders = [];

    /**
     * Paginate the given query.
     * @var PageRequest
     */
    private $paginate = null;


    /**
     * The action associated with the model.
     */
    protected static $action;

    /**
     * The endpoint associated with the model.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The attributes that are operable.
     *
     * @var array
     */
    protected $operable = [];

    /**
     * The error detail whether has error
     * @var array
     */
    private $error;

    private $grayId;


    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdAt';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updatedAt';

    const GET_ACTION = 'get';
    const MULTI_GET_ACTION = 'multi.get';
    const SAVE_ACTION = 'save';
    const CREATE_ACTION = 'create';

    /**
     * Format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    const TO_STRING_FORMAT = 'Y-m-d H:i:s';

    const OK = 0;
    const HTTP_METHOD = 'POST';


    public function where($parameter, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $operator = $operator ?: '=';
        if ($parameter === 'shop_id' && is_array($value)) {
            $value = array_filter(array_values($value));
        }
        $this->wheres[$parameter] = [$operator, $value];

        return $this;
    }

    public function whereIn($parameter, array $value = [])
    {
        if ($parameter === 'shop_id') {
            $value = array_filter(array_values($value));
        }
        $this->wheres[$parameter] = ['in', $value];

        return $this;
    }

    public function orderBy($parameter, $direction = 'asc')
    {
        $this->orders[$parameter] = $direction;
        return $this;
    }

    /**
     * @return Model | Shop | Platform | Merchant | WeChat
     * @throws ArgumentInvalidException
     */
    public function get()
    {
        return $this->perform(static::GET_ACTION);
    }

    /**
     * @return Model | Shop | Platform | Merchant | WeChat
     * @throws ArgumentInvalidException
     */
    public function lists()
    {
        return $this->perform(static::MULTI_GET_ACTION);
    }

    /**
     * @return Model
     * @throws ArgumentInvalidException
     */
    public function first()
    {
        $this->paginate = PageRequest::of(0, 1);
        return $this->get();
    }

    /**
     * @param PageRequest
     * @return Model
     * @throws ArgumentInvalidException
     */
    public function paginate(PageRequest $pageRequest)
    {
        $this->paginate = $pageRequest;
        return $this->lists();
    }


    /**
     * @return boolean|Model
     * @throws ArgumentInvalidException
     */
    public function create()
    {
        return $this->perform(static::CREATE_ACTION);
    }


    /**
     * @param $action
     * @return boolean|Model
     * @throws ArgumentInvalidException
     */
    public function save()
    {
        return $this->perform(static::SAVE_ACTION);
    }

    public function gray($shopId)
    {
        $this->grayId = $shopId;
        return $this;
    }


    /**
     * @param $action
     * @return Model
     * @throws ArgumentInvalidException
     */
    private function perform($action)
    {
        static::$action = $action;
        list($code, $msg, $data) = $this->exec();

        if ($code === static::OK) {
            return static::decode($data);
        }
        $current = new self();
        $current->error = compact('code', 'msg', 'data');

        return $current;
    }

    protected static function decode($data)
    {
        $caller = static::caller(__METHOD__);
        $debug = self::$logger->isHandling(Logger::DEBUG);

        $model = new static;
        $fillData = [];
        $that = get_class($model);

        self::$logger->info("${caller} get data !", [
            'size' => count((array)$data),
            'type' => gettype($data),
            'data' => $debug ? $data : '...',
        ]);

        if (!is_array($data) || !is_object($data)) {
            $data = (array)$data;
        }
        foreach ($data as $k => $v) {
            //model fillable 中的字段
            $key = Str::camel($k);
            //如果 $v 是 object 则，直接 fill 到 $k 对应的对象，如果是数组，则转成 colletcion，赋值
            $value = null;
            //如果返回的$v为空object，不生成对应model对象
            if (is_object($v) && count((array)$v)) {
                $class = $that. '\\' . $key;
                self::$logger->debug("${caller} foreach data, get class !", [
                    'k' => $k,
                    'class' => $class,
                    'v' => $debug ? $v : '...',
                ]);

                $value = $v;
                if (class_exists($class)) {
                    $value = new $class;
                    $fillable = $value->getFillable();
                    $default = [];
                    if(is_array($fillable)){
                        foreach($fillable as $item){
                            $default[$item] = null;
                        }
                    }
                    $value->fill(array_merge($default,(array)$v));

                    self::$logger->debug("${caller} foreach data, get class !", [
                        'k' => $k,
                        'class' => $class,
                        'v' => $debug ? $v : '...',
                    ]);

                } else{
                    self::$logger->debug("${caller} foreach data, class not exists, skip!", [
                        'k' => $k,
                        'class' => $class,
                        'v' => $debug ? $v : '...',
                    ]);
                }
            } elseif (is_array($v)) {
                self::$logger->debug("${caller} foreach data, v is is_array, and will be collect!", [
                    'k' => $k,
                    'v' => $debug ? $v : '...',
                ]);

                $value = collect($v);
            }
            $fillData[$key] = $value;
        }

        self::$logger->info("${caller} get fillData", [
            'keys' => array_keys($fillData),
            'size' => count($fillData),
            'data' => $debug ? $fillData : '...',
        ]);

        $model->fill($fillData);

        return $model;
    }

    protected function compile()
    {
       $caller = static::caller(__METHOD__);
        $body = [];
        //2. 拿各个模块
        $attributes = $this->getAttributes();
        $that = get_class($this);
        if (empty($attributes)) {
            throw new ArgumentInvalidException($that . ' attributes must be required.');
        }

        $debug = self::$logger->isHandling(Logger::DEBUG);

        self::$logger->info("${caller} ${that} get attributes and foreach to compile modules !", [
            'size' => count($attributes),
            'keys' => array_keys($attributes),
        ]);

        $opsWheres = [];
        $i = 0;
        foreach ($attributes as $k => $v) {
            $i++;
            $module = Str::snake($k);
            $class = $that.'\\' . ucfirst(Str::camel($k));

            if (!class_exists($class)) {
                self::$logger->info("${caller} class ${that}'s subClass ${class} not exists, skip...",
                    compact('k', 'v')
                );
                continue;
            }

            if ($v instanceof Collection) {
                self::$logger->debug("${caller} class ${that} v instanceof Collection!!", [
                    'k' => $k,
                    'size' => $v->count(),
                    'v' => $v
                ]);
                $moduleVal = $v;
            } else {
                self::$logger->debug("${caller} class ${that} v must instanceof Model !!", [
                    'k' => $k,
                    'type' => gettype($v),
                    'v' => $debug ? $v : '...'
                ]);

                if (!$v instanceof Model) {
                    $v = new $class;
                }
                $opsWheres = array_merge($opsWheres, $v->operable);

                self::$logger->debug("${caller} class ${that} get operator wheres", [
                    'k' => $k,
                    'wheres_size' => count($opsWheres),
                    'wheres' => $debug ? $opsWheres : '...'
                ]);

                $moduleVal = $v->getAttributes();

                self::$logger->debug("${caller} class ${that} get attributes", [
                    'k' => $k,
                    'v' => $debug ? $v : '...',
                    'size' => count($moduleVal),
                    'keys' => array_keys($moduleVal),
                ]);

                foreach ($moduleVal as $mk => $mv) {
                    if (!$v->isFillable($mk)) {
                        self::$logger->debug("${caller} class ${that} attribute not fillable, unset!", [
                            'k' => $k,
                            'mk' => $mk,
                            'v' => $debug ? $v : '...',
                        ]);
                        unset($moduleVal[$mk]);
                    }
                }
            }

            if (static::$action === static::GET_ACTION || static::$action === static::MULTI_GET_ACTION) {
                $body['modules'][] = $module;
            } elseif (static::$action === static::CREATE_ACTION && $this->endpoint === 'shop'){
                $body = array_merge($body, $moduleVal);
            }else {
                $body['modules'][$module] = $moduleVal;
            }
        }

        $modules = Arr::get($body, 'modules', []);
        self::$logger->info("${caller} class ${that} compile modules", [
            'modules' => $debug ?  $modules: '...',
            'size' => count($modules),
        ]);

        self::$logger->info("${caller} class ${that} compile wheres", [
            'wheres' => $debug ? $this->wheres : '...',
            'size' => count($this->wheres),
            'keys' => array_keys($this->wheres),
        ]);

        //3. 合并 where，orderby, limit
        foreach ($this->wheres as $k => $v) {
            list($operator, $value) = $v;
            if (in_array($k, $opsWheres)) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                $body[$k] = compact('operator', 'value');
            } else {
                $body[$k] = $value;
            }
        }

        self::$logger->info("${caller} class ${that} compile orders", [
            'orders' => $this->orders,
            'size' => count($this->orders),
            'keys' => array_keys($this->orders),
        ]);

        $sort = [];
        foreach ($this->orders as $k => $v) {
            $sort[] = "${k}:${v}";
        }
        $body['sort'] = implode(',', $sort);


        if ($this->paginate) {
            $paginate = $this->paginate->toArray();
            self::$logger->info("${caller} class ${that} compile paginate", [
                'paginate' => $paginate,
                'keys' => array_keys($this->orders),
            ]);
            $body = array_merge($body, $paginate);
        }

        $headers = [];
        if($this->grayId){
            $headers['App-Id'] = $this->grayId;
        }

        return [$body, $headers];
    }

    /**
     * @return array
     * @throws ArgumentInvalidException
     */
    protected function exec()
    {
        list($body, $headers) = $this->compile();
        //4. 发送请求
        return $this->request(static::HTTP_METHOD, static::$endpoints[$this->endpoint][static::$action], $body, $headers);
    }

    protected function request($method, $uri, $json, $headers = [])
    {
        $code = 500;
        $msg = 'ok';
        $response = null;
        $body = null;

        $traceId = Str::random();
        $beginTime = microtime(true);

        $host = static::$client->getConfig('base_uri')->__toString();

        if (empty($host)){
            throw new ArgumentInvalidException('host can not be empty.');
        }
        $url = $host .$uri;

        self::$logger->info("[strace] begin to request...", [
            'trace_id' => $traceId,
            'service' => 'confhub',
            'start_time' => $beginTime,
            'url' =>  $url,
            'http_method' => $method
        ]);

        $container = [];

        $debug = self::$logger->isHandling(Logger::DEBUG);
        if ($debug && class_exists('\GuzzleHttp\Middleware')) {
            $history = \GuzzleHttp\Middleware::history($container);
            $stack = static::$client->getConfig('handler');
            $stack->push($history);
        }
        $params = [];
        if (defined('CONFHUB_SDK_VERSION')){
            $headers['ClockHub-SDK-Version'] = CONFHUB_SDK_VERSION;
            $params['sdk_version'] = CONFHUB_SDK_VERSION;
        }

        if(!empty($json['shop_id'])){
            $params['shop_id'] = is_array($json['shop_id']) ? Arr::get($json['shop_id'], 0) : $json['shop_id'];
        }
        $query = '';
        if(count($params)){
            $query = '?' . http_build_query($params);
        }
        $success = 0;
        $uri = $uri.$query;
        try {
            $response = static::$client->request($method, $uri, ['json' => $json, 'headers'=> $headers]);
            $success = 1;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
                $response = $e->getResponse();
            } else{
                self::$logger->error("request error:${msg} and response null!!!", compact('url', 'json'));
            }
        }
        $endTime = microtime(true);
        $responsed = $response instanceof ResponseInterface;

        if ($debug) {
            $arrayFirst = function (array $array){
                foreach ($array as $value){
                    if (!empty($value)){
                        return $value;
                    }
                }
            };
            $caller = static::caller(__METHOD__);
            foreach ($container as $transaction) {
                self::$logger->debug(sprintf('%s ---> %s %s HTTP %s',
                    $caller,
                    $transaction['request']->getMethod(),
                    $transaction['request']->getUri(),
                    $transaction['request']->getProtocolVersion()
                ));

                self::$logger->debug(sprintf('%s Content-Type: %s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['request']->getHeaders(), 'Content-Type', []))
                ));

                self::$logger->debug(sprintf('%s ClockHub-SDK-Version: %s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['request']->getHeaders(), 'ClockHub-SDK-Version', []))
                ));

                self::$logger->debug(sprintf('%s App-Id: %s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['request']->getHeaders(), 'App-Id', []))
                ));

                self::$logger->debug("${caller} ");
                self::$logger->debug(sprintf('%s %s ',
                    $caller,
                    json_encode($json)
                ));
                self::$logger->debug(sprintf('%s ---> END HTTP (%d-byte body)',
                    $caller,
                    $arrayFirst(Arr::get($transaction['request']->getHeaders(), 'Content-Length', []))
                ));

                if (!$transaction['response']){
                    self::$logger->debug(sprintf('%s Get null response',$caller),compact('msg'));
                    break;
                }
                self::$logger->debug(sprintf('%s <--- HTTP/%s %d %s (%dms)',
                    $caller,
                    $transaction['response']->getProtocolVersion(),
                    $transaction['response']->getStatusCode(),
                    $transaction['response']->getReasonPhrase(),
                    ($endTime - $beginTime) * 1000
                ));

                self::$logger->debug(sprintf('%s cache-control:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Cache-Control', []))
                ));

                self::$logger->debug(sprintf('%s connection:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Connection', []))
                ));
                self::$logger->debug(sprintf('%s content-type:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Content-Type', []))
                ));
                self::$logger->debug(sprintf('%s date:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Date', []))
                ));
                self::$logger->debug(sprintf('%s server:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Server', []))
                ));
                self::$logger->debug(sprintf('%s transfer-encoding:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Transfer-Encoding', []))
                ));
                self::$logger->debug(sprintf('%s vary:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Vary', []))
                ));
                self::$logger->debug(sprintf('%s x-powered-by:%s',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'X-Powered-By', []))
                ));
                self::$logger->debug("${caller}");
                self::$logger->debug(sprintf("%s %s",
                    $caller,
                    $responsed ? (string)$response->getBody() : ''
                ));

                self::$logger->debug(sprintf('%s <--- END HTTP (%d-byte body)',
                    $caller,
                    $arrayFirst(Arr::get($transaction['response']->getHeaders(), 'Content-Length', []))
                ));

                self::$logger->debug(sprintf('%s <--- ERROR: %s',
                    $caller,
                    $transaction['error']
                ));

            }
        }

        self::$logger->info("[strace] finish request...", [
            'trace_id' => $traceId,
            'service' => 'confhub',
            'end_time' => $endTime,
            'cost_time' => sprintf("%.4fms", ($endTime - $beginTime) * 1000),
            'uri' => $uri,
            'http_method' => $method,
            'http_status' => $responsed ? $response->getStatusCode() : 500,
            'success' => $success,
            'data' => $debug ? $json : '...',
            'response' => $debug && $responsed ? (string)$response->getBody() : '...',
        ]);

        $data = [];
        if ($responsed) {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            $data = json_decode($body);

            if (is_object($data)) {
                $code = object_get($data, 'code');
                $msg = object_get($data, 'msg');
                $data = object_get($data, 'data');
            } else {
                $data = $body;
                $code = $statusCode;
            }
        }

        $data = $data ?: [];

        return [$code, $msg, $data];

    }

    /**
     * @throws ArgumentInvalidException
     */
    public function toString()
    {
        if ($this->hasError()) {
            return json_encode($this->error);
        }
        list($body, $headers) = $this->compile();
        $method = static::HTTP_METHOD;
        $endpoint = Arr::get(static::$endpoints, $this->endpoint, []);
        $uri = key_exists(static::$action, $endpoint) ? $endpoint[static::$action] : '';

        $headerLines = '';
        foreach ($headers as $k=>$v){
            $headerLines .= "${k}: ${v}\n";
        }
        $body = json_encode($body);

        return <<<EOF
       $method $uri
       $headerLines
       ------------------
       $body
       ------------------
EOF;

    }

    public function getError()
    {
        return $this->error;
    }

    public function hasError()
    {
        return $this->error && is_array($this->error) && Arr::get($this->error, 'code') !== static::OK;
    }


    /**
     * Get the current client associated with the model.
     *
     * @return string
     */
    public static function getClient()
    {
        return static::$client;
    }

    /**
     * Set the client associated with the model.
     *
     * @param object $client
     * @return Model
     */
    public static function setClient($client)
    {
        static::$client = $client;
    }

    /**
     * Get the current logger associated with the model.
     *
     * @return string
     */
    public static function getLogger()
    {
        return static::$logger;
    }

    /**
     * Set the logger associated with the model.
     *
     * @param object $logger
     * @return void
     */
    public static function setLogger($logger)
    {
        static::$logger = $logger;
    }

    /**
     * Get the current logger associated with the model.
     *
     * @return string
     */
    public static function getEndpoints()
    {
        return static::$endpoints;
    }

    /**
     * Set the logger associated with the model.
     *
     * @param object $endpoints
     * @return void
     */
    public static function setEndpoints($endpoints)
    {
        static::$endpoints = $endpoints;
    }


    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     *
     * @throws JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return $this
     *
     * @throws MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     *
     * @param array $attributes
     * @return Model
     */
    public function forceFill(array $attributes)
    {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }

    /**
     * Determine if the given value can be convert to
     * an array using the `toArray` method.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function hasToArray($value)
    {
        return (is_object($value)
            && !($value instanceof UploadedFile)
            && is_callable([$value, 'toArray']));
    }

    /**
     * Remove the table name from a given key.
     *
     * @param string $key
     * @return string
     */
    protected function removeTableFromKey($key)
    {
        return Str::contains($key, '.') ? last(explode('.', $key)) : $key;
    }

    protected static function caller($method, $subMethod = null)
    {
        $path = explode('\\', $method);
        $subMethod = $subMethod ?: Arr::get($path, count($path)-1);
        return '['.Arr::get($path, 1) .'#' . $subMethod .']';
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|mixed|null
     * @todo 待补充作用
     */
    public function __call($name, $arguments)
    {
        $class = get_class($this);
        $debug = self::$logger->isHandling(Logger::DEBUG);
        $caller = static::caller(__METHOD__);

        self::$logger->debug("${caller} class: ${class}, method:${name}", compact('arguments'));

        if ($this->isFillable($name)) {
            self::$logger->debug("${caller} [1] ${class}\\${name} can be fillable, setAttribute and return");
            $this->setAttribute($name, Arr::get($arguments, 0));
            return $this;
        }

        $method = lcfirst(str_replace('get', '', $name));
        if (Str::startsWith($name, 'get')) {
            if ($this->hasError()) {
                self::$logger->debug("${caller} [2] ${class}\\${name} startsWith get, has error, return null");
                return null;
            }

            if ($this->isFillable($method)) {
                self::$logger->debug("${caller} [3] ${class}\\${name} startsWith get and can be fillable, will call ${method}");
                return $this->$method;
            }
        }
        self::$logger->error("!!!!!!! ${caller} [4] class: ${class}, method:${name}, can not be fillable or ${method} not find.");

        throw new BadMethodCallException("class ${class} method ${name} not found.");
    }
}