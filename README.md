# FlatConnect

FlatConnect is a Laravel Livewire-based client management system designed for ISP or network administrators to monitor and control connected clients on a TP-Link Omada-managed network. It integrates with TP-Link devices to fetch real-time client data, allowing admins to view client details such as hostname, MAC address, IP, repeater association, status (blocked or connected), and next payment due date.

## Features

- Real-time client discovery and synchronization with TP-Link Omada network devices.
- Block and unblock clients with immediate status update on devices and in the database.
- Inline editing of client details, including repeater name and next payment due date.
- Search and filter clients by repeater name, MAC address, and block status.
- Paginated client listing with responsive and mobile-friendly UI.
- Audit logging of admin actions for edits, blocks, and unblocks.
- Easy integration with TP-Link Omada APIs via a dedicated discovery service.

## Usage

- Access the admin dashboard via `/admin` (or configured route).
- The dashboard displays connected clients synced from your TP-Link Omada devices.
- Use the search box to filter clients by repeater name or MAC address.
- Toggle "Show Only Blocked" to list clients currently blocked.
- Click **Block** or **Unblock** buttons to control client access.
- Edit client repeater name or next due date inline and save changes.
- All admin actions are logged for auditing purposes.

## Notes

- Requires access to TP-Link Omada API or compatible discovery service.
- Make sure your API credentials and network configurations are set in the service provider.
- Tested with Laravel 10 and Livewire 3.

---

