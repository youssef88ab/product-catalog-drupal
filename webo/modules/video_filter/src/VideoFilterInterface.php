<?php

namespace Drupal\video_filter;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for ice cream flavor plugins.
 */
interface VideoFilterInterface extends PluginInspectionInterface {

  /**
   * Return the name of the Video Filter codec.
   *
   * @return string
   *   The human-readable name of the codec.
   */
  public function getName();

  /**
   * Return codec sample URL.
   *
   * @return string
   *   The codec sample URL.
   */
  public function getExampleUrl();

  /**
   * Return an array of regular expressions for the codec.
   *
   * @return array
   *   Regular expression for the codec.
   */
  public function getRegexp();

  /**
   * Return video player ratio.
   *
   * @return string
   *   Aspect ratio of the video player.
   */
  public function getRatio();

  /**
   * Return video player control bar height.
   *
   * @return int
   *   Video player control bar height.
   */
  public function getControlBarHeight();

  /**
   * Return Video Filter coded usage instructions.
   *
   * @return string
   *   Video Filter coded usage instructions.
   */
  public function instructions();

  /**
   * Return video HTML5 video (iframe).
   *
   * @param array $video
   *   The video definition to load.
   *
   * @return array
   *   A list of attributes to add to an IFRAME HTML tag.
   */
  public function iframe($video);

  /**
   * Return Flash video (flv).
   *
   * @param array $video
   *   The video definition to load.
   *
   * @return array
   *   A list of attributes to add to an OBJECT HTML tag.
   */
  public function flash($video);

  /**
   * Return HTML code of the video player.
   *
   * @param array $video
   *   The video definition to load.
   *
   * @return array
   *   A list of attributes to add to a VIDEO HTML tag.
   */
  public function html($video);

  /**
   * Return embed options (Form API elements).
   *
   * @return array
   *   Embed options (Form API elements).
   */
  public function options();

  /**
   * Returns the absolute URL to a preview image.
   *
   * @param array $video
   *   The video definition to load.
   *
   * @return string
   *   Absolute URL to a preview image.
   */
  public function preview($video);

}
