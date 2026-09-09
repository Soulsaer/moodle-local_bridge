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
use local_bridge\local\automation_service;
use local_bridge\local\delivery_service;
use local_bridge\local\event_catalogue;

/**
 * Automation AJAX endpoints.
 *
 * @package local_bridge
 */
class automation extends external_api {
    /**
     * Validate the current user's automation management access.
     */
    private static function validate_manage(): void {
        self::validate_context(\context_system::instance());
        require_capability('local/bridge:manage', \context_system::instance());
    }

    /**
     * Describe parameters for listing automations.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function get_list_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, get_string('pagenumber', 'local_bridge'), VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, get_string('rowsperpageparam', 'local_bridge'), VALUE_DEFAULT, 10),
            'search' => new external_value(PARAM_TEXT, get_string('searchautomations', 'local_bridge'), VALUE_DEFAULT, ''),
            'sortkey' => new external_value(PARAM_ALPHANUMEXT, get_string('automation', 'local_bridge'), VALUE_DEFAULT, 'name'),
            'sortdir' => new external_value(PARAM_ALPHA, 'asc', VALUE_DEFAULT, 'asc'),
        ]);
    }

    /**
     * Return a paginated list of automations.
     *
     * @param int $page Page number.
     * @param int $perpage Number of rows per page.
     * @param string $search Search text.
     * @param string $sortkey Sort key.
     * @param string $sortdir Sort direction.
     * @return array Automation list data.
     */
    public static function get_list(
        int $page = 0,
        int $perpage = 10,
        string $search = '',
        string $sortkey = 'name',
        string $sortdir = 'asc'
    ): array {
        global $DB;
        $params = self::validate_parameters(self::get_list_parameters(), [
            'page' => $page, 'perpage' => $perpage, 'search' => $search, 'sortkey' => $sortkey, 'sortdir' => $sortdir,
        ]);
        [$page, $perpage, $search, $sortkey, $sortdir] = [
            $params['page'], $params['perpage'], trim($params['search']), $params['sortkey'], strtolower($params['sortdir']),
        ];
        self::validate_manage();
        $perpage = min(100, max(10, $perpage));
        $where = '';
        $queryparams = [];
        if ($search !== '') {
            $where = "WHERE (a.name LIKE :searchname OR a.eventname LIKE :searchevent OR a.endpoint LIKE :searchendpoint)";
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $queryparams['searchname'] = $searchvalue;
            $queryparams['searchevent'] = $searchvalue;
            $queryparams['searchendpoint'] = $searchvalue;
        }

        $total = (int) $DB->count_records_sql(
            "SELECT COUNT(1) FROM {local_bridge_automation} a $where",
            $queryparams
        );
        $sortmap = [
            'name' => 'a.name',
            'event' => 'a.eventname',
            'endpoint' => 'a.endpoint',
            'enabled' => 'a.enabled',
            'updated' => 'a.timemodified',
        ];
        $sortcolumn = $sortmap[$sortkey] ?? $sortmap['name'];
        $sortdirection = $sortdir === 'desc' ? 'DESC' : 'ASC';
        $offset = max(0, $page) * $perpage;
        $records = $DB->get_records_sql(
            "SELECT a.* FROM {local_bridge_automation} a $where ORDER BY $sortcolumn $sortdirection, a.id DESC",
            $queryparams,
            $offset,
            $perpage
        );
        $rows = [];
        foreach ($records as $record) {
            $rows[] = self::format_record($record);
        }
        return [
            'automations' => $rows,
            'total' => $total,
            'page' => max(0, $page),
            'perpage' => $perpage,
            'events' => event_catalogue::get_events(),
        ];
    }

    /**
     * Describe the return structure for the automation list.
     *
     * @return external_single_structure The return structure.
     */
    public static function get_list_returns(): external_single_structure {
        return new external_single_structure([
            'automations' => new external_multiple_structure(self::automation_structure()),
            'total' => new external_value(PARAM_INT),
            'page' => new external_value(PARAM_INT),
            'perpage' => new external_value(PARAM_INT),
            'events' => new external_multiple_structure(new external_single_structure([
                'name' => new external_value(PARAM_RAW),
                'label' => new external_value(PARAM_TEXT),
                'group' => new external_value(PARAM_ALPHANUMEXT),
                'icon' => new external_value(PARAM_TEXT),
                'description' => new external_value(PARAM_TEXT),
            ])),
        ]);
    }

    /**
     * Describe parameters for retrieving an automation.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function get_one_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, get_string('automationid', 'local_bridge')),
        ]);
    }

    /**
     * Return one automation and its available events.
     *
     * @param int $id Automation ID.
     * @return array Automation data.
     */
    public static function get_one(int $id): array {
        global $DB;
        $params = self::validate_parameters(self::get_one_parameters(), ['id' => $id]);
        $id = $params['id'];
        self::validate_manage();
        if ($id === 0) {
            return [
                'automation' => [
                    'id' => 0,
                    'name' => '',
                    'eventname' => array_key_first(event_catalogue::EVENTS),
                    'event' => event_catalogue::label(array_key_first(event_catalogue::EVENTS)),
                    'endpoint' => '',
                    'enabled' => true,
                    'timemodified' => 0,
                    'updated' => '',
                    'updatedlabel' => get_string('updatedat', 'local_bridge'),
                    'secretset' => false,
                    'secret' => '',
                ],
                'events' => event_catalogue::get_events(),
            ];
        }
        $record = $DB->get_record('local_bridge_automation', ['id' => $id], '*', MUST_EXIST);
        return ['automation' => self::format_record($record, true), 'events' => event_catalogue::get_events()];
    }

    /**
     * Describe the return structure for one automation.
     *
     * @return external_single_structure The return structure.
     */
    public static function get_one_returns(): external_single_structure {
        return new external_single_structure([
            'automation' => self::automation_structure(true),
            'events' => new external_multiple_structure(new external_single_structure([
                'name' => new external_value(PARAM_RAW),
                'label' => new external_value(PARAM_TEXT),
                'group' => new external_value(PARAM_ALPHANUMEXT),
                'icon' => new external_value(PARAM_TEXT),
                'description' => new external_value(PARAM_TEXT),
            ])),
        ]);
    }

    /**
     * Describe parameters for saving an automation.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function save_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, get_string('automationid', 'local_bridge'), VALUE_DEFAULT, 0),
            'name' => new external_value(PARAM_TEXT, get_string('automationname', 'local_bridge')),
            'eventname' => new external_value(PARAM_RAW, get_string('moodleeventname', 'local_bridge')),
            'endpoint' => new external_value(PARAM_URL, get_string('httpsendpoint', 'local_bridge')),
            'secret' => new external_value(PARAM_TEXT, get_string('optionalsigningsecret', 'local_bridge'), VALUE_DEFAULT, ''),
            'enabled' => new external_value(PARAM_BOOL, get_string('enabled', 'local_bridge'), VALUE_DEFAULT, true),
        ]);
    }

    /**
     * Create or update an automation.
     *
     * @param int $id Automation ID, or zero for a new automation.
     * @param string $name Automation name.
     * @param string $eventname Moodle event name.
     * @param string $endpoint HTTPS endpoint.
     * @param string $secret Optional signing secret.
     * @param bool $enabled Whether the automation is enabled.
     * @return array Saved automation data.
     */
    public static function save(
        int $id,
        string $name,
        string $eventname,
        string $endpoint,
        string $secret = '',
        bool $enabled = true
    ): array {
        global $USER, $DB;
        $params = self::validate_parameters(self::save_parameters(), [
            'id' => $id, 'name' => $name, 'eventname' => $eventname, 'endpoint' => $endpoint,
            'secret' => $secret, 'enabled' => $enabled,
        ]);
        [$id, $name, $eventname, $endpoint, $secret, $enabled] = [
            $params['id'], $params['name'], $params['eventname'], $params['endpoint'],
            $params['secret'], $params['enabled'],
        ];
        self::validate_manage();
        $name = trim($name);
        $endpoint = trim($endpoint);
        if ($name === '') {
            throw new \invalid_parameter_exception(get_string('automationnamerequired', 'local_bridge'));
        }
        if (!isset(event_catalogue::EVENTS[$eventname])) {
            throw new \invalid_parameter_exception(get_string('invalidtrigger', 'local_bridge'));
        }
        if (!automation_service::is_safe_endpoint($endpoint)) {
            throw new \invalid_parameter_exception(get_string('invalidendpoint', 'local_bridge'));
        }
        $data = (object) compact('name', 'eventname', 'endpoint', 'secret', 'enabled');
        if ($id) {
            automation_service::update($id, $data, $USER->id);
        } else {
            $id = automation_service::create($data, $USER->id);
        }
        $record = $DB->get_record('local_bridge_automation', ['id' => $id], '*', MUST_EXIST);
        return [
            'automation' => self::format_record($record, true),
            'message' => get_string($id ? 'automationupdated' : 'automationcreated', 'local_bridge'),
        ];
    }

    /**
     * Describe the return structure for saving an automation.
     *
     * @return external_single_structure The return structure.
     */
    public static function save_returns(): external_single_structure {
        return new external_single_structure([
            'automation' => self::automation_structure(true),
            'message' => new external_value(PARAM_TEXT),
        ]);
    }

    /**
     * Describe parameters for deleting an automation.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function delete_parameters(): external_function_parameters {
        return new external_function_parameters(['id' => new external_value(PARAM_INT)]);
    }

    /**
     * Delete an automation.
     *
     * @param int $id Automation ID.
     * @return array Operation result.
     */
    public static function delete(int $id): array {
        $params = self::validate_parameters(self::delete_parameters(), ['id' => $id]);
        $id = $params['id'];
        self::validate_manage();
        automation_service::delete($id);
        return ['success' => true, 'message' => get_string('automationdeleted', 'local_bridge')];
    }

    /**
     * Describe the return structure for deleting an automation.
     *
     * @return external_single_structure The return structure.
     */
    public static function delete_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
            'message' => new external_value(PARAM_TEXT),
        ]);
    }

    /**
     * Describe parameters for enabling or disabling an automation.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function toggle_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT),
            'enabled' => new external_value(PARAM_BOOL),
        ]);
    }

    /**
     * Enable or disable an automation.
     *
     * @param int $id Automation ID.
     * @param bool $enabled Whether the automation should be enabled.
     * @return array Updated automation data.
     */
    public static function toggle(int $id, bool $enabled): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::toggle_parameters(), ['id' => $id, 'enabled' => $enabled]);
        $id = $params['id'];
        $enabled = $params['enabled'];
        self::validate_manage();
        $record = $DB->get_record('local_bridge_automation', ['id' => $id], '*', MUST_EXIST);
        $record->enabled = $enabled ? 1 : 0;
        $record->timemodified = time();
        $record->userid = $USER->id;
        $DB->update_record('local_bridge_automation', $record);
        return [
            'automation' => self::format_record($record),
            'message' => get_string(
                $enabled ? 'automationenabled' : 'automationdisabledbyuser',
                'local_bridge'
            ),
        ];
    }

    /**
     * Describe the return structure for toggling an automation.
     *
     * @return external_single_structure The return structure.
     */
    public static function toggle_returns(): external_single_structure {
        return new external_single_structure([
            'automation' => self::automation_structure(),
            'message' => new external_value(PARAM_TEXT),
        ]);
    }

    /**
     * Describe parameters for testing an automation.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function test_parameters(): external_function_parameters {
        return new external_function_parameters(['id' => new external_value(PARAM_INT)]);
    }

    /**
     * Test an automation endpoint.
     *
     * @param int $id Automation ID.
     * @return array Test result.
     */
    public static function test(int $id): array {
        global $DB;
        $params = self::validate_parameters(self::test_parameters(), ['id' => $id]);
        $id = $params['id'];
        self::validate_manage();
        $record = $DB->get_record('local_bridge_automation', ['id' => $id], '*', MUST_EXIST);
        $payload = json_encode([
            'bridge_version' => '0.3',
            'event' => 'bridge.test',
            'automation_id' => (int) $record->id,
            'automation_name' => format_string($record->name),
            'test' => true,
            'timecreated' => time(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        try {
            $result = delivery_service::send($record->endpoint, $payload, 15, $record->secret ?? '');
            return [
                'success' => (bool) $result['success'],
                'code' => (int) $result['code'],
                'response' => (string) $result['response'],
                'message' => $result['success']
                    ? get_string('testsuccess', 'local_bridge')
                    : get_string('testfailed', 'local_bridge', ['code' => $result['code']]),
            ];
        } catch (\Throwable $exception) {
            return ['success' => false, 'code' => 0, 'response' => '', 'message' => $exception->getMessage()];
        }
    }

    /**
     * Describe the return structure for testing an automation.
     *
     * @return external_single_structure The return structure.
     */
    public static function test_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
            'code' => new external_value(PARAM_INT),
            'response' => new external_value(PARAM_RAW),
            'message' => new external_value(PARAM_TEXT),
        ]);
    }

    /**
     * Build the external automation return structure.
     *
     * @param bool $detail Whether to include detailed fields.
     * @return external_single_structure The automation structure.
     */
    private static function automation_structure(bool $detail = false): external_single_structure {
        $fields = [
            'id' => new external_value(PARAM_INT),
            'name' => new external_value(PARAM_TEXT),
            'eventname' => new external_value(PARAM_RAW),
            'event' => new external_value(PARAM_TEXT),
            'endpoint' => new external_value(PARAM_URL),
            'enabled' => new external_value(PARAM_BOOL),
            'timemodified' => new external_value(PARAM_INT),
            'updated' => new external_value(PARAM_TEXT),
            'updatedlabel' => new external_value(PARAM_TEXT),
        ];
        if ($detail) {
            $fields['secretset'] = new external_value(PARAM_BOOL);
            $fields['secret'] = new external_value(PARAM_TEXT);
        }
        return new external_single_structure($fields);
    }

    /**
     * Format an automation database record for the external API.
     *
     * @param object $record Automation record.
     * @param bool $detail Whether to include detailed fields.
     * @return array Formatted automation data.
     */
    private static function format_record(object $record, bool $detail = false): array {
        $row = [
            'id' => (int) $record->id,
            'name' => format_string($record->name),
            'eventname' => $record->eventname,
            'event' => event_catalogue::label($record->eventname),
            'endpoint' => $record->endpoint,
            'enabled' => !empty($record->enabled),
            'timemodified' => (int) $record->timemodified,
            'updated' => userdate($record->timemodified, get_string('strftimedatetimeshort', 'langconfig')),
            'updatedlabel' => get_string('updatedat', 'local_bridge'),
        ];
        if ($detail) {
            $row['secretset'] = !empty($record->secret);
            $row['secret'] = '';
        }
        return $row;
    }
}
