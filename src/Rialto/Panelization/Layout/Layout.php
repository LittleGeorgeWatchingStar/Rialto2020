<?php


namespace Rialto\Panelization\Layout;

use Rialto\Panelization\Panel;
use Rialto\Panelization\PlacedBoard;

/**
 * A strategy for laying out boards on one or more Panels.
 */
interface Layout
{
    /**
     * Positions the boards and adds them to the panel.
     *
     * @param PlacedBoard[] $boards
     */
    public function placeBoards(Panel $panel, array $boards);
}
