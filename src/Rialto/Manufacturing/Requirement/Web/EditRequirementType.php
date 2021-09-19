<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing a work order requirement.
 */
class EditRequirementType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'EditRequirement';
    }

    public function getParent()
    {
        return RequirementType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Requirement::class,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param Requirement $woReq
     */
    protected function updateForm(FormInterface $form, $woReq)
    {
        $stockItem = $woReq->getStockItem();
        if ($woReq->isVersioned() && ! $woReq->isProvidedByChild()) {
            $form->add('version', EntityType::class, [
                'class' => ItemVersion::class,
                'choices' => $stockItem->getActiveVersions(),
                'preferred_choices' => [$stockItem->getAutoBuildVersion()],
            ]);
        }
        if ($woReq->isCustomizable()) {
            $form->add('customization', EntityType::class, [
                'class' => Customization::class,
                'query_builder' => function (CustomizationRepository $repo) use ($stockItem) {
                    return $repo->createBuilder()
                        ->bySku($stockItem)
                        ->getQueryBuilder();
                },
                'choice_label' => 'name',
                'required' => false,
            ]);
        }
        $form->add('submit', SubmitType::class);
    }
}
