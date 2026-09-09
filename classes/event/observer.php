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

namespace local_bridge\event;

use local_bridge\local\automation_service;

/**
 * Bridge event observer.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Queue automations for user creation events.
     *
     * @param \core\event\user_created $event Event data.
     */
    public static function user_created(\core\event\user_created $event): void {
        automation_service::queue_event($event);
    }

    /**
     * Queue automations for user update events.
     *
     * @param \core\event\user_updated $event Event data.
     */
    public static function user_updated(\core\event\user_updated $event): void {
        automation_service::queue_event($event);
    }

    /**
     * Queue automations for course creation events.
     *
     * @param \core\event\course_created $event Event data.
     */
    public static function course_created(\core\event\course_created $event): void {
        automation_service::queue_event($event);
    }

    /**
     * Queue automations for course update events.
     *
     * @param \core\event\course_updated $event Event data.
     */
    public static function course_updated(\core\event\course_updated $event): void {
        automation_service::queue_event($event);
    }

    /**
     * Queue automations for enrolment creation events.
     *
     * @param \core\event\user_enrolment_created $event Event data.
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event): void {
        automation_service::queue_event($event);
    }
}
