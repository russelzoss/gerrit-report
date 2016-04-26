<?php

/*
 * Global settings. Adjust to your needs.
 */

define ('CURRENT_DATE', date('Y-m-d'));

/*** DIRECTORY settings ***/
define('TEAMS_DIR', 'teams');

/*** GERRIT settings ***/
define('GERRIT_HOST', 'review.your-gerrit-host.org');
define('GERRIT_SSH_PORT', '22222');
define('GERRIT_USER', 'gerrituser');
define('GERRIT_QUERY', '"status:open (branch:some-branch OR branch:another-branch)"');

/* Regular expression to match against Subject. If found, the patch will be skipped.
 * You can extend the regex if needed, for example '/HOTFIX|ES2\.0/i'
 * When not in use, comment out.
 */
define('GERRIT_EXCLUDE_REGEX', '/HOTFIX/i');

/*** PROXY settings ***/
define('PROXY', '10.0.0.1:80');
define('USE_PROXY', false);

/*** SHAREPOINT settings ***/
define('SHAREPOINT_ROW_LIMIT', 150);
define('SHAREPOINT_SERVER', 'sharepoint.example.com');
define('SHAREPOINT_USER', 'shareuser');
define('SHAREPOINT_PASSWORD', 'sharepass');

/* Local path to the Lists.asmx WSDL file (localhost). You must first download
 * it manually from your SharePoint site (which should be available at
 * yoursharepointsite.com/subsite/_vti_bin/Lists.asmx?WSDL)
 */
define('SHAREPOINT_WSDL', 'Lists.asmx.xml');

/* A string that contains either the display name or the GUID for the list.
 * It is recommended that you use the GUID, which must be surrounded by curly
 * braces ({}). 
 */
define('SHAREPOINT_LIST_NAME', '{AA888888-Z6Z6-4B44-X99X-QQQ1QQ1Q1Q11}');

/*** Export settings ***/
define ('REPORT_DIR', 'reports');
define ('EXCEL_FILENAME', 'open_changes_report-'.CURRENT_DATE.'.xlsx');

/*** MAIL settigs ***/
define ('SMTP_HOST', 'smtp.foo.bar');

$MAIL_FROM = array('Your Name' => 'your.mail@example.com');

$MAIL_TO = array(
    'Your Boss'         => 'cto@example.com', 
);

$MAIL_CC = array(
    'Your Name'         => 'your.mail@examle.com', 
);

define ('MAIL_SUBJECT', 'Open changes report for '.CURRENT_DATE);

define ('MSG_BODY', 'Open changes report for '
        .CURRENT_DATE.' [some-branch, another-branch]
Please see attachment '.EXCEL_FILENAME.'

--
Best regards,
Integration Team
');


?>
