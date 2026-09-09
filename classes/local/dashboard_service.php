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

namespace local_bridge\local;

/**
 * Bridge plugin file.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Dashboard query service.
 *
 * @package local_bridge
 */
class dashboard_service {
    /**
     * Return dashboard summary data.
     *
     * @return array<string, mixed> Dashboard data.
     */
    public function get_dashboard_data(): array {
        global $DB;
        $automationcount = (int) $DB->count_records('local_bridge_automation');
        $activecount = (int) $DB->count_records('local_bridge_automation', ['enabled' => 1]);
        $inactivecount = $automationcount - $activecount;
        $activepercent = $automationcount > 0 ? round(($activecount / $automationcount) * 100) : 0;
        $activedash = round((M_PI * 52) * ($activepercent / 100), 2);
        $totaljobs = (int) $DB->count_records('local_bridge_job');
        $successjobs = (int) $DB->count_records('local_bridge_job', ['status' => 'success']);
        $failedjobs = (int) $DB->count_records('local_bridge_job', ['status' => 'failed']);
        $completed = $successjobs + $failedjobs;
        $successrate = $completed > 0 ? round(($successjobs / $completed) * 100, 1) : 0.0;

        $sql = "SELECT j.id, j.eventname, j.status, j.timecreated, j.attempts,
                       a.name AS automationname
                  FROM {local_bridge_job} j
             LEFT JOIN {local_bridge_automation} a ON a.id = j.automationid
              ORDER BY j.timecreated DESC";
        $recent = $DB->get_records_sql($sql, [], 0, 6);
        $executions = [];
        foreach ($recent as $job) {
            $executions[] = [
                'id' => (int) $job->id,
                'event' => event_catalogue::label($job->eventname),
                'automation' => $job->automationname
                ? format_string($job->automationname)
                : get_string('notavailable', 'local_bridge'),
                'status' => $job->status,
                'statuslabel' => get_string($job->status, 'local_bridge'),
                'time' => userdate($job->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
                'attempts' => (int) $job->attempts,
                'code' => $job->responsecode ? (string) (int) $job->responsecode : get_string('notavailable', 'local_bridge'),
            ];
        }

        return [
            'activecount' => $activecount,
            'inactivecount' => $inactivecount,
            'hasautomations' => $automationcount > 0,
            'activepercent' => $activepercent,
            'activedash' => $activedash,
            'automationcount' => $automationcount,
            'totaljobs' => $totaljobs,
            'successjobs' => $successjobs,
            'failedjobs' => $failedjobs,
            'successrate' => $successrate,
            'hasexecutions' => !empty($executions),
            'executions' => $executions,
        ];
    }
}
