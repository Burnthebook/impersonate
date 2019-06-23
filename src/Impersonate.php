<?php
/**
 * Impersonate plugin for Craft CMS 3.x
 *
 * Allows non-admin users to log in as other users.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\impersonate;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\User;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\records\UserGroup;
use craft\services\UserPermissions;
use devkokov\impersonate\elements\actions\Impersonate as ActionImpersonate;

use yii\base\Event;

/**
 * Class Impersonate
 *
 * @author    Dimitar Kokov
 * @package   Impersonate
 * @since     1.0.0
 *
 */
class Impersonate extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Impersonate
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(User::class, Element::EVENT_REGISTER_ACTIONS, function (RegisterElementActionsEvent $event) {
            $event->actions[] = ActionImpersonate::class;
        });

        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function (RegisterUserPermissionsEvent $event) {
                $nestedPermissions = [];

                $userGroups = UserGroup::find()
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                /** @var UserGroup $userGroup */
                foreach ($userGroups as $userGroup) {
                    $nestedPermissions['impersonate' . $userGroup->handle] = [
                        'label' => $userGroup->name
                    ];
                }

                $event->permissions['Impersonate'] = [
                    'impersonateUsers' => [
                        'label' => 'Impersonate non-admin users',
                        'nested' => $nestedPermissions,
                    ]
                ];
            }
        );

        Craft::info(
            Craft::t(
                'impersonate',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }
}
