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
 * Bridge administration application module.
 *
 * @module     local_bridge/app/settings
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/notification'
], function(Notification) {
    'use strict';

    return {
        loadSettings: function() {
            this.loading();
            this.call('local_bridge_get_dashboard').then((data) => {
                data.eventcount = data.events.length;
                return this.render('local_bridge/settings', data);
            }).catch(Notification.exception);
        }
    };
});
