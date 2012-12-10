<?php

class dmLogFileCleanTask extends dmContextTask
{

  /**
   * @see sfTask
   */
  protected function configure()
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption('file', 'f', sfCommandOption::PARAMETER_REQUIRED, 'Cleans only one log file with given path', null),
      new sfCommandOption('section', 's', sfCommandOption::PARAMETER_REQUIRED, 'Cleans log files within given section name', null)
    ));

    $this->namespace = 'dm';
    $this->name = 'log-file-cleaner';
    $this->briefDescription = 'Cleans the log files';

    $this->detailedDescription = $this->briefDescription;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $logCleaner = $this->get('log_file_cleaner')->setLogCallable(array($this, 'customLog'));
    if ($options['file'] || $options['section']) {
        if ($options['file']) {
            $logCleaner->cleanLogFile($options['file']);
        }
        if ($options['section']) {
            $logCleaner->cleanLogSection($options['section']);
        }
    } else {
        $logCleaner->cleanAll();
    }
  }

  public function customLog($msg)
  {
    return $this->logSection('log-file-cleaner', $msg);
  }
}