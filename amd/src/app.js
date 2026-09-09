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
 * Bridge administration application.
 *
 * @module     local_bridge/app
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Bridge administration application.
 *
 * @module     local_bridge/app
 */
define([
    'core/str',
    'core/notification',
    'local_bridge/app/navigation',
    'local_bridge/app/dashboard',
    'local_bridge/app/automations',
    'local_bridge/app/executions',
    'local_bridge/app/settings',
    'local_bridge/app/utilities'
], function(Str, Notification, Navigation, Dashboard, Automations, Executions, Settings, Utilities) {
    'use strict';

    const STRING_KEYS = [
        'activity', 'activitydesc', 'activeautomations', 'totalexecutions', 'successrate', 'faileddeliveries',
        'recentactivity', 'recentactivitydesc', 'nodeliveryactivity', 'nodeliveryactivitydesc',
        'automationhealth', 'automationhealthdesc', 'yes', 'no', 'active', 'inactive', 'neutral',
        'activecountlabel', 'inactivecountlabel', 'activepercentlabel', 'bridgeisready', 'bridgeisreadydesc',
        'yourautomations', 'automationsdesc', 'searchautomations', 'clearsearch', 'rows', 'rowsperpage',
        'automation', 'trigger', 'destination', 'actions', 'edit', 'delete', 'updated', 'updatedat',
        'notavailable', 'noautomationsfound', 'noautomationsdisplay', 'searchtryagain',
        'createfirstautomation', 'showingrange', 'noresults', 'pageof', 'deliveryhistory',
        'deliveryhistorydesc', 'deliveries', 'searchdeliveries', 'nodeliveriesfound', 'nodeliveriesdesc',
        'createautomation', 'createanautomation', 'editautomation', 'newautomation', 'updateautomation',
        'setautomationdesc', 'nameit', 'nameitdesc', 'choosewhatstarts', 'choosestartsdesc',
        'choosewhere', 'choosewheredesc', 'endpoint', 'endpointplaceholder', 'destinationhelp',
        'advancedoptions', 'signingsecretoptional', 'optional', 'signingsecretplaceholder',
        'signingsecrethelp', 'enableafter', 'testbefore', 'testbeforedesc', 'sendtest', 'testdelivered',
        'testneedsattention', 'successfulresponse', 'requestreturnederror', 'nohttpresponse',
        'responsebody', 'noresponsebody', 'done', 'deleteconfirmtitle', 'deleteconfirmmessage',
        'deleteconfirmyes', 'deleteconfirmno', 'unabletosave', 'loading', 'httpstatus', 'result',
        'execution', 'executiondetails', 'executionid', 'ofthree', 'copy', 'copydestination',
        'started', 'finished', 'retrydelivery', 'payload', 'payloadraw', 'payloadjson', 'copypayload',
        'payloadcopied', 'urlcopied', 'response', 'error', 'close', 'event', 'time', 'attempts',
        'status', 'on', 'off', 'viewall', 'viewexecutions', 'managebridge', 'back', 'cancel',
        'saveautomation', 'automations', 'settings', 'executions', 'overview', 'previous', 'next',
        'page', 'manageautomations', 'ofautomationsactive', 'deliveryprocessing',
        'deliveryprocessingdesc', 'automaticretries', 'automaticretriesdesc', 'supportedtriggers',
        'moodleeventsavailable', 'events', 'enabled', 'automationname', 'destinationurl',
        'savechanges', 'deleteautomation'
    ];

    const App = {
        root: null,
        content: null,
        currentView: 'dashboard',
        strings: {},
        stringsReady: null,
        healthChart: null,
        automationTable: {
            page: 0,
            pageSize: 10,
            sortKey: 'name',
            sortDir: 'asc',
            query: ''
        },
        executionTable: {
            page: 0,
            pageSize: 10,
            sortKey: 'time',
            sortDir: 'desc',
            query: '',
            loading: false
        },
        executionRequestId: 0,
        automationRequestId: 0,
        executionModal: null,

        init: function() {
            this.root = document.querySelector('[data-local_bridge-app]');
            if (!this.root) {
                return;
            }
            this.content = this.root.querySelector('[data-local_bridge-content]');
            this.bindNavigation();
            this.bindGlobalActions();
            this.stringsReady = Str.getStrings(
                STRING_KEYS.map((key) => ({key: key, component: 'local_bridge'}))
            ).then((values) => {
                STRING_KEYS.forEach((key, index) => {
                    this.strings[key] = values[index];
                });
                return this.routeFromHash();
            }).catch(Notification.exception);
            window.addEventListener('hashchange', () => this.routeFromHash());
        },
    };

    Object.assign(App, Navigation, Dashboard, Automations, Executions, Settings, Utilities);

    return {
        init: function() {
            App.init();
        }
    };
});
