<?php

namespace Rialto\Stock\Publication\Web;

use Rialto\Stock\Publication\Publication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for adding publications to stock items.
 *
 * @see Publication
 */
class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', TextType::class);
    }
}
