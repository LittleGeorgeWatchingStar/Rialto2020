<?php


namespace Rialto\Stock\Item\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Bom\Orm\BomItemRepository;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;

/**
 * Listen to doctrine events on the default work type of a stock item.
 */
final class DefaultWorkTypeListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof StockItem) { continue; }

            $changeset = $uow->getEntityChangeSet($entity);
            if ($workType = $this->getNewDefaultWorkType($changeset)) {
                if (in_array($workType->getId(), self::mountingSubTypes())) {
                    $this->cascadeChangeToBomItems($em, $entity, $workType);
                }
            }
        }
    }

    /**
     * @param array $changeset
     * @return WorkType|null The new default work type, null if not changed.
     */
    private function getNewDefaultWorkType(array $changeset)
    {
        if ($change = $changeset['defaultWorkType'] ?? null) {
            // TODO: PHP7.1 List Unpacking
            $pre = $change[0] ?? null;
            $post = $change[1] ?? null;

            if ($pre != $post) {
                return $post;
            } else {
                return null;
            }
        }

        return null;
    }

    private function cascadeChangeToBomItems(EntityManagerInterface $em,
                                             StockItem $item,
                                             WorkType $type)
    {
        /** @var BomItemRepository $bomItemRepo */
        $bomItemRepo = $em->getRepository(BomItem::class);

        // Only change the work type if it corresponds to a mounting type.
        // TODO: Reflect these semantics in the StockItem schema.
        //  SMT and Through-Hole are children of a generic 'Mount' work type.
        /** @var BomItem[] $bomItems */
        $bomItems = $bomItemRepo->findBy([
            'component' => $item->getSku(),
            'workType' => self::mountingSubTypes(),
        ]);

        $metaData = $em->getClassMetadata(BomItem::class);
        foreach ($bomItems as $bomItem) {
            $bomItem->setWorkType($type);
            $em->persist($bomItem);
            $em->getUnitOfWork()->computeChangeSet($metaData, $bomItem);
        }

        $em->getUnitOfWork()->computeChangeSets();

    }

    private static function mountingSubTypes(): array
    {
        return [WorkType::SMT, WorkType::THROUGH_HOLE];
    }
}
