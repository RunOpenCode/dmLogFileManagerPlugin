<?php

use_helper('File');
use_helper('Date');

$totalLogSize = 0;

echo _open('div.dm-log-file-manager-plugin');

    echo _open('form', array(
        'action' => _link('dmLogFileManagerAdmin/batchClean')->getHref(),
        'method' => 'post'
    ));

        echo _open('table.dm-log-file-manager-table.dm_data', 
            array('json' => array(
                'translation_url' => _link('dmPage/tableTranslation')->getHref()
            )));

            echo _open('thead');
                echo _tag('tr',
                        _tag('th', _tag('input.check-all', array('type'=>'checkbox'))).
                        _tag('th', __('Section')).
                        _tag('th', __('Directory')).
                        _tag('th', __('File')).  
                        _tag('th', __('Modified')).
                        _tag('th', __('Group')).
                        _tag('th', __('Owner')).
                        _tag('th', __('Permissions')).
                        _tag('th', __('Size')).
                        _tag('th', __('Actions'))
                    );
            echo _close('thead');

            echo _open('tbody');

                foreach ($locations as $location => $files) {
                    if (count($files)) {
                        foreach ($files as $file) {
                            $fileInfo = get_file_properties($file);

                            $actionsHTML = _open('ul.sf_admin_td_actions').
                                    _tag('li.sf_admin_action_download',
                                        _tag(
                                            'a.s16.s16_download.dm_download_link.sf_admin_action', 
                                            array(
                                                'title' => __('Download this log'), 
                                                'href' => _link('dmLogFileManagerAdmin/download')->param('key',$fileInfo['root_path'])->getHref(),
                                                'target' => '_blank'
                                            ), 
                                            __('Download'))
                                        ).
                                   _tag('li.sf_admin_action_clean', array('json'=>array('message'=>__('Are you shore that you want to clean this log file?'))),
                                        _link('dmLogFileManagerAdmin/cleanFile')
                                            ->set('a.s16.s16_delete.dm_clean_link.sf_admin_action')
                                            ->param('key', $fileInfo['root_path'])
                                            ->title(__('Clean this log file'))
                                            ->text(__('Clean'))).                               
                                _close('ul');

                            echo _tag('tr',
                                _tag('td', _tag('input.log-file', array('type'=>'checkbox', 'name'=>'file['.$fileInfo['root_path'].']'))).
                                _tag('td', __($location)).
                                _tag('td', $fileInfo['root_path']).
                                _tag('td', $fileInfo['basename']).  
                                _tag('td', format_date($fileInfo['modified'], 'g', $sf_user->getCulture())).
                                _tag('td', get_posix_file_group_info_by_id($fileInfo['group'])).
                                _tag('td', get_posix_file_owner_info_by_id($fileInfo['owner'])).
                                _tag('td', format_posix_file_permissions_to_human($fileInfo['permissions'])).
                                _tag('td', format_file_size_from_bytes($fileInfo['size'])).
                                _tag('td', $actionsHTML)
                            );
                            $totalLogSize += $fileInfo['size'];
                        }            
                    }    
                }
            echo _close('tbody');
        echo _close('table');

        echo _open('div.dm_form_action_bar.dm_form_action_bar_bottom.clearfix');
            echo _open('ul.sf_admin_actions.clearfix');
                echo _tag('li', _tag('input.batch-clean-button', array('type'=>'submit', 'value'=>__('Batch clean selected'), 'name'=>'_batch_clean', 'json'=>array(
                    'message'=>__('Please select logs for batch clean.')
                ))));
                echo _tag('li', _tag('input.clean-all-button', array('type'=>'button', 'value'=>__('Clean all logs'), 'name'=>'_clean_all', 'json'=>array(
                    'message'=>__('Are you shore that you want to clean all logs?'),
                    'action' => _link('dmLogFileManagerAdmin/cleanAll')->param('key','CleanAll')->getHref()
                ))));
            echo _close('ul');
            echo _tag('div.dm_help_wrap', array('style'=>'float:right; margin-top:2px;'), __('Total log stored in: '). " ". format_file_size_from_bytes($totalLogSize));
        echo _close('div');
    echo _close('div');

    echo _open('div.dm_box.big');
        echo _tag('h1.title', __('Setup a cron to clean up logs'));
        echo _open('div.dm_box_inner.documentation');
            echo _tag('p', __('Most UNIX and GNU/Linux systems allows for task planning through a mechanism known as cron. The cron checks a configuration file (a crontab) for commands to run at a certain time.'));
            echo _tag('p.mt10.mb10', __('Open /etc/crontab and add the line:'));
            echo _tag('code', _tag('pre', sprintf('@monthly www-data /path/to/php %s/symfony dm:log-file-cleaner', sfConfig::get('sf_root_dir'))));
            echo _tag('p.mt10', __('For more information on the crontab configuration file format, type man 5 crontab in a terminal.'));
        echo _close('div');
    echo _close('form');
echo _close('div');