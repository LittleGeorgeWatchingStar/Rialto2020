<?php

namespace Rialto\Email\Attachment\Web;

use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Email\Attachment\AttachmentSelector;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for choosing attachments for an email that uses
 * AttachmentSelector.
 */
class AttachmentSelectorType extends DynamicFormType
{
    /**
     * @param AttachmentSelector $selector
     */
    protected function updateForm(FormInterface $form, $selector)
    {
        $form->add('selected', ChoiceType::class, [
            'choices' => $this->getAttachmentOptions($selector),
            'required' => false,
            'expanded' => true,
            'multiple' => true,
            'label' => false,
            'attr' => ['class' => 'checkbox_group'],
        ]);
    }

    private function getAttachmentOptions(AttachmentSelector $selector)
    {
        $options = [];
        foreach ($selector->getAvailable() as $filename => $attachment) {
            $label = $filename;
            if (!$attachment->exists()) {
                $label .= ' (missing)';
            }
            $options[$label] = $filename;
        }
        return $options;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AttachmentSelector::class,
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'attachments';
    }

}
