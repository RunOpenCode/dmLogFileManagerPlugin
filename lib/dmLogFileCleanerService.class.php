<?php

/**
 * @author TheCelavi
 */
class dmLogFileCleanerService extends dmConfigurable
{
    /*
     * Log files
     */

    protected $logFiles = array();
    /*
     * Callable system log function - used for tasks
     */
    protected $logCallable = null;
    /*
     * Event log service
     */
    protected $eventLog = null;

    /**
     * Creates dmLogFileCleanerService class
     * Should not be used directly - only via service container
     * 
     * @param event_log service $eventLog
     * @param array $options
     */
    public function __construct($eventLog, array $options = array())
    {
        $this->eventLog = $eventLog;
        $this->configure($options);
        $this->initialize();
    }

    /**
     * Loads configured log files sections, extensions and paths to log files
     */
    protected function initialize()
    {
        $sections = sfConfig::get('dm_dmLogFileManagerPlugin_locations');
        $logExtensions = sfConfig::get('dm_dmLogFileManagerPlugin_extensions');

        $logFileFinder = sfFinder::type('file');

        foreach ($logExtensions as $exstension) {
            if (substr($exstension, 0, 1) == '.') {
                $logFileFinder->name('*' . $exstension);
            } else {
                $logFileFinder->name('*.' . $exstension);
            }
        }

        foreach ($sections as $section => $dirs) {
            $this->logFiles[$section] = array();
            foreach ($dirs as $dir) {
                $this->logFiles[$section] = array_merge($this->logFiles[$section], $logFileFinder->in($dir));
            }
        }
    }

    /**
     * Checks if provided file path is registered as log file
     * 
     * @param string $theFile path to file
     * @return boolean
     */
    public function isRegisteredLogFile($theFile)
    {
        foreach ($this->logFiles as $section => $files) {
            foreach ($files as $file) {
                if ($file == $theFile) {
                    return true;
                }
                if (str_replace(sfConfig::get('sf_root_dir'), '', $file) == $theFile) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Fetches the log files
     * 
     * @return array
     */
    public function getRegisteredLogFiles()
    {
        $registeredFiles = array();
        foreach ($this->logFiles as $section => $files) {
            foreach ($files as $file) {
                $registeredFiles[] = $file;
            }
        }
        return $registeredFiles;
    }

    /**
     * Fetches the log files, grouped in sections
     * 
     * @return array
     */
    public function getRegisteredLogFilesBySections()
    {
        return $this->logFiles;
    }

    /**
     * Cleans one log file
     * 
     * @param string $theFile path to the log file
     * @return boolean if cleaning was successfull
     * @throws dmException
     */
    public function cleanLogFile($theFile)
    {
        $this->log(sprintf('About to clean log file "%s".', $theFile));
        try {
            if (!$this->doCleanLogFile($theFile)) {
                throw new dmException(sprintf('The log file "%s" is not cleaned.', $theFile));
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $this->eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'error',
                'type' => 'Log',
                'subject' => 'File clean error'
            ));
            return false;
        }
        $this->log(sprintf('The log file "%s" successfully cleaned.', $theFile));
        $this->eventLog->log(array(
            'server' => $_SERVER,
            'action' => 'log_cleaned',
            'type' => 'Log',
            'subject' => 'Log file cleaned'
        ));
        return true;
    }
    /**
     * Cleans all log files in one section
     * 
     * @param string $section
     * @return boolean if cleaning was successfull
     * @throws dmException
     */
    public function cleanLogSection($section)
    {
        $this->log(sprintf('About to clean log section "%s".', $section));
        if (isset($this->logFiles[$section])) {
            $errors = 0;
            $totalFiles = 0;
            foreach ($files = $this->logFiles[$section] as $file) {
                $totalFiles++;
                try {
                    if (!$this->doCleanLogFile($file)) {
                        throw new dmException(sprintf('The log file "%s" is not cleaned.', $file));
                    }
                } catch (Exception $e) {
                    $this->log($e->getMessage());
                    $errors++;
                }
            }
            if ($errors) {
                $this->log(sprintf('The log section "%s" is not successfully cleaned. From total %s log files, %s encountered errors.', $section, $totalFiles, $errors));
                $this->eventLog->log(array(
                    'server' => $_SERVER,
                    'action' => 'error',
                    'type' => 'Log',
                    'subject' => 'Section clean error'
                ));
                return false;
            } else {
                $this->log(sprintf('The log section "%s" successfully cleaned.', $section));
                $this->eventLog->log(array(
                    'server' => $_SERVER,
                    'action' => 'log_cleaned',
                    'type' => 'Log',
                    'subject' => 'Log section cleaned'
                ));
                return true;
            }
        } else {
            $this->log(sprintf('Log section "%s" does not exist.', $section));
            $this->eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'error',
                'type' => 'Log',
                'subject' => 'Section clean error'
            ));
            return false;
        }
    }
    /**
     * Cleans all log files
     * 
     * @return boolean if cleaning was successfull
     * @throws dmException
     */
    public function cleanAll()
    {
        $this->log('About to clean all log files');
        $errors = 0;
        $totalFiles = 0;
        foreach ($this->logFiles as $section => $files) {
            foreach ($files as $file) {
                $totalFiles++;
                try {
                    if (!$this->doCleanLogFile($file)) {
                        throw new dmException(sprintf('The log file "%s" is not cleaned.', $file));
                    }
                } catch (Exception $e) {
                    $this->log($e->getMessage());
                    $errors++;
                }
            }
        }
        if ($errors) {
            $this->log(sprintf('All log files are not successfully cleaned. From total %s log files, %s encountered errors.', $totalFiles, $errors));
            $this->eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'error',
                'type' => 'Log',
                'subject' => 'All logs clean error'
            ));
            return false;
        } else {
            $this->log('All log files are successfully cleaned.');
            $this->eventLog->log(array(
                'server' => $_SERVER,
                'action' => 'log_cleaned',
                'type' => 'Log',
                'subject' => 'All logs cleaned'
            ));
            return true;
        }
    }

    /**
     * Actualy cleans the log file
     * 
     * @param string $theFile path to log file
     * @return boolean the signal if the operations is successfull
     * @throws dmException log file does not exists or it is not registered
     */
    protected function doCleanLogFile($theFile)
    {
        if ($this->isRegisteredLogFile($theFile)) {
            if (!file_exists($theFile)) {
                $originalPath = $theFile;
                $theFile = dmOs::join(sfConfig::get('dm_root_dir'), $theFile);
                if (!file_exists($theFile)) {
                    throw new dmException(sprintf('Provided log file on path "%s" does not exists.', $originalPath));
                }
            }
            if (file_put_contents($theFile, '') === false) {
                return false;
            } else {
                return true;
            }
        } else {
            throw new dmException(sprintf('Provided file on path "%s" is not valid registered log file.', $theFile));
        }
    }
    
    /**
     * Sets the log method for console
     * Used for task 
     * 
     * @param type $callableFunction
     * @return dmLogFileCleanerService
     */
    public function setLogCallable($callableFunction)
    {
        $this->logCallable = $callableFunction;
        return $this;
    }

    /**
     * Logs message to the console
     * Used for task
     * 
     * @param string $message message to log
     */
    protected function log($message)
    {
        if (is_callable($this->logCallable)) {
            call_user_func($this->logCallable, $message);
        }
    }

}
