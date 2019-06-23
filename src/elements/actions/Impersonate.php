<?php
/**
 * Impersonate plugin for Craft CMS 3.x
 *
 * Allows non-admin users to log in as other users.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\impersonate\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Json;

/**
 * @author    Dimitar Kokov
 * @package   Impersonate
 * @since     1.0.0
 */
class Impersonate extends ElementAction
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The trigger label
     */
    public $label;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->label === null) {
            $this->label = Craft::t('impersonate', 'Impersonate');
        }
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        $type = Json::encode(static::class);

        $error = Craft::t('impersonate', 'Unable to impersonate user. Check permission settings');
        $unknownError = Craft::t('impersonate', 'An unknown error occurred');

        $js = <<<EOD
(function()
{
    var trigger = new Craft.ElementActionTrigger({
        type: {$type},
        batch: false,
        validateSelection: function(\$selectedItems)
        {
            return true;
        },
        activate: function(\$selectedItems)
        {
            var selectedElementIds = Craft.elementIndex.getSelectedElementIds(),
                totalSelected = selectedElementIds.length;

            if (totalSelected === 0) {
                return;
            }
            
            var action = Craft.elementIndex.settings.submitActionsAction,
                viewParams = Craft.elementIndex.getViewParams(),
                params = $.extend(viewParams, {
                    elementAction: $type,
                    elementIds: selectedElementIds
                });
            
            Craft.elementIndex.setIndexBusy();

            Craft.postActionRequest(action, params, function(response, textStatus) {
                if (textStatus === 'success') {
                    if (response.success) {
                        Craft.redirectTo(Craft.getSiteUrl());
                    }
                    else {
                        Craft.elementIndex.setIndexAvailable();
                        Craft.cp.displayError('$error');
                    }
                } else {
                    Craft.elementIndex.setIndexAvailable();
                    Craft.cp.displayError('$unknownError');
                }
            });
        }
    });
})();
EOD;

        Craft::$app->getView()->registerJs($js);
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        /** @var User $user */
        $user = $query->one();

        if (!$user instanceof User || $user->getIsCurrent() || $user->admin) {
            return false;
        }

        $userSession = Craft::$app->getUser();

        $allowImpersonate = true;
        foreach ($user->getGroups() as $userGroup) {
            if (!$userSession->checkPermission('impersonate' . $userGroup->handle)) {
                $allowImpersonate = false;
                break;
            }
        }

        if (!$allowImpersonate) {
            return false;
        }

        $session = Craft::$app->getSession();

        // Save the original user ID to the session now so User::findIdentity()
        // knows not to worry if the user isn't active yet
        $session->set(User::IMPERSONATE_KEY, $userSession->getId());

        if (!$userSession->loginByUserId($user->id)) {
            $session->remove(User::IMPERSONATE_KEY);
            Craft::error(
                $userSession->getIdentity()->username . ' tried to impersonate userId: ' . $user->id
                . ' but something went wrong.',
                __METHOD__
            );

            return false;
        }

        return true;
    }
}
