<?php

namespace madebyraygun\crafterrorreporter\records;

use craft\db\ActiveRecord;

class ErrorRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%error_reporter_logged}}';
    }
}
