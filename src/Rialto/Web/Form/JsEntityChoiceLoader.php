<?php

namespace Rialto\Web\Form;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * A choice loader implementation for select inputs where the options
 * are dynamically determined by Javascript and Ajax.
 *
 * Used by @see JsEntityType, for example.
 *
 * @see https://symfony.com/doc/current/reference/forms/types/choice.html#choice-loader
 */
class JsEntityChoiceLoader implements ChoiceLoaderInterface
{
    /** @var RialtoEntityToIdTransformer */
    private $transformer;

    /** @var ChoiceListInterface */
    private $choiceList;

    private $valueGenerator;

    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
        $this->choiceList = new ArrayChoiceList([]);
        $this->valueGenerator = function ($object) {
            return $this->transformer->transform($object);
        };
    }

    /**
     * Loads the values corresponding to the given choices.
     *
     * The values are returned with the same keys and in the same order as the
     * corresponding choices in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param array $choices An array of choices. Non-existing choices in
     *                               this array are ignored
     * @param null|callable $value The callable generating the choice values
     *
     * @return string[] An array of choice values
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        $values = [];
        $choiceList = [];
        foreach ($choices as $k => $object) {
            if (null === $object) {
                continue;
            }
            $id = $this->transformer->transform($object);
            $values[$k] = $id;
            $choiceList[$id] = $object;
        }
        $this->choiceList = new ArrayChoiceList($choiceList, $this->valueGenerator);
        return $values;
    }

    /**
     * Loads the choices corresponding to the given values.
     *
     * The choices are returned with the same keys and in the same order as the
     * corresponding values in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param string[] $values An array of choice values. Non-existing
     *                              values in this array are ignored
     * @param null|callable $value The callable generating the choice values
     *
     * @return array An array of choices
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        $choices = [];
        $choiceList = [];
        foreach ($values as $k => $id) {
            if (!$id) {
                continue;
            }
            $object = $this->transformer->reverseTransform($id);
            $choices[$k] = $object;
            $choiceList[$id] = $object;
        }
        $this->choiceList = new ArrayChoiceList($choiceList, $this->valueGenerator);
        return $choiceList;
    }

    /**
     * Loads a list of choices.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param null|callable $value The callable which generates the values
     *                             from choices
     *
     * @return ChoiceListInterface The loaded choice list
     */
    public function loadChoiceList($value = null)
    {
        return $this->choiceList;
    }

}
