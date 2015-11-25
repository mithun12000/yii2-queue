<?php
/**
 * 
 */
namespace mithun\queue\services;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * AMQPQueue
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class AMQPQueue extends Component implements QueueInterface
{
    /**
     * @var AMQPConnection
     */
    public $amqp;
    
    /**
     * @var array
     */
    public $config = [
    		'host' 		=> '127.0.0.1',
    		'port' 		=> 5672,
    		'username' 	=> '',
    		'password' 	=> '',
    		'vhost'		=> '',
    ];
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
     * Routing Key for exchange
     * @var string
     */
    public $routing_key;
    
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
        if ($this->config === null) {
            throw new InvalidConfigException('The "config" property must be set.');
        }
        if (!$this->amqp instanceof AMQPConnection) {
            $this->amqp = new AMQPConnection(
            			$this->config['host'],
            			$this->config['port'],
            			$this->config['username'],
            			$this->config['password'],
            			$this->config['vhost']
            		);
        }
        
        if(!$this->channel){
        	$this->channel = $this->amqp->channel();
        }
        register_shutdown_function(array($this, 'shutdown'));
        
        if($this->exchange){
        	$this->channel->exchange_declare($this->exchange, $this->exchangeType, false, false, false);
        }
    }
    
    public function shutdown(){
    	$this->channel->close();
    	$this->amqp->close();
    }
    
    /**
     * Destructor
     */
    public function __destruct(){
    	$this->shutdown();
    }
    
    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
    	if(!$this->exchange){
    		$this->channel->queue_declare($queue, false, true, false, false);
    		if($payload['route_key']){
    			$this->routing_key = $payload['route_key'];
    			unset($payload['route_key']);
    		}
    	}else{
    		$this->routing_key = $queue;
    	}
    	
    	if(is_array($payload['body']) || is_object($payload['body'])){
        	$payload['body'] = Json::encode($payload['body']);
    	}
    	$msg_body = $payload['body'];
    	unset($payload['body']);
        
        $msg = new AMQPMessage($msg_body,$payload);
        
        $this->channel->basic_publish($msg, $this->exchange,$this->routing_key);
        return $msg;
    }
    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        if(!$this->exchange){
    		$this->channel->queue_declare($queue, false, true, false, false);
    	}else{
    		list($queue_name, ,) = $this->channel->queue_declare("", false, false, true, false);
    		$this->channel->queue_bind($queue_name, $this->exchange, $queue);
    		$queue = $queue_name;
    	}
    	
    	$this->channel->basic_qos(null, 1, null);
        return $this->channel->basic_get($queue);
    }
    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $this->channel->queue_purge($queue);
    }
    /**
     * @inheritdoc
     */
    public function release(array $message, $delay = 0)
    {   
        foreach ($message as $msg){
        	$this->channel->basic_ack($msg->delivery_info['delivery_tag']);
    	}
    }
    /**
     * @inheritdoc
     */
    public function delete(array $message)
    {
    	foreach ($message as $msg){
         	$this->channel->basic_cancel($msg->delivery_info['consumer_tag']);
    	}
    }
}