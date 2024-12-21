<?php

namespace Drupal\socializer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\socializer\Networks;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Socializer block.
 *
 * @Block(
 *   id = "socializer_block",
 *   admin_label = @Translation("Socializer"),
 *   category = @Translation("Socializer")
 * )
 */
class SocializerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a SocializerBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $networks = new Networks();
    $networks = $networks->networksList();
    $config = $this->configFactory->get('socializer.settings');
    $links = $config->get('links');

    if ($links) {
      foreach ($links as $link) {
        if ($link['value']) {
          $build[] = [
            '#type' => 'link',
            '#title' => Markup::create('<i class="socializer__icon socializer__icon--' . $link['network'] . '">' . $networks[$link['network']] . '</i>'),
            '#attributes' => [
              'title' => $networks[$link['network']],
              'class' => [
                'socializer__link',
                'socializer__link--' . $link['network'],
              ],
            ],
            '#url' => Url::fromUri($link['value'], [
              'attributes' => [
                'target' => $link['attributes']['target'] ?: NULL,
                'rel' => $link['attributes']['rel'] ?: NULL,
              ],
            ]),
          ];
        }
      }
    }

    return [
      '#theme' => 'socializer',
      '#links' => $build,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->configFactory->get('socializer.settings')->getCacheTags()
    );
  }

}
