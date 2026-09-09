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
 * @module     local_bridge/app/executions
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/templates',
    'core/notification',
    'core/modal_factory',
    'core/modal_events'
], function(Templates, Notification, ModalFactory, ModalEvents) {
    'use strict';

    return {
        loadExecutions: function() {
            this.loading();
            this.fetchExecutions(true);
        },

        fetchExecutions: function(initial) {
            const state = this.executionTable;
            const requestId = ++this.executionRequestId;
            if (!initial) {
                state.loading = true;
                this.setExecutionTableLoading(true);
            }

            const request = Promise.resolve(this.call('local_bridge_get_executions', {
                page: state.page,
                perpage: state.pageSize,
                search: state.query,
                sortkey: state.sortKey,
                sortdir: state.sortDir
            }));
            return request.then((data) => {
                if (requestId !== this.executionRequestId) {
                    return null;
                }

                if (initial) {
                    data.search = state.query;
                    const currentPage = Number(data.page) || 0;
                    const total = Number(data.total) || 0;
                    const perpage = Number(data.perpage) || state.pageSize;
                    const lastPage = Math.max(0, Math.ceil(total / perpage) - 1);

                    data.firstpage = currentPage <= 0;
                    data.lastpage = currentPage >= lastPage;
                    this.executionInitialData = data;

                    return this.render('local_bridge/executions', data);
                }

                return this.updateExecutionTable(data);
            }).finally(() => {
                if (requestId === this.executionRequestId) {
                    state.loading = false;
                    this.setExecutionTableLoading(false);
                }
            }).catch(Notification.exception);
        },

        refreshExecutions: function() {
            if (this.executionTable.loading) {
                return;
            }
            this.fetchExecutions(false);
        },

        setExecutionTableLoading: function(loading) {
            const card = this.content.querySelector('[data-execution-table-card]');
            if (!card) {
                return;
            }
            card.classList.toggle('is-loading', loading);
            card.querySelectorAll(
                '[data-execution-sort-key] button, [data-action="execution-prev"], ' +
                '[data-action="execution-next"]'
            ).forEach((button) => {
                button.disabled = loading || button.dataset.executionDisabled === 'true';
            });
        },

        updateExecutionMeta: function(data) {
            const state = this.executionTable;
            const total = Number(data.total) || 0;
            const page = Number(data.page) || 0;
            const perpage = Number(data.perpage) || state.pageSize;
            const pages = Math.max(1, Math.ceil(total / perpage));
            state.page = Math.min(page, pages - 1);

            this.content.querySelectorAll('[data-execution-info], [data-execution-info-bottom]').forEach((el) => {
                el.textContent = total ? this.formatString('showingrange', {
                    from: page * perpage + 1,
                    to: Math.min((page + 1) * perpage, total),
                    total: total
                }) : this.strings.noresults;
            });

            const footer = this.content.querySelector('[data-execution-table-footer]');
            if (footer) {
                footer.hidden = false;
            }

            const pagination = this.content.querySelector('[data-execution-pagination]');
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
                        this.refreshExecutions();
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
                        this.refreshExecutions();
                    }, true));
                }
            }
            this.updateSortIndicators(this.content.querySelector('[data-execution-table]'), state);
        },

        updateExecutionTable: function(data) {
            const state = this.executionTable;
            const tbody = this.content.querySelector('[data-execution-tbody]');

            if (!tbody) {
                return Promise.resolve();
            }

            return Templates.render(
                'local_bridge/execution_rows',
                Object.assign({}, data, {strings: this.strings})
            ).then((html) => {
                tbody.innerHTML = html;
                this.updateExecutionMeta(data);

                const table = this.content.querySelector('[data-execution-table]');
                if (table) {
                    this.updateSortIndicators(table, state);
                }

                return null;
            });
        },

        bindExecutionTable: function() {
            const state = this.executionTable;
            const input = this.content.querySelector('[data-execution-search]');
            const pageSize = this.content.querySelector('[data-execution-page-size]');
            if (input) {
                input.value = state.query;
                let timer;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        state.query = input.value.trim();
                        state.page = 0;
                        this.refreshExecutions();
                    }, 250);
                });
            }
            if (pageSize) {
                pageSize.value = String(state.pageSize);
                pageSize.addEventListener('change', () => {
                    state.pageSize = parseInt(pageSize.value, 10) || 10;
                    state.page = 0;
                    this.refreshExecutions();
                });
            }
            this.content.querySelectorAll('[data-execution-sort-key]').forEach((th) => {
                const button = th.querySelector('.local_bridge-th-sort');
                if (!button) {
                    return;
                }
                button.addEventListener('click', () => {
                    const key = th.dataset.executionSortKey;
                    if (state.sortKey === key) {
                        state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        state.sortKey = key;
                        state.sortDir = 'asc';
                    }
                    state.page = 0;
                    this.updateSortIndicators(this.content.querySelector('[data-execution-table]'), state);
                    this.refreshExecutions();
                });
            });
            const table = this.content.querySelector('[data-execution-table]');
            if (table) {
                this.updateSortIndicators(table, state);
            }
        },

        openExecution: function(id) {
            this.call('local_bridge_get_execution', {id: parseInt(id, 10)})
                .then((data) => {
                    const execution = data.execution;
                    let payloadjson = execution.payload || '';

                    try {
                        payloadjson = JSON.stringify(
                            JSON.parse(execution.payload || ''),
                            null,
                            2
                        );
                    } catch (e) {
                        payloadjson = execution.payload || '';
                    }

                    return Templates.render(
                        'local_bridge/execution_detail',
                        Object.assign({}, data, {
                            strings: this.strings,
                            execution: Object.assign({}, execution, {payloadjson: payloadjson})
                        })
                    );
                })
                .then((html) => {
                    return ModalFactory.create({
                        title: this.strings.executiondetails,
                        body: html,
                        large: true,
                        removeOnClose: true
                    });
                })
                .then((modal) => {
                    this.executionModal = modal;
                    modal.getRoot().addClass('local_bridge-execution-modal');
                    modal.getRoot().on(ModalEvents.hidden, () => {
                        if (this.executionModal === modal) {
                            this.executionModal = null;
                        }
                        modal.destroy();
                    });
                    modal.getBody().find('[data-action="retry"]').on('click', (event) => {
                        event.preventDefault();
                        this.retryExecution(id);
                    });
                    modal.getBody().find('[data-execution-close]').on('click', (event) => {
                        event.preventDefault();
                        modal.hide();
                    });
                    this.bindExecutionPayload(modal);
                    this.bindExecutionDestination(modal);
                    modal.show();
                    return null;
                })
                .catch(Notification.exception);
        },

        bindExecutionDestination: function(modal) {
            const button = modal.getBody().find('[data-destination-copy]')[0];
            const destination = modal.getBody().find('.local_bridge-destination-value')[0];
            if (!button || !destination) {
                return;
            }
            const text = destination.textContent || '';
            const copied = () => {
                button.classList.add('local_bridge-copy-success');
                button.setAttribute('aria-label', this.strings.urlcopied || '');
                button.setAttribute('title', this.strings.urlcopied || '');
                this.toast(this.strings.urlcopied, 'success');
                window.setTimeout(() => button.classList.remove('local_bridge-copy-success'), 1400);
            };
            button.addEventListener('click', (event) => {
                event.preventDefault();
                this.copyText(text).then(copied).catch(() => {
                    return null;
                });
            });
        },

        bindExecutionPayload: function(modal) {
            const body = modal.getBody();
            const content = body.find('[data-payload-content]')[0];
            const raw = content ? content.getAttribute('data-payload-raw') || '' : '';
            const json = content ? content.getAttribute('data-payload-json') || raw : raw;
            const rawButton = body.find('[data-payload-view="raw"]')[0];
            const jsonButton = body.find('[data-payload-view="json"]')[0];
            const copyButton = body.find('[data-payload-copy]')[0];
            const setView = (view) => {
                if (!content) {
                    return;
                }
                content.textContent = view === 'json' ? json : raw;
                if (rawButton) {
                    rawButton.classList.toggle('active', view === 'raw');
                    rawButton.setAttribute('aria-pressed', view === 'raw' ? 'true' : 'false');
                }
                if (jsonButton) {
                    jsonButton.classList.toggle('active', view === 'json');
                    jsonButton.setAttribute('aria-pressed', view === 'json' ? 'true' : 'false');
                }
            };
            if (rawButton) {
                rawButton.addEventListener('click', () => setView('raw'));
            }
            if (jsonButton) {
                jsonButton.addEventListener('click', () => setView('json'));
            }
            if (copyButton) {
                copyButton.addEventListener('click', () => {
                    const text = content ? content.textContent : raw;
                    const copied = () => {
                        copyButton.classList.add('local_bridge-copy-success');
                        copyButton.setAttribute('aria-label', this.strings.payloadcopied || '');
                        copyButton.setAttribute('title', this.strings.payloadcopied || '');
                        this.toast(this.strings.payloadcopied, 'success');
                        window.setTimeout(() => copyButton.classList.remove('local_bridge-copy-success'), 1400);
                    };
                    this.copyText(text).then(copied).catch(() => {
                        return null;
                    });
                });
            }
            setView('json');
        },

        copyText: function(text) {
            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                return navigator.clipboard.writeText(text);
            }

            return new Promise((resolve, reject) => {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.setAttribute('readonly', 'readonly');
                textarea.style.position = 'fixed';
                textarea.style.top = '0';
                textarea.style.left = '-9999px';
                textarea.style.width = '1px';
                textarea.style.height = '1px';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                textarea.setSelectionRange(0, textarea.value.length);
                let copied = false;
                try {
                    copied = document.execCommand('copy');
                } catch (e) {
                    copied = false;
                }
                document.body.removeChild(textarea);
                if (copied) {
                    resolve();
                } else {
                    reject(new Error('Clipboard copy failed'));
                }
            });
        },

        closeDetail: function() {
            if (this.executionModal) {
                this.executionModal.hide();
            }
        },

        retryExecution: function(id) {
            return this.call('local_bridge_retry_execution', {
                id: parseInt(id, 10)
            }).then((data) => {
                this.toast(data.message, 'success');
                this.closeDetail();
                return this.loadExecutions();
            }).catch(Notification.exception);
        }
    };
});
