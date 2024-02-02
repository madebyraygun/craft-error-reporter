<?php

namespace madebyraygun\crafterrorreporter\models;

use craft\base\Model;
use craft\helpers\App;
use madebyraygun\crafterrorreporter\ErrorReporter as Plugin;

/**
 * Error Reporter settings
 */
class Settings extends Model
{
    public $basicLoggingEnabled = true;
    public ?string $githubToken = null;
    public ?string $githubRepoHandle = null;
    public ?string $githubIssueAssignees = null;
    public ?string $excludedStatusCodes = '404';

    public function defineRules(): array
    {
        return [
            [['githubToken', 'githubRepoHandle'], 'required'],
        ];
    }
    
    public function getBasicLoggingEnabled(): bool
    {
        return (bool) Plugin::getInstance()->getSettings()->basicLoggingEnabled;
    }

    public function getGithubToken(): string
    {
        return App::parseEnv(Plugin::getInstance()->getSettings()->githubToken);
    }

    public function getGithubRepoHandle(): string
    {
        return App::parseEnv(Plugin::getInstance()->getSettings()->githubRepoHandle);
    }

    public function getGithubIssueAssignees(): array
    {
        return explode(',', App::parseEnv(Plugin::getInstance()->getSettings()->githubIssueAssignees));
    }

    public function getExcludedStatusCodes(): array
    {
        return explode(',', App::parseEnv(Plugin::getInstance()->getSettings()->excludedStatusCodes));
    }
}
