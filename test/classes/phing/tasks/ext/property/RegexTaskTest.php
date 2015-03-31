<?php

require_once 'phing/BuildFileTest.php';

/**
 * Tests the PropertyRegexTask Task
 *
 * @author  SiadArdroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class RegexTaskTest extends BuildFileTest
{

    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/RegExTaskTest.xml'
        );
    }

    public function testPropertyRegex()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('test.name', 'ABC');
    }

    public function testPropertyRegexReplace()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('test.name', 'test.DEF.name');
    }
}
