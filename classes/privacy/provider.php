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

namespace local_bridge\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for Bridge.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the personal data stored by the plugin.
     *
     * @param collection $collection Privacy metadata collection.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_bridge_automation',
            [
                'userid' => 'privacy:metadata:automation:userid',
            ],
            'privacy:metadata:automation'
        );
        $collection->add_database_table(
            'local_bridge_job',
            [
                'userid' => 'privacy:metadata:job:userid',
                'payload' => 'privacy:metadata:job:payload',
            ],
            'privacy:metadata:job'
        );
        $collection->add_external_location_link(
            'bridge_endpoint',
            [],
            'privacy:metadata:job:external'
        );
        return $collection;
    }

    /**
     * Return contexts containing data for a user.
     *
     * @param int $userid User ID.
     * @return contextlist Contexts containing user data.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :contextlevel
                   AND (EXISTS (
                        SELECT 1 FROM {local_bridge_automation} a WHERE a.userid = :automationuserid
                    ) OR EXISTS (
                        SELECT 1 FROM {local_bridge_job} j WHERE j.userid = :jobuserid
                    ))";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'automationuserid' => $userid,
            'jobuserid' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Export a user's Bridge data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        if (!$contextlist->count()) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $data = [
            'automations' => array_values($DB->get_records(
                'local_bridge_automation',
                ['userid' => $userid],
                'id ASC',
                'id,name,eventname,endpoint,enabled,timecreated,timemodified'
            )),
            'executions' => array_values($DB->get_records(
                'local_bridge_job',
                ['userid' => $userid],
                'id ASC',
                'id,automationid,eventname,payload,status,attempts,timecreated,timefinished,responsecode,error'
            )),
        ];
        writer::with_context(\context_system::instance())->export_data([get_string('pluginname', 'local_bridge')], (object) $data);
    }

    /**
     * Delete all Bridge data in a context.
     *
     * @param \context $context Context whose data should be deleted.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $DB->delete_records('local_bridge_job');
        $DB->delete_records('local_bridge_automation');
    }

    /**
     * Delete a user's Bridge data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        if (!$contextlist->count()) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_bridge_job', ['userid' => $userid]);
        $DB->delete_records('local_bridge_automation', ['userid' => $userid]);
    }

    /**
     * Add users with Bridge data to a context user list.
     *
     * @param userlist $userlist User list to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        $sql = "SELECT userid
                  FROM {local_bridge_automation}
                 WHERE userid IS NOT NULL
                 UNION
                SELECT userid
                  FROM {local_bridge_job}
                 WHERE userid IS NOT NULL";
        $userids = $DB->get_records_sql($sql);
        foreach ($userids as $record) {
            $userlist->add_user($record->userid);
        }
    }

    /**
     * Delete Bridge data for selected users.
     *
     * @param approved_userlist $userlist Approved user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$usersql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_bridge_job', "userid {$usersql}", $params);
        $DB->delete_records_select('local_bridge_automation', "userid {$usersql}", $params);
    }
}
