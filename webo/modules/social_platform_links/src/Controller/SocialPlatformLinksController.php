<?php

namespace Drupal\social_platform_links\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the Social Platform Links module.
 */
class SocialPlatformLinksController extends ControllerBase {

  /**
   * Returns the Social Platform Links page.
   *
   * @return array
   *   The render array for the Social Platform Links page.
   */
  public function socialPlatformLinksPage() {
    $build = [];
    $build['social_platform_links_block'] = [
      '#type' => 'block',
      '#block' => 'social_platform_links_block',
    ];
    return $build;
  }
}