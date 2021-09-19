<?php


namespace Infrastructure\CommandBus;


use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rialto\Port\CommandBus\Command;
use Rialto\Port\CommandBus\CommandQueue;
use JMS\JobQueueBundle\Entity\Job;
use Rialto\Port\CommandBus\HandleCommandConsoleCommand;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * A CommandQueue that leverages JmsJobQueue to store and execute queued Commands.
 */
final class JmsCommandQueue implements CommandQueue
{

    /** @var SerializerInterface */
    private $serializer;

    /** @var EntityManagerInterface */
    private $em;

    /** @var EntityRepository */
    private $jobRepository;

    public function __construct(SerializerInterface $serializer,
                                EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->jobRepository = $em->getRepository(Job::class);
    }

    public function queue(Command $command, bool $flush = true): ?int
    {
        $job = new Job(HandleCommandConsoleCommand::NAME,
            [
                get_class($command),
                $this->serializer->serialize($command, 'json'),
            ]);

        $this->em->persist($job);

        if ($flush) {
            $this->em->flush();
        }

        return $job->getId();
    }

    public function findRecentJobForCommand(Command $command): ?Job
    {
        return $this->jobRepository->createQueryBuilder('job')
            ->orderBy('job.id', 'DESC')
            ->andWhere('job.command = :command')
            ->setParameter('command', HandleCommandConsoleCommand::NAME)
            ->andWhere('job.args = :args')
            ->setParameter('args', [
                get_class($command),
                $this->serializer->serialize($command, 'json'),
            ], Type::JSON_ARRAY)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
