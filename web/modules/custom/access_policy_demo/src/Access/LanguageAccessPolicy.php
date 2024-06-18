<?php

namespace Drupal\access_policy_demo\Access;

use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\CalculatedPermissionsItem;
use Drupal\Core\Session\RefinableCalculatedPermissionsInterface;

/**
 * Access policy for the language.
 */
class LanguageAccessPolicy extends AccessPolicyBase {

  /**
   * {@inheritdoc}
   */
  public function alterPermissions(
    AccountInterface $account,
    string $scope,
    RefinableCalculatedPermissionsInterface $calculated_permissions,
  ): void {
    if (\Drupal::languageManager()->getCurrentLanguage()->getId() == 'en') {
      $calculated_permissions->addItem(
        item     : new CalculatedPermissionsItem(
          permissions: ['access promotional banners'],
          isAdmin    : FALSE
        ),
        overwrite: FALSE
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentCacheContexts(): array {
    return ['languages'];
  }

}
