<?php
/**
 * Reads property files into a PropertySet.
 * @author Matthias Pigulla <mp@webfactory.de>
 * @author Shaked Klein Orbach (phing@shakedos.com)
 */
class PropertyFileReader {
	
	protected $properties;
	
	/**
	 * @var const - decides how to retreive the extended environment string 
	 */
	const EXTENDS_REGEX = '\{\sextends(.*)\}';
	
	public function __construct(PropertySet $s) {
		$this->properties = $s;
	}
	
	 /**
     * Load properties from a file.
     *
     * @param PhingFile $file
     * @return void
     * @throws IOException - if unable to read file.
     */
    public function load(PhingFile $file, $section = null) {
        if ($file->canRead()) {
            $this->parse($file->getPath(), $section);                    
        } else {
            throw new IOException("Can not read file ".$file->getPath());
        }
        
    }
    
    protected function parse($filePath, $section) {

    	$section = (string) $section;
    	
        // load() already made sure that file is readable                
        // but we'll double check that when reading the file into 
        // an array

    	if (($lines = @file($filePath)) === false) {
            throw new IOException("Unable to parse contents of $filePath");
        }
        
        // check extends 
        $extendedFilename = $this->getExtendedFilename($lines[0]);   
        if ($extendedFilename){
        	//get path create file and load again
        	$path = substr($filePath,0,strrpos($filePath,'/')); 
        	$path = $path? $path . '/': '';  
        	$newFileName =   $path . $extendedFilename . '.properties';
        	$file = new PhingFile($newFileName); 
        	//load before so we can override later 
        	
        	$this->load($file);
        }   
        
        $currentSection = '';
        $sect = array($currentSection => array(), $section => array());
        $depends = array();
        
        foreach ($lines as $l) {
        	
        	if (($p = strpos($l, '#')) !== false)
        		$l = substr($l, 0, $p);

        	if (($p = strpos($l, ';')) !== false)
        		$l = substr($l, 0, $p);
        
       		if (($p = strpos($l, '{')) !== false){
       			$l = substr($l, 0, $p); 
       		}

        	if (!($l = trim($l))) 
        		continue;

        	if (preg_match('/^\[([\w-]+)(?:\s*:\s*([\w-]+))?\]$/', $l, $matches)) {
        		$currentSection = $matches[1];
        		$sect[$currentSection] = array();
        		if (isset($matches[2])) $depends[$currentSection] = $matches[2];
        		continue;
        	}
        	
			$pos = strpos($l, '=');
			$name = trim(substr($l, 0, $pos));
			$value = $this->inVal(trim(substr($l, $pos + 1)));

			/*
			 * Take care: Property file may contain identical keys like
			 * a[] = first
			 * a[] = second
			 */
			$sect[$currentSection][] = array($name, $value);
			 
        }

        $dependencyOrder = array();
        while ($section) {
        	array_unshift($dependencyOrder, $section);
        	$section = isset($depends[$section]) ? $depends[$section] : '';
        }
        array_unshift($dependencyOrder, '');

        foreach ($dependencyOrder as $section) 
        	foreach ($sect[$section] as $def) {
        		list ($name, $value) = $def;
        		$this->properties[$name] = $value;
        	} 
    }
    
    /**
     * Process values when being read in from properties file.
     * does things like convert "true" => true
     * @param string $val Trimmed value.
     * @return mixed The new property value (may be boolean, etc.)
     */
    protected function inVal($val) {
        if ($val === "true") { 
            $val = true;
        } elseif ($val === "false") { 
            $val = false; 
        }
        return $val;
    }
	
     /** 
     * Get the the name of the extended file 
     * @param string $line
     * @return string|false 
     */
    protected function getExtendedFilename($line){
    	if (preg_match('#'.self::EXTENDS_REGEX.'#', $line,$matches)){
    		return trim($matches[1]); 
    	}
    	return false; 
    } 
	
}