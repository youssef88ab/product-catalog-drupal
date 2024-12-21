<?php

namespace Drupal\better_social_share\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("node_better_social_share")
 */
class NodeBetterSocialShare extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    if ($entity->access('view')) {
      $data = better_social_share_create_entity_data($entity);

      // Get button type.
      $btn_type = $data['btn_type'];
      $btn_bg_color = $data['btn_bg_color'];
      if (!in_array($btn_type, ['custom', 'transparent'])) {
        $btn_bg_color = '';
      }

      $build['better_social_share'] = [
        '#entity_url' => $data['entity_url'],
        '#entity_title' => $data['entity_title'],
        '#more_button_type' => $data['more_button_type'],
        '#button_image' => $data['button_image'],
        '#btn_bg_color' => $btn_bg_color,
        '#btn_type' => $data['btn_type'],
        '#icon_color_type' => $data['icon_color_type'],
        '#icon_color' => $data['icon_color'],
        '#btn_border_round' => $data['btn_border_round'],
        '#btn_show_label' => $data['btn_show_label'],
        '#enable_button_spacing' => $data['enable_button_spacing'],
        '#buttons_label' => $data['buttons_label'],
        '#more_button_placement' => $data['more_button_placement'],
        '#social_share_platforms' => $data['social_share_platforms'],
        '#buttons_size' => $data['buttons_size'],
        '#entity_type' => $entity->getEntityType()->id(),
        '#bundle' => $entity->bundle(),
        '#theme' => 'better_social_share_standard',
      ];

      $url_options = [
        'absolute' => TRUE,
        'language' => \Drupal::languageManager()->getCurrentLanguage(),
      ];
      $base_url = Url::fromRoute('better_social_share.ajax', [], $url_options)->toString();
      $build['#attached']['drupalSettings']['base_url'] = $base_url;
      $build['#attached']['library'][] = 'better_social_share/better_social_share.front';

      return $build;

    }
  }

}
