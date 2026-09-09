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
 * Supported Moodle events exposed by Bridge.
 *
 * @package local_bridge
 */
class event_catalogue {
    /** @var array<string, array<string, string>> */
    public const EVENTS = [
        '\\core\\event\\user_created' => [
            'string' => 'eventusercreated',
            'group' => 'users',
            'icon' => 'bi-person-plus',
            'description' => 'eventdescriptionusercreated',
        ],
        '\\core\\event\\user_updated' => [
            'string' => 'eventuserupdated',
            'group' => 'users',
            'icon' => 'bi-person-badge',
            'description' => 'eventdescriptionuserupdated',
        ],
        '\\core\\event\\course_created' => [
            'string' => 'eventcoursecreated',
            'group' => 'courses',
            'icon' => 'bi-journal-plus',
            'description' => 'eventdescriptioncoursecreated',
        ],
        '\\core\\event\\course_updated' => [
            'string' => 'eventcourseupdated',
            'group' => 'courses',
            'icon' => 'bi-journal-text',
            'description' => 'eventdescriptioncourseupdated',
        ],
        '\\core\\event\\user_enrolment_created' => [
            'string' => 'eventuserenrolled',
            'group' => 'enrolments',
            'icon' => 'bi-person-check',
            'description' => 'eventdescriptionuserenrolled',
        ],
    ];

    /**
     * Return the supported Moodle events.
     *
     * @return array<int, array<string, string>> Supported event definitions.
     */
    public static function get_events(): array {
        $events = [];
        foreach (self::EVENTS as $name => $definition) {
            $events[] = [
                'name' => $name,
                'label' => get_string($definition['string'], 'local_bridge'),
                'group' => $definition['group'],
                'icon' => $definition['icon'],
                'description' => get_string($definition['description'], 'local_bridge'),
            ];
        }
        return $events;
    }

    /**
     * Return the display label for an event.
     *
     * @param string $eventname Event name.
     * @return string Event label.
     */
    public static function label(string $eventname): string {
        return isset(self::EVENTS[$eventname])
            ? get_string(self::EVENTS[$eventname]['string'], 'local_bridge')
            : get_string('trigger', 'local_bridge');
    }
}
