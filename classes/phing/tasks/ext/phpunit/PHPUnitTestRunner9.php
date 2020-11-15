<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

/**
 * Simple Testrunner for PHPUnit that runs all tests of a testsuite.
 *
 * @author  Blair Cooper <dev@raincitysolutions.com>
 * @package phing.tasks.ext.phpunit
 */
class PHPUnitTestRunner9 implements \PHPUnit\Runner\TestHook, \PHPUnit\Framework\TestListener
{
    private $hasErrors = false;
    private $hasFailures = false;
    private $hasWarnings = false;
    private $hasIncomplete = false;
    private $hasSkipped = false;
    private $hasRisky = false;
    private $lastErrorMessage = '';
    private $lastFailureMessage = '';
    private $lastWarningMessage = '';
    private $lastIncompleteMessage = '';
    private $lastSkippedMessage = '';
    private $lastRiskyMessage = '';
    private $formatters = [];

    /**
     * @var \PHPUnit\Framework\TestListener[]
     */
    private $listeners = [];

    /**
     * @var \SebastianBergmann\CodeCoverage\CodeCoverage
     */
    private $codecoverage;

    /**
     * @var Project $project
     */
    private $project;

    private $groups = [];
    private $excludeGroups = [];

    private $processIsolation = false;

    private $useCustomErrorHandler = true;

    /**
     * @param Project $project
     * @param array $groups
     * @param array $excludeGroups
     * @param bool $processIsolation
     */
    public function __construct(
        Project $project,
        array $groups = [],
        array $excludeGroups = [],
        bool $processIsolation = false
    ) {
        $this->project = $project;
        $this->groups = $groups;
        $this->excludeGroups = $excludeGroups;
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param $codecoverage
     */
    public function setCodecoverage(\SebastianBergmann\CodeCoverage\CodeCoverage $codecoverage): void
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param $useCustomErrorHandler
     */
    public function setUseCustomErrorHandler(bool $useCustomErrorHandler): void
    {
        $this->useCustomErrorHandler = $useCustomErrorHandler;
    }

    /**
     * @param $formatter
     */
    public function addFormatter(\PHPUnit\Framework\TestListener $formatter): void
    {
        $this->addListener($formatter);
        $this->formatters[] = $formatter;
    }

    public function addListener(\PHPUnit\Framework\TestListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param $level
     * @param $message
     * @param $file
     * @param $line
     * @return bool
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        $invoke = new PHPUnit\Util\ErrorHandler(true, true, true, true);
        return $invoke($level, $message, $file, $line);
    }

    /**
     * Run a test
     *
     * @param  PHPUnit\Framework\TestSuite $suite
     * @throws \BuildException
     * @throws ReflectionException
     */
    public function run(PHPUnit\Framework\TestSuite $suite)
    {
        $res = new PHPUnit\Framework\TestResult();

        if ($this->codecoverage) {
            // Check if Phing coverage is being utlizied
            if ($this->project->getProperty('coverage.database')) {
                $whitelist = \Phing\Tasks\Ext\Coverage\CoverageMerger::getWhiteList($this->project);
                $filter = $this->codecoverage->filter();

                if (method_exists($filter, 'includeFiles')) {
                    $filter->includeFiles($whitelist);
                } else if (method_exists($filter, 'addFilesToWhiteList')) {
                    $filter->addFilesToWhiteList($whitelist);
                }
            }

            $res->setCodeCoverage($this->codecoverage);
        }

        // $res->addListener($this);

        foreach ($this->formatters as $formatter) {
            $res->addListener($formatter);
        }

        /* Set PHPUnit error handler */
        if ($this->useCustomErrorHandler) {
            set_error_handler([$this, 'handleError'], E_ALL | E_STRICT);
        }

        $this->injectFilters($suite);
        $suite->run($res);

        foreach ($this->formatters as $formatter) {
            $formatter->processResult($res);
        }

        /* Restore Phing error handler */
        if ($this->useCustomErrorHandler) {
            restore_error_handler();
        }

        // Check if Phing coverage is being utlizied
        if ($this->codecoverage && $this->project->getProperty('coverage.database')) {
            try {
                \Phing\Tasks\Ext\Coverage\CoverageMerger::merge($this->project, $this->codecoverage->getData());
            } catch (IOException $e) {
                throw new BuildException('Merging code coverage failed.', $e);
            }
        }
        $this->checkResult($res);
    }

    /**
     * @param PHPUnit\Framework\TestSuite $suite
     * @throws ReflectionException
     */
    private function injectFilters(PHPUnit\Framework\TestSuite $suite): void
    {
        $filterFactory = new PHPUnit\Runner\Filter\Factory();

        if (empty($this->excludeGroups) && empty($this->groups)) {
            return;
        }

        if (!empty($this->excludeGroups)) {
            $filterFactory->addFilter(
                new ReflectionClass(\PHPUnit\Runner\Filter\ExcludeGroupFilterIterator::class),
                $this->excludeGroups
            );
        }

        if (!empty($this->groups)) {
            $filterFactory->addFilter(
                new ReflectionClass(\PHPUnit\Runner\Filter\IncludeGroupFilterIterator::class),
                $this->groups
            );
        }

        $suite->injectFilter($filterFactory);
    }

    /**
     * @param \PHPUnit\Framework\TestResult $res
     */
    private function checkResult(\PHPUnit\Framework\TestResult $res): void
    {
        $this->hasSkipped = $res->skippedCount() > 0;
        $this->hasIncomplete = $res->notImplementedCount() > 0;
        $this->hasWarnings = $res->warningCount() > 0;
        $this->hasFailures = $res->failureCount() > 0;
        $this->hasErrors = $res->errorCount() > 0;
        $this->hasRisky = $res->riskyCount() > 0;
    }

    /**
     * @return boolean
     */
    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * @return boolean
     */
    public function hasFailures(): bool
    {
        return $this->hasFailures;
    }

    /**
     * @return boolean
     */
    public function hasWarnings(): bool
    {
        return $this->hasWarnings;
    }

    /**
     * @return boolean
     */
    public function hasIncomplete(): bool
    {
        return $this->hasIncomplete;
    }

    /**
     * @return boolean
     */
    public function hasSkipped(): bool
    {
        return $this->hasSkipped;
    }

    /**
     * @return boolean
     */
    public function hasRisky(): bool
    {
        return $this->hasRisky;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage(): string
    {
        return $this->lastErrorMessage;
    }

    /**
     * @return string
     */
    public function getLastFailureMessage(): string
    {
        return $this->lastFailureMessage;
    }

    /**
     * @return string
     */
    public function getLastIncompleteMessage(): string
    {
        return $this->lastIncompleteMessage;
    }

    /**
     * @return string
     */
    public function getLastSkippedMessage(): string
    {
        return $this->lastSkippedMessage;
    }

    /**
     * @return string
     */
    public function getLastWarningMessage(): string
    {
        return $this->lastWarningMessage;
    }

    /**
     * @return string
     */
    public function getLastRiskyMessage(): string
    {
        return $this->lastRiskyMessage;
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @param float $time
     */
    public function addError(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastErrorMessage = $this->composeMessage('ERROR', $test, $e);
    }

    /**
     * @param string $message
     * @param PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @return string
     */
    protected function composeMessage(string $message, PHPUnit\Framework\Test $test, Throwable $e): string
    {
        $name = ($test instanceof \PHPUnit\Framework\TestCase ? $test->getName() : '');
        $message = "Test {$message} ({$name} in class " . get_class($test) . ' ' . $e->getFile()
            . ' on line ' . $e->getLine() . '): ' . $e->getMessage();

        if ($e instanceof PHPUnit\Framework\ExpectationFailedException && $e->getComparisonFailure()) {
            $message .= "\n" . $e->getComparisonFailure()->getDiff();
        }

        return $message;
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit\Framework\Test $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(
        PHPUnit\Framework\Test $test,
        PHPUnit\Framework\AssertionFailedError $e,
        float $time
    ): void {
        $this->lastFailureMessage = $this->composeMessage('FAILURE', $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit\Framework\Test $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addWarning(PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
        $this->lastWarningMessage = $this->composeMessage("WARNING", $test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastIncompleteMessage = $this->composeMessage("INCOMPLETE", $test, $e);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception $e
     * @param float $time
     * @since Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastSkippedMessage = $this->composeMessage('SKIPPED', $test, $e);
    }

    /**
     * Risky test
     *
     * @param PHPUnit\Framework\Test $test
     * @param Throwable $e
     * @param float $time
     */
    public function addRiskyTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastRiskyMessage = $this->composeMessage('RISKY', $test, $e);
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function startTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit\Framework\Test $test
     */
    public function startTest(PHPUnit\Framework\Test $test): void
    {
    }

    /**
     * A test ended.
     *
     * @param PHPUnit\Framework\Test $test
     * @param float $time
     */
    public function endTest(PHPUnit\Framework\Test $test, float $time): void
    {
        if (($test instanceof PHPUnit\Framework\TestCase) && !$test->hasExpectationOnOutput()) {
            echo $test->getActualOutput();
        }
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param  string $message
     * @throws BuildException
     */
    protected function runFailed($message): void
    {
        throw new BuildException($message);
    }
}
