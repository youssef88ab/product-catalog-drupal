<?php

/**
 * @file
 * Views integration for BetterSocialShare.
 */

/**
 * Implements hook_views_data_alter().
 */
function better_social_share_views_data_alter(array &$data) {
  // Add the better_social_share link to the node fields.
  $data['node']['better_social_share'] = [
    'title' => t('Better Social Share Buttons'),
    'field' => [
      'title' => t('Better Social Share Buttons'),
      'help' => t('Provides social share buttons for sharing the content.'),
      'id' => 'node_better_social_share',
    ],
  ];
}
