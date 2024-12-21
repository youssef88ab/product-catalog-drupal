<?php

namespace Drupal\better_social_share\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides an 'BetterSocialShare share' block.
 *
 * @Block(
 *   id = "better_social_share_block",
 *   admin_label = @Translation("Better Social Share Buttons"),
 * )
 */
class BetterSocialShareBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The title resolver service.
   *
   * @var \Drupal\Core\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Constructs a new BetterSocialShareBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    RouteMatchInterface $route_match,
    ModuleHandlerInterface $module_handler,
    RequestStack $request_stack,
    TitleResolverInterface $title_resolver
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->titleResolver = $title_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('module_handler'),
      $container->get('request_stack'),
      $container->get('title_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'float' => 'left',
      'social_share_platforms' => [
        'facebook' => [
          'enabled' => 1,
          'key' => 'facebook',
        ],
        'twitter' => [
          'enabled' => 1,
          'key' => 'twitter',
        ],
        'linkedin' => [
          'enabled' => 1,
          'key' => 'linkedin',
        ],
        'pinterest' => [
          'enabled' => 1,
          'key' => 'pinterest',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $data = $this->configuration;

    // Get button type.
    $btn_type = $data['btn_type'] ?? '';
    $btn_bg_color = in_array($btn_type, ['custom', 'transparent']) ? $data['btn_bg_color'] : '';

    $url_options = [
      'absolute' => TRUE,
      'language' => $this->languageManager->getCurrentLanguage(),
    ];
    $base_url = Url::fromRoute('better_social_share.ajax', [], $url_options)->toString();

    // Get the current URL.
    $current_url = Url::fromRoute('<current>', [], $url_options)->toString();
    
    // Access the current request object.
    $current_request = $this->requestStack->getCurrentRequest();

    // Get the current page title.
    $current_title = $this->titleResolver->getTitle($current_request, $this->routeMatch->getRouteObject());

    $build = [
      '#entity_url' => $data['entity_url'] ?? $current_url,
      '#entity_title' => $data['entity_title'] ?? $current_title,
      '#more_button_type' => $data['more_button_type'] ?? '',
      '#button_image' => $data['button_image'] ?? '',
      '#btn_bg_color' => $btn_bg_color,
      '#btn_type' => $btn_type,
      '#icon_color_type' => $data['icon_color_type'] ?? '',
      '#icon_color' => $data['icon_color'] ?? '',
      '#btn_border_round' => $data['btn_border_round'] ?? '',
      '#btn_show_label' => $data['btn_show_label'] ?? '',
      '#enable_button_spacing' => $data['enable_button_spacing'] ?? '',
      '#more_button_placement' => $data['more_button_placement'] ?? '',
      '#social_share_platforms' => $data['social_share_platforms'] ?? '',
      '#buttons_size' => $data['buttons_size'] ?? '',
      '#float' => $data['float'] ?? '',
      '#top' => $data['top'] ?? '',
      '#theme' => 'better_social_share_standard',
      '#cache' => [
        'contexts' => ['url'],
      ],
    ];

    $build['#attached']['drupalSettings']['base_url'] = $base_url;
    $build['#attached']['drupalSettings']['current_url'] = $current_url;
    $build['#attached']['drupalSettings']['current_title'] = $current_title;
    $build['#attached']['library'][] = 'better_social_share/better_social_share.front';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    global $base_path;

    $form = parent::buildConfigurationForm($form, $form_state);
    $config = $this->getConfiguration();
    $module_path = $this->moduleHandler->getModule('better_social_share')->getPath();

    $button_img = '<img src="' . $base_path . $module_path . '/images/%s" width="%d" height="%d"%s />';
    $max_weight = 10;
    $social_platforms = better_social_share_platforms();

    $button_options = [
      'default' => sprintf($button_img, 'more.svg', 32, 32, ' class="better_social_share-round-icon"'),
      'custom' => $this->t('Custom button'),
      'none' => $this->t('None'),
    ];

    $attributes_for_code = [
      'autocapitalize' => ['off'],
      'autocomplete' => ['off'],
      'autocorrect' => ['off'],
      'spellcheck' => ['false'],
    ];

    $form['config'] = [
      '#type'         => 'details',
      '#title'        => $this->t('Share Buttons Configuration'),
      '#open'         => TRUE,
    ];

    $form['config']['button_config'] = [
      '#type'  => 'details',
      '#title' => $this->t('Button order'),
      '#open'  => FALSE,
      '#description'  => $this->t('Select the checkbox corresponding to the social media share buttons you want and either drag or configure the weight for their order.'),
    ];

    $form['config']['button_config']['social_share_platforms'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Social media'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'platform-order-weight',
      ],
      ],
    ];

    $social_share_platforms = $config['social_share_platforms'] ?? [];
    $default_weight = 0;

    $platforms = [];
    foreach ($social_platforms as $platform_id => $platform) {
      $platforms[$platform_id]['weight'] = (isset($social_share_platforms[$platform_id]['weight']) && $social_share_platforms[$platform_id]['weight'] != '') ? $social_share_platforms[$platform_id]['weight'] : $default_weight++;

      $platforms[$platform_id]['enabled'] = $social_share_platforms[$platform_id]['enabled'] ?? 0;
      $platforms[$platform_id]['name'] = $platform;
    }

    uasort($platforms, function ($a, $b) {
      // First, sort by "enabled" in descending order.
      $enabledComparison = $b['enabled'] - $a['enabled'];

      // If "enabled" values are equal then sort by "weight" in ascending order.
      if ($enabledComparison === 0) {
        return $a['weight'] - $b['weight'];
      }

      return $enabledComparison;
    });

    $inc = 0;
    foreach ($platforms as $platform_id => $platform) {
      $form['config']['button_config']['social_share_platforms'][$platform_id]['#attributes']['class'][] = 'draggable';
      $form['config']['button_config']['social_share_platforms'][$platform_id]['#weight'] = $platform['weight'];
      if ($platform['weight'] > $max_weight) {
        $max_weight = $platform['weight'];
      }

      $form['config']['button_config']['social_share_platforms'][$platform_id]['enabled'] = [
        '#type' => 'checkbox',
        '#default_value' => $social_share_platforms[$platform_id]['enabled'] ?? FALSE,
        '#title' => $platform['name'],
        '#title_display' => 'after',
        '#default_value' => $social_share_platforms[$platform_id]['enabled'] ?? '',
      ];

      $form['config']['button_config']['social_share_platforms'][$platform_id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for type @type', ['@type' => $platform_id]),
        '#title_display' => 'invisible',
        '#default_value' => $inc,
        '#attributes' => [
          'class' => [
            'platform-order-weight',
            'platform-order-weight-' . $platform_id,
          ],
        ],
        '#delta' => $max_weight + count($platforms),
      ];
      $inc++;
    }

    $form['config']['button_size'] = [
      '#type'  => 'details',
      '#title' => $this->t('Button size'),
      '#open'  => FALSE,
      '#description'  => $this->t('Configure the size of social media share buttons.'),
    ];

    $form['config']['button_size']['buttons_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Icon size'),
      '#field_suffix' => ' ' . $this->t('pixels'),
      '#default_value' => $config['buttons_size'] ?? '32',
      '#size' => 10,
      '#maxlength' => 3,
      '#min' => 8,
      '#max' => 999,
      '#required' => TRUE,
    ];

    $form['config']['more_button'] = [
      '#type' => 'details',
      '#title' => $this->t('"More" Button configuration'),
      '#open' => FALSE,
      '#description' => $this->t('Social Media Share "More" Button Configuration'),
    ];

    $form['config']['more_button']['more_button'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button'),
      '#default_value' => $config['more_button'] ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $button_options,
    ];
    $form['config']['more_button']['custom_more_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom button URL'),
      '#default_value' => $config['custom_more_button'] ?? '',
      '#description' => $this->t('URL of the button image. Example: http://example.com/share.png'),
      '#states' => [
        'visible' => [
          ':input[name="settings[config][more_button][more_button]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['config']['more_button']['more_button_placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button placement'),
      '#default_value' => $config['more_button_placement'] ?? 'before',
      '#options' => [
        'after' => $this->t('After the service buttons'),
        'before' => $this->t('Before the service buttons'),
      ],
      '#states' => [
        'invisible' => [
          ':input[name="settings[config][more_button][more_button]"]' => ['value' => 'none'],
        ],
      ],
    ];

    $form['config']['background'] = [
      '#type' => 'details',
      '#title' => $this->t('Button Style'),
      '#open' => FALSE,
    ];

    $btn_types = [
      'default' => $this->t('Default background'),
      'transparent' => $this->t('Transparent background'),
      'custom' => $this->t('Custom'),
    ];

    $form['config']['background']['btn_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button styles'),
      '#default_value' => $config['btn_type'] ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $btn_types,
    ];

    $form['config']['background']['btn_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Custom backgraound'),
      '#default_value' => ($config['btn_bg_color']) ?? '#ffffff',
      '#states' => [
        'visible' => [
          ':input[name="settings[config][background][btn_type]"]' => ['value' => 'custom'],
        ],
      ],
    ];

    $form['config']['background']['btn_border_round'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Rounded border'),
      '#default_value' => $config['btn_border_round'] ?? '',
    ];

    $form['config']['background']['btn_show_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show social media label'),
      '#default_value' => $config['btn_show_label'] ?? TRUE,
    ];

    $form['config']['background']['enable_button_spacing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable button spacing'),
      '#default_value' => $config['enable_button_spacing'] ?? '',
    ];

    $form['config']['background']['buttons_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Buttons label'),
      '#default_value' => $config['buttons_label'] ?? '',
    ];

    $button_icon_color = [
      'default' => $this->t('Default background'),
      'custom' => $this->t('Custom'),
    ];

    $form['config']['color'] = [
      '#type' => 'details',
      '#title' => $this->t('Button icon color'),
      '#open' => FALSE,
    ];

    $form['config']['color']['icon_color_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button icon color'),
      '#default_value' => $config['icon_color_type'] ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $button_icon_color,
    ];
    $form['config']['color']['icon_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Icon color'),
      '#default_value' => ($config['icon_color']) ?? '#ffffff',
      '#states' => [
        'visible' => [
          ':input[name="settings[config][color][icon_color_type]"]' => ['value' => 'custom'],
        ],
      ],
    ];

    $form['config']['float_position'] = [
      '#type' => 'details',
      '#title' => $this->t('Button Position'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['config']['float_position']['float'] = [
      '#type' => 'select',
      '#title' => $this->t('Float position'),
      '#options' => [
        'none' => $this->t('None'),
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $config['float'] ?? '',
    ];
    $form['config']['float_position']['top'] = [
      '#type' => 'select',
      '#title' => $this->t('Position top'),
      '#options' => [
        'top-25' => $this->t('Top 25%'),
        'top-50' => $this->t('Top 50%'),
        'top-75' => $this->t('Top 75%'),
      ],
      '#default_value' => $config['top'] ?? 'top-50',
      '#states' => [
        'invisible' => [
          ':input[name="settings[config][float_position][float]"]' => ['value' => 'none'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

    $config = $form_state->getValue('config');
    $float_position = $config['float_position'];
    $button_config = $config['button_config'];
    $button_size = $config['button_size'];
    $more_button = $config['more_button'];
    $background = $config['background'];
    $color = $config['color'];

    foreach ($button_config['social_share_platforms'] as $key => $value) {
      if ($value['enabled'] == 0) {
          unset($button_config['social_share_platforms'][$key]);
      } else {
        $button_config['social_share_platforms'][$key]['key'] = $key;
      }
    }

    $this->configuration['float'] = $float_position['float'];
    if ($float_position['float'] != 'none') {
      $this->configuration['top'] = $float_position['top'];
    }
    else {
      $this->configuration['top'] = '';
    }

    $this->configuration['social_share_platforms'] = $button_config['social_share_platforms'];
    $this->configuration['buttons_size'] = $button_size['buttons_size'];
    $this->configuration['more_button'] = $more_button['more_button'];
    $this->configuration['custom_more_button'] = $more_button['custom_more_button'];
    $this->configuration['more_button_placement'] = $more_button['more_button_placement'];
    $this->configuration['btn_type'] = $background['btn_type'];
    $this->configuration['btn_bg_color'] = $background['btn_bg_color'];
    $this->configuration['btn_border_round'] = $background['btn_border_round'];
    $this->configuration['btn_show_label'] = $background['btn_show_label'];
    $this->configuration['enable_button_spacing'] = $background['enable_button_spacing'];
    $this->configuration['buttons_label'] = $background['buttons_label'];
    $this->configuration['icon_color_type'] = $color['icon_color_type'];
    $this->configuration['icon_color'] = $color['icon_color'];

  }

}
