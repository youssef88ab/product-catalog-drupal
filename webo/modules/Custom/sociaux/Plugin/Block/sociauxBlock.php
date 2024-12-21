<?php

namespace Drupal\sociaux\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\sociaux\Networks;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the sociaux block.
 *
 * @Block(
 *   id = "sociaux_block",
 *   admin_label = @Translation("sociaux"),
 *   category = @Translation("sociaux")
 * )
 */
class sociauxBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a sociauxBlock instance.
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
    $config = $this->configFactory->get('sociaux.settings');
    $links = $config->get('links');

    if ($links) {
      foreach ($links as $link) {
        if ($link['value']) {
          $build[] = [
            '#type' => 'link',
            '#title' => Markup::create('<i class="sociaux__icon sociaux__icon--' . $link['network'] . '">' . $networks[$link['network']] . '</i>'),
            '#attributes' => [
              'title' => $networks[$link['network']],
              'class' => [
                'sociaux__link',
                'sociaux__link--' . $link['network'],
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
      '#theme' => 'sociaux',
      '#links' => $build,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->configFactory->get('sociaux.settings')->getCacheTags()
    );
  }

}
