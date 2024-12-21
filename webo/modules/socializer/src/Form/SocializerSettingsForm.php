<?php

namespace Drupal\socializer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\socializer\Networks;

/**
 * Configure Socializer settings for this site.
 */
class SocializerSettingsForm extends ConfigFormBase {

  const SETTINGS = 'socializer.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'socializer_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $count = $form_state->get('networks_count');
    $removed_networks = $form_state->get('removed_networks');
    $networks = new Networks();
    $networks = $networks->networksList();
    $form['networks']['links']['#tree'] = TRUE;

    if (is_null($count)) {
      $count = $config->get('links') ? count($config->get('links')) : 1;
      $form_state->set('networks_count', $count);
    }

    if (is_null($removed_networks)) {
      $form_state->set('removed_networks', []);
      $removed_networks = $form_state->get('removed_networks');
    }

    $form['networks'] = [
      '#type' => 'container',
      '#prefix' => '<div id="socializer-networks">',
      '#suffix' => '</div>',
    ];

    $form['networks']['links'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Network'),
        $this->t('Link'),
        $this->t('Link Attributes'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];

    for ($i = 0; $i < $count; $i++) {

      if (in_array($i, $removed_networks)) {
        continue;
      }

      $form['networks']['links'][$i]['#attributes']['class'][] = 'draggable';
      $form['networks']['links'][$i]['#weight'] = $config->get('links') ? $config->get('links')[$i]['weight'] : 0;

      $form['networks']['links'][$i]['network'] = [
        '#type' => 'select',
        '#options' => $networks,
        '#default_value' => $config->get('links') ? $config->get('links')[$i]['network'] : NULL,
        '#sort_options' => TRUE,
      ];

      $form['networks']['links'][$i]['value'] = [
        '#type' => 'url',
        '#title' => $config->get('links') ? $networks[$config->get('links')[$i]['network']] : '',
        '#title_display' => 'invisible',
        '#default_value' => $config->get('links') ? $config->get('links')[$i]['value'] : NULL,
      ];

      $form['networks']['links'][$i]['attributes'] = [
        '#type' => 'details',
        '#title' => $config->get('links') ? $networks[$config->get('links')[$i]['network']] . ' ' . $this->t('Link Attributes') : $this->t('Link Attributes'),
      ];

      $form['networks']['links'][$i]['attributes']['rel'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Rel'),
        '#default_value' => $config->get('links') ? $config->get('links')[$i]['attributes']['rel'] : NULL,
      ];

      $form['networks']['links'][$i]['attributes']['target'] = [
        '#type' => 'select',
        '#title' => $this->t('Target'),
        '#options' => [
          '' => $this->t('- None -'),
          '_self' => $this->t('Same window (_self)'),
          '_blank' => $this->t('New window (_blank)'),
        ],
        '#default_value' => $config->get('links') ? $config->get('links')[$i]['attributes']['target'] : NULL,
      ];

      $form['networks']['links'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', [
          '@title' => $config->get('links') ? $networks[$config->get('links')[$i]['network']] : '',
        ]),
        '#title_display' => 'invisible',
        '#default_value' => $config->get('links') ? $config->get('links')[$i]['weight'] : 0,
        '#attributes' => [
          'class' => ['table-sort-weight'],
        ],
      ];

      $form['networks']['links'][$i]['actions'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => $i,
        '#submit' => ['::removeNetwork'],
        '#ajax' => [
          'callback' => '::addMoreCallback',
          'wrapper' => 'socializer-networks',
        ],
      ];
    }

    $form['networks']['actions'] = [
      '#type' => 'actions',
    ];

    $form['networks']['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add new network'),
      '#submit' => ['::addNetwork'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'socializer-networks',
      ],
    ];

    $form['networks']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $values = $form_state->cleanValues()->getValues();
    $values['links'] = is_array($values['links']) ? array_values($values['links']) : [];
    $config->setData($values);
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Ajax Callback for Add new network button.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['networks'];
  }

  /**
   * Callback for Add new network button.
   */
  public function addNetwork(array &$form, FormStateInterface $form_state) {
    $networks_count = $form_state->get('networks_count');
    $add_network = $networks_count + 1;
    $form_state->set('networks_count', $add_network);
    $form_state->setRebuild();
  }

  /**
   * Callback for Remove network button.
   */
  public function removeNetwork(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $network_to_remove = $trigger['#name'];

    unset($form['networks']['links'][$network_to_remove]);

    $networks_container = $form_state->getValue('links');
    unset($networks_container[$network_to_remove]);
    $form_state->setValue('links', $networks_container);

    $removed_networks = $form_state->get('removed_networks');
    $removed_networks[] = $network_to_remove;

    $form_state->set('removed_networks', $removed_networks);
    $form_state->setRebuild();
  }

}
