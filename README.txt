wpkgExpress is a web-frontend to the WPKG software deployment system.

Requirements
============
 - Apache (with at least mod_rewrite enabled)
 - PHP 5.0.2 or newer
 - Any (SQL-based) CakePHP 1.2.x DataSource (i.e. mysql, mssql, postgres, etc) -- Note: thus far only mysql, sqlite (v2.x), and sqlite3 have been tested
	* Note: Most DataSources rely on certain PHP extensions being enabled, whether they're compiled in or dynamically loaded.
		This is important to know because some of the extensions required by some DataSources may not be available by default for some PHP distributions.
		For example: sqlite DataSource requires only the sqlite PHP extension, whereas the sqlite3 DataSource requires the pdo and pdo_sqlite PHP extensions.

Getting Started
===============
 - Uncompress this archive to a directory on your webserver that is reachable via a browser (Firefox 3.x is recommended for best results).
 - Ensure Apache is correctly configured by following steps 1 and 2 from here: http://book.cakephp.org/view/37/Apache-and-mod_rewrite-and-htaccess
 - Start the wpkgExpress installation process by navigating to (replacing 'yourserver' with your hostname and 'someplace' with the
   directory containing wpkgExpress): http://yourserver/someplace/installer
 - Follow and complete the short installation wizard and you're set!
	* Note: sqlite/sqlite3 users only need to set the value of the "Database Name" field to the absolute path to the sqlite/sqlite3 database.
		If the sqlite/sqlite3 database file does not exist, it will automatically be created.
 - If you have any problems installing or operating wpkgExpress, please first check this wiki page if the issue has been previously addressed: http://code.google.com/p/wpkgexpress/wiki/CommonIssues
   If the issue is not listed there, feel free to file an issue (bug or enhancement) here: http://code.google.com/p/wpkgexpress/issues/list
   Otherwise, you can leave a message on the wpkgExpress Google group here: http://groups.google.com/group/wpkgexpress-support
 
For the latest updates and relevant information for this project, visit http://code.google.com/p/wpkgexpress

Upgrading
=========
See UPGRADE.txt.