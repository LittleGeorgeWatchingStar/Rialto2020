<?php

namespace Rialto\Manufacturing\WorkOrder;

use Gumstix\Storage\FileStorage;
use Rialto\Filesystem\FilesystemException;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\Filesystem\FlashFilesystem;

/**
 * Creates the build instructions PDF for a work order.
 */
class WorkOrderPdfGenerator
{
    /** @var PdfGenerator */
    private $generator;

    /** @var FileStorage */
    private $storage;

    /** @var FlashFilesystem */
    private $flashFS;

    public function __construct(
        PdfGenerator $generator,
        FileStorage $storage,
        FlashFilesystem $shareFS)
    {
        $this->generator = $generator;
        $this->storage = $storage;
        $this->flashFS = $shareFS;
    }

    /**
     * @return string
     *  The PDF data.
     */
    public function getPdf(WorkOrder $wo)
    {
        $ins = new WorkOrderInstructions($wo);
        return $ins->isFlashBuild() ?
            $this->createFlashPdf($ins) :
            $this->createBoardPdf($ins);
    }

    /**
     * @return string
     *  The PDF data.
     */
    private function createFlashPdf(WorkOrderInstructions $ins)
    {
        $wo = $ins->getWorkOrder();
        $codeInstructions = $this->flashFS->getInstructions($wo->getVersion());
        return $this->generator->render(
            'manufacturing/work-order/instructions/flash.tex.twig', [
            'workOrder' => $wo,
            'instructions' => $ins,
            'codeInstructions' => $codeInstructions,
        ]);
    }

    /**
     * @return string The PDF data
     */
    private function createBoardPdf(WorkOrderInstructions $ins)
    {
        $wo = $ins->getWorkOrder();
        $components = $wo->getAllComponents();
        $fab = $ins->getFabComponent();
//        $images = $fab ? $this->getPcbImages($fab) : [];
        // TODO: Large images cause pdflatex to take several minutes to complete, killing server performance.
        $images = [];

        $pdfData = $this->generator->render(
            'manufacturing/work-order/instructions/pdf.tex.twig', [
            'workOrder' => $wo,
            'components' => $components,
            'images' => $images,
            'instructions' => $ins,
        ]);
        $this->removeTempFiles($images);
        return $pdfData;
    }

    /** @return \SplFileInfo[] */
    private function getPcbImages(Requirement $fab)
    {
        $files = [
            PcbBuildFiles::IMAGE_TOP,
            PcbBuildFiles::IMAGE_BOTTOM,
        ];
        $images = [];
        $buildFiles = BuildFiles::create(
            $fab->getStockItem(),
            $fab->getVersion(),
            $this->storage);
        foreach ($files as $filename) {
            if ($buildFiles->exists($filename)) {
                $imageData = $buildFiles->getContents($filename);
                $filepath = $buildFiles->getFilepath($filename);
                $images[] = $this->createTempFile($filepath, $imageData);
            }
        }
        return $images;
    }

    private function createTempFile($filename, $filedata, $type = FILE_BINARY)
    {
        $tempfile = join(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            uniqid() . basename($filename)
        ]);
        if (! file_put_contents($tempfile, $filedata, $type)) {
            throw new FilesystemException($tempfile, 'unable to write');
        }
        return new \SplFileInfo($tempfile);
    }

    /**
     * @param \SplFileInfo[] $files
     */
    private function removeTempFiles(array $files)
    {
        foreach ($files as $file) {
            unlink($file->getRealPath());
        }
    }
}
