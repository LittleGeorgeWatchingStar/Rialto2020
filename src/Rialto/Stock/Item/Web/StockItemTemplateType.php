<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Database\Orm\DbManager;
use Rialto\Measurement\Web\UnitsType;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Shipping\Export\Orm\HarmonizationCodeRepository;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemTemplate;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating a new stock item.
 */
class StockItemTemplateType extends AbstractType
{
    /** @var DbManager */
    private $dbm;

    /** @var StockItemRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(StockItem::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stockCode', TextType::class, [
            'label' => 'SKU',
            'required' => true,
        ]);
        $builder->add('category', EntityType::class, [
            'class' => StockCategory::class,
            'placeholder' => '-- choose --',
        ]);
        $builder->add('mbFlag', ChoiceType::class, [
            'label' => 'Purchased/manufactured',
            'required' => true,
            'choices' => StockItem::getStockTypeOptions(),
            'placeholder' => '-- choose --',
        ]);
        $builder->add('name', TextType::class, [
            'required' => true,
            'attr' => ['class' => 'publicInfo'],
        ]);
        $builder->add('longDescription', TextareaType::class, [
            'label' => 'Long description',
            'required' => true,
            'attr' => ['class' => 'publicInfo'],
        ]);
        $builder->add('package', TextType::class, [
            'label' => 'Package',
            'required' => false,
            'attr' => ['class' => 'engineeringInfo'],
        ]);
        $builder->add('partValue', TextType::class, [
            'label' => 'Part value',
            'required' => false,
            'attr' => ['class' => 'engineeringInfo'],
        ]);

        $builder->add('closeCount', ChoiceType::class, [
            'label' => 'Close-count',
            'required' => true,
            'choices' => [
                'no' => false,
                'yes' => true,
            ],
        ]);

        $builder->add('orderQuantity', IntegerType::class, [
            'label' => 'Economic order quantity',
            'required' => false,
        ]);

        $builder->add('units', UnitsType::class);

        $builder->add('rohs', RohsType::class);

        /* Sales info section */
        $builder->add('taxAuthority', EntityType::class, [
            'class' => TaxAuthority::class,
            'label' => 'Tax level',
        ]);

        $topCountries = $this->repo->findPreferredCountriesOfOrigin();
        $builder->add('countryOfOrigin', CountryType::class, [
            'label' => 'Country of origin',
            'preferred_choices' => $topCountries,
        ]);
        $builder->add('eccnCode', EccnType::class, [
            'label' => 'ECCN code',
            'required' => false,
        ]);
        $builder->add('harmonizationCode', EntityType::class, [
            'class' => HarmonizationCode::class,
            'query_builder' => function (HarmonizationCodeRepository $repo) {
                return $repo->queryActive();
            },
            'label' => 'Harmonization code',
            'required' => false,
            'choice_label' => 'label',
        ]);
        $builder->add('initialVersion', InitialVersionType::class, [
            'required' => false,
            'label' => 'Initial version',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItemTemplate::class,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $template = $form->getData();
                if (! $template) {
                    return $groups;
                }
                if ($template->isSellable()) {
                    $groups[] = 'sellable';
                }
                if ($template->isVersioned()) {
                    $groups[] = 'versioned';
                } else {
                    $groups[] = 'unversioned';
                }
                if ($template->isPhysicalPart() && $template->isSellable()) {
                    $groups[] = 'dimensions';
                }
                return $groups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockItem';
    }

}
