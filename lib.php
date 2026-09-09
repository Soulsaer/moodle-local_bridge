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

/**
 * Bridge navigation callbacks.
 *
 * @param global_navigation $navigation The global navigation object.
 * @return void
 */
function local_bridge_extend_navigation(global_navigation $navigation): void {
    if (!isloggedin() || isguestuser() || !has_capability('local/bridge:manage', context_system::instance())) {
        return;
    }

    $url = new moodle_url('/local/bridge/index.php');
    $navigation->add(
        get_string('pluginname', 'local_bridge'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_bridge'
    );
}
