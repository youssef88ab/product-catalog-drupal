<?php
namespace Drupal\social_platform_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * Provides the Social Platform Links Block with image upload.
 *
 * @Block(
 *   id="social_platform_links_block",
 *   admin_label = @Translation("Social Platform Links"),
 * )
 */

class SocialPlatformLinksBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    // Platforms.
    $platforms = [
      'facebook' => 'Facebook',
      'twitter' => 'Twitter',
      'LinkedIn' => 'LinkedIn',
      'email' => 'email',
      'pinterest' => 'pinterest',
    ];

    foreach ($platforms as $platform_id => $platform_name) {
      $form['platforms'][$platform_id] = [
        '#type' => 'fieldset',
        '#title' => $platform_name,
      ];

error_reporting(E_ERROR | E_PARSE);
 
      $form['platforms'][$platform_id]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Description for !platform', ['!platform' => $platform_name]),
        '#title_display' => 'invisible',
        '#size' => 40,
        '#default_value' => isset($config['platforms'][$platform_id]
        ['value']) ? $config['platforms'][$platform_id]['value'] : '',
          [
        ],
      ];
      // Upload image for each platform.
      $form['platforms'][$platform_id]['image_upload'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload Image for !platform', ['!platform' => $platform_name]),
        '#upload_location' => 'public://social_platform_icons/',
        '#default_value' => isset($config['platforms'][$platform_id]['image_upload']) ? $config['platforms'][$platform_id]['image_upload'] : '',
        '#description' => $this->t('Upload an image for the !platform icon.', ['!platform' => $platform_name]),
      ];
    }

    return $form;
  }

  /**
 * {@inheritdoc}
 */
public function blockSubmit($form, FormStateInterface $form_state) {
  $values = $form_state->getValues();
  $this->configuration['platforms'] = $values['platforms'];

  foreach ($values['platforms'] as $platform_id => $platform_settings) {
    $this->configuration['platforms'][$platform_id]['value'] = $platform_settings['value'];
  }
}

/**
 * {@inheritdoc}
 */
public function build() {
  $config = $this->getConfiguration();
  $output = [];

  $file_url_generator = \Drupal::service('file_url_generator');
  $file_system = \Drupal::service('file_system');

  foreach ($config['platforms'] as $platform_id => $platform_settings) {
    $file = File::load($platform_settings['image_upload'][0]);

    if ($file) {
      $output[] = [
        '#markup' => '<a href="' . $config['platforms'][$platform_id]['value'] . '" target="_blank"><img src="' . $file_url_generator->generateString($file->getFileUri()) . '" alt="' . $file->getFilename() . '" /></a>',
      ];
    }
  }
  return $output;
}

}