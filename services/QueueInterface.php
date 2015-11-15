<?php
/**
 * 
 */
namespace mithun\queue\services;
/**
 * QueueInterface
 *
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
interface QueueInterface
{
    /**
     * Pushs payload to the queue.
     *
     * @param mixed $payload
     * @param integer $delay
     * @param string $queue
     * @return string
     */
    public function push($payload, $queue, $delay = 0);
    /**
     * Pops message from the queue.
     *
     * @param string $queue
     * @return array|false
     */
    public function pop($queue);
    /**
     * Purges the queue.
     *
     * @param string $queue
     */
    public function purge($queue);
    /**
     * Releases the message.
     *
     * @param array $message
     * @param integer $delay
     */
    public function release(array $message, $delay = 0);
    /**
     * Deletes the message.
     *
     * @param array $message
     */
    public function delete(array $message);
}