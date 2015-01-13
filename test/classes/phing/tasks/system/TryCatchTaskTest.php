<?php

use Phing\Test\AbstractBuildFileTest;


/**
 * Tests the TryCatch Task
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @version $Id$
 * @package phing.tasks.system
 */
class TryCatchTaskTest extends AbstractBuildFileTest
{

    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/TryCatchTest.xml'
        );
    }

    public function testTryCatchFinally()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('In <catch>.');
        $this->assertInLogs('In <finally>.');
        $this->assertStringEndsWith('Tada!', $this->project->getProperty("prop." . __FUNCTION__));
    }
}
