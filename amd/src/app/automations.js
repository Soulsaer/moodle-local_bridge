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
 * @module     local_bridge/app/automations
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/notification',
    'core/templates'
], function(Notification, Templates) {
    'use strict';

    return {
        loadAutomations: function() {
            this.loading();
            this.fetchAutomations(true);
        },

        fetchAutomations: function(initial) {
            const state = this.automationTable;
            const requestId = ++this.automationRequestId;

            if (!initial) {
                state.loading = true;
                this.setAutomationTableLoading(true);
            }

            return Promise.resolve(this.call('local_bridge_get_automations', {
                page: state.page,
                perpage: state.pageSize,
                search: state.query,
                sortkey: state.sortKey,
                sortdir: state.sortDir
            })).then((data) => {
                if (requestId !== this.automationRequestId) {
                    return null;
                }

                if (initial) {
                    state.page = Number(data.page) || 0;
                    state.pageSize = Number(data.perpage) || state.pageSize;
                    this.automationInitialData = data;
                    return this.render('local_bridge/automations', data);
                }

                return this.updateAutomationTable(data);
            }).catch((error) => {
                if (requestId === this.automationRequestId) {
                    state.loading = false;
                    this.setAutomationTableLoading(false);
                }

                Notification.exception(error);
                return null;
            }).then(() => {
                if (requestId === this.automationRequestId) {
                    state.loading = false;
                    this.setAutomationTableLoading(false);
                }

                return null;
            });
        },

        refreshAutomations: function() {
            if (this.automationTable.loading) {
                return;
            }
            this.fetchAutomations(false);
        },

        setAutomationTableLoading: function(loading) {
            const card = this.content.querySelector(
                '[data-automation-table-card], .local_bridge-datatable-card'
            );
            if (!card || !this.content.querySelector('[data-automation-table]')) {
                return;
            }
            card.classList.toggle('is-loading', loading);
            card.querySelectorAll(
                '[data-sort-key] button, [data-action="automation-prev"], ' +
                '[data-action="automation-next"], [data-table-page-size]'
            ).forEach((button) => {
                button.disabled = loading || button.dataset.tableDisabled === 'true';
            });
        },

        bindAutomationTable: function() {
            const search = this.content.querySelector('[data-automation-search]');
            const pageSize = this.content.querySelector('[data-table-page-size]');
            if (search) {
                search.value = this.automationTable.query;
                let timer;
                search.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        this.automationTable.query = search.value.trim();
                        this.automationTable.page = 0;
                        this.refreshAutomations();
                    }, 250);
                });
            }
            if (pageSize) {
                pageSize.value = String(this.automationTable.pageSize);
                pageSize.addEventListener('change', () => {
                    this.automationTable.pageSize = parseInt(pageSize.value, 10) || 10;
                    this.automationTable.page = 0;
                    this.refreshAutomations();
                });
            }
            this.content.querySelectorAll('[data-sort-key]').forEach((th) => {
                const button = th.querySelector('.local_bridge-th-sort');
                if (!button) {
                    return;
                }
                button.addEventListener('click', () => {
                    const key = th.dataset.sortKey;
                    if (this.automationTable.sortKey === key) {
                        this.automationTable.sortDir = this.automationTable.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.automationTable.sortKey = key;
                        this.automationTable.sortDir = 'asc';
                    }
                    this.automationTable.page = 0;
                    this.refreshAutomations();
                });
            });
            this.updateSortIndicators(this.content.querySelector('[data-automation-table]'), this.automationTable);
        },

        updateAutomationMeta: function(data) {
            const state = this.automationTable;
            const total = Number(data.total) || 0;
            const page = Number(data.page) || 0;
            const perpage = Number(data.perpage) || state.pageSize;
            const pages = Math.max(1, Math.ceil(total / perpage));
            state.page = Math.min(page, pages - 1);
            this.content.querySelectorAll('[data-table-info], [data-table-info-bottom]').forEach((el) => {
                el.textContent = total ? this.formatString('showingrange', {
                    from: page * perpage + 1,
                    to: Math.min((page + 1) * perpage, total),
                    total: total
                }) : this.strings.noresults;
            });
            const footer = this.content.querySelector('[data-table-footer]');
            if (footer) {
                footer.hidden = false;
            }
            const pagination = this.content.querySelector('[data-table-pagination]');
            if (pagination) {
                pagination.innerHTML = '';
                if (pages > 1) {
                    pagination.append(this.makeButton(
                        'btn btn-sm btn-outline-secondary local_bridge-btn local_bridge-table-pagination-btn',
                        'bi-chevron-left',
                        this.strings.previous || '',
                        page <= 0 || state.loading,
                        () => {
                        state.page = Math.max(0, page - 1);
                        this.refreshAutomations();
                    }));
                    const label = document.createElement('span');
                    label.className = 'local_bridge-pagination-label';
                    label.textContent = this.formatString('pageof', {page: page + 1, pages: pages});
                    pagination.append(label);
                    pagination.append(this.makeButton(
                        'btn btn-sm btn-outline-secondary local_bridge-btn local_bridge-table-pagination-btn',
                        'bi-chevron-right',
                        this.strings.next || '',
                        page >= pages - 1 || state.loading,
                        () => {
                        state.page = page + 1;
                        this.refreshAutomations();
                    }, true));
                }
            }
            this.updateSortIndicators(this.content.querySelector('[data-automation-table]'), state);
        },

        updateAutomationTable: function(data) {
            const tbody = this.content.querySelector('[data-automation-tbody]');
            if (!tbody) {
                return Promise.resolve();
            }

            return Templates.render(
                'local_bridge/automation_rows',
                Object.assign({}, data, {strings: this.strings})
            ).then((html) => {
                tbody.innerHTML = html;
                this.updateAutomationMeta(data);
                return null;
            });
        },

        updateSortIndicators: function(table, state) {
            if (!table) {
                return;
            }

            table.querySelectorAll('thead th[data-sort-key], thead th[data-execution-sort-key]').forEach((th) => {
                const button = th.querySelector('.local_bridge-th-sort');
                const icon = button && button.querySelector('i');
                const key = th.dataset.sortKey || th.dataset.executionSortKey;
                const active = key === state.sortKey;
                let ariaSort = 'none';
                let iconClass = 'bi bi-chevron-expand';

                if (active) {
                    ariaSort = state.sortDir === 'asc' ? 'ascending' : 'descending';
                    iconClass = state.sortDir === 'asc' ? 'bi bi-sort-up' : 'bi bi-sort-down';
                }

                th.classList.toggle('is-sorted', active);
                th.setAttribute('aria-sort', ariaSort);

                if (icon) {
                    icon.className = iconClass;
                }
            });
        },

        makeButton: function(className, icon, label, disabled, handler, iconAfter) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.disabled = disabled;
            const iconEl = document.createElement('i');
            iconEl.className = 'bi ' + icon;
            iconEl.setAttribute('aria-hidden', 'true');
            const span = document.createElement('span');
            span.textContent = label;
            if (iconAfter) {
                button.append(span, iconEl);
            } else {
                button.append(iconEl, span);
            }
            button.addEventListener('click', handler);
            return button;
        },

        loadAutomationForm: function(id) {
            this.loading();

            this.call('local_bridge_get_automation', {id: id}).then((data) => {
                if (!id) {
                    data.automation = {
                        id: 0,
                        name: '',
                        eventname: data.events[0].name,
                        endpoint: '',
                        enabled: true
                    };
                }

                data.editing = !!id;

                data.events.forEach((event) => {
                    event.selected = event.name === data.automation.eventname;
                });

                return this.render('local_bridge/automation_form', data);
            }).then(() => {
                return this.bindAutomationForm();
            }).catch(Notification.exception);
        },

        bindAutomationForm: function() {
            const form = this.content.querySelector('[data-automation-form]');
            if (!form) {
                return;
            }
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                this.saveAutomation(form);
            });
            const advanced = form.querySelector('[data-toggle-advanced]');
            if (advanced) {
                advanced.addEventListener('click', () => {
                    const body = form.querySelector('.local_bridge-advanced-body');
                    const open = advanced.getAttribute('aria-expanded') === 'true';
                    const nextOpen = !open;
                    advanced.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                    body.hidden = !nextOpen;
                    const chevron = advanced.querySelector('.local_bridge-advanced-chevron');
                    const plus = advanced.querySelector('.local_bridge-advanced-label i');
                    if (chevron) {
                        chevron.className = nextOpen
                            ? 'bi bi-chevron-up local_bridge-advanced-chevron'
                            : 'bi bi-chevron-down local_bridge-advanced-chevron';
                    }

                    if (plus) {
                        plus.className = nextOpen ? 'bi bi-dash-lg' : 'bi bi-plus-lg';
                    }
                });
            }
        },

        saveAutomation: function(form) {
            const values = new FormData(form);
            const args = {
                id: parseInt(values.get('id') || '0', 10),
                name: values.get('name') || '',
                eventname: values.get('eventname') || '',
                endpoint: values.get('endpoint') || '',
                secret: values.get('secret') || '',
                enabled: values.get('enabled') === 'on'
            };
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.classList.add('is-loading');
            this.call('local_bridge_save_automation', args).then((data) => {
                this.toast(data.message, 'success');
                this.navigate('automations');
                return null;
            }).catch((error) => {
                button.disabled = false;
                button.classList.remove('is-loading');
                this.showFormError(form, error && error.message ? error.message : this.strings.unabletosave);
            });
        },

        showFormError: function(form, message) {
            let box = form.querySelector('[data-form-error]');
            if (!box) {
                box = document.createElement('div');
                box.className = 'local_bridge-form-error';
                box.dataset.formError = '1';
                form.prepend(box);
            }
            box.textContent = message;
            box.scrollIntoView({behavior: 'smooth', block: 'center'});
        },

        deleteAutomation: function(id) {
            Notification.confirm(
                this.strings.deleteconfirmtitle,
                this.strings.deleteconfirmmessage,
                this.strings.deleteconfirmyes,
                this.strings.deleteconfirmno,
                () => {
                    return this.call('local_bridge_delete_automation', {
                        id: parseInt(id, 10)
                    }).then((data) => {
                        this.toast(data.message, 'success');
                        return this.loadAutomations();
                    }).catch(Notification.exception);
                }
            );
        },

        toggleAutomation: function(id, enabled) {
            return this.call('local_bridge_toggle_automation', {
                id: parseInt(id, 10),
                enabled: enabled
            }).then((data) => {
                this.toast(data.message, 'success');
                return this.loadAutomations();
            }).catch(Notification.exception);
        },

        testAutomation: function(id) {
            const button = this.content.querySelector('[data-action="test"][data-id="' + id + '"]');
            if (!button || button.disabled) {
                return;
            }
            button.disabled = true;
            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');
            const reset = () => {
                if (button && document.body.contains(button)) {
                    button.disabled = false;
                    button.classList.remove('is-loading');
                    button.removeAttribute('aria-busy');
                }
            };
            this.call('local_bridge_test_automation', {id: parseInt(id, 10)})
                .then((data) => this.showTestResult(data, reset))
                .catch((error) => {
                reset();
                Notification.exception(error);
            });
        },

        showTestResult: function(data, onClose) {
            const overlay = document.createElement('div');
            overlay.className = 'local_bridge-modal-backdrop';
            const response = String(data.response || '').trim();
            const code = Number(data.code || 0);
            let statusText = this.strings.nohttpresponse;

            if (code) {
                if (code >= 200 && code < 300) {
                    statusText = this.strings.successfulresponse;
                } else {
                    statusText = this.strings.requestreturnederror;
                }
            }
            const responseMarkup = response
                ? '<div class="local_bridge-modal-response">' +
                    '<span class="local_bridge-modal-response-label">' +
                    this.escape(this.strings.responsebody) +
                    '</span><pre>' +
                    this.escape(response) +
                    '</pre></div>'
                : '<div class="local_bridge-modal-response">' +
                    '<span class="local_bridge-modal-response-label">' +
                    this.escape(this.strings.responsebody) +
                    '</span><p class="local_bridge-modal-empty-response">' +
                    this.escape(this.strings.noresponsebody) +
                    '</p></div>';
            overlay.innerHTML = '<div class="local_bridge-modal">' +
                '<button type="button" class="local_bridge-modal-close" aria-label="' +
                    this.escape(this.strings.close) +
                    '"><i class="bi bi-x-lg" aria-hidden="true"></i></button>' +
                '<span class="local_bridge-result-icon ' +
                    (data.success ? 'local_bridge-success' : 'local_bridge-error-state') +
                    '"><i class="bi ' +
                    (data.success ? 'bi-check-lg' : 'bi-exclamation-lg') +
                    '" aria-hidden="true"></i></span>' +
                '<h2>' + this.escape(data.success ? this.strings.testdelivered : this.strings.testneedsattention) + '</h2>' +
                '<p>' + this.escape(data.message) + '</p>' +
                '<div class="local_bridge-result-meta">' +
                    '<span><span>' +
                    this.escape(this.strings.httpstatus) +
                    '</span><b>' +
                    this.escape(String(code || this.strings.notavailable)) +
                    '</b></span><span><span>' +
                    this.escape(this.strings.result) +
                    '</span><b>' +
                    this.escape(statusText) +
                    '</b></span></div>' +
                responseMarkup +
                '<button type="button" class="btn btn-primary local_bridge-btn ' +
                    'local_bridge-btn-primary local_bridge-btn-block local_bridge-modal-done">' +
                    this.escape(this.strings.done) +
                    '</button>' +
                '</div>';
            document.body.appendChild(overlay);
            const close = () => {
                overlay.remove();

                if (onClose) {
                    onClose();
                }
            };
            overlay.querySelector('.local_bridge-modal-close').addEventListener('click', close);
            overlay.querySelector('.local_bridge-modal-done').addEventListener('click', close);
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    close();
                }
            });
        }
    };
});
