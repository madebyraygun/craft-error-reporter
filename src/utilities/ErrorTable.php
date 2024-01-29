<?php

namespace madebyraygun\crafterrorreporter\utilities;

use Craft;
use craft\base\Utility;
use madebyraygun\crafterrorreporter\records\ErrorRecord;

class ErrorTable extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Error Reporter');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'error-reporter-table';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath(): ?string
    {
        $iconPath = Craft::getAlias('@madebyraygun/crafterrorreporter/icon-mask.svg');

        if (!is_string($iconPath)) {
            return null;
        }

        return $iconPath;
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('error-reporter/_utilityErrorTable.twig', [
            'errorsQuery' => ErrorRecord::find()->orderBy('dateUpdated DESC'),
        ]);
    }
}
