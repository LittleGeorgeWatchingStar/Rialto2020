<?php

namespace Rialto\Printing\Printer\Cli;



use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Simulates the live printserver by listening for incoming printer
 * connections. Use this to test the printers during development.
 */
class DevPrintServer extends Command
{
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 9100;

    /** @var string|null */
    private $outfile = null;

    protected function configure()
    {
        $this->setName('print:dev-server')
            ->setDescription("Run a fake printserver for development")
            ->addArgument('port', InputArgument::OPTIONAL,
                'Which port to listen on', self::DEFAULT_PORT)
            ->addOption('outfile', 'o', InputOption::VALUE_OPTIONAL,
                'write output to this file', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->outfile = $input->getOption('outfile');

        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $host = self::DEFAULT_HOST;
        $port = (int) $input->getArgument('port');
        socket_bind($sock, $host, $port);
        socket_listen($sock);
        $io->note("Listening on $host:$port...");
        do {
            $msgsock = socket_accept($sock);
            $io->section("Incoming connection...");
            $this->clearOutfile();
            $reading = true;
            $numBytes = 0;
            while ($reading) {
                $data = socket_read($msgsock, 2048);
                $reading = $reading && (bool) $data;
                $this->writeData($data);
                $numBytes += strlen($data);
            }
            socket_close($msgsock);
            $io->success("Connection closed; read $numBytes bytes.");
        } while (true);
        socket_close($sock);
    }

    private function clearOutfile()
    {
        if ($this->outfile) {
            file_put_contents($this->outfile, '');
        }
    }

    private function writeData($data)
    {
        if ($this->outfile) {
            file_put_contents($this->outfile, $data, FILE_APPEND | FILE_BINARY);
        }
    }
}
