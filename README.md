# Bridge for Moodle

Bridge is an open-source Moodle local plugin for creating reliable event-driven automations between Moodle and external HTTPS services.

## Features

- Modern Moodle administration dashboard.
- Create, edit, enable, disable, and delete automations.
- Connect supported Moodle events to external HTTPS endpoints.
- JSON payload delivery using HTTP POST.
- Background delivery using Moodle scheduled tasks.
- Automatic retries for failed deliveries.
- Execution history with delivery status and response information.
- Search, sorting, and pagination for automation and execution data.
- Manual retry of failed executions.
- Moodle capability checks and session-key protection for state-changing actions.
- Moodle Privacy API support for stored user-related data.
- Cross-database compatible Moodle database access.
- No external PHP framework or Composer dependency.

## Supported Events

The current release supports five curated Moodle events:

- Course created
- Course updated
- User created
- User enrolled
- User profile updated

## How It Works

1. An administrator creates an automation.
2. A supported Moodle event is selected.
3. An HTTPS destination endpoint is configured.
4. Bridge listens for the selected event.
5. When the event occurs, Bridge creates a delivery job.
6. Moodle scheduled tasks process queued jobs in the background.
7. Bridge sends the event data to the configured endpoint as JSON.
8. Delivery results are recorded in Bridge.
9. Failed deliveries are retried according to the current delivery logic.
10. Administrators can review execution history and retry failed executions.

## Requirements

- Moodle 4.5 LTS or a later supported Moodle branch.
- PHP and database requirements provided by the supported Moodle version.
- Moodle cron/scheduled tasks must be running for background delivery.
- An HTTPS endpoint capable of accepting JSON HTTP POST requests.

## Installation

### Moodle Plugin Installer

1. Download the Bridge ZIP package.
2. Log in to Moodle as an administrator.
3. Go to **Site administration → Plugins → Install plugins**.
4. Upload the Bridge ZIP package.
5. Follow Moodle's installation or upgrade prompts.
6. Open the Bridge administration page.

### Manual Installation

Extract the plugin into Moodle's `local` directory:

```text
moodle/
└── local/
    └── bridge/
```

Then log in as a Moodle administrator and visit **Site administration → Notifications**.

## Configuration

After installation:

1. Open Bridge from the Moodle administration interface.
2. Select **Create automation**.
3. Enter an automation name.
4. Select a supported Moodle event.
5. Configure the HTTPS destination.
6. Enable the automation.
7. Save the automation.

For reliable background processing, configure Moodle cron to run regularly.

## External Endpoints

Bridge sends event data to the configured destination using an HTTPS POST request with a JSON payload.

Only HTTPS destinations are accepted by Bridge. Administrators should configure only trusted endpoints and ensure that receiving systems handle incoming data securely.

## Delivery and Execution History

Bridge processes deliveries through Moodle's scheduled task system and records execution information, including:

- Automation
- Triggering event
- Delivery status
- HTTP response information
- Number of attempts
- Execution time
- Error information when available

Failed executions can be inspected from the administration interface and retried where supported.

## Security

Bridge uses Moodle's native security mechanisms, including:

- Moodle capability checks.
- Session-key protection for state-changing operations.
- Moodle Database API and parameterised queries.
- HTTPS-only destination validation.
- Moodle outbound HTTP security controls.
- Escaping of template output.

Administrators should configure only trusted external destinations.

## Privacy

Bridge implements the Moodle Privacy API.

Bridge may store automation configuration, queued delivery jobs, execution history, and information associated with Moodle events.

Depending on the triggering event, an outgoing payload may contain Moodle user, course, or other event-related information.

When an automation sends data to an external endpoint, that information is transmitted outside the Moodle site to the destination configured by the Moodle administrator.

Administrators should review configured destinations and ensure that their use of Bridge complies with applicable privacy and data-protection requirements.

Bridge does not require a Bridge-operated cloud service for event delivery.

## Third-Party Libraries

Bridge includes Bootstrap Icons for interface icons.

The bundled library and its license are documented in:

- `thirdpartylibs.xml`
- `thirdparty/bootstrap-icons/readme_moodle.txt`

Bootstrap Icons is distributed under the MIT license.

## Compatibility

The minimum Moodle version required by this release is declared in `version.php`.

This release targets Moodle 4.5 LTS and later supported Moodle branches.

## Development

Bridge uses Moodle's native APIs and conventions, including:

- Database API
- Events API
- External API
- Scheduled Tasks API
- Privacy API
- Capabilities
- Mustache templates
- AMD JavaScript modules
- Moodle administration and form APIs

The project is checked using Moodle development quality tools, including Moodle Code Checker and Moodle Plugin CI.

## Troubleshooting

### Deliveries are not being processed

Check that Moodle cron is running correctly. Bridge uses Moodle scheduled tasks for background delivery.

### An endpoint is rejected

Check that the configured destination is a valid HTTPS URL and review Moodle's outbound HTTP security configuration.

### A delivery fails

Open the execution history and inspect the recorded status and error information. Failed deliveries may be retried according to the current delivery logic.

## Support

Bug reports and feature requests should be submitted through the project's public issue tracker.

For production deployments, test Bridge in a staging environment before enabling automations on a production Moodle site.

## License

Bridge is free and open-source software.

The Moodle plugin code is licensed under the GNU General Public License version 3 or later (GPL-3.0-or-later).

See the source files for applicable license headers and `thirdpartylibs.xml` for third-party library licensing information.
