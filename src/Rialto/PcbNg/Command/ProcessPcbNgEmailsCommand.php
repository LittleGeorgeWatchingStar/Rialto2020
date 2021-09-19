<?php

namespace Rialto\PcbNg\Command;


use Rialto\Port\CommandBus\Command;

/**
 * Lookup any ordering/manufacturing events PCB:NG has sent us via email and
 * forward them to relevant systems.
 */
final class ProcessPcbNgEmailsCommand implements Command
{
}
