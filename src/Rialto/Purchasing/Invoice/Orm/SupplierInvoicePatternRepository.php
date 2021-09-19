<?php

namespace Rialto\Purchasing\Invoice\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Purchasing\Invoice\Reader\Email\SupplierEmail;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Supplier\Attribute\SupplierAttribute;
use Zend\Mail\Exception\ExceptionInterface as MailException;

class SupplierInvoicePatternRepository extends RialtoRepositoryAbstract
{
    /**
     * Returns the invoice pattern that matches the email, if any exists.
     *
     * @return SupplierInvoicePattern|null
     */
    public function findMatching(SupplierEmail $email, array $patterns = null)
    {
        if (null === $patterns) {
            $patterns = $this->findAll();
        }
        foreach ( $patterns as $pattern ) {
            try {
                if ($pattern->matches($email) ) {
                    return $pattern;
                }
            } catch ( MailException $ex ) {
                $email->setContents('ERROR: '. $ex->getMessage());
                return null;
            }
        }
        return null;
    }

    /**
     * Returns patterns for suppliers whose invoices can be imported
     * automatically.
     *
     * @return SupplierInvoicePattern[]
     */
    public function findAutoImportable()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->join('p.supplier', 's')
            ->join(SupplierAttribute::class, 'a', Join::WITH,
                    'a.supplier = s')
            ->where('a.attribute = :attr')
            ->setParameter('attr', SupplierAttribute::AUTO_IMPORT_EMAIL);
        return $qb->getQuery()->getResult();
    }
}
