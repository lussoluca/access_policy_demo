<?php

declare(strict_types=1);

namespace Drupal\access_policy_demo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Access Policy Demo routes.
 */
final class AccessPolicyDemoController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function __invoke(): array {
    $build['content'] = [
      '#markup' => $this->currentUser()->hasPermission('access promotional banners') ? 'Some promotional banner' : '',
      '#cache' => [
        'contexts' => ['languages'],
      ],
    ];

    return $build;
  }

}
