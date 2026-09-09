<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for Bridge.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Action';
$string['actions'] = 'Actions';
$string['active'] = 'Active';
$string['activeautomations'] = 'Active automations';
$string['activecountlabel'] = '{$a} active';
$string['activepercentlabel'] = '{$a}% of automations are active';
$string['activity'] = 'Integration activity';
$string['activitydesc'] = 'Keep an eye on your Moodle connections and deliveries.';
$string['administrator'] = 'Administrator';
$string['advancedoptions'] = 'Advanced delivery options';
$string['attempts'] = 'Attempts';
$string['automaticretries'] = 'Automatic retries';
$string['automaticretriesdesc'] = 'Temporary delivery failures are retried automatically up to three times.';
$string['automation'] = 'Automation';
$string['automationcreated'] = 'Automation created.';
$string['automationdeleted'] = 'Automation deleted.';
$string['automationdisabled'] = 'Automation is disabled or no longer exists.';
$string['automationdisabledbyuser'] = 'Automation disabled.';
$string['automationenabled'] = 'Automation enabled.';
$string['automationhealth'] = 'Automation health';
$string['automationhealthdesc'] = 'Current configuration status.';
$string['automationid'] = 'Automation ID';
$string['automationname'] = 'Automation name';
$string['automationname_help'] = 'Give this automation a short, recognisable name.';
$string['automationnamerequired'] = 'Please enter an automation name.';
$string['automations'] = 'Automations';
$string['automationsdesc'] = 'Simple rules that connect Moodle activity to your services.';
$string['automationupdated'] = 'Automation updated.';
$string['back'] = 'Back';
$string['basics'] = 'Basics';
$string['blockedendpoint'] = 'This endpoint is blocked by Moodle\'s outbound HTTP security policy.';
$string['bridgedashboard'] = 'Bridge';
$string['bridgeisready'] = 'Bridge is ready';
$string['bridgeisreadydesc'] = 'Create an automation when you are ready. Deliveries and system activity will appear here as they happen.';
$string['cancel'] = 'Cancel';
$string['cannotretry'] = 'Only failed deliveries can be retried.';
$string['choosestartsdesc'] = 'Bridge will listen for this Moodle event.';
$string['choosewhatstarts'] = 'Choose what starts it';
$string['choosewhere'] = 'Choose where it goes';
$string['choosewheredesc'] = 'Enter the HTTPS endpoint that should receive the event.';
$string['clearsearch'] = 'Clear search';
$string['close'] = 'Close';
$string['connections'] = 'Connections';
$string['copy'] = 'Copy';
$string['copydestination'] = 'Copy destination';
$string['copypayload'] = 'Copy payload';
$string['coreedition'] = 'Core';
$string['courses'] = 'Courses';
$string['createanautomation'] = 'Create an automation';
$string['createautomation'] = 'Create automation';
$string['createfirstautomation'] = 'Create your first automation and it will appear here.';
$string['dashboard'] = 'Dashboard';
$string['delete'] = 'Delete';
$string['deleteautomation'] = 'Delete automation';
$string['deleteconfirmmessage'] = 'Delete this automation? Its delivery history will also be removed.';
$string['deleteconfirmno'] = 'Cancel';
$string['deleteconfirmtitle'] = 'Delete automation';
$string['deleteconfirmyes'] = 'Delete';
$string['deliveries'] = 'deliveries';
$string['deliveryhistory'] = 'Delivery history';
$string['deliveryhistorydesc'] = 'See what was delivered and quickly investigate anything that needs attention.';
$string['deliveryinfo'] = 'Bridge sends a small JSON event envelope using HTTPS POST. The request is queued and retried automatically when a temporary failure occurs.';
$string['deliveryprocessing'] = 'Delivery processing';
$string['deliveryprocessingdesc'] = "Bridge uses Moodle's scheduled task system to process queued deliveries.";
$string['destination'] = 'Destination';
$string['destinationhelp'] = 'Bridge sends a JSON POST request when the event occurs.';
$string['destinationurl'] = 'Destination URL';
$string['disabled'] = 'Disabled';
$string['documentation'] = 'Documentation';
$string['done'] = 'Done';
$string['edit'] = 'Edit';
$string['editautomation'] = 'Edit automation';
$string['enableafter'] = 'Enable automation after saving';
$string['enabled'] = 'Enabled';
$string['endpoint'] = 'Endpoint URL';
$string['endpoint_help'] = 'The HTTPS endpoint Bridge should call when the selected Moodle event occurs.';
$string['endpointplaceholder'] = 'https://example.com/webhooks/moodle';
$string['enrolments'] = 'Enrolments';
$string['error'] = 'Error';
$string['event'] = 'Event';
$string['eventcoursecreated'] = 'Course created';
$string['eventcourseupdated'] = 'Course updated';
$string['eventdescriptioncoursecreated'] = 'A new Moodle course is created.';
$string['eventdescriptioncourseupdated'] = 'A Moodle course is updated.';
$string['eventdescriptionusercreated'] = 'A new Moodle user is created.';
$string['eventdescriptionuserenrolled'] = 'A user is enrolled in a course.';
$string['eventdescriptionuserupdated'] = 'A Moodle user profile is updated.';
$string['eventqueued'] = 'Event queued for delivery.';
$string['events'] = 'events';
$string['eventusercreated'] = 'User created';
$string['eventuserenrolled'] = 'User enrolled';
$string['eventuserupdated'] = 'User updated';
$string['execution'] = 'Execution';
$string['executiondetails'] = 'Execution details';
$string['executionid'] = 'Execution ID';
$string['executions'] = 'Executions';
$string['failed'] = 'Failed';
$string['faileddeliveries'] = 'Failed deliveries';
$string['failurerate'] = 'Failure rate';
$string['finished'] = 'Finished';
$string['firstautomation'] = 'Create your first automation';
$string['gettingstarted'] = 'Getting started';
$string['help'] = 'Need help?';
$string['howitworks'] = 'How Bridge works';
$string['howitworksdesc'] = 'Choose a Moodle event, tell Bridge where to send it, and Bridge takes care of delivery and retries in the background.';
$string['httprequest'] = 'HTTP request';
$string['httpresponseerror'] = 'HTTP response {$a}';
$string['httpsendpoint'] = 'HTTPS endpoint';
$string['httpstatus'] = 'HTTP status';
$string['inactive'] = 'Inactive';
$string['inactivecountlabel'] = '{$a} inactive';
$string['invalidendpoint'] = 'Please enter a valid HTTPS endpoint URL.';
$string['invalidtrigger'] = 'Please choose a supported Moodle event.';
$string['loading'] = 'Loading...';
$string['manageautomations'] = 'Manage automations';
$string['managebridge'] = 'Manage Bridge';
$string['moodleeventname'] = 'Moodle event name';
$string['moodleeventsavailable'] = 'Moodle events are currently available to automations.';
$string['nameit'] = 'Name it';
$string['nameitdesc'] = 'Use a short name you will recognise later.';
$string['neutral'] = 'No data';
$string['newautomation'] = 'New automation';
$string['next'] = 'Next';
$string['no'] = 'No';
$string['noautomations'] = 'No automations yet';
$string['noautomationsdesc'] = 'Create your first automation in less than a minute. Bridge will watch Moodle events and deliver them reliably to your endpoint.';
$string['noautomationsdisplay'] = 'There are no automations to display.';
$string['noautomationsfound'] = 'No automations found';
$string['nodeliveriesdesc'] = 'Try another search or wait for an automation to run.';
$string['nodeliveriesfound'] = 'No deliveries found';
$string['nodeliveryactivity'] = 'No delivery activity yet';
$string['nodeliveryactivitydesc'] = 'Once an automation runs, its deliveries will appear here.';
$string['nohttpresponse'] = 'No HTTP response';
$string['noresponsebody'] = 'No response body was returned by the destination.';
$string['noresults'] = 'No results';
$string['notavailable'] = 'Not available';
$string['ofautomationsactive'] = 'of automations are active';
$string['off'] = 'Off';
$string['ofthree'] = 'of 3';
$string['on'] = 'On';
$string['opennavigation'] = 'Open navigation';
$string['optional'] = 'Optional';
$string['optionalsigningsecret'] = 'Optional signing secret';
$string['overview'] = 'Overview';
$string['page'] = 'Page';
$string['pagenumber'] = 'Page number';
$string['pageof'] = 'Page {$a->page} of {$a->pages}';
$string['payload'] = 'Payload';
$string['payloadcopied'] = 'Payload copied';
$string['payloadjson'] = 'JSON';
$string['payloadraw'] = 'Raw';
$string['pending'] = 'Pending';
$string['pluginname'] = 'Bridge';
$string['plural'] = 's';
$string['previous'] = 'Previous';
$string['privacy:metadata:automation'] = 'Bridge stores automation configuration created by an administrator.';
$string['privacy:metadata:automation:userid'] = 'The user who created or last modified the automation.';
$string['privacy:metadata:job'] = 'Bridge stores delivery jobs required to deliver configured automation events.';
$string['privacy:metadata:job:external'] = 'The payload may be transmitted to an external endpoint selected by the site administrator.';
$string['privacy:metadata:job:payload'] = 'The event payload queued for delivery to the administrator-configured endpoint.';
$string['privacy:metadata:job:userid'] = 'The Moodle user ID associated with the triggering event, where available.';
$string['processing'] = 'Processing';
$string['productinformation'] = 'Bridge product information';
$string['recentactivity'] = 'Recent activity';
$string['recentactivitydesc'] = 'Your latest delivery activity.';
$string['recentexecutions'] = 'Recent executions';
$string['requestreturnederror'] = 'Request returned an error';
$string['response'] = 'Response';
$string['responsebody'] = 'Response body';
$string['result'] = 'Result';
$string['retrydelivery'] = 'Retry delivery';
$string['retrying'] = 'Retrying';
$string['retryqueued'] = 'Delivery queued for retry.';
$string['rows'] = 'Rows';
$string['rowsperpage'] = 'Rows per page';
$string['rowsperpageparam'] = 'Rows per page';
$string['saveautomation'] = 'Save automation';
$string['savechanges'] = 'Save changes';
$string['searchautomations'] = 'Search automations...';
$string['searchdeliveries'] = 'Search deliveries...';
$string['searchtryagain'] = 'Try a different search term.';
$string['secret'] = 'Signing secret';
$string['secret_help'] = 'Optional. Bridge sends an X-Bridge-Signature header using HMAC-SHA256.';
$string['sendtest'] = 'Send test';
$string['setautomationdesc'] = 'Set what should start the automation and where Bridge should send it.';
$string['settings'] = 'Settings';
$string['showingrange'] = 'Showing {$a->from}–{$a->to} of {$a->total}';
$string['signingsecret'] = 'Signing secret';
$string['signingsecrethelp'] = 'When set, Bridge adds an HMAC-SHA256 signature header to each request.';
$string['signingsecretoptional'] = 'Signing secret';
$string['signingsecretplaceholder'] = 'Leave empty to disable signing';
$string['started'] = 'Started';
$string['status'] = 'Status';
$string['stepconnect'] = 'Connect your endpoint';
$string['stepcreate'] = 'Create your first automation';
$string['stepmonitor'] = 'Monitor delivery';
$string['steptest'] = 'Send a test event';
$string['success'] = 'Success';
$string['successfulresponse'] = 'Successful response';
$string['successrate'] = 'Success rate';
$string['supportedtriggers'] = 'Supported triggers';
$string['testautomation'] = 'Send test';
$string['testbefore'] = 'Test before you go live';
$string['testbeforedesc'] = 'Bridge will send a safe test event to the configured destination.';
$string['testdelivered'] = 'Test delivered successfully';
$string['testfailed'] = 'Test delivery failed (HTTP {$a->code}).';
$string['testfailedmessage'] = 'Test delivery failed: {$a->message}';
$string['testneedsattention'] = 'Test delivery needs attention';
$string['testqueued'] = 'Test delivery queued.';
$string['testresponse'] = 'Test response';
$string['testsuccess'] = 'Test delivered successfully.';
$string['time'] = 'Time';
$string['totalexecutions'] = 'Total executions';
$string['trigger'] = 'Trigger';
$string['unabletosave'] = 'Unable to save the automation.';
$string['updateautomation'] = 'Update your automation';
$string['updated'] = 'Updated {$a}';
$string['updatedat'] = 'Updated';
$string['urlcopied'] = 'URL copied';
$string['users'] = 'Users';
$string['version'] = 'Version';
$string['versionlabel'] = 'Version';
$string['viewall'] = 'View all';
$string['viewexecutions'] = 'View Bridge executions';
$string['yes'] = 'Yes';
$string['yourautomations'] = 'Your automations';
