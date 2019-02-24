<?php
/**
 * reStructuredText rendering task for Phing, the PHP build tool.
 *
 * PHP version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */

/**
 * reStructuredText rendering task for Phing, the PHP build tool.
 *
 * PHP version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */
class RSTTask extends Task
{
    use FileSetAware;
    use FilterChainAware;

    /**
     * @var string Taskname for logger
     */
    protected $taskName = 'rST';

    /**
     * Result format, defaults to "html".
     *
     * @see $supportedFormats for all possible options
     *
     * @var string
     */
    protected $format = 'html';

    /**
     * Array of supported output formats
     *
     * @var array
     * @see $format
     * @see $targetExt
     */
    protected static $supportedFormats = [
        'html',
        'latex',
        'man',
        'odt',
        's5',
        'xml'
    ];

    /**
     * Maps formats to file extensions
     *
     * @var array
     */
    protected static $targetExt = [
        'html' => 'html',
        'latex' => 'tex',
        'man' => '3',
        'odt' => 'odt',
        's5' => 'html',
        'xml' => 'xml',
    ];

    /**
     * Input file in rST format.
     * Required
     *
     * @var string
     */
    protected $file = null;

    /**
     * Additional rst2* tool parameters.
     *
     * @var string
     */
    protected $toolParam = null;

    /**
     * Full path to the tool, i.e. /usr/local/bin/rst2html
     *
     * @var string
     */
    protected $toolPath = null;

    /**
     * Output file or directory. May be omitted.
     * When it ends with a slash, it is considered to be a directory
     *
     * @var string
     */
    protected $destination = null;

    /** @var Mapper */
    protected $mapperElement = null;

    /**
     * mode to create directories with
     *
     * @var integer
     */
    protected $mode = 0;

    /**
     * Only render files whole source files are newer than the
     * target files
     *
     * @var boolean
     */
    protected $uptodate = false;

    /**
     * @var FileUtils
     */
    private $fileUtils;

    /**
     * Sets up this object internal stuff. i.e. the default mode.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mode = 0777 - umask();
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $tool = $this->getToolPath($this->format);
        if (count($this->filterChains)) {
            $this->fileUtils = new FileUtils();
        }

        if ($this->file != '') {
            $file = $this->file;
            $targetFile = $this->getTargetFile($file, $this->destination);
            $this->render($tool, new PhingFile($file), new PhingFile($targetFile));

            return;
        }

        if (!count($this->filesets)) {
            throw new BuildException(
                '"file" attribute or "fileset" subtag required'
            );
        }

        // process filesets
        $mapper = null;
        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner();
            $fromDir = $fs->getDir();
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $src) {
                $file = new PhingFile($fromDir, $src);
                if ($mapper !== null) {
                    $results = $mapper->main($file);
                    if ($results === null) {
                        throw new BuildException(
                            sprintf(
                                'No filename mapper found for "%s"',
                                $file
                            )
                        );
                    }
                    $targetFile = reset($results);
                } else {
                    $targetFile = $this->getTargetFile($file, $this->destination);
                }
                $this->render($tool, $file, $targetFile);
            }
        }
    }

    /**
     * Renders a single file and applies filters on it
     *
     * @param string $tool conversion tool to use
     * @param PhingFile $source rST source file
     * @param PhingFile $targetFile target file name
     *
     * @return void
     */
    protected function render($tool, PhingFile $source, PhingFile $targetFile)
    {
        if (count($this->filterChains) === 0) {
            $this->renderFile($tool, $source, $targetFile);
            return;
        }

        $tmpTarget = PhingFile::createTempFile('rST-', '', new PhingFile(PhingFile::getTempDir()));
        $this->renderFile($tool, $source, $tmpTarget);

        $this->fileUtils->copyFile(
            $tmpTarget,
            $targetFile,
            $this->getProject(),
            true,
            false,
            $this->filterChains,
            $this->mode
        );
        $tmpTarget->removeTempFile();
    }

    /**
     * Renders a single file with the rST tool.
     *
     * @param string $tool conversion tool to use
     * @param PhingFile $source rST source file
     * @param PhingFile $targetFile target file name
     *
     * @return void
     *
     * @throws BuildException When the conversion fails
     */
    protected function renderFile($tool, PhingFile $source, PhingFile $targetFile)
    {
        if ($this->uptodate && $targetFile->exists()
            && $source->lastModified() <= $targetFile->lastModified()
        ) {
            //target is up to date
            return;
        }

        $targetDir = $targetFile->getParentFile();
        if ($targetDir === null) {
            $this->log("Creating directory '$targetDir'", Project::MSG_VERBOSE);
            $targetDir->mkdir($this->mode);
        }

        $cmd = $tool
            . ' --exit-status=2'
            . ' ' . $this->toolParam
            . ' ' . escapeshellarg($source->getAbsolutePath())
            . ' ' . escapeshellarg($targetFile->getAbsolutePath())
            . ' 2>&1';

        $this->log('command: ' . $cmd, Project::MSG_VERBOSE);
        exec($cmd, $arOutput, $retval);
        if ($retval != 0) {
            $this->log(implode("\n", $arOutput), Project::MSG_INFO);
            throw new BuildException('Rendering rST failed');
        }
        $this->log(implode("\n", $arOutput), Project::MSG_DEBUG);
    }

    /**
     * Finds the rst2* binary path
     *
     * @param string $format Output format
     *
     * @return string Full path to rst2$format
     *
     * @throws BuildException When the tool cannot be found
     */
    protected function getToolPath($format)
    {
        if ($this->toolPath !== null) {
            return $this->toolPath;
        }

        $tool = 'rst2' . $format;
        $fs = FileSystem::getFileSystem();
        $path = $fs->which($tool);
        if (!$path) {
            throw new BuildException(
                sprintf('"%s" not found. Install python-docutils.', $tool)
            );
        }

        return $path;
    }

    /**
     * Determines and returns the target file name from the
     * input file and the configured destination name.
     *
     * @param string $file Input file
     * @param string $destination Destination file or directory name,
     *                            may be null
     *
     * @return string Target file name
     *
     * @uses $format
     * @uses $targetExt
     */
    public function getTargetFile($file, $destination = null)
    {
        if ($destination != ''
            && substr($destination, -1) !== '/'
            && substr($destination, -1) !== '\\'
        ) {
            return $destination;
        }

        if (StringHelper::endsWith('.rst', strtolower($file))) {
            $file = substr($file, 0, -4);
        }

        return $destination . $file . '.' . self::$targetExt[$this->format];
    }

    /**
     * The setter for the attribute "file"
     *
     * @param string $file Path of file to render
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * The setter for the attribute "format"
     *
     * @param string $format Output format
     *
     * @return void
     *
     * @throws BuildException When the format is not supported
     */
    public function setFormat($format)
    {
        if (!in_array($format, self::$supportedFormats)) {
            throw new BuildException(
                sprintf(
                    'Invalid output format "%s", allowed are: %s',
                    $format,
                    implode(', ', self::$supportedFormats)
                )
            );
        }
        $this->format = $format;
    }

    /**
     * The setter for the attribute "destination"
     *
     * @param string $destination Output file or directory. When it ends
     *                            with a slash, it is taken as directory.
     *
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * The setter for the attribute "toolparam"
     *
     * @param string $param Additional rst2* tool parameters
     *
     * @return void
     */
    public function setToolparam($param)
    {
        $this->toolParam = $param;
    }

    /**
     * The setter for the attribute "toolpath"
     *
     * @param    $path
     * @throws   BuildException
     * @internal param string $param Full path to tool path, i.e. /usr/local/bin/rst2html
     *
     * @return void
     */
    public function setToolpath($path)
    {
        if (!file_exists($path)) {
            $fs = FileSystem::getFileSystem();
            $fullpath = $fs->which($path);
            if ($fullpath === false) {
                throw new BuildException(
                    'Tool does not exist. Path: ' . $path
                );
            }
            $path = $fullpath;
        }
        if (!is_executable($path)) {
            throw new BuildException(
                'Tool not executable. Path: ' . $path
            );
        }
        $this->toolPath = $path;
    }

    /**
     * The setter for the attribute "uptodate"
     *
     * @param string $uptodate True/false
     *
     * @return void
     */
    public function setUptodate($uptodate)
    {
        $this->uptodate = (boolean) $uptodate;
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @return Mapper The created Mapper type object
     *
     * @throws BuildException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException(
                'Cannot define more than one mapper',
                $this->getLocation()
            );
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }
}
