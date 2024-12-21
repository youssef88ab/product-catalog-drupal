<?php

declare(strict_types=1);

namespace Drupal\social_media_platforms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Social Media Platforms settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  const SETTINGS = [
    'show_icon' => 'Show icon',
    'show_label' => 'Show label',
    'target_blank' => 'Open link in new tab',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'social_media_platforms_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['social_media_platforms.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config('social_media_platforms.settings');
    $display_options = $config->get('display_options');

    $form['display'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Display options'),
    ];

    foreach (self::SETTINGS as $key => $setting) {
      $form['display'][$key] = [
        '#type' => 'checkbox',
        '#title' => $setting,
        '#default_value' => $display_options[$key],
      ];
    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('URL'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];

    $platforms = $config->get('platforms');

    $weights = array_combine(
      array_keys($platforms),
      array_column($platforms, 'weight')
    );
    asort($weights);

    foreach ($weights as $index => $weight) {
      $platform = $platforms[$index];
      $form['table'][$index]['#attributes']['class'][] = 'draggable';
      $form['table'][$index]['#weight'] = $weight;

      $form['table'][$index]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $platform['label'],
      ];

      $form['table'][$index]['url'] = [
        '#type' => 'url',
        '#default_value' => $platform['url'],
      ];

      $form['table'][$index]['weight'] = [
        '#type' => 'weight',
        '#default_value' => $weight,
        '#attributes' => [
          'class' => [
            'table-sort-weight',
          ],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $config = $this->configFactory()->getEditable('social_media_platforms.settings');

    $display_options = $config->get('display_options');
    foreach (self::SETTINGS as $key => $setting) {
      $display_options[$key] = $form_state->getValue($key) == 1;
    }
    $config->set('display_options', $display_options);

    $platforms = $config->get('platforms');
    $value = $form_state->getValue('table');
    foreach ($value as $key => $platform) {

      if ($platform['url'] == '') {
        $platform['url'] = NULL;
      }

      $platforms[$key]['label'] = $platform['label'];
      $platforms[$key]['url'] = $platform['url'];
      $platforms[$key]['weight'] = intval($platform['weight']);
    }
    $config->set('platforms', $platforms);

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->isValueEmpty('show_label') && $form_state->isValueEmpty('show_icon')) {
      $form_state->setErrorByName('form', $this->t('Show label or show icon must be checked'));
    }
    parent::validateForm($form, $form_state);
  }

}
