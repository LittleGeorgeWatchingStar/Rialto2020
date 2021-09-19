<?php

namespace Rialto\Panelization;


/**
 * The combined, translated position data for all components in a Panel.
 */
class ConsolidatedXY
{
    /**
     * @var float[] Position data indexed by reference designator.
     */
    private $index = [];

    /**
     * @param string|float[] $row The raw row from the XY csv file.
     * @param Pose $boardPos The pose of the PlacedBoard, which
     *   determines how to translate the pose of each of its components.
     * @param string $boardId The identifier of the placed board, to
     *   distinguish its components from those of other boards in the Panel.
     */
    public function addRow(array $row, Pose $boardPos, $boardId)
    {
        if ($this->isHeaderRow($row)) {
            return;
        }
        list($designator, $componentPos, $side, $package) = $this->decodeRow($row);
        $key = "$designator-$boardId";
        $panelPos = $boardPos->transform($componentPos);
        $this->index[$key] = [
            'key' => $key,
            'designator' => $designator,
            'x' => $panelPos->getX(),
            'y' => $panelPos->getY(),
            'side' => $side,
            'rotation' => $panelPos->getRotation(),
            'package' => $package,
        ];
    }

    private function isHeaderRow(array $row)
    {
        list($designator, $x, $y, $side, $rotation) = $row;
        return ! is_numeric($x);
    }

    private function decodeRow(array $row)
    {
        if (count($row) == 5) {
            $row[] = ''; // If package is missing, add an empty one.
        }
        list($designator, $x, $y, $side, $rotation, $package) = $row;
        assertion(is_numeric($x));
        assertion(is_numeric($y));
        assertion(is_numeric($rotation));

        return [
            $designator,
            new Pose($x, $y, $rotation),
            $side,
            $package,
        ];
    }

    public function toArray()
    {
        return $this->index;
    }

}
