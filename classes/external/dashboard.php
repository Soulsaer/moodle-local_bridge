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

namespace local_bridge\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_bridge\local\dashboard_service;
use local_bridge\local\event_catalogue;

/**
 * Dashboard AJAX endpoint.
 *
 * @package local_bridge
 */
class dashboard extends external_api {
    /**
     * Describe parameters for retrieving dashboard data.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Return dashboard data.
     *
     * @return array Dashboard data.
     */
    public static function execute(): array {
        global $DB;
        self::validate_parameters(self::execute_parameters(), []);
        self::validate_context(\context_system::instance());
        require_capability('local/bridge:manage', \context_system::instance());

        $data = (new dashboard_service())->get_dashboard_data();
        $data['events'] = event_catalogue::get_events();
        $data['eventcount'] = count($data['events']);
        $data['automationcount'] = (int) $DB->count_records('local_bridge_automation');
        return $data;
    }

    /**
     * Describe the dashboard return structure.
     *
     * @return external_single_structure The return structure.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'activecount' => new external_value(PARAM_INT),
            'inactivecount' => new external_value(PARAM_INT),
            'activepercent' => new external_value(PARAM_INT),
            'automationcount' => new external_value(PARAM_INT),
            'totaljobs' => new external_value(PARAM_INT),
            'successjobs' => new external_value(PARAM_INT),
            'failedjobs' => new external_value(PARAM_INT),
            'successrate' => new external_value(PARAM_FLOAT),
            'hasexecutions' => new external_value(PARAM_BOOL),
            'executions' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT),
                'event' => new external_value(PARAM_TEXT),
                'automation' => new external_value(PARAM_TEXT),
                'status' => new external_value(PARAM_ALPHANUMEXT),
                'statuslabel' => new external_value(PARAM_TEXT),
                'time' => new external_value(PARAM_TEXT),
                'attempts' => new external_value(PARAM_INT),
                'code' => new external_value(PARAM_TEXT),
            ])),
            'events' => new external_multiple_structure(new external_single_structure([
                'name' => new external_value(PARAM_RAW),
                'label' => new external_value(PARAM_TEXT),
                'group' => new external_value(PARAM_ALPHANUMEXT),
                'icon' => new external_value(PARAM_TEXT),
                'description' => new external_value(PARAM_TEXT),
            ])),
            'eventcount' => new external_value(PARAM_INT),
        ]);
    }
}
