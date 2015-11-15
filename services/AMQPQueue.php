<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace mithun\queue\services;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * RedisQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class AMQPQueue extends Component implements QueueInterface
{
    /**
     * @var AMQPConnection|array
     */
    public $amqp;
    
    /**
     * Channel
     * @var unknown
     */
    public $channel;
    /**
     * @var integer
     */
    public $expire = 60;
    
    /**
     * whether to use exchange or not. default null.
     * @var boolean
     */
    public $exchange = '';
    
    /**
     * Exchange Type
     * @var string
     */
    public $exchangeType = 'fanout';
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->amqp === null) {
            throw new InvalidConfigException('The "redis" property must be set.');
        }
        if (!$this->amqp instanceof AMQPConnection) {
            $this->amqp = new AMQPConnection(
            			$this->amqp['host'],
            			$this->amqp['port'],
            			$this->amqp['username'],
            			$this->amqp['password']
            		);
        }
        
        if(!$this->channel){
        	$this->channel = $this->amqp->channel();
        }
        
        if($this->exchange){
        	$this->channel->exchange_declare($this->exchange, $this->exchangeType, false, false, false);
        }
    }
    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
    	$this->channel->queue_declare($queue, false, true, false, false);
    	
    	$routing_key = '';
    	if($payload['route_key']){
    		$routing_key = $payload['route_key'];
    		unset($payload['route_key']);
    	}
    	
    	if(is_array($payload['body']) || is_object($payload['body'])){
        	$payload['body'] = Json::encode($payload['body']);
    	}
    	$msg_body = $payload['body'];
    	unset($payload['body']);
        
        $msg = new AMQPMessage($msg_body,$payload);
        
        $channel->basic_publish($msg, $this->exchange,$routing_key);
        return $msg;
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