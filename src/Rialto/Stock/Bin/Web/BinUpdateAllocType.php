<?php

namespace Rialto\Stock\Bin\Web;

use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for adjusting stock levels.
 */
class BinUpdateAllocType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->authChecker = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->authChecker->isGranted(Role::STOCK_CREATE)) {
            /** @var $bin StockBin */
            $bin = $options['stockBin'];
            $builder->add('allocatable', CheckboxType::class,
                [
                    'required' => false,
                    'data' => $bin->getAllocatable()
                ]);
            $builder->add('reason', TextType::class,
                [
                    'required' => true
                ]);
            $builder->add('submit', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['stockBin']);
        $resolver->setAllowedTypes('stockBin', StockBin::class);
    }
}
