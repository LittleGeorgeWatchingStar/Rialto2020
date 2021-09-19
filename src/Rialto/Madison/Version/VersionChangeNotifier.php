<?php

namespace Rialto\Madison\Version;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;
use Rialto\Madison\MadisonClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Notifies Madison of stock items whose shipping version has changed.
 */
class VersionChangeNotifier implements EventSubscriberInterface
{
    /**
     * @var MadisonClient
     */
    private $client;

    /**
     * @var VersionChangeCache
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MadisonClient $client,
                                VersionChangeCache $cache,
                                LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'notifyMadison',
        ];
    }

    /**
     * When the request terminates, notify Madison that the shipping version
     * has changed.
     */
    public function notifyMadison(PostResponseEvent $event)
    {
        $changed = $this->cache->getItems();
        foreach ($changed as $item) {
            try {
                $this->client->updateCurrentVersion($item);
            } catch (ClientException $exception) {
                if ($exception->getCode() != 404) {
                    $this->logger->warning($this->getLogMessage($exception));
                }
            } catch (TransferException $exception) {
                $this->logger->error($this->getLogMessage($exception));
            }
        }
        $this->cache->clear();
    }

    private function getLogMessage(TransferException $exception)
    {
        $code = $exception->getCode();
        $msg = $exception->getMessage();
        return "Madison returned $code: $msg";
    }
}
