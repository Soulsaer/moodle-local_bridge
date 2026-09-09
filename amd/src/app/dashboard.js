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
 * @module     local_bridge/app/dashboard
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/notification',
    'local_bridge/bridgechart'
], function(Notification, BridgeChart) {
    'use strict';

    return {
        loadDashboard: function() {
            this.loading();
            let dashboardData;
            this.call('local_bridge_get_dashboard')
                .then((data) => {
                    dashboardData = data;
                    return this.render('local_bridge/dashboard', data);
                })
                .then(() => {
                    this.initHealthChart(dashboardData);
                    return null;
                })
                .catch(Notification.exception);
        },

        initHealthChart: function(data) {
            const canvas = this.content.querySelector('[data-health-chart]');
            if (!canvas) {
                return;
            }
            const active = Math.max(0, Number(data.activecount) || 0);
            const inactive = Math.max(0, Number(data.inactivecount) || 0);
            if (this.healthChart && typeof this.healthChart.destroy === 'function') {
                this.healthChart.destroy();
            }
            this.healthChart = BridgeChart.createHalfDonut(canvas, {
                active: active,
                inactive: inactive,
                activeColor: '#42bd86',
                inactiveColor: '#ef6874',
                neutralColor: '#e5eaf2',
                lineWidth: 18,
                duration: 500,
                labels: {active: this.strings.active, inactive: this.strings.inactive, neutral: this.strings.neutral}
            });
        }
    };
});
