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
 * @module     local_bridge/app/navigation
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    return {
        bindNavigation: function() {
            this.root.querySelectorAll('[data-view]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.navigate(button.getAttribute('data-view'));
                });
            });
            const toggle = this.root.querySelector('[data-local_bridge-sidebar-toggle]');
            if (toggle) {
                toggle.addEventListener('click', () => this.root.classList.toggle('sidebar-open'));
            }
        },

        bindGlobalActions: function() {
            this.root.addEventListener('click', (event) => {
                const target = event.target.closest('[data-action]');
                if (!target) {
                    return;
                }
                const action = target.getAttribute('data-action');
                if (action === 'create') {
                    this.navigate('create');
                } else if (action === 'edit') {
                    this.navigate('edit', target.dataset.id);
                } else if (action === 'delete') {
                    this.deleteAutomation(target.dataset.id);
                } else if (action === 'toggle') {
                    this.toggleAutomation(target.dataset.id, target.dataset.enabled !== 'true');
                } else if (action === 'test') {
                    this.testAutomation(target.dataset.id);
                } else if (action === 'execution') {
                    this.openExecution(target.dataset.id);
                } else if (action === 'clear-automation-search') {
                    const input = this.content.querySelector('[data-automation-search]');
                    if (input) {
                        input.value = '';
                    }
                    this.automationTable.query = '';
                    this.automationTable.page = 0;
                    this.refreshAutomations();
                } else if (action === 'execution-prev') {
                    if (this.executionTable.page > 0) {
                        this.executionTable.page--;
                        this.refreshExecutions();
                    }
                } else if (action === 'execution-next') {
                    this.executionTable.page++;
                    this.refreshExecutions();
                } else if (action === 'close-detail') {
                    this.closeDetail();
                } else if (action === 'retry') {
                    this.retryExecution(target.dataset.id);
                }
            });
        },

        navigate: function(view, id) {
            const value = id ? view + '/' + id : view;
            window.location.hash = value;
            this.root.classList.remove('sidebar-open');
        },

        routeFromHash: function() {
            if (!this.stringsReady) {
                return;
            }
            const hash = window.location.hash.replace(/^#/, '') || 'dashboard';
            const parts = hash.split('/');
            this.currentView = parts[0];
            const id = parts[1] ? parseInt(parts[1], 10) : 0;
            this.setActiveNav(this.currentView);
            if (this.currentView === 'dashboard') {
                this.loadDashboard();
            } else if (this.currentView === 'automations') {
                this.loadAutomations();
            } else if (this.currentView === 'create') {
                this.loadAutomationForm(0);
            } else if (this.currentView === 'edit') {
                this.loadAutomationForm(id);
            } else if (this.currentView === 'executions') {
                this.loadExecutions();
            } else if (this.currentView === 'settings') {
                this.loadSettings();
            } else {
                this.navigate('dashboard');
            }
        },

        setActiveNav: function(view) {
            const actual = view === 'create' || view === 'edit' ? 'automations' : view;
            this.root.querySelectorAll('.local_bridge-nav-item[data-view]').forEach((item) => {
                item.classList.toggle('is-active', item.getAttribute('data-view') === actual);
            });
        }
    };
});
