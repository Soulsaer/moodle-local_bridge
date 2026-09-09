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
 * Bridge plugin file.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/bridge:manage', $context);

$PAGE->set_url(new moodle_url('/local/bridge/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_bridge'));
$PAGE->set_heading(get_string('pluginname', 'local_bridge'));
$PAGE->requires->css(new moodle_url('/local/bridge/thirdparty/bootstrap-icons/bootstrap-icons.min.css'));
$PAGE->requires->css(new moodle_url('/local/bridge/styles.css'));
$PAGE->requires->js_call_amd('local_bridge/app', 'init');

$plugininfo = core_plugin_manager::instance()->get_plugin_info('local_bridge');
$release = $plugininfo ? $plugininfo->release : '0.3.10';

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_bridge/shell', [
    'sesskey' => sesskey(),
    'username' => fullname($USER),
    'avatarinitial' => strtoupper(substr(fullname($USER), 0, 1)),
    'version' => $release,
    'strings' => [
        'pluginname' => get_string('pluginname', 'local_bridge'),
        'coreedition' => get_string('coreedition', 'local_bridge'),
        'overview' => get_string('overview', 'local_bridge'),
        'automations' => get_string('automations', 'local_bridge'),
        'executions' => get_string('executions', 'local_bridge'),
        'settings' => get_string('settings', 'local_bridge'),
        'administrator' => get_string('administrator', 'local_bridge'),
        'productinformation' => get_string('productinformation', 'local_bridge'),
        'opennavigation' => get_string('opennavigation', 'local_bridge'),
    ],
]);
echo $OUTPUT->footer();
