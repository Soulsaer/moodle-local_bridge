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
 * Bridge plugin file.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_bridge_get_dashboard' => [
        'classname' => '\\local_bridge\\external\\dashboard',
        'methodname' => 'execute',
        'description' => 'Return Bridge dashboard data.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_get_automations' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'get_list',
        'description' => 'Return Bridge automations.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_get_automation' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'get_one',
        'description' => 'Return one Bridge automation.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_save_automation' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'save',
        'description' => 'Create or update a Bridge automation.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_delete_automation' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'delete',
        'description' => 'Delete a Bridge automation.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_toggle_automation' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'toggle',
        'description' => 'Enable or disable a Bridge automation.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_test_automation' => [
        'classname' => '\\local_bridge\\external\\automation',
        'methodname' => 'test',
        'description' => 'Test a Bridge automation endpoint.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
    'local_bridge_get_executions' => [
        'classname' => '\\local_bridge\\external\\execution',
        'methodname' => 'get_list',
        'description' => 'Return Bridge execution history.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/bridge:viewexecutions',
    ],
    'local_bridge_get_execution' => [
        'classname' => '\\local_bridge\\external\\execution',
        'methodname' => 'get_one',
        'description' => 'Return one Bridge execution.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/bridge:viewexecutions',
    ],
    'local_bridge_retry_execution' => [
        'classname' => '\\local_bridge\\external\\execution',
        'methodname' => 'retry',
        'description' => 'Retry a Bridge execution.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/bridge:manage',
    ],
];
