<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace mithun\queue\services;
use Aws\Sqs\SqsClient;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * SqsQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class SqsQueue extends Component implements QueueInterface
{
    /**
     * @var SqsClient
     */
    public $sqs;
    
    /**
     * @var array
     */
    public $config;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->config === null) {
            throw new InvalidConfigException('The "config" property must be set.');
        }
        if (!$this->sqs instanceof SqsClient) {
            $this->sqs = new SqsClient($this->config);
        }
    }
    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        return $this->sqs->sendMessage([
            'QueueUrl' => $queue,
            'MessageBody' => is_string($payload) ? $payload : Json::encode($payload),
            'DelaySeconds' => $delay,
        ])->get('MessageId');
    }
    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        $response = $this->sqs->receiveMessage(['QueueUrl' => $queue]);
        if (empty($response['Messages'])) {
            return false;
        }
        $data = reset($response['Messages']);
        return [
            'id' => $data['MessageId'],
            'body' => $data['Body'],
            'queue' => $queue,
            'receipt-handle' => $data['ReceiptHandle'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $this->sqs->purgeQueue(['QueueUrl' => $queue]);
    }
    /**
     * @inheritdoc
     */
    public function release(array $message, $delay = 0)
    {
        $this->sqs->changeMessageVisibility([
            'QueueUrl' => $message['queue'],
            'ReceiptHandle' => $message['receipt-handle'],
            'VisibilityTimeout' => $delay,
        ]);
    }
    /**
     * @inheritdoc
     */
    public function delete(array $message)
    {
        $this->sqs->deleteMessage([
            'QueueUrl' => $message['queue'],
            'ReceiptHandle' => $message['receipt-handle'],
        ]);
    }
}