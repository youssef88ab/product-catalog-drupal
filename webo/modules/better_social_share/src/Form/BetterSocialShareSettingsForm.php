<?php

namespace Drupal\better_social_share\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure BetterSocialShare settings for this site.
 */
class BetterSocialShareSettingsForm extends ConfigFormBase {
  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module extension list service.
   *
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a BetterSocialShareSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Extension\ExtensionList $module_extension_list
   *   The module extension list service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ExtensionList $module_extension_list, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->moduleExtensionList = $module_extension_list;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('config.factory'),
    $container->get('module_handler'),
    $container->get('extension.list.module'),
    $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bss_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'better_social_share.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    global $base_path;

    $bss_settings = $this->config('better_social_share.settings');

    $button_img = '<img src="' . $base_path . $this->moduleExtensionList->getPath('better_social_share') . '/images/%s" width="%d" height="%d"%s />';

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

    $max_weight = 10;
    $social_platforms = better_social_share_platforms();

    $social_share_platforms = $bss_settings->get('social_share_platforms');
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

    $form['config']['button_size']['bss_buttons_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Icon size'),
      '#field_suffix' => ' ' . $this->t('pixels'),
      '#default_value' => $bss_settings->get('buttons_size'),
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

    $form['config']['more_button']['better_social_share_more_button'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button'),
      '#default_value' => $bss_settings->get('more_button') ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $button_options,
    ];
    $form['config']['more_button']['better_social_share_custom_more_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom button URL'),
      '#default_value' => $bss_settings->get('custom_more_button'),
      '#description' => $this->t('URL of the button image. Example: http://example.com/share.png'),
      '#states' => [
        'visible' => [
          ':input[name="better_social_share_more_button"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['config']['more_button']['better_social_share_more_button_placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Button placement'),
      '#default_value' => $bss_settings->get('more_button_placement') ?? 'before',
      '#options' => [
        'after' => $this->t('After the service buttons'),
        'before' => $this->t('Before the service buttons'),
      ],
      '#states' => [
        'invisible' => [
          ':input[name="better_social_share_more_button"]' => ['value' => 'none'],
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
      '#default_value' => $bss_settings->get('btn_type') ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $btn_types,
    ];

    $form['config']['background']['btn_bg_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Custom backgraound'),
      '#default_value' => ($bss_settings->get('btn_bg_color')) ?? '#ffffff',
      '#states' => [
        'visible' => [
          ':input[name="btn_type"]' => ['value' => 'custom'],
        ],
      ],
    ];

    $form['config']['background']['btn_border_round'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Rounded border'),
      '#default_value' => $bss_settings->get('btn_border_round'),
    ];

    $form['config']['background']['btn_show_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show social media label'),
      '#default_value' => $bss_settings->get('btn_show_label'),
    ];

    $form['config']['background']['enable_button_spacing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable button spacing'),
      '#default_value' => $bss_settings->get('enable_button_spacing'),
    ];

    $form['config']['background']['buttons_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Buttons label'),
      '#default_value' => $bss_settings->get('buttons_label'),
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
      '#default_value' => $bss_settings->get('icon_color_type') ?? 'default',
      '#attributes' => ['class' => ['better_social_share-more-button-option']],
      '#options' => $button_icon_color,
    ];
    $form['config']['color']['icon_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Icon color'),
      '#default_value' => ($bss_settings->get('icon_color')) ?? '#ffffff',
      '#states' => [
        'visible' => [
          ':input[name="icon_color_type"]' => ['value' => 'custom'],
        ],
      ],
    ];

    $form['better_social_share_entity_settings'] = [
      '#type'         => 'details',
      '#title'        => $this->t('Entities'),
    ];

    $form['better_social_share_entity_settings']['better_social_share_entity_tip_1'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this
        ->t('BetterSocialShare is available on the &quot;Manage display&quot; pages of enabled entities, e.g. Structure &gt; Content types &gt; Article &gt; Manage display.'),
    ];

    $entities = self::getContentEntities();

    // Allow modules to alter the entity types.
    $this->moduleHandler->alter('better_social_share_entity_types', $entities);

    // Whitelist the entity IDs that let us link to each bundle's Manage
    // Display page.
    $linkableEntities = [
      'block_content', 'comment', 'commerce_product', 'commerce_store',
      'contact_message', 'media', 'node', 'paragraph',
    ];

    foreach ($entities as $entity) {
      $entityId = $entity->id();
      $entityType = $entity->getBundleEntityType();
      // Get all available bundles for the current entity.
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entityId);
      $links = [];

      foreach ($bundles as $machine_name => $bundle) {
        $label = $bundle['label'];

        // Some labels are TranslatableMarkup objects (such as the File entity).
        if ($label instanceof TranslatableMarkup) {
          $label = $label->render();
        }

        // Check if Field UI module enabled.
        if ($this->moduleHandler->moduleExists('field_ui')) {
          // Link to the bundle's Manage Display page if the entity ID supports
          // the route pattern.
          if (in_array($entityId, $linkableEntities) && $entityType) {
            $links[] = Link::createFromRoute($this->t('@label', ['@label' => $label]), "entity.entity_view_display.{$entityId}.default", [
              $entityType => $machine_name,
            ])->toString();
          }
        }
      }

      $description = empty($links) ? '' : '( ' . implode(' | ', $links) . ' )';

      $form['better_social_share_entity_settings'][$entityId] = [
        '#type' => 'checkbox',
        '#title' => $this->t('@entity', ['@entity' => $entity->getLabel()]),
        '#default_value' => $bss_settings->get("entities.{$entityId}"),
        '#description' => $description,
        '#attributes' => ['class' => ['better_social_share-entity-checkbox']],
      ];
    }

    $form['better_social_share_entity_settings']['better_social_share_entity_tip_2'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('A cache rebuild may be required before changes take effect.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('better_social_share.settings')
      ->set('social_share_platforms', $values['social_share_platforms'])
      ->set('buttons_size', $values['bss_buttons_size'])
      ->set('custom_more_button', $values['better_social_share_custom_more_button'])
      ->set('more_button', $values['better_social_share_more_button'])
      ->set('btn_border_round', $values['btn_border_round'])
      ->set('btn_show_label', $values['btn_show_label'])
      ->set('enable_button_spacing', $values['enable_button_spacing'])
      ->set('buttons_label', $values['buttons_label'])
      ->set('btn_type', $values['btn_type'])
      ->set('btn_bg_color', $values['btn_bg_color'])
      ->set('icon_color_type', $values['icon_color_type'])
      ->set('icon_color', $values['icon_color'])
      ->set('btn_bg_color', $values['btn_bg_color'])
      ->set('more_button_placement', $values['better_social_share_more_button_placement']);

    foreach (self::getContentEntities() as $entity) {
      $entityId = $entity->id();
      $this->config('better_social_share.settings')
        ->set("entities.{$entityId}", $values[$entityId]);
    }

    $this->config('better_social_share.settings')->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get all available content entities in the environment.
   *
   * @return array
   *   Get array of entity types
   */
  public static function getContentEntities() {
    $content_entity_types = [];
    $entity_type_definitions = \Drupal::entityTypeManager()->getDefinitions();
    /** @var EntityTypeInterface $definition */
    foreach ($entity_type_definitions as $definition) {
      if ($definition instanceof ContentEntityType) {
        $content_entity_types[] = $definition;
      }
    }

    return $content_entity_types;
  }

}
