<?php
namespace CreditJeeves\TestBundle\Swift;

use JMS\DiExtraBundle\Annotation as DI;
use \Swift_Transport_MailInvoker;
use \Swift_Events_EventDispatcher;
use \Swift_Events_EventListener;
use \Swift_Mime_Message;
use \Swift_Transport;

/**
 * @DI\Service("swiftmailer.transport.file_transport")
 */
class FileTransport implements Swift_Transport
{
    /**
     * The event dispatcher from the plugin API
     *
     * @var Swift_Events_EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Not in use
     *
     * @var An invoker that calls the mail() function
     */
    private $invoker;

    /**
     * Create a new MailTransport with the $log.
     *
     * @DI\InjectParams({
     *     "invoker" = @DI\Inject("swiftmailer.transport.mailinvoker"),
     *     "eventDispatcher" = @DI\Inject("swiftmailer.transport.eventdispatcher")
     * })
     */
    public function __construct(Swift_Transport_MailInvoker $invoker, Swift_Events_EventDispatcher $eventDispatcher)
    {
        $this->invoker = $invoker;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Not used.
     */
    public function isStarted()
    {
        return false;
    }

    /**
     * Not used.
     */
    public function start()
    {
    }

    /**
     * Not used.
     */
    public function stop()
    {
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retreived from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[] &$failedRecipients to collect failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array)$failedRecipients;

        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $count = (
            count((array)$message->getTo())
                + count((array)$message->getCc())
                + count((array)$message->getBcc())
        );

        $toHeader = $message->getHeaders()->get('To');
        $subjectHeader = $message->getHeaders()->get('Subject');

        $to = $toHeader->getFieldBody();
        $subject = $subjectHeader->getFieldBody();

        $reversePath = $this->getReversePath($message);

        //Remove headers that would otherwise be duplicated
        $message->getHeaders()->remove('Subject');

        $messageStr = $message->toString();

        $message->getHeaders()->set($toHeader);
        $message->getHeaders()->set($subjectHeader);

        //Separate headers from body
        if (false !== $endHeaders = strpos($messageStr, "\r\n\r\n")) {
            $headers = substr($messageStr, 0, $endHeaders) . "\r\n"; //Keep last EOL
            $body = substr($messageStr, $endHeaders + 4);
        } else {
            $headers = $messageStr . "\r\n";
            $body = '';
        }

        unset($messageStr);

        if ("\r\n" != PHP_EOL) { //Non-windows (not using SMTP)
            $headers = str_replace("\r\n", PHP_EOL, $headers);
            $body = str_replace("\r\n", PHP_EOL, $body);
        } else { //Windows, using SMTP
            $headers = str_replace("\r\n.", "\r\n..", $headers);
            $body = str_replace("\r\n.", "\r\n..", $body);
        }

        $dir = realpath(__DIR__ . '/../../../..') . '/vendor/credit-jeeves/credit-jeeves/cache/mail';

        if (!is_dir($dir)) {
            $path = '';
            foreach (explode(DIRECTORY_SEPARATOR, substr($dir, 1)) as $pathDir) {
                $path .= DIRECTORY_SEPARATOR . $pathDir;
                if (!is_dir($path)) {
                    @mkdir($path);
                    @chmod($path, 0777);
                }
            }
        }

        $base = $dir . DIRECTORY_SEPARATOR . date('Y-m-d_H:i:s');
        $i = 1;
        while (true) {
            $filename = "{$base}-{$i}.eml";
            if (is_file($filename)) {
                $i++;
            } else {
                break;
            }
        }

        $fout = fopen($filename, "w");
        if (!$fout) {
            throw new \Exception("Cant open file {$filename} for writing");
        }

        fwrite($fout, $headers);
        fwrite($fout, "Subject: " . $subject);
        fwrite($fout, "\n\n");
        fwrite($fout, $body);

        fclose($fout);

        @chmod($filename, 0777);

        return $count;
    }

    /**
     * Register a plugin.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }

    // -- Private methods

    /**
     * Determine the best-use reverse path for this message
     *
     * @param Swift_Mime_Message $message
     *
     * @return string
     */
    private function getReversePath(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            $keys = array_keys($sender);
            $path = array_shift($keys);
        } elseif (!empty($from)) {
            $keys = array_keys($from);
            $path = array_shift($keys);
        }

        return $path;
    }
}
