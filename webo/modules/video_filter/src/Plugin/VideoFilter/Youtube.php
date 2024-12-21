<?php

namespace Drupal\video_filter\Plugin\VideoFilter;

use Drupal\video_filter\VideoFilterBase;

/**
 * Provides Youtube codec for Video Filter.
 *
 * @VideoFilter(
 *   id = "youtube",
 *   name = @Translation("YouTube"),
 *   example_url = "https://www.youtube.com/watch?v=EQ1HKCYJM5U",
 *   regexp = {
 *     "/youtube\.com\/watch\?v=([a-z0-9\-_]+)/i",
 *     "/youtu.be\/([a-z0-9\-_]+)/i",
 *     "/youtube\.com\/v\/([a-z0-9\-_]+)/i",
 *     "/youtube\.com\/embed\/([a-z0-9\-_]+)/i",
 *   },
 *   ratio = "16/9",
 *   control_bar_height = 0
 * )
 */
class Youtube extends VideoFilterBase {

  /**
   * Video embed tag attributes.
   */
  protected function attributes($video) {
    $attributes = [
      'modestbranding' => !empty($video['modestbranding']) ? 'modestbranding=1' : 'modestbranding=0',
      'html5' => 'html5=1',
      'rel' => !empty($video['related']) ? 'rel=1' : 'rel=0',
      'autoplay' => !empty($video['autoplay']) ? 'autoplay=1' : 'autoplay=0',
      'wmode' => 'wmode=opaque',
      'loop' => !empty($video['loop']) ? 'loop=1' : 'loop=0',
      'controls' => !empty($video['controls']) ? 'controls=' . (int) $video['controls'] : (!isset($video['controls']) ? 'controls=1' : 'controls=0'),
      'autohide' => !empty($video['autohide']) ? 'autohide=1' : 'autohide=0',
      'showinfo' => !empty($video['showinfo']) ? 'showinfo=1' : 'showinfo=0',
      'theme' => !empty($video['theme']) ? 'theme=' . $video['theme'] : 'theme=dark',
      'color' => !empty($video['color']) ? 'color=' . $video['color'] : 'color=red',
    ];
    // YouTube Playlist.
    // Example URL: https://www.youtube.com/watch?v=YQHsXMglC9A&list=PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI
    if (preg_match_all('/youtube\.com\/watch\?v=(.*)list=([a-z0-9\-_]+)/i', $video['source'], $matches)) {
      if (!empty($matches[2][0])) {
        $attributes['list'] = 'list=' . $matches[2][0];
      }
    }

    // Look for a start time attribute.
    $start = $this->convertStartTime($video);
    if (!empty($start)) {
      $attributes['start'] = $start;
    }

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function iframe($video) {
    $attributes = $this->attributes($video);
    return [
      'src' => '//www.youtube.com/embed/' . $video['codec']['matches'][1] . '?' . implode('&', $attributes),
      'properties' => [
        'allowfullscreen' => 'true',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function flash($video) {
    $attributes = $this->attributes($video);
    return [
      'src' => '//www.youtube.com/v/' . $video['codec']['matches'][1] . '?' . implode('&', $attributes),
      'params' => [
        'wmode' => 'opaque',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function options() {
    $form = parent::options();
    $form['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
    ];
    $form['related'] = [
      '#title' => $this->t('Show related videos'),
      '#type' => 'checkbox',
    ];
    $form['loop'] = [
      '#title' => $this->t('Loop'),
      '#type' => 'checkbox',
    ];
    $form['autohide'] = [
      '#title' => $this->t('Autohide'),
      '#type' => 'checkbox',
    ];
    $form['showinfo'] = [
      '#title' => $this->t('Show info'),
      '#type' => 'checkbox',
    ];
    $form['modestbranding'] = [
      '#title' => $this->t('Modest branding'),
      '#type' => 'checkbox',
    ];
    $form['enablejsapi'] = [
      '#title' => $this->t('Enable control via IFrame or JavaScript Player API calls.'),
      '#type' => 'checkbox',
    ];
    $form['start'] = [
      '#title' => $this->t('Start time (optional)'),
      '#type' => 'textfield',
      '#description' => $this->t('Example: <strong>2m3s</strong> or <strong>15s</strong>.'),
      '#attributes' => [
        'placeholder' => '2m3s',
      ],
    ];
    $form['theme'] = [
      '#title' => $this->t('Theme'),
      '#type' => 'select',
      '#options' => [
        'dark' => $this->t('Dark'),
        'light' => $this->t('Light'),
      ],
      '#description' => $this->t('Setting the color parameter to white will disable the modestbranding option.'),
    ];
    $form['color'] = [
      '#title' => $this->t('Color'),
      '#type' => 'select',
      '#options' => [
        'red' => $this->t('Red'),
        'white' => $this->t('White'),
      ],
      '#description' => $this->t('Setting the color parameter to white will disable the modestbranding option.'),
    ];
    return $form;
  }

  /**
   * Extract a start time from a YouTube video URL.
   *
   * @param array $video
   *  The video array.
   *
   * @return string|null
   *   The start time ready to add as an attribute.
   */
  function convertStartTime(array $video) {
    $start = NULL;

    $fields_to_search = [
      'source',
      'start',
    ];

    foreach ($fields_to_search as $field) {
      if (!empty($video[$field]) && preg_match('/[\?|&]t=((?<minutes>\d+m)?(?<seconds>\d+s?)?)/', $video[$field], $matches)) {
        $minutes = intval($matches['minutes']);
        $seconds = intval($matches['seconds']);

        // Total up the number of minutes and seconds.
        $start = 'start=' . (preg_replace("/[^0-9]/", "", $minutes) * 60 + (preg_replace("/[^0-9]/", "", $seconds)));
      }
    }

    return $start;
  }

}
