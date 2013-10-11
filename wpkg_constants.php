<?php
/*
 * wpkgExpress : A web-based frontend to wpkg
 * Copyright 2009 Brian White
 *
 * This file is part of wpkgExpress.
 *
 * wpkgExpress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wpkgExpress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wpkgExpress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>
<?php
// XSD stuff
define('XSD_PATH_PACKAGES', APP . 'extra' . DS . 'packages.xsd');
define('XSD_PATH_PROFILES', APP . 'extra' . DS . 'profiles.xsd');
define('XSD_PATH_HOSTS', APP . 'extra' . DS . 'hosts.xsd');

// Packages
define('PACKAGE_REBOOT_FALSE', 0);
define('PACKAGE_REBOOT_TRUE', 1);
define('PACKAGE_REBOOT_POSTPONED', 2);

define('PACKAGE_EXECUTE_NORMAL', 0);
define('PACKAGE_EXECUTE_ALWAYS', 1);
define('PACKAGE_EXECUTE_ONCE', 2);

define('PACKAGE_NOTIFY_FALSE', 0);
define('PACKAGE_NOTIFY_TRUE', 1);

// Package Actions
define('ACTION_TYPE_INSTALL', 0);
define('ACTION_TYPE_UPGRADE', 1);
define('ACTION_TYPE_DOWNGRADE', 2);
define('ACTION_TYPE_REMOVE', 3);

// Package Action Exit Code Reboot options
define('EXITCODE_REBOOT_FALSE', 0);
define('EXITCODE_REBOOT_TRUE', 1);
define('EXITCODE_REBOOT_DELAYED', 2);
define('EXITCODE_REBOOT_POSTPONED', 3);

// Package Checks
define('CHECK_TYPE_LOGICAL', 0);
define('CHECK_TYPE_REGISTRY', 1);
define('CHECK_TYPE_FILE', 2);
define('CHECK_TYPE_UNINSTALL', 3);
define('CHECK_TYPE_EXECUTE', 4);

// Package Check Conditions
define('CHECK_CONDITION_LOGICAL_NOT', 0);
define('CHECK_CONDITION_LOGICAL_AND', 1);
define('CHECK_CONDITION_LOGICAL_OR', 2);
define('CHECK_CONDITION_LOGICAL_AT_LEAST', 3);
define('CHECK_CONDITION_LOGICAL_AT_MOST', 4);

define('CHECK_CONDITION_REGISTRY_EXISTS', 5);
define('CHECK_CONDITION_REGISTRY_EQUALS', 6);

define('CHECK_CONDITION_FILE_EXISTS', 7);
define('CHECK_CONDITION_FILE_SIZE_EQUALS', 8);
define('CHECK_CONDITION_FILE_VERSION_SMALLER_THAN', 9);
define('CHECK_CONDITION_FILE_VERSION_LESS_THAN_OR_EQUAL_TO', 10);
define('CHECK_CONDITION_FILE_VERSION_EQUAL_TO', 11);
define('CHECK_CONDITION_FILE_VERSION_GREATER_THAN', 12);
define('CHECK_CONDITION_FILE_VERSION_GREATER_THAN_OR_EQUAL_TO', 13);

define('CHECK_CONDITION_EXECUTE_EXIT_CODE_SMALLER_THAN', 14);
define('CHECK_CONDITION_EXECUTE_EXIT_CODE_LESS_THAN_OR_EQUAL_TO', 15);
define('CHECK_CONDITION_EXECUTE_EXIT_CODE_EQUAL_TO', 16);
define('CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN', 17);
define('CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN_OR_EQUAL_TO', 18);

define('CHECK_CONDITION_UNINSTALL_EXISTS', 19);

// Variable Owner Type
define('VARIABLE_TYPE_PACKAGE', 0);
define('VARIABLE_TYPE_PROFILE', 1);
define('VARIABLE_TYPE_HOST', 2);

// Tooltip and other string constants
define('TOOLTIP_PACKAGE_ENABLED', 'If enabled, this package will appear in package (xml) lists.');
define('TOOLTIP_PACKAGE_NAME', 'Human-readable name that identifies this package (e.g. Foo Bar Baz Ultra Edition).');
define('TOOLTIP_PACKAGE_ID', 'Unique ID containing only letters, numbers, underscores and hyphens (e.g. fooBarBaz_ultra OR foo-bar-baz-ultra).');
define('TOOLTIP_PACKAGE_REVISION', 'An positive integer or positive decimal value that denotes the version/revision of this package (e.g. 1 OR 1.0.3). If you have periods in the revision, you must have at least one digit after each period.');
define('TOOLTIP_PACKAGE_PRIORITY', "An integer value (any positive non-decimal number) that indicates this package's priority. Higher priorities take precedence over lower priorities (e.g. 10).");
define('TOOLTIP_PACKAGE_EXECUTE', "'Always' will always execute all of this package's Install actions every time, whereas 'Once' will only execute them once. Both of these modes will always ignore all Package checks.");
define('TOOLTIP_PACKAGE_REBOOT', 'If set, the system will reboot after installation of this package.');
define('TOOLTIP_PACKAGE_NOTIFY', "If set, the system will notify the user via 'net send' when installation starts and stops.");
define('TOOLTIP_PACKAGE_NOTES', 'Additional notes about this package. Not used by WPKG.');
define('TOOLTIP_PACKAGE_LASTMODIFIED', 'The last date and time this package was modified.');
define('TOOLTIP_PACKAGE_DEPENDSON', 'Packages that this package depends on.');
define('TOOLTIP_PACKAGE_DEPENDEDONBY', 'Packages that depend on this package.');
define('TOOLTIP_PACKAGE_PROFILES', 'Profiles that contain this package.');

define('TOOLTIP_PACKAGECHECK_TYPE', 'The kind of check to be performed.');
define('TOOLTIP_PACKAGECHECK_CONDITION', 'Describes how the package check is to be carried out for the selected type.');
define('TOOLTIP_PACKAGECHECK_PATH', 'The contents of this field vary depending on the package check type and condition.');
define('TOOLTIP_PACKAGECHECK_VALUE', 'The contents of this field vary depending on the package check type and condition.');
define('TOOLTIP_PACKAGECHECK_PARENT', 'Determines where the package check should be placed in the package check hierarchy. All potential parents are listed in order.');

define('TOOLTIP_PACKAGEACTION_TYPE', 'The kind of action to be executed.');
define('TOOLTIP_PACKAGEACTION_COMMAND', 'The command to be executed.');
define('TOOLTIP_PACKAGEACTION_TIMEOUT', 'The maximum number of seconds to wait for the action to execute. Default is 3600.');
define('TOOLTIP_PACKAGEACTION_WORKDIR', 'The working directory to use when executing this action.');

define('TOOLTIP_EXITCODE_CODE', 'This is the expected exit code produced by the associated command/package action.');
define('TOOLTIP_EXITCODE_REBOOT', 'Determines if and what kind of a reboot is performed when the specified exit code is detected.');

define('TOOLTIP_PROFILE_ENABLED', 'If enabled, this profile will appear in profile (xml) lists.');
define('TOOLTIP_PROFILE_ID', 'Unique ID containing only letters, numbers, underscores and hyphens (e.g. base_software OR developer-suite OR complabsoft8).');
define('TOOLTIP_PROFILE_NOTES', 'Additional notes about this profile. Not used by WPKG.');
define('TOOLTIP_PROFILE_LASTMODIFIED', 'The last date and time this profile was modified.');
define('TOOLTIP_PROFILE_DEPENDSON', 'Profiles that this profile depends on.');

define('TOOLTIP_HOST_ENABLED', 'If enabled, this host will appear in profile (xml) lists.');
define('TOOLTIP_HOST_POSITION', 'The position of this host entry. This is important for regular expression-based host names as they are evaluated in order. However, non-regular expression-based host names are ALWAYS checked first.');
define('TOOLTIP_HOST_NAME', 'Either a regular expression or a literal host name. (e.g. libraryPC[0-9]+ OR principalcomp OR .+).');
define('TOOLTIP_HOST_NOTES', 'Additional notes about this host. Not used by WPKG.');
define('TOOLTIP_HOST_LASTMODIFIED', 'The last date and time this host was modified.');
define('TOOLTIP_HOST_MAINPROFILE', 'The main profile that will always be evaluated for this host.');
?>