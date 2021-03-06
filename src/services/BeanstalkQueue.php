<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace mithun\queue\services;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Job;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * RedisQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class BeanstalkQueue extends Component implements QueueInterface
{
    /**
     * @var Pheanstalk
     */
    public $beanstalk;
    
    /**
     * Configuration
     * @var array
     */
    public $config = [
    		'host' => '127.0.0.1',
    		'port' => 11300,
    		'timeout' => null,
    		'Persistent' => FALSE,
    ];
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
        if ($this->config === null) {
            throw new InvalidConfigException('The "config" property must be set.');
        }
        if (!$this->beanstalk instanceof Pheanstalk) {
            $this->beanstalk = new Pheanstalk(
            						$this->config['host'],
            						$this->config['port'],
            						$this->config['timeout'],
            						$this->config['Persistent']
            					);
        }
    }
    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = PheanstalkInterface::DEFAULT_DELAY)
    {
    	$priority = PheanstalkInterface::DEFAULT_PRIORITY;
    	$ttr = PheanstalkInterface::DEFAULT_TTR;
    	if(is_array($payload) && isset($payload['priority'])){
    		$priority = $payload['priority'];
    		unset($payload['priority']);
    	}
    	
    	if(is_array($payload) && isset($payload['ttr'])){
    		$ttr = $payload['ttr'];
    		unset($payload['ttr']);
    	}
    	
        $payload = Json::encode(['id' => $id = md5(uniqid('', true)), 'body' => $payload]);
        $this->beanstalk->useTube($queue);
        $this->beanstalk->put(
        		$payload,
        		$priority,
        		$delay,
        		$ttr
        );
        return $id;
    }
    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
    	
    	$job = $this->beanstalk->watch($queue)
    					->ignore('default')
    					->reserve();
    	if($job instanceof Job){
	    	$data = $job->getData();
	        $data = Json::decode($data);
	        return [
	            'id' => $data['id'],
	            'body' => $data['body'],
	            'queue' => $queue,
	        	'message' => $job,
	        ];
    	}else{
    		return false;
    	}
    }
    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $stat = $this->beanstalk->statsTube($queue);
        $this->beanstalk->watch($queue);
        for($i=0; $i<$stat->current_jobs_ready; $i++){
        	$job = $this->beanstalk->peekReady();
        	$this->beanstalk->delete($job);
        }
        
        for($i=0; $i<$stat->current_jobs_delayed; $i++){
        	$job = $this->beanstalk->peekDelayed();
        	$this->beanstalk->delete($job);
        }
        
        for($i=0; $i<$stat->current_jobs_buried; $i++){
        	$job = $this->beanstalk->peekBuried();
        	$this->beanstalk->delete($job);
        }
    }
    /**
     * @inheritdoc
     */
    public function release(array $message, $delay = 0)
    {
    	$priority = PheanstalkInterface::DEFAULT_PRIORITY;
    	$ttr = PheanstalkInterface::DEFAULT_TTR;
    	if(isset($message['priority'])){
    		$priority = $message['priority'];
    		unset($message['priority']);
    	}
    	 
    	if(isset($message['ttr'])){
    		$ttr = $message['ttr'];
    		unset($message['ttr']);
    	}
    	
    	
        $this->beanstalk->release($message,$priority,$delay);
    }
    /**
     * @inheritdoc
     */
    public function delete(array $message)
    {
        $this->beanstalk->delete($message['message']);
    }
    
    /**
     *
     * @return number
     */
    public function is_message($queue = ''){
    	try{
    	if($queue){
    		$result = $this->beanstalk->statsTube($queue);
    	}else{
    		$result = $this->beanstalk->stats();
    	}
    	
    	
    	$messageCount = 0;
    	
    	foreach ($result as $key => $val){
    		if(in_array($key, ['current-jobs-urgent', 'current-jobs-ready', 'current-jobs-reserved', 'current-jobs-delayed'])){
    			$messageCount += $val;
    		}
    	}
    	
    	return $messageCount;
    	}catch (ServerException $e){
    		return 1;
    	}
    }
}