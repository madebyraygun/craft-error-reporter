<?php

namespace madebyraygun\crafterrorreporter\services;

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use madebyraygun\crafterrorreporter\models\ErrorModel;
use madebyraygun\crafterrorreporter\models\Settings;
use madebyraygun\crafterrorreporter\records\ErrorRecord;

class BasicLoggerService extends Component
{
    private $token;

    private $repo;

    private $assignees;

    private $excludedStatusCodes;

    public function __construct()
    {
        $settings = new Settings();
        $this->token = $settings->getGithubToken();
        $this->repo = $settings->getGithubRepoHandle();
        $this->assignees = $settings->getGithubIssueAssignees();
        $this->excludedStatusCodes = $settings->getExcludedStatusCodes();
    }

    /**
     * @param $exception
     * @return void
     */

    public function logException(object $exception): void
    {
        if (isset($exception->statusCode) && in_array($exception->statusCode, $this->excludedStatusCodes)) {
            Craft::info(
                "Exception code {$exception->statusCode} is excluded from reporting and was not recorded.",
                __METHOD__
            );
            return;
        }
        $formatted = $this->formatException($exception);
        $record = $this->getErrorRecord($exception) ?? null;
        if (!$record) {
            $response = $this->recordError($formatted);
            $this->createRecord($exception, $response);
        } else {
            $this->updateRecord($record);
        }
    }

    public function recordError(array $formatted): ?object
    {
        /* @todo Errors can be sent to different services from here */
        $client = new Client();
        $response = $client->request('POST', 'https://api.github.com/repos/' . $this->repo . '/issues', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-GitHub-Api-Version' => '2022-11-28',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'body' => json_encode([
                'title' => $formatted['title'],
                'body' => $formatted['body'],
                'assignees' => $this->assignees,
                'labels' => [
                    'bug',
                ],
            ]),
        ]);
        if (!$response) {
            return null;
        }
        return $response;
    }

    public function createRecord(object $exception, object $response): bool
    {
        if ($response->getStatusCode() !== 201) {
            Craft::warning(
                'Could not create issue on Github',
                __METHOD__
            );
            return false;
        }

        $apiRecordLocation = $response->getHeaderLine('Location');
        $githubIssueUrl = str_replace('https://api.github.com/repos/', 'https://github.com/', $apiRecordLocation);

        $record = new ErrorRecord();
        $formatted = $this->formatException($exception);
        $model = new ErrorModel([
            'hash' => $this->getErrorHash($exception),
            'dateCreated' => new \DateTime(),
            'dateUpdated' => new \DateTime(),
            'title' => $formatted['title'],
            'githubIssueUrl' => $githubIssueUrl,
            'count' => 1,
        ]);

        if (!$model->validate()) {
            Craft::warning(
                'Could not create new issue record',
                __METHOD__
            );
            return false;
        }

        $record->hash = $model->hash;
        $record->dateCreated = $model->dateCreated;
        $record->dateUpdated = $model->dateUpdated;
        $record->title = $model->title;
        $record->githubIssueUrl = $model->githubIssueUrl;
        $record->count = $model->count;

        return $record->save();
    }

    public function updateRecord(object $record): void
    {
        $record->dateUpdated = new \DateTime();
        $record->count = $record->count + 1;
        $record->save();
    }
    private function formatException(object $exception): array
    {
        $message = rtrim($exception->getMessage(), '.');
        $body = preg_replace('/(^[^\S\r\n]+|[^\S\r\n]+$)/m', '', "
            ### Error
            `{$exception->getMessage()}`
            ### Exception Code
            `Code {$exception->getCode()}`
            ### File
            `{$exception->getFile()}`
            ### Line
            `on line {$exception->getLine()}`
            ### Stack Trace
            ```
            {$exception->getTraceAsString()}
            ```
        ");
        if (isset($exception->statusCode)) {
            $body .= preg_replace('/(^[^\S\r\n]+|[^\S\r\n]+$)/m', '', "
                ### Status Code
                `{$exception->statusCode}`
            ");
        }
        $formatted = [
           'title' => "Error: {$message} in {$exception->getFile()} on line {$exception->getLine()}",
            'body' => $body,
        ];
        return $formatted;
    }

    public function getErrorRecord(object $exception): ?ErrorRecord
    {
        $hash = $this->getErrorHash($exception);
        return ErrorRecord::find()->where(['hash' => $hash])->one();
    }

    public function getErrorHash(object $exception): string
    {
        $hash = md5(json_encode(
            $exception->getMessage() . $exception->getFile() . $exception->getLine()
        ));
        return $hash;
    }
}
