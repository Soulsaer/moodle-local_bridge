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
use local_bridge\local\event_catalogue;

/**
 * Execution AJAX endpoints.
 *
 * @package local_bridge
 */
class execution extends external_api {
    /**
     * Validate access to execution records.
     */
    private static function validate_view(): void {
        self::validate_context(\context_system::instance());
        require_capability('local/bridge:viewexecutions', \context_system::instance());
    }

    /**
     * Describe parameters for listing executions.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function get_list_parameters(): external_function_parameters {
        return new external_function_parameters([
            'page' => new external_value(PARAM_INT, get_string('pagenumber', 'local_bridge'), VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, get_string('rowsperpageparam', 'local_bridge'), VALUE_DEFAULT, 25),
            'search' => new external_value(PARAM_TEXT, get_string('searchdeliveries', 'local_bridge'), VALUE_DEFAULT, ''),
            'sortkey' => new external_value(PARAM_ALPHANUMEXT, 'time', VALUE_DEFAULT, 'time'),
            'sortdir' => new external_value(PARAM_ALPHA, 'desc', VALUE_DEFAULT, 'desc'),
        ]);
    }

    /**
     * Return a paginated list of executions.
     *
     * @param int $page Page number.
     * @param int $perpage Number of rows per page.
     * @param string $search Search text.
     * @param string $sortkey Sort key.
     * @param string $sortdir Sort direction.
     * @return array Execution list data.
     */
    public static function get_list(
        int $page = 0,
        int $perpage = 25,
        string $search = '',
        string $sortkey = 'time',
        string $sortdir = 'desc'
    ): array {
        global $DB;
        $params = self::validate_parameters(self::get_list_parameters(), [
            'page' => $page, 'perpage' => $perpage, 'search' => $search, 'sortkey' => $sortkey, 'sortdir' => $sortdir,
        ]);
        [$page, $perpage, $search, $sortkey, $sortdir] = [
            $params['page'],
            $params['perpage'],
            $params['search'],
            $params['sortkey'],
            strtolower($params['sortdir']),
        ];
        self::validate_view();
        $perpage = min(100, max(10, $perpage));
        $search = trim($search);
        $params = [];
        $where = '';
        if ($search !== '') {
            $where = "WHERE (j.eventname LIKE :searchevent OR a.name LIKE :searchautomation OR j.status LIKE :searchstatus)";
            $searchvalue = '%' . $DB->sql_like_escape($search) . '%';
            $params['searchevent'] = $searchvalue;
            $params['searchautomation'] = $searchvalue;
            $params['searchstatus'] = $searchvalue;
        }
        $countsql = "SELECT COUNT(1)
            FROM {local_bridge_job} j
            LEFT JOIN {local_bridge_automation} a ON a.id = j.automationid
            $where";
        $total = (int) $DB->count_records_sql($countsql, $params);
        $offset = max(0, $page) * $perpage;
        $sortmap = [
            'event' => 'j.eventname',
            'automation' => 'a.name',
            'status' => 'j.status',
            'http' => 'j.responsecode',
            'attempts' => 'j.attempts',
            'time' => 'j.timecreated',
        ];
        $sortcolumn = $sortmap[$sortkey] ?? $sortmap['time'];
        $sortdirection = $sortdir === 'asc' ? 'ASC' : 'DESC';
        $sql = "SELECT j.*, a.name AS automationname
            FROM {local_bridge_job} j
            LEFT JOIN {local_bridge_automation} a ON a.id = j.automationid
            $where
            ORDER BY $sortcolumn $sortdirection, j.id DESC";
        $records = $DB->get_records_sql($sql, $params, $offset, $perpage);
        $rows = [];
        foreach ($records as $job) {
            $rows[] = self::format_record($job);
        }
        return ['executions' => $rows, 'total' => $total, 'page' => max(0, $page), 'perpage' => $perpage];
    }

    /**
     * Describe the return structure for the execution list.
     *
     * @return external_single_structure The return structure.
     */
    public static function get_list_returns(): external_single_structure {
        return new external_single_structure([
            'executions' => new external_multiple_structure(self::execution_structure()),
            'total' => new external_value(PARAM_INT),
            'page' => new external_value(PARAM_INT),
            'perpage' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * Describe parameters for retrieving an execution.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function get_one_parameters(): external_function_parameters {
        return new external_function_parameters(['id' => new external_value(PARAM_INT)]);
    }

    /**
     * Return one execution.
     *
     * @param int $id Execution ID.
     * @return array Execution data.
     */
    public static function get_one(int $id): array {
        global $DB;
        $params = self::validate_parameters(self::get_one_parameters(), ['id' => $id]);
        $id = $params['id'];
        self::validate_view();
        $sql = "SELECT j.*, a.name AS automationname, a.endpoint
                  FROM {local_bridge_job} j
                  LEFT JOIN {local_bridge_automation} a ON a.id = j.automationid
                 WHERE j.id = :id";
        $job = $DB->get_record_sql($sql, ['id' => $id], '*', MUST_EXIST);
        $row = self::format_record($job, true);
        $row['payload'] = $job->payload;
        $row['response'] = $job->response ?? '';
        $row['error'] = $job->error ?? '';
        $row['endpoint'] = $job->endpoint ?? '';
        $row['canretry'] = $job->status === 'failed';
        return ['execution' => $row];
    }

    /**
     * Describe the return structure for one execution.
     *
     * @return external_single_structure The return structure.
     */
    public static function get_one_returns(): external_single_structure {
        return new external_single_structure(['execution' => self::execution_structure(true)]);
    }

    /**
     * Describe parameters for retrying an execution.
     *
     * @return external_function_parameters The external function parameters.
     */
    public static function retry_parameters(): external_function_parameters {
        return new external_function_parameters(['id' => new external_value(PARAM_INT)]);
    }

    /**
     * Retry a failed execution.
     *
     * @param int $id Execution ID.
     * @return array Operation result.
     */
    public static function retry(int $id): array {
        global $DB;
        $params = self::validate_parameters(self::retry_parameters(), ['id' => $id]);
        $id = $params['id'];
        self::validate_view();
        $job = $DB->get_record('local_bridge_job', ['id' => $id], '*', MUST_EXIST);
        if ($job->status !== 'failed') {
            throw new \moodle_exception('cannotretry', 'local_bridge');
        }
        $job->status = 'pending';
        $job->attempts = 0;
        $job->nextrun = time();
        $job->timefinished = null;
        $job->error = null;
        $DB->update_record('local_bridge_job', $job);
        return ['success' => true, 'message' => get_string('retryqueued', 'local_bridge')];
    }

    /**
     * Describe the return structure for retrying an execution.
     *
     * @return external_single_structure The return structure.
     */
    public static function retry_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL),
            'message' => new external_value(PARAM_TEXT),
        ]);
    }

    /**
     * Build the external execution return structure.
     *
     * @param bool $detail Whether to include detailed fields.
     * @return external_single_structure The execution structure.
     */
    private static function execution_structure(bool $detail = false): external_single_structure {
        $fields = [
            'id' => new external_value(PARAM_INT),
            'event' => new external_value(PARAM_TEXT),
            'automation' => new external_value(PARAM_TEXT),
            'status' => new external_value(PARAM_ALPHANUMEXT),
            'statuslabel' => new external_value(PARAM_TEXT),
            'time' => new external_value(PARAM_TEXT),
            'attempts' => new external_value(PARAM_INT),
            'code' => new external_value(PARAM_TEXT),
            'error' => new external_value(PARAM_TEXT),
            'canretry' => new external_value(PARAM_BOOL),
        ];
        if ($detail) {
            $fields['payload'] = new external_value(PARAM_RAW);
            $fields['response'] = new external_value(PARAM_RAW);
            $fields['endpoint'] = new external_value(PARAM_URL);
            $fields['started'] = new external_value(PARAM_TEXT);
            $fields['finished'] = new external_value(PARAM_TEXT);
        }
        return new external_single_structure($fields);
    }

    /**
     * Format an execution database record for the external API.
     *
     * @param object $job Execution record.
     * @param bool $detail Whether to include detailed fields.
     * @return array Formatted execution data.
     */
    private static function format_record(object $job, bool $detail = false): array {
        $row = [
            'id' => (int) $job->id,
            'event' => event_catalogue::label($job->eventname),
            'automation' => !empty($job->automationname)
                ? format_string($job->automationname)
                : get_string('notavailable', 'local_bridge'),
            'status' => $job->status,
            'statuslabel' => get_string($job->status, 'local_bridge'),
            'time' => userdate($job->timecreated),
            'attempts' => (int) $job->attempts,
            'code' => !empty($job->responsecode) ? (string) (int) $job->responsecode : get_string('notavailable', 'local_bridge'),
            'error' => !empty($job->error) ? format_text($job->error, FORMAT_PLAIN) : '',
            'canretry' => $job->status === 'failed',
        ];
        if ($detail) {
            $row['payload'] = '';
            $row['response'] = '';
            $row['endpoint'] = !empty($job->endpoint) ? $job->endpoint : '';
            $row['started'] = !empty($job->timestarted) ? userdate($job->timestarted) : get_string('notavailable', 'local_bridge');
            $row['finished'] = !empty($job->timefinished)
                ? userdate($job->timefinished)
                : get_string('notavailable', 'local_bridge');
        }
        return $row;
    }
}
