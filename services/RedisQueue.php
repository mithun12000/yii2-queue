<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace mithun\queue\services;
use Predis\Client;
use Predis\Transaction\MultiExec;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * RedisQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class RedisQueue extends Component implements QueueInterface
{
    /**
     * @var Client|array
     */
    public $redis;
    /**
     * @var integer
     */
    public $expire = 60;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->redis === null) {
            throw new InvalidConfigException('The "redis" property must be set.');
        }
        if (!$this->redis instanceof Client) {
            $this->redis = new Client($this->redis);
        }
    }
    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        $payload = Json::encode(['id' => $id = md5(uniqid('', true)), 'body' => $payload]);
        if ($delay > 0) {
            $this->redis->zadd($queue . ':delayed', [$payload => time() + $delay]);
        } else {
            $this->redis->rpush($queue, [$payload]);
        }
        return $id;
    }
    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        foreach ([':delayed', ':reserved'] as $type) {
            $options = ['cas' => true, 'watch' => $queue . $type];
            $this->redis->transaction($options, function (MultiExec $transaction) use ($queue, $type) {
                $data = $this->redis->zrangebyscore($queue . $type, '-inf', $time = time());
                if (!empty($data)) {
                    $transaction->zremrangebyscore($queue . $type, '-inf', $time);
                    $transaction->rpush($queue, $data);
                }
            });
        }
        $data = $this->redis->lpop($queue);
        if ($data === null) {
            return false;
        }
        $this->redis->zadd($queue . ':reserved', [$data => time() + $this->expire]);
        $data = Json::decode($data);
        return [
            'id' => $data['id'],
            'body' => $data['body'],
            'queue' => $queue,
        ];
    }
    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $this->redis->del([$queue, $queue . ':delayed', $queue . ':reserved']);
    }
    /**
     * @inheritdoc
     */
    public function release(array $message, $delay = 0)
    {
        if ($delay > 0) {
            $this->redis->zadd($message['queue'] . ':delayed', [$message['body'] => time() + $delay]);
        } else {
            $this->redis->rpush($message['queue'], [$message['body']]);
        }
    }
    /**
     * @inheritdoc
     */
    public function delete(array $message)
    {
        $this->redis->zrem($message['queue'] . ':reserved', $message['body']);
    }
}