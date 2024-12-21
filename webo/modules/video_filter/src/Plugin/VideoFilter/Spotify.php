<?php

namespace Drupal\video_filter\Plugin\VideoFilter;

use Drupal\video_filter\VideoFilterBase;

/**
 * Provides Spotify codec for Video Filter.
 *
 * @VideoFilter(
 *   id = "spotify",
 *   name = @Translation("Spotify"),
 *   example_url = "https://open.spotify.com/user/spotify/playlist/1yHZ5C3penaxRdWR7LRIOb",
 *   regexp = {
 *     "/open\.spotify\.com\/(\S+)/",
 *   },
 *   ratio = "300/380",
 * )
 */
class Spotify extends VideoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function instruction() {
    return $this->t('Use the "Copy * URL" button to generate the link.');
  }

  /**
   * {@inheritdoc}
   */
  public function iframe($video) {
    $uri = sprintf('spotify:%s', str_replace('/', ':', trim($video['codec']['matches'][1], '/')));
    return [
      'src' => 'https://embed.spotify.com/?uri=' . $uri,
    ];
  }

}
