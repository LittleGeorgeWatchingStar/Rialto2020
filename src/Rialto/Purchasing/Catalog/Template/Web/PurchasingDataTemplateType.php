<?php

namespace Rialto\Purchasing\Catalog\Template\Web;

use Rialto\Purchasing\Catalog\Template\PricingVariable;
use Rialto\Purchasing\Catalog\Template\PurchasingDataStrategy;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Form type for maintaining purchasing data templates.
 */
class PurchasingDataTemplateType extends AbstractType implements DataMapperInterface
{
    /** @var PropertyPathMapper */
    private $mapper;

    /**
     * @var PurchasingDataStrategy
     */
    private $strategy;

    public function __construct()
    {
        $this->mapper = new PropertyPathMapper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->strategy = $options['strategy'];

        $builder->add('strategy', HiddenType::class, [
            'mapped' => false,
        ]);
        $builder->add('supplier', JsEntityType::class, [
            'class' => Supplier::class,
        ]);
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
        ]);
        $builder->add('incrementQty', IntegerType::class, [
            'label' => 'Minimum increment qty',
        ]);
        $builder->add('binSize', IntegerType::class);

        foreach($this->strategy->getVariableNames() as $index => $name) {
            $formType = $this->strategy->getVariableFormTypes()[$index];
            $builder->add($name, $formType, [
                'mapped' => false,
            ]);
        }

        $builder->setDataMapper($this);
    }

    /**
     * @param PurchasingDataTemplate $data
     * @param FormInterface[]|Traversable $forms
     */
    public function mapDataToForms($data, $forms)
    {
        $this->mapper->mapDataToForms($data, $forms);

        $forms = iterator_to_array($forms);
        $forms['strategy']->setData($this->strategy);
        foreach ($this->strategy->getVariableNames() as $name) {
            if(isset($data->getVariables()[$name])) {
                $forms[$name]->setData($data->getVariables()[$name]);
            }
        }
    }

    /**
     * @param FormInterface[]|Traversable $forms
     * @param PurchasingDataTemplate $data
     */
    public function mapFormsToData($forms, &$data)
    {
        $this->mapper->mapFormsToData($forms, $data);

        $forms = iterator_to_array($forms);
        $data->setStrategy($forms['strategy']->getData());
        $pricingVariables = [];
        foreach ($data->getVariableNames() as $variableName) {
            $pricingVariables[$variableName] = $forms[$variableName]->getData();
        }
        $data->setVariables($pricingVariables);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', PurchasingDataTemplate::class);
        $resolver->setRequired('strategy');
    }

    public function getBlockPrefix()
    {
        return 'PurchasingDataTemplate';
    }

}
