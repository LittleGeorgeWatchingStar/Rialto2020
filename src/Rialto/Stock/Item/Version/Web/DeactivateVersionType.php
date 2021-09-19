<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\ChangeNotice\Web\ChangeNoticeListType;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Stock\Item\Version\Version;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for deactivating an ItemVersion.
 */
class DeactivateVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $version ItemVersion */
        $version = $options['version'];

        $choiceFilter = function (ItemVersionRepository $repo) use ($version) {
            return $repo->createQueryBuilder('v')
                ->andWhere('v.active = 1')
                ->andWhere('v.stockItem = :item')
                ->setParameter('item', $version->getStockItem())
                ->andWhere('v.version not in (:versions)')
                ->setParameter('versions', [
                    $version->getVersionCode(),
                    Version::NONE,
                ]);
        };

        if ($version->isAutoBuildVersion()) {
            $builder->add('autoBuildVersion', EntityType::class, [
                'class' => ItemVersion::class,
                'query_builder' => $choiceFilter,
                'placeholder' => '-- choose --',
                'label' => 'New auto-build version',
                'label_attr' => ['class' => 'version'],
            ]);
        }
        if ($version->isShippingVersion()) {
            $builder->add('shippingVersion', EntityType::class, [
                'class' => ItemVersion::class,
                'query_builder' => $choiceFilter,
                'placeholder' => '-- choose --',
                'label' => 'New shipping version',
                'label_attr' => ['class' => 'version'],
            ]);
        }
        $builder->add('notices', ChangeNoticeListType::class, [
            'label' => 'You can optionally add product change notices to this deactivation:',
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', StockItem::class);
        $resolver->setRequired('version');
    }

    public function getBlockPrefix()
    {
        return 'Deactivate';
    }
}
