<?php

namespace madebyraygun\crafterrorreporter;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Utilities;
use craft\web\ErrorHandler;
use madebyraygun\crafterrorreporter\models\Settings;
use madebyraygun\crafterrorreporter\services\BasicLoggerService;
use madebyraygun\crafterrorreporter\utilities\ErrorTable;
use yii\base\Event;

/**
 * Error Reporter plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author Raygun <dev@madebyraygun.com>
 * @copyright Raygun Design, LLC
 * @license https://craftcms.github.io/license/ Craft License
 */
class ErrorReporter extends BasePlugin
{
    public const EDITION_LITE = 'lite';
    public const EDITION_PRO = 'pro';

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'basicLoggerService' => BasicLoggerService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('error-reporter/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = ErrorTable::class;
            }
        );

        Event::on(
            ErrorHandler::class,
            ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
            function(ExceptionEvent $event) {
                if (!$this->getSettings()->getBasicLoggingEnabled()) {
                    return;
                }
                $this->basicLoggerService->logException($event->exception);
            }
        );
    }
}
