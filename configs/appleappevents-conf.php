<?
// This file contains the information needed for the application to connect to its MySQL
//  data store.  Do not delete this file unless you know what you are doing!

$host = 'localhost';
#$host = '10.10.1.248';
$user = 'techit';
$pass = 'PASSWORD';
$db   = 'appleappevents';

define('APPLICATION_NAME', 'AppleProEvents');
define('BUG_REPORT_ADDRESS', 'bugs@techitsolutions.com');
#define('DEBUG_CONSOLE', '1');

$PROJECT_ADDRESS = 'http://appletour.techitweb.com/'; // Site URL with trailing slash

$TOTAL_DATE_FIELDS = 7; // This is how many date fields we will give the client for Events
?>
