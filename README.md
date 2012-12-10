dmLogFileManagerPlugin for Diem Extended
===============================

Author: [TheCelavi](http://www.runopencode.com/about/thecelavi)
Version: 1.0.0
Stability: Stable  
Date: December 9th, 2012  
Courtesy of [Run Open Code](http://www.runopencode.com)   
License: [Free for all](http://www.runopencode.com/terms-and-conditions/free-for-all)

dmLogFileManagerPlugin for Diem Extended is manager for log files in Diem Extended. 
It can be used for download of log files, as well as for cleaning their 
content.

Configuration
-------------

In `dmLogFileManagerPlugin/config/dm/config.yml` are default configuration values.

	default:
	  dmLogFileManagerPlugin:
	    extensions:
	      - log
	    locations:
	      Production:
	        - %SF_ROOT_DIR%/data/dm/log
	      Development:
	        - %SF_LOG_DIR%

`extensions` configures possible extensions of log files. Default is `.log`.

`locations` defines the sections of log files, as well as directory where those
files can be found.

dmLogFileManagerPlugin will manage only files that are configured.

Administration
---------------
Go to `System > Log > Files` and you will see the list of log files. Each
file can be:

- Downloaded
- Cleaned (emptied content)

In order to see list of log files and to download some or all of them, user 
must have a `see_log` permission associated.

In order to clean log file, user must have a `log` permission associated.

Task
----------------
Logs can be cleaned via console, by using a task `dm:log-file-cleaner`.

By default, all log files are being cleaned. However, you can provide additional
parameters:

- `section=[name-of-the-section]` - cleans logs from provided section name only
- `file=[path-to-log-file]` - cleans given log file

Shortcuts for `section` is `s` and for `file` is `f`.

The task can be executed periodically, via cron.

