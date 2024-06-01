<?php

namespace Drupal\access_policy_demo\Access;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Defines the UserTermsCacheContext service, for "per terms" caching.
 */
class UserTermsCacheContext implements CalculatedCacheContextInterface {

  /**
   * UserTermsCacheContext constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   */
  public function __construct(
    protected readonly AccountInterface $account,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): string {
    return t("User's terms");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($term = NULL): string {
    $user = User::load($this->account->id());
    $user_terms = array_map(
      fn($loaded_term) => $loaded_term->id(),
      $user->get('field_term_access')->referencedEntities()
    );

    if ($term === NULL) {
      return implode(',', $user_terms);
    }
    else {
      return (in_array($term, $user_terms) ? 'true' : 'false');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($term = NULL): CacheableMetadata {
    return (new CacheableMetadata())->setCacheTags(['user:' . $this->account->id()]);
  }

}
