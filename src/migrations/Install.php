<?php

namespace madebyraygun\crafterrorreporter\migrations;

use craft\db\Migration;
use madebyraygun\crafterrorreporter\records\ErrorRecord;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTables();
        return true;
    }

    public function safeDown()
    {
        $this->dropTableIfExists(ErrorRecord::tableName());
        return true;
    }

    private function createTables()
    {
        $this->archiveTableIfExists(ErrorRecord::tableName());
        $this->createTable(ErrorRecord::tableName(), [
            'errorId' => $this->primaryKey(),
            'hash' => $this->string(32),
            'dateCreated' => $this->dateTime(),
            'dateUpdated' => $this->dateTime(),
            'title' => $this->string(256),
            'githubIssueUrl' => $this->string(256),
            'count' => $this->integer(),
        ]);
    }
}
