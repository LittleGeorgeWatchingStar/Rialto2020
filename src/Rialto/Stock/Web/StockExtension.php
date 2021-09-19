<?php

namespace Rialto\Stock\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Item;
use Rialto\Stock\Location;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\VersionedItem;
use Rialto\Web\EntityLinkExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Twig extensions for the stock subsystem.
 */
class StockExtension extends EntityLinkExtension
{
    /** @var ObjectManager */
    private $om;

    /** @var StockRouter */
    private $router;

    public function __construct(ObjectManager $om,
                                StockRouter $router,
                                AuthorizationCheckerInterface $auth)
    {
        parent::__construct($auth);
        $this->om = $om;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('stock_hq', 'stockHeadquarters', []),
            $this->simpleFunction('item_link', 'itemLink', ['html']),
            $this->simpleFunction('versioned_item_link', 'versionedItemLink', ['html']),
            $this->simpleFunction('stock_bin_link', 'binLink', ['html']),
            $this->simpleFunction('stock_location_link', 'locationLink', ['html']),
            $this->simpleFunction('bin_transaction_origin_link', 'transactionOriginLink',  ['html']),
            /* @deprecated */
            $this->simpleFunction('rialto_stock_location_selector', 'locationSelector', ['html']),
        ];
    }

    public function transactionOriginLink(StockBin $bin)
    {
        $stockMoveRepo = $this->om->getRepository(StockMove::class);
        $moveFilters = [
            'bin' => $bin->getId(),
            'showTransit' => 'yes',
            '_limit' => 0, // fetch all stock moves for this bin
        ];
        $stockMoveOrigin = new EntityList($stockMoveRepo, $moveFilters);

        if ($stockMoveOrigin->total() == 0) {
            return '';
        }

        /** @var StockMove $event */
        $event = $stockMoveOrigin->first(); // use only the first of Stock Move
        $sysType = $event->getSystemType();
        $typeNo = $event->getSystemTypeNumber();
        if ($sysType->getName() === 'Credit Note') {
            $label = 'Return Material Auth '.$typeNo;
        } else {
            $label = $sysType->getName() .' '. $typeNo;
        }

        $trans = $this->om->getRepository(Transaction::class)
            ->findOneBy(['systemType' => $sysType, 'groupNo' => $typeNo]);

        return $this->transactionLink($trans, $label);
    }

    private function transactionLink(Transaction $trans = null, $label = null)
    {
        if (!$trans) {
            return $this->none();
        }
        $label = $label ?: $trans->getId();
        $url = $this->router->transactionView($trans);
        return $this->link($url, $label);
    }

    /**
     * Returns the stock headquarters facility.
     *
     * @return Facility
     */
    public function stockHeadquarters()
    {
        return Facility::fetchHeadquarters($this->om);
    }

    public function itemLink(Item $item = null, $label = null)
    {
        if (!($item && $item->getSku())) {
            return $this->none();
        }
        $url = $this->router->itemView($item);
        $label = $label ?: $item->getSku();
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label, "_top");
    }

    public function versionedItemLink(VersionedItem $item = null, $label = null)
    {
        if (!$item) {
            return $this->none();
        }
        $label = $label ?: $item->getFullSku();
        return $this->itemLink($item, $label);
    }

    public function binLink(StockBin $bin = null, $label = null)
    {
        if (!$bin) {
            return $this->none();
        }
        $label = $label ?: trim($bin);
        $url = $this->router->binView($bin);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label, "_top");
    }

    public function locationLink(Location $location = null, $label = null)
    {
        if (!$location) {
            return $this->none();
        }
        $label = $label ?: (string) $location;
        $url = $location instanceof Facility
            ? $this->router->facilityView($location)
            : $this->router->transferView($location);
        return $this->linkIfGranted(Role::EMPLOYEE, $url, $label);
    }

    /** @deprecated */
    public function locationSelector($data = null, $name = 'location', $id = null, $allowAll = false)
    {
        if ($data instanceof Request) {
            $data = $data->get($name);
        } elseif (is_array($data)) {
            $data = isset($data[$name]) ? $data[$name] : null;
        }


        if (!$id) $id = $name;
        /** @var $repo FacilityRepository */
        $repo = $this->om->getRepository(Facility::class);
        $locations = $repo->findActive();

        $output = "<select name=\"$name\" id=\"$id\">";
        if ($allowAll) {
            $selected = (!$data) ? 'selected' : '';
            $output .= "<option value=\"\" $selected>-- all --</option>";
        }
        foreach ($locations as $location) {
            $currentId = $location->getId();
            $selected = ($data == $currentId) ? 'selected' : '';
            $output .= "<option value=\"$currentId\" $selected>";
            $output .= htmlentities($location->getName());
            $output .= "</option>";
        }
        $output .= "</select>";
        return $output;
    }
}
