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

use local_bridge\local\event_catalogue;

/**
 * Automation service for Bridge.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class automation_service {
    /** @var array<string, string> Supported core events in this release. */
    public const SUPPORTED_EVENTS = [
        '\\core\\event\\user_created' => 'eventusercreated',
        '\\core\\event\\user_updated' => 'eventuserupdated',
        '\\core\\event\\course_created' => 'eventcoursecreated',
        '\\core\\event\\course_updated' => 'eventcourseupdated',
        '\\core\\event\\user_enrolment_created' => 'eventuserenrolled',
    ];

    /**
     * Create an automation.
     *
     * @param object $data
     * @param int $userid
     * @return int
     */
    public static function create(object $data, int $userid): int {
        global $DB;

        $now = time();
        $record = (object) [
            'name' => trim($data->name),
            'eventname' => $data->eventname,
            'endpoint' => trim($data->endpoint),
            'secret' => !empty($data->secret) ? trim($data->secret) : null,
            'enabled' => !empty($data->enabled) ? 1 : 0,
            'timecreated' => $now,
            'timemodified' => $now,
            'userid' => $userid,
        ];

        return $DB->insert_record('local_bridge_automation', $record);
    }

    /**
     * Update an automation.
     *
     * @param int $id
     * @param object $data
     * @param int $userid
     * @return void
     */
    public static function update(int $id, object $data, int $userid): void {
        global $DB;

        $record = $DB->get_record('local_bridge_automation', ['id' => $id], '*', MUST_EXIST);
        $record->name = trim($data->name);
        $record->eventname = $data->eventname;
        $record->endpoint = trim($data->endpoint);
        if (isset($data->secret) && trim($data->secret) !== '') {
            $record->secret = trim($data->secret);
        }
        $record->enabled = !empty($data->enabled) ? 1 : 0;
        $record->timemodified = time();
        $record->userid = $userid;
        $DB->update_record('local_bridge_automation', $record);
    }

    /**
     * Delete an automation and its jobs.
     *
     * @param int $id
     * @return void
     */
    public static function delete(int $id): void {
        global $DB;
        $DB->delete_records('local_bridge_job', ['automationid' => $id]);
        $DB->delete_records('local_bridge_automation', ['id' => $id]);
    }

    /**
     * Queue an event for all matching active automations.
     *
     * @param \core\event\base $event
     * @return int Number of jobs queued.
     */
    public static function queue_event(\core\event\base $event): int {
        global $DB;

        $eventname = '\\' . ltrim(get_class($event), '\\');
        if (!isset(event_catalogue::EVENTS[$eventname])) {
            return 0;
        }

        $automations = $DB->get_records('local_bridge_automation', [
            'eventname' => $eventname,
            'enabled' => 1,
        ]);

        $queued = 0;
        $payload = self::build_payload($event);
        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return 0;
        }

        foreach ($automations as $automation) {
            $job = (object) [
                'automationid' => $automation->id,
                'eventname' => $eventname,
                'userid' => !empty($event->userid) ? (int) $event->userid : null,
                'payload' => $encoded,
                'status' => 'pending',
                'attempts' => 0,
                'nextrun' => time(),
                'timecreated' => time(),
            ];
            $DB->insert_record('local_bridge_job', $job);
            $queued++;
        }

        return $queued;
    }

    /**
     * Build the event envelope delivered by Bridge.
     *
     * @param \core\event\base $event
     * @return array<string, mixed>
     */
    public static function build_payload(\core\event\base $event): array {
        $data = $event->get_data();
        return [
            'bridge_version' => '0.3',
            'event' => $data['eventname'] ?? get_class($event),
            'component' => $data['component'] ?? null,
            'action' => $data['action'] ?? null,
            'target' => $data['target'] ?? null,
            'objectid' => $data['objectid'] ?? null,
            'contextid' => $data['contextid'] ?? null,
            'courseid' => $data['courseid'] ?? null,
            'userid' => $data['userid'] ?? null,
            'timecreated' => $data['timecreated'] ?? time(),
        ];
    }

    /**
     * Validate a destination URL before Bridge makes an outbound request.
     *
     * @param string $url
     * @return bool
     */
    public static function is_safe_endpoint(string $url): bool {
        $parts = parse_url(trim($url));
        if (!$parts || empty($parts['scheme']) || strtolower($parts['scheme']) !== 'https') {
            return false;
        }
        if (empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            return false;
        }

        // Literal IPs can be checked without DNS resolution. Hostnames are
        // allowed here; the final transport layer should still reject unsafe
        // destinations and redirects in production hardening.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }
}
