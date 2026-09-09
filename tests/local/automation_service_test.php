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

namespace local_bridge;

use advanced_testcase;
use local_bridge\local\automation_service;

/**
 * Automation service tests.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \local_bridge\local\automation_service
 */
final class automation_service_test extends advanced_testcase {
    /**
     * Test that supported events are classified.
     *
     * @covers ::SUPPORTED_EVENTS
     */
    public function test_supported_events_are_classified(): void {
        $this->assertArrayHasKey('\\core\\event\\user_created', automation_service::SUPPORTED_EVENTS);
        $this->assertArrayHasKey('\\core\\event\\course_created', automation_service::SUPPORTED_EVENTS);
    }

    /**
     * Test that unsafe endpoints are rejected.
     *
     * @covers ::is_safe_endpoint
     */
    public function test_safe_endpoint_requires_https(): void {
        $this->assertTrue(automation_service::is_safe_endpoint('https://example.com/hook'));
        $this->assertFalse(automation_service::is_safe_endpoint('http://example.com/hook'));
        $this->assertFalse(automation_service::is_safe_endpoint('https://localhost/hook'));
        $this->assertFalse(automation_service::is_safe_endpoint('https://127.0.0.1/hook'));
    }
}
