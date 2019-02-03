<?php

/**
 * Tests the Echo Task
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.tasks.system
 */
class TryCatchTaskTest extends BuildFileTest
{
    public function setUp(): void    {
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
