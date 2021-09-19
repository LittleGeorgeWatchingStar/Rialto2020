<?php

namespace Rialto\Purchasing\Quotation\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Company\Company;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Purchasing\Quotation\Email\RequestForQuote;
use Rialto\Purchasing\Quotation\QuotationRequest;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use SplObjectStorage;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user manually creates a quotation request for a single item.
 */
class ManualQuotationRequest
{
    /** @var User */
    private $requestedBy;

    /**
     * @var ItemVersion  The item version for which to get a quote.
     */
    private $version;

    /**
     * @var SupplierContact[]
     * @Assert\Count(min=1, minMessage="Please choose at least one contact.")
     */
    public $contacts;

    /**
     * @var string
     */
    public $comments = '';

    /**
     * @var Customization|null
     * @Assert\Valid
     */
    public $customization = null;

    /**
     * @var int[]
     */
    public $quantities = [];

    /**
     * @var int[]
     */
    public $leadTimes = [];

    public function __construct(User $requestedBy, ItemVersion $version)
    {
        $this->requestedBy = $requestedBy;
        $this->version = $version;
        assertion($version->getStockItem()->isPhysicalPart());
        $this->contacts = new ArrayCollection();
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->version->getStockItem();
    }

    public function getStockCategory()
    {
        return $this->getStockItem()->getCategory();
    }

    public function createRequests()
    {
        $requests = [];
        foreach ($this->getSuppliers() as $supplier) {
            $requests[] = $this->createRequest($supplier);
        }
        return $requests;
    }

    /** @return Supplier[] */
    private function getSuppliers()
    {
        $set = new SplObjectStorage();
        foreach ($this->contacts as $c) {
            $supplier = $c->getSupplier();
            $set->attach($supplier);
        }
        return iterator_to_array($set);
    }

    private function createRequest(Supplier $supplier)
    {
        $request = new QuotationRequest($this->requestedBy, $supplier);
        $request->setComments($this->comments);
        $rItem = $request->createItem($this->getStockItem());
        $rItem->setVersion($this->version);
        $rItem->setCustomization($this->customization);
        $rItem->setQuantities($this->quantities);
        $rItem->setLeadTimes($this->leadTimes);
        return $request;
    }

    /**
     * @return RequestForQuote[]
     */
    public function createEmails(array $requests, Company $company)
    {
        return array_map(function(QuotationRequest $rfq) use ($company) {
            return $this->createEmail($rfq, $company);
        }, $requests);
    }

    private function createEmail(QuotationRequest $rfq, Company $company)
    {
        $contacts = $this->getContactsForSupplier($rfq->getSupplier());
        $email = new RequestForQuote($rfq, $company);
        $email->setTo($contacts);
        return $email;
    }

    private function getContactsForSupplier(Supplier $supplier)
    {
        return $this->contacts->filter(function(SupplierContact $c) use ($supplier) {
            return $c->isForSupplier($supplier);
        })->getValues();
    }
}
