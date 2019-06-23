# Impersonate plugin for Craft CMS 3.x

Craft 3 comes with a "Log in as ..." feature that is restricted to Admin users only.

This plugin allows you to give non-admin users the permission to log in as other non-admin users, on a per user group basis.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require devkokov/impersonate

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Impersonate.

## Configuring Impersonate

The User Permissions settings in the Control Panel allow you to configure which user groups can be impersonated. These permissions can be applied to user groups and/or individual users.

## Using Impersonate

Select a user from the Users listing page (using the checkbox), then click on Impersonate in the actions drop-down menu.

Brought to you by [Dimitar Kokov](https://github.com/devkokov)
