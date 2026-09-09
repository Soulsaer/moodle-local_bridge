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

/**
 * Bridge upgrade steps.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the local_bridge plugin.
 *
 * @param int $oldversion The old plugin version.
 * @return bool
 */
function xmldb_local_bridge_upgrade(int $oldversion): bool {
    global $DB;

    if ($oldversion < 2026083001) {
        upgrade_plugin_savepoint(true, 2026083001, 'local', 'bridge');
    }

    if ($oldversion < 2026083002) {
        upgrade_plugin_savepoint(true, 2026083002, 'local', 'bridge');
    }

    if ($oldversion < 2026083003) {
        // UI refinement release: bundled Bootstrap Icons and layout improvements.
        upgrade_plugin_savepoint(true, 2026083003, 'local', 'bridge');
    }

    if ($oldversion < 2026083004) {
        // Core edition badge, version display and dashboard refinement.
        upgrade_plugin_savepoint(true, 2026083004, 'local', 'bridge');
    }

    if ($oldversion < 2026083005) {
        // Sidebar hierarchy, typography and automation health chart refinement.
        upgrade_plugin_savepoint(true, 2026083005, 'local', 'bridge');
    }

    if ($oldversion < 2026083006) {
        // Correct active/inactive health chart rendering and pin account metadata to the sidebar bottom.
        upgrade_plugin_savepoint(true, 2026083006, 'local', 'bridge');
    }

    if ($oldversion < 2026083007) {
        // Refine automation data table and fix outbound test delivery bootstrap.
        upgrade_plugin_savepoint(true, 2026083007, 'local', 'bridge');
    }

    if ($oldversion < 2026083008) {
        // Refine health visualization, test action state and delivery result modal.
        upgrade_plugin_savepoint(true, 2026083008, 'local', 'bridge');
    }

    if ($oldversion < 2026083009) {
        upgrade_plugin_savepoint(true, 2026083009, 'local', 'bridge');
    }

    if ($oldversion < 2026083010) {
        // Canvas chart library, full-width builder, controls and test-result UI refinements.
        upgrade_plugin_savepoint(true, 2026083010, 'local', 'bridge');
    }

    if ($oldversion < 2026083011) {
        // Namespaced UI selectors, Moodle Bootstrap controls, language strings, execution data table and modal confirmation.
        upgrade_plugin_savepoint(true, 2026083011, 'local', 'bridge');
    }

    if ($oldversion < 2026083012) {
        // AJAX execution table refresh, stable table shell and corrected upper half-donut rendering.
        upgrade_plugin_savepoint(true, 2026083012, 'local', 'bridge');
    }

    if ($oldversion < 2026083013) {
        // Shared AJAX data-table behaviour, server-side automation sorting and corrected Chart.js radians.
        upgrade_plugin_savepoint(true, 2026083013, 'local', 'bridge');
    }

    if ($oldversion < 2026090101) {
        // Correct half-donut orientation and replace execution drawer with Moodle native modal.
        upgrade_plugin_savepoint(true, 2026090101, 'local', 'bridge');
    }

    if ($oldversion < 2026090102) {
        // Consistent execution data table controls and improved execution detail modal readability.
        upgrade_plugin_savepoint(true, 2026090102, 'local', 'bridge');
    }

    if ($oldversion < 2026090501) {
        // Use unique named SQL parameters for multi-column search queries.
        upgrade_plugin_savepoint(true, 2026090501, 'local', 'bridge');
    }

    if ($oldversion < 2026090502) {
        // Marketplace readiness: privacy cleanup and bundled third-party library metadata.
        upgrade_plugin_savepoint(true, 2026090502, 'local', 'bridge');
    }

    return true;
}
