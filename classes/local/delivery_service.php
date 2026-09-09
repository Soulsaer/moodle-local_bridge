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

namespace local_bridge\local;

/**
 * Handles outbound Bridge deliveries.
 *
 * @package    local_bridge
 * @copyright  2026 Bridge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delivery_service {
    /** Maximum response body retained in execution history. */
    private const MAX_RESPONSE_LENGTH = 4000;

    /**
     * Send a JSON payload to an administrator-configured endpoint.
     *
     * @param string $endpoint The configured endpoint URL.
     * @param string $payload The JSON payload to send.
     * @param int $timeout The request timeout in seconds.
     * @param string $secret The optional signing secret.
     * @return array<string, mixed>
     */
    public static function send(string $endpoint, string $payload, int $timeout = 15, string $secret = ''): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        if (!automation_service::is_safe_endpoint($endpoint)) {
            throw new \moodle_exception('invalidendpoint', 'local_bridge');
        }

        $curl = new \curl();
        $security = $curl->get_security();
        if ($security->url_is_blocked($endpoint)) {
            throw new \moodle_exception('blockedendpoint', 'local_bridge');
        }

        $options = [
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Moodle-Bridge/0.3',
            ],
            'CURLOPT_TIMEOUT' => $timeout,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_FOLLOWLOCATION' => false,
            'CURLOPT_RESOLVE' => $security->get_resolve_info(),
        ];

        if ($secret !== '') {
            $options['CURLOPT_HTTPHEADER'][] = 'X-Bridge-Signature: sha256=' . hash_hmac('sha256', $payload, $secret);
        }

        $response = $curl->post($endpoint, $payload, $options);
        $info = $curl->get_info();
        $code = !empty($info['http_code']) ? (int) $info['http_code'] : 0;

        return [
            'code' => $code,
            'response' => substr((string) $response, 0, self::MAX_RESPONSE_LENGTH),
            'success' => $code >= 200 && $code < 300,
        ];
    }
}
