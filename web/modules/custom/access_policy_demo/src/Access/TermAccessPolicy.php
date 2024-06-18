<?php

declare(strict_types=1);

namespace Drupal\access_policy_demo\Access;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccessPolicyBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\CalculatedPermissionsItem;
use Drupal\Core\Session\RefinableCalculatedPermissionsInterface;
use Drupal\taxonomy\TermInterface;
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
    $calculated_permissions = parent::calculatePermissions($account, $scope);

    if ($scope != self::SCOPE_TERM) {
      return $calculated_permissions;
    }

    $user = User::load($account->id());
    /** @var \Drupal\taxonomy\TermInterface[] $user_terms */
    $user_terms = $user->get('field_access')->referencedEntities();
    foreach ($user_terms as $user_term) {
      $cacheability = new CacheableMetadata();
      $cacheability->addCacheableDependency($user_term);

      $restricted = $this->isRestricted($user_term);
      if ($restricted) {
        $cacheability->addCacheContexts(['is_restricted']);
        $permissions = [];
      }
      else {
        $permissions = $this->getPermissions($account);
      }

      $calculated_permissions
        ->addItem(
          new CalculatedPermissionsItem(
            permissions: $permissions,
            isAdmin    : FALSE,
            scope      : self::SCOPE_TERM,
            identifier : $user_term->id()
          )
        )
        ->addCacheableDependency($cacheability);
    }

    return $calculated_permissions;
  }

  private function getPermissions(AccountInterface $account): array {
    /** @var \Drupal\user\Entity\Role[] $extra_roles */
    $extra_roles = User::load($account->id())
      ->get('field_extra_role')
      ->referencedEntities();

    if (count($extra_roles) === 0) {
      return [];
    }

    $extra_role = reset($extra_roles);

    return $extra_role->getPermissions();
  }

  /**
   * {@inheritdoc}
   */
  public function getPersistentCacheContexts(): array {
    return ['user.terms'];
  }

  /**
   * @param \Drupal\taxonomy\TermInterface $user_term
   *
   * @return bool
   */
  private function isRestricted(TermInterface $user_term): bool {
    $restriction = $user_term->get('field_restriction')->getValue();

    if (count($restriction) == 0 || count($restriction) == 2) {
      return FALSE;
    }

    $field_value = $restriction[0]['value'];

    if ($field_value === 'weekend' && IsWeekendCacheContext::isWeekend()) {
      return FALSE;
    }

    if ($field_value === 'weekdays' && !IsWeekendCacheContext::isWeekend()) {
      return FALSE;
    }

    return TRUE;
  }

}

