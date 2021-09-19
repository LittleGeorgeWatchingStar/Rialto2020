<?php


namespace Rialto\Port\CommandBus;

/**
 * The Command interface has no functional value but is useful because we can
 * mark and typehint for the explicit usage of a class that is meant to serve
 * as a data transfer object representing an intent to modify state.
 */
interface Command
{
}