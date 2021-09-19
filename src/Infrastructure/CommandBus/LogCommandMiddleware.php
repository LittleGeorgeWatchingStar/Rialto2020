<?php


namespace Infrastructure\CommandBus;


use League\Tactician\Middleware;
use Psr\Log\LoggerInterface;
use Rialto\Security\User\UserManager;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Logs all incoming commands on the bus.
 */
final class LogCommandMiddleware implements Middleware
{
    /**
     * Commands that run faster than this threshold will not be logged.
     */
    const THRESHOLD = 1; // seconds

    /** @var LoggerInterface */
    private $logger;

    /** @var SerializerInterface */
    private $serializer;

    /** @var UserManager */
    private $userManager;

    public function __construct(LoggerInterface $logger,
                                SerializerInterface $serializer,
                                UserManager $userManager)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->userManager = $userManager;
    }

    public function execute($command, callable $next)
    {
        $start = microtime(true);
        $returnValue = $next($command);
        $elapsed = microtime(true) - $start;

        $elapsedFormat = number_format($elapsed, 4);

        if ($elapsed > self::THRESHOLD) {
            $this->logCommand($command, $elapsedFormat);
        }

        return $returnValue;
    }

    private function logCommand($command, float $elapsed): void
    {
        $name = get_class($command);
        $args = $this->serializer->serialize($command, 'json');

        $message = "Command $name with args $args took $elapsed seconds to run.";

        $context = [
            'command' => $name,
        ];

        $user = $this->userManager->getUserOrNull();
        if ($user) {
            $context['username'] = $user->getUsername();
        }

        $this->logger->notice($message, $context);
    }
}
