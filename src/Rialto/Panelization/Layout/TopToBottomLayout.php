<?php

namespace Rialto\Panelization\Layout;

use Rialto\Panelization\Panel;
use Rialto\Panelization\PlacedBoard;
use Rialto\Panelization\Pose;


/**
 * Lays out boards along the left edge, top to bottom.
 */
class TopToBottomLayout implements Layout
{
    /**
     * @param PlacedBoard[] $boards
     */
    public function placeBoards(Panel $panel, array $boards)
    {
        $margin = $panel->getMargin() + 0.001; // Add wiggle room to prevent rounding errors
        /**
         * all the boards y size + all the margin
         * @var float
         */
        $totalYofBoards = 0.0;
        foreach ($boards as $board) {
            $boardSize = $board->getDimensions();
            if (! $boardSize) {
                throw new \UnexpectedValueException("$board has no dimensions");
            }

            $totalYofBoards = $totalYofBoards + $boardSize->getY() + $margin;
        }
        if ($totalYofBoards > $panel->getHeight()) {
            $panel->setHeight($totalYofBoards);
        }

        $panelSize = $panel->getSize();
        $xPos = $margin;
        $yPos = $panelSize->getY(); // top of the panel
        $rotation = 0;
        foreach ($boards as $board) {
            $boardSize = $board->getDimensions();

            $yPos = $yPos - $boardSize->getY() - $margin;

            $position = new Pose($xPos, $yPos, $rotation);
            $panel->addBoard($board, $position);
        }
    }
}
