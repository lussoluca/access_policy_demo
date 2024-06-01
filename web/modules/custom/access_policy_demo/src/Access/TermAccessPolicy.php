<?php

declare(strict_types=1);

namespace Drupal\access_policy_demo\Access;

use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\CalculatedPermissionsItem;
use Drupal\Core\Session\RefinableCalculatedPermissionsInterface;
use Drupal\user\Entity\User;

class TermAccessPolicy extends AccessPolicyBase {

  public const SCOPE_TERM = 'term';

  /**
   * {@inheritdoc}
   */
  public function applies(string $scope): bool {
    return $scope === self::SCOPE_TERM;
  }

  /**
   * {@inheritdoc}
   */
  public function calculatePermissions(AccountInterface $account, string $scope): RefinableCalculatedPermissionsInterface {
    if ($scope != self::SCOPE_TERM) {
      return parent::calculatePermissions($account, $scope);
    }

    $calculated_permissions = parent::calculatePermissions($account, $scope);

    $user = User::load($account->id());
    $user_terms = $user->get('field_term_access')->referencedEntities();
    foreach ($user_terms as $user_term) {
      $calculated_permissions
        ->addItem(
          new CalculatedPermissionsItem(
            permissions: $this->getPermissions($account),
            isAdmin    : FALSE,
            scope      : self::SCOPE_TERM,
            identifier : $user_term->id()
          )
        )
        ->addCacheableDependency($user_term);
    }

    return $calculated_permissions;
  }

  private function getPermissions(AccountInterface $account): array {
    /** @var \Drupal\user\Entity\Role[] $extra_roles */
    $extra_roles = User::load($account->id())->get('field_extra_role')->referencedEntities();

    if (count($extra_roles) === 0) {
      return [];
    }

    $extra_role = reset($extra_roles);

    return $extra_role->getPermissions();
  }

  public function getPersistentCacheContexts(): array {
    return ['user.terms'];
  }

}

