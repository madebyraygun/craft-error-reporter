# Error Reporter

Automatically log Craft CMS application errors and send to email, Github, and more.

## Requirements

This plugin requires Craft CMS 4.5.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Error Reporter”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require madebyraygun/craft-error-reporter

# tell Craft to install the plugin
./craft plugin/install error-reporter
```

## Configuration

Edit your settings via the plugin settings in the Craft Control Panel or create a new file in your `craft\config` folder called  `error-reporter.php` and add the following:

```php
<?php

use craft\helpers\App;

return [
  'basicLoggingEnabled' => true, //Could be set to env value
  'githubToken' => App::env('GITHUB_TOKEN'), //Required
  'githubRepoHandle' => App::env('GITHUB_REPO_HANDLE'), //Required
  'githubIssueAssignees' => App::env('GITHUB_ISSUE_ASSIGNEE'), //Optional
  'excludedStatusCodes' => '404' //Comma separated
];
```
Note: it's recommended to use environment values so as to not save sensitive tokens in your Craft database.

Add values for your `githubToken`, `githubRepoHandle`, and (optional) `githubIssueAssignees` to your .env file. Github repo should be formatted as `OWNER/REPO`.

At the moment, the plugin uses [Github personal access tokens](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens). Support for other authentication methods are on the roadmap. The plugin will not work without a valid token that has access to create an issue in the specified repo.

By default, the plugin ignores exceptions with a 404 status code. You can specify other codes to ignore with the `excludedStatusCodes` setting.

## Usage

The plugin will automatically log application errors to the Craft database. You can optionally send errors to email, Github, or other targets. You can use the included Utility panel to view any recorded errors along with the relevant notifications and statistics.

## Roadmap

* Native logging target for more comprehensive logging
* Optionally add additional details to issues, such as browser information or Craft environment details
* Customize issue title format
* Save stacktrace to db?
* Ability to mark records as handled in the Craft
* Support for wildcard error code ignore list
* Use oAuth or Github App in favor of personal tokens
* Support for email notifications
* Support for Slack integrations
* Support for push notifications via Simplepush
* Dashboard widget
