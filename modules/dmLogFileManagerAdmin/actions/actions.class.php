<?php

class dmLogFileManagerAdminActions extends dmAdminBaseActions
{

    protected $logFileCleanerService = null;
    
    public function __construct($context, $moduleName, $actionName)
    {        
        parent::__construct($context, $moduleName, $actionName);
        $this->logFileCleanerService = $this->getService('log_file_cleaner');
    }

    public function executeIndex(dmWebRequest $request)
    {
        $this->locations = $this->logFileCleanerService->getRegisteredLogFilesBySections();
    }

    public function executeDownload(dmWebRequest $request)
    {
        if (
            $request->hasParameter('key') && 
            file_exists(dmOs::join(sfConfig::get('sf_root_dir'), $request->getParameter('key'))) &&
            $this->logFileCleanerService->isRegisteredLogFile($request->getParameter('key'))
            ) {
            return $this->download(dmOs::join(sfConfig::get('sf_root_dir'), $request->getParameter('key')));
        } else {
            $this->forward404($this->getI18n()->__('The %file% is not valid log file for download.', array('%file%'=>$request->getParameter('key'))));
        }
    }
    
    public function executeCleanFile(dmWebRequest $request)
    {
        if (
            $request->hasParameter('key') && 
            file_exists(dmOs::join(sfConfig::get('sf_root_dir'), $request->getParameter('key'))) &&
            $this->logFileCleanerService->isRegisteredLogFile(dmOs::join(sfConfig::get('sf_root_dir'), $request->getParameter('key')))
            ) {
            if ($this->logFileCleanerService->cleanLogFile(dmOs::join(sfConfig::get('sf_root_dir'), $request->getParameter('key')))) {
                $this->getUser()->setFlash('notice', $this->getI18n()->__('The log file "%file%" has been successfully cleaned.', array('%file%' =>  $request->getParameter('key'))));
            } else {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Something went wrong, log file "%file%" is not cleaned.', array('%file%' =>  $request->getParameter('key'))));
            }
            $this->redirect('dmLogFileManagerAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('The %file% is not valid log file for cleaning.', array('%file%'=>$request->getParameter('key'))));
        }
    }
    
    public function executeCleanAll(dmWebRequest $request)
    {
        if ($request->hasParameter('key') && $request->getParameter('key') == 'CleanAll') {
            if ($this->logFileCleanerService->cleanAll()) {
                $this->getUser()->setFlash('notice', $this->getI18n()->__('All log files are successfully cleaned.'));
            } else {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Something went wrong, not all log files are successfully cleaned.'));
            }
            $this->redirect('dmLogFileManagerAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('You can not access this action directly.'));
        }       
    }
    
    public function executeBatchClean(dmWebRequest $request)
    {
        if ($request->hasParameter('_batch_clean')) {
            $files = $request->getParameter('file');
            $totalFiles = 0;
            $successfullFiles = array();
            $errorFiles = array();
            foreach ($files as $file => $val) {
                $totalFiles++;
                if (file_exists(dmOs::join(sfConfig::get('sf_root_dir'), $file)) && $this->logFileCleanerService->cleanLogFile(dmOs::join(sfConfig::get('sf_root_dir'), $file))) {
                    $successfullFiles[] = $file;
                } else {
                    $errorFiles[] = $file;
                }
            }
            if (count($errorFiles)) {
                $this->getUser()->setFlash('error', $this->getI18n()->__('Not all selected log files are successfully cleaned. Successfull files are "%successfull_files%", error occured with these files "%error_files%".', array('%successfull_files%' => implode('", "', $successfullFiles), '%error_files%' => implode('", "', $errorFiles))));
            } else {                
                $this->getUser()->setFlash('notice', $this->getI18n()->__('Selected log files "%files%" are successfully cleaned.', array('%files%' => implode('", "', $successfullFiles))));
            }            
            $this->redirect('dmLogFileManagerAdmin/index');
        } else {
            $this->forward404($this->getI18n()->__('You can not access this action directly.'));
        }
    }

}