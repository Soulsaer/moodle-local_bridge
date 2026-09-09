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
use local_bridge\local\delivery_service;

/**
 * Delivery service tests.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \local_bridge\local\delivery_service
 */
final class delivery_service_test extends advanced_testcase {
    /**
     * Test that unsafe endpoints are rejected before making a request.
     *
     * @covers ::send
     */
    public function test_delivery_rejects_unsafe_endpoint_before_request(): void {
        $this->expectException(\moodle_exception::class);
        delivery_service::send('http://example.com/hook', '{}');
    }
}
