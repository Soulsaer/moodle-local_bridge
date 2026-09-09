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
 * Bridge health chart integration using Moodle's bundled Chart.js library.
 *
 * @module     local_bridge/bridgechart
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Bridge health chart integration using Moodle's bundled Chart.js library.
 *
 * @module     local_bridge/bridgechart
 */
define(['core/chartjs'], function(Chart) {
    'use strict';

    const instances = new WeakMap();

    const createHalfDonut = function(canvas, options) {
        const settings = Object.assign({
            activeColor: '#42bd86',
            inactiveColor: '#ef6874',
            neutralColor: '#e5eaf2',
            duration: 450,
            labels: {active: '', inactive: '', neutral: ''}
        }, options || {});
        const active = Math.max(0, Number(settings.active) || 0);
        const inactive = Math.max(0, Number(settings.inactive) || 0);
        const total = active + inactive;
        const existing = instances.get(canvas);

        if (existing) {
            existing.destroy();
        }

        const chart = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: total ? [settings.labels.active, settings.labels.inactive] : [settings.labels.neutral],
                datasets: [{
                    data: total ? [active, inactive] : [1],
                    backgroundColor: total ? [settings.activeColor, settings.inactiveColor] : [settings.neutralColor],
                    borderWidth: 0,
                    hoverOffset: 0,
                    spacing: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                // Chart.js uses degrees here. Start at 9 o'clock and travel clockwise
                // through the upper half to 3 o'clock.
                rotation: -90,
                circumference: 180,
                cutout: '72%',
                animation: {
                    duration: settings.duration,
                    easing: 'easeOutCubic'
                },
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = Number(context.parsed) || 0;
                                const percent = total ? Math.round((value / total) * 100) : 0;
                                return label + ': ' + value + ' (' + percent + '%)';
                            }
                        }
                    }
                }
            }
        });

        instances.set(canvas, chart);
        return chart;
    };

    return {createHalfDonut: createHalfDonut};
});
