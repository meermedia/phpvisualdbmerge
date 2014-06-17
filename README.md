phpvisualdbmerge
================

PHP Visual DB Merge

This is a tool to visually merge two databases and download the result, built for PHP and MySQL.

I have tweaked it to merge databases for Drupal 6 and 7, but other CMS based databases like WordPress will generally also work.
PHP Visual DB Merge will help you deploy new versions of websites if you work in DTAP or other release cycles. Select you develop, test or staging database on the left, and your live website on the right. You can now merge all tables in the databases, selecting for each table if you want to use the development or live version.

Option are: use left, use right, but you can also merge two tables, exclude them or empty them.
If you use Drupal, you can use one of the presets to pre-select which tables contain content. I have also made a drupal-auto setting which detects tables with content based on the kinds of columns available in the table.

This tool will NOT write back to your database. It is designed to use read-only users for safety, and gives you a gzipped sql file to download. You can import this file in any mysql tool you like.

Please note this is a very early version and currently not yet completed.

DONE:
- database selection
- table comparison
- presets for drupal 6/7/auto
- warning for column mismatch
- the actual export function

TODO:
- customisable presets

INSTALLATION INSTRUTIONS:
- copy the sample.config.php to a config.php
- create (if not already available) read-only users for your MySQL databases: global 'read' privileges
- fill in details for your users in the config.php
- put folder behind .htaccess or other password restricted environment

===============

PHP Visual DB Merge uses a modified version of phpMyDumper (details see subdirectory). It was modified to work work OO mysqli instead of procedural mysql extensions, in order to make it PHP5.5 proof.