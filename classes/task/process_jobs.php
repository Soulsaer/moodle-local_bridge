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

namespace local_bridge\task;

use local_bridge\local\delivery_service;

/**
 * Process Bridge delivery jobs.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_jobs extends \core\task\scheduled_task {
    /**
     * Return the scheduled task name.
     *
     * @return string Task name.
     */
    public function get_name(): string {
        return get_string('pluginname', 'local_bridge') . ': process deliveries';
    }

    /**
     * Process pending Bridge delivery jobs.
     */
    public function execute(): void {
        global $DB;

        $jobs = $DB->get_records_select(
            'local_bridge_job',
            "status IN ('pending', 'retrying') AND nextrun <= :now",
            ['now' => time()],
            'id ASC',
            '*',
            0,
            25
        );

        foreach ($jobs as $job) {
            $automation = $DB->get_record('local_bridge_automation', ['id' => $job->automationid]);
            if (!$automation || empty($automation->enabled)) {
                $job->status = 'failed';
                $job->error = get_string('automationdisabled', 'local_bridge');
                $job->timefinished = time();
                $DB->update_record('local_bridge_job', $job);
                continue;
            }

            $job->status = 'processing';
            $job->attempts++;
            $job->timestarted = time();
            $DB->update_record('local_bridge_job', $job);

            try {
                $result = delivery_service::send($automation->endpoint, $job->payload, 15, $automation->secret ?? '');
                $job->responsecode = $result['code'];
                $job->response = $result['response'];

                if ($result['success']) {
                    $job->status = 'success';
                    $job->timefinished = time();
                    $job->error = null;
                } else if ($result['code'] === 429 || $result['code'] >= 500 || $result['code'] === 0) {
                    self::mark_failure($job, get_string('httpresponseerror', 'local_bridge', $result['code']));
                } else {
                    $job->status = 'failed';
                    $job->timefinished = time();
                    $job->error = get_string('httpresponseerror', 'local_bridge', $result['code']);
                }
            } catch (\Throwable $exception) {
                self::mark_failure($job, $exception->getMessage());
            }

            $DB->update_record('local_bridge_job', $job);
        }
    }

    /**
     * Mark a delivery job as failed or retrying.
     *
     * @param object $job Delivery job.
     * @param string $message Failure message.
     */
    private static function mark_failure(object $job, string $message): void {
        if ($job->attempts < 3) {
            $job->status = 'retrying';
            $job->nextrun = time() + (60 * (2 ** ($job->attempts - 1)));
            $job->error = substr($message, 0, 1000);
        } else {
            $job->status = 'failed';
            $job->timefinished = time();
            $job->error = substr($message, 0, 1000);
        }
    }
}
