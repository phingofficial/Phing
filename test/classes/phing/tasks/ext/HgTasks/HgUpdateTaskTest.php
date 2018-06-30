<?php

class HgUpdateTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgUpdateTaskTest.xml'
        );
    }

    public function tearDown()
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testWrongRepositoryDirDoesntExist()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepositoryDirDoesntExist',
            'repository directory does not exist',
            "Repository directory 'inconcievable-buttercup' does not exist."
        );
    }

    public function testWrongRepository()
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'wrong repository',
            "abort"
        );
        $this->assertInLogs("Executing: hg update");
    }
}
