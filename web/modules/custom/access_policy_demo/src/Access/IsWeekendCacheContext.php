<?php

namespace Drupal\access_policy_demo\Access;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Defines a cache context that determines whether today is a weekend.
 *
 * Copied from a bpekker's blog post: https://bpekker.dev/access-policy-api.
 */
class IsWeekendCacheContext implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): string {
    return t('Is Weekend?');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL): string {
    $result = static::isWeekend() ? 'weekend' : 'weekday';

    return "is_restricted.{$result}";
  }

  /**
   * @return bool
   */
  public static function isWeekend(): bool {
    return date('w', time()) % 6 === 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL): CacheableMetadata {
    return (new CacheableMetadata());
  }

}
