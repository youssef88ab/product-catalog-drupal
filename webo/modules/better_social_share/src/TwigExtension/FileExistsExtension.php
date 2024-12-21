<?php

namespace Drupal\better_social_share\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides the Commerce Twig extensions.
 */
class FileExistsExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('media_file_exists', [$this, 'fileExists']),
      new TwigFunction('get_media_file_path', [$this, 'getFullPath']),
    ];
  }

  /**
   * Twig function callback to check if a file exists.
   *
   * @param string $filename
   *   The filename.
   *
   * @return bool
   *   TRUE if the file exists, FALSE otherwise.
   */
  public function fileExists($filename) {
    // Check if the file exists in the current active theme.
    $theme_path = \Drupal::theme()->getActiveTheme()->getPath();
    if (file_exists($theme_path . '/templates/' . $filename)) {
      return TRUE;
    }

    // Check if the file exists in the module directory.
    $module_path = \Drupal::service('extension.list.module')->getPath('better_social_share');

    if (file_exists($module_path . '/templates/template-parts/' . $filename)) {
      return TRUE;
    }

    return FALSE;

  }

  /**
   * Twig function callback to check if a file exists.
   *
   * @param string $filename
   *   The filename.
   *
   * @return bool
   *   TRUE if the file exists, FALSE otherwise.
   */
  public function getFullPath($filename) {
    // Check if the file exists in the current active theme.
    $theme_path = \Drupal::theme()->getActiveTheme()->getPath();
    if (file_exists($theme_path . '/templates/' . $filename)) {
      $theme_name = \Drupal::service('theme.manager')->getActiveTheme()->getName();
      return '@' . $theme_name . '/templates/' . $filename;
    }

    // Check if the file exists in the module directory.
    $module_path = \Drupal::service('extension.list.module')->getPath('better_social_share');
    if (file_exists($module_path . '/templates/template-parts/' . $filename)) {
      return '@better_social_share/templates/template-parts/' . $filename;
    }

    return '';

  }

}
