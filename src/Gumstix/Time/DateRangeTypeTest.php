<?php

namespace Gumstix\Time;


use Rialto\Web\Form\FormTypeTestCase;

class DateRangeTypeTest extends FormTypeTestCase
{
    public function testSubmit_empty_returnsDateRange()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit([]);
        $data = $form->getData();
        $this->assertInstanceOf(DateRange::class, $data);
    }

    public function testSubmit_empty_hasNoStartDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit([]);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertFalse($range->hasStart());
    }

    public function testSubmit_empty_hasNoEndDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit([]);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertFalse($range->hasEnd());
    }

    public function testSubmit_withStart_hasStartDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['start' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertTrue($range->hasStart());
    }

    public function testSubmit_withStart_hasNoEndDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['start' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertFalse($range->hasEnd());
    }

    public function testSubmit_withStart_startIsDateInstance()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['start' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertInstanceOf(\DateTimeInterface::class, $range->getStart());
    }

    public function testSubmit_withEnd_hasNoStartDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['end' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertFalse($range->hasStart());
    }

    public function testSubmit_withBoth_hasStartDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['start' => '2016-02-14', 'end' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertTrue($range->hasStart());
    }

    public function testSubmit_withBoth_hasEndDate()
    {
        $form = $this->createForm(DateRangeType::class);
        $form->submit(['start' => '2016-02-14', 'end' => '2016-12-25']);
        /** @var $range DateRange */
        $range = $form->getData();
        $this->assertTrue($range->hasEnd());
    }

    public function testView_withInitialData_works()
    {
        $range = DateRange::create()
            ->withStart('-1 month');
        $form = $this->createForm(DateRangeType::class, $range);
        $view = $form->createView();
        $this->assertNotNull($view['start']->vars['data']);
    }
}
