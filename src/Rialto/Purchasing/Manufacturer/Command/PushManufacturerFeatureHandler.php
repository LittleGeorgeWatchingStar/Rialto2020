<?php


namespace Rialto\Purchasing\Manufacturer\Command;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Madison\Feature\StockItemFeatureCalculator;
use Rialto\Madison\MadisonClient;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * Handler service for @see PushManufacturerFeatureCommand.
 */
final class PushManufacturerFeatureHandler
{
    /** @var StockItemRepository */
    private $repo;

    /** @var StockItemFeatureCalculator */
    private $featureCalculator;

    /** @var MadisonClient */
    private $madisonClient;

    public function __construct(EntityManagerInterface $em,
                                StockItemFeatureCalculator $calculator,
                                MadisonClient $client)
    {
        $this->repo = $em->getRepository(StockItem::class);
        $this->featureCalculator = $calculator;
        $this->madisonClient = $client;
    }

    public function handle(PushManufacturerFeatureCommand $command)
    {
        $payload = [];

        /** @var StockItem[] $modules */
        $modules = $this->repo->findBy([
            'stockCode' => $command->getModuleIds(),
        ]);

        foreach ($modules as $module) {
            if ($this->hasEmptyBom($module)) {
                continue;
            }
            $feature = $this->featureCalculator
                ->getFeaturesWithCode('manufacturer', $module, Version::any())[0] ?? null;
            if ($feature) {
                $payload[] = [
                    'sku' => $module->getSku(),
                    'manufacturer' => $feature->getValue(),
                ];
            }
        }

        $this->madisonClient->pushManufacturerFeatures($payload);
    }

    private function hasEmptyBom(StockItem $item): bool
    {
        if ($bom = $item->getAutoBuildVersion()->getBom()) {
            return $bom->isEmpty();
        }
        return true;
    }
}
