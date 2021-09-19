<?php

namespace Rialto\Stock\Item\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Measurement\Temperature\Web\TemperatureRangeType;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Shipping\Export\Orm\HarmonizationCodeRepository;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing all types of stock item.
 */
class EditType extends DynamicFormType
{
    /** @var DbManager */
    protected $dbm;

    /** @var StockItemRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(StockItem::class);
    }

    public function getParent()
    {
        return BaseType::class;
    }

    /**
     * @param StockItem $item
     */
    protected function updateForm(FormInterface $form, $item)
    {
        /* ERP info section */
        $form->add('discontinued', ChoiceType::class, [
            'label' => 'Status',
            'choices' => StockItem::getDiscontinuedOptions(),
        ]);

        if ($item->isPhysicalPart()) {
            $form->add('economicOrderQty', IntegerType::class, [
                'label' => 'Economic order quantity',
                'required' => false,
            ]);
        }
        if ($item->isPurchased()) {
            $form->add('temperatureRange', TemperatureRangeType::class, [
                'required' => false,
                'label' => 'Operating temperature',
            ]);
        }

        if ($item->isVersioned()) {
            $versions = $item->getActiveVersions();
            $form->add('shippingVersion', EntityType::class, [
                'class' => ItemVersion::class,
                'choices' => $versions,
                'label' => 'Shipping version',
                'required' => true,
            ]);
            $form->add('autoBuildVersion', EntityType::class, [
                'class' => ItemVersion::class,
                'choices' => $versions,
                'label' => 'Autobuild version',
                'required' => true,
            ]);
        }

        /* Sales info section */
        $form->add('taxLevel', EntityType::class, [
            'class' => TaxAuthority::class,
            'label' => 'Tax level',
        ]);

        $topCountries = $this->repo->findPreferredCountriesOfOrigin();
        $form->add('countryOfOrigin', CountryType::class, [
            'label' => 'Country of origin',
            'preferred_choices' => $topCountries,
        ]);
        $form->add('eccnCode', EccnType::class, [
            'label' => 'ECCN code',
            'required' => $item->isSellable(),
            'placeholder' => $item->isSellable() ? '-- choose --' : 'none',
        ]);
        $form->add('discountCategory', TextType::class, [
            'label' => 'Discount category',
            'required' => false,
        ]);
        $form->add('harmonizationCode', EntityType::class, [
            'class' => HarmonizationCode::class,
            'query_builder' => function (HarmonizationCodeRepository $repo) {
                return $repo->queryActive();
            },
            'label' => 'Harmonization code',
            'required' => $item->isSellable(),
            'choice_label' => 'label',
            'placeholder' => $item->isSellable() ? '-- select --' : '-- none --',
        ]);
        $form->add('defaultWorkType', EntityType::class, [
            'class' => WorkType::class,
            'required' => false,
            'placeholder' => '-- none --',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItem::class,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $item = $form->getData();
                if ($item && $item->isSellable()) {
                    $groups[] = 'sellable';
                }
                return $groups;
            },
        ]);
    }
}

