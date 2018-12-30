<?php

namespace TemplateEngineFactory\Test;

use PHPUnit\Framework\TestCase;
use TemplateEngineFactory\TemplateVariables;

/**
 * Tests for the TemplateVariables class.
 *
 * @coversDefaultClass \ProcessWire\TemplateEngineFactory
 *
 * @group TemplateEngineFactory
 */
class TemplateVariablesTest extends TestCase
{
    public function testConstructor_CalledEmptyOrWithData_ReturnsCorrectData()
    {
        $vars1 = new TemplateVariables();

        $this->assertEmpty($vars1->getArray());

        $data = [
            'foo' => 'bar',
            'true' => true,
        ];

        $vars2 = new TemplateVariables($data);

        $this->assertEquals($data, $vars2->getArray());
        $this->assertEquals('bar', $vars2->get('foo'));
        $this->assertEquals(true, $vars2->get('true'));
    }
}
