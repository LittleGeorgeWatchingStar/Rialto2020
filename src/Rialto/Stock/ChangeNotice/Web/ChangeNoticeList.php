<?php

namespace Rialto\Stock\ChangeNotice\Web;

use InvalidArgumentException;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\ChangeNotice\ChangeNotice;
use Rialto\Stock\ChangeNotice\ChangeNoticeEvent;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Allows you to create a new change notice, select existing ones, and
 * link them to a stock item or version.
 */
class ChangeNoticeList
{
    /** @var ChangeNotice[] */
    private $existingNotices = [];

    /** @var ChangeNotice|null */
    private $newNotice = null;


    public function getExistingNotices()
    {
        return $this->existingNotices;
    }

    public function addExistingNotice(ChangeNotice $notice)
    {
        $this->existingNotices[] = $notice;
    }

    public function removeExistingNotice(ChangeNotice $notice)
    {
        $i = array_search($notice, $this->existingNotices);
        if ( false !== $i ) {
            unset($this->existingNotices[$i]);
        }
    }

    public function getNewNotice()
    {
        return $this->newNotice;
    }

    public function setNewNotice(ChangeNotice $newNotice = null)
    {
        $this->newNotice = $newNotice;
    }

    /** @return ChangeNotice[] */
    public function getNotices(Item $item)
    {
        $notices = $this->existingNotices;
        if ( isset($this->newNotice) ) {
            $notices[] = $this->newNotice;
        }
        $notices = array_filter($notices, function(ChangeNotice $notice) {
            return $notice->getDescription() != ''; // remove empty notices
        });

        foreach ( $notices as $notice ) {
            assert($notice instanceof ChangeNotice);
            assert($notice->getDescription() != '');
            if ( $item instanceof ItemVersion ) {
                $notice->addVersion($item);
            } elseif ( $item instanceof StockItem ) {
                $notice->addStockItem($item);
            } else {
                throw new InvalidArgumentException("Wrong type ". get_class($item));
            }
        }
        return $notices;
    }

    public function persistNotices(array $notices, DbManager $dbm, EventDispatcherInterface $dispatcher)
    {
        foreach ( $notices as $notice ) {
            $event = new ChangeNoticeEvent($notice);
            $dispatcher->dispatch(StockEvents::CHANGE_NOTICE, $event);
            $dbm->persist($notice);
        }
    }

}
