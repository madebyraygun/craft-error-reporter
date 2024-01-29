<?php

namespace madebyraygun\crafterrorreporter\models;

use craft\base\Model;

class ErrorModel extends Model
{
    public int $errorId;
    public string $hash;
    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;
    public string $title;
    public string $githubIssueUrl;
    public int $count;

    public function rules(): array
    {
        return [
            [['hash', 'dateCreated', 'dateUpdated', 'title', 'githubIssueUrl', 'count'], 'required'],
        ];
    }
}
