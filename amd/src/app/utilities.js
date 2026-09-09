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
 * @module     local_bridge/app/utilities
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/templates'
], function(Ajax, Templates) {
    'use strict';

    return {
        call: function(methodname, args) {
            return Ajax.call([{methodname: methodname, args: args || {}}])[0];
        },

        render: function(template, data) {
            const viewData = Object.assign({}, data, {strings: this.strings});
            return Templates.render(template, viewData).then((html) => {
                this.content.innerHTML = html;
                this.content.querySelectorAll('[data-view]').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        this.navigate(button.getAttribute('data-view'));
                    });
                });
                return html;
            });
        },

        loading: function() {
            this.content.innerHTML =
                '<div class="local_bridge-loading"><span></span><span></span><span></span><p>' +
                this.escape(this.strings.loading || '') +
                '</p></div>';
        },

        toast: function(message, type) {
            const region = this.root.querySelector('[data-local_bridge-toasts]');
            const toast = document.createElement('div');
            toast.className = 'local_bridge-toast ' + (type || 'success');
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'bi bi-check-circle' : 'bi bi-exclamation-circle';
            icon.setAttribute('aria-hidden', 'true');
            const text = document.createElement('b');
            text.textContent = message;
            toast.append(icon, text);
            region.appendChild(toast);
            setTimeout(() => toast.classList.add('is-visible'), 10);
            setTimeout(() => {
                toast.classList.remove('is-visible');

                setTimeout(() => {
                    toast.remove();
                }, 200);
            }, 3200);
        },

        formatString: function(key, values) {
            let value = this.strings[key] || '';
            Object.keys(values || {}).forEach((name) => {
                value = value.replace(new RegExp('\\{\\$a->' + name + '\\}', 'g'), values[name]);
            });
            return value;
        },

        escape: function(value) {
            const div = document.createElement('div');
            div.textContent = value === null ? '' : String(value);
            return div.innerHTML;
        }
    };
});
