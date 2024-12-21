<?php

namespace Drupal\social_media\Form;

use Drupal\core\Form\ConfigFormBase;
use Drupal\core\Form\FormStateInterface;

class SocialMediaConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_media.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_media_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_media.settings');

    $form['social_media_links'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Social Media Links'),
    ];

    $social_medias = ['facebook', 'twitter', 'instagram', 'linkedin'];

    foreach ($social_medias as $social_media) {
      $form['social_media_links'][$social_media] = [
        '#type' => 'textfield',
        '#title' => ucfirst($social_media) . ' ' . $this->t('URL'),
        '#default_value' => $config->get($social_media . '_url'),
      ];
      $form['social_media_links'][$social_media . '_icon'] = [
        '#type' => 'textfield',
        '#title' => ucfirst($social_media) . ' ' . $this->t('Icon Class'),
        '#default_value' => $config->get($social_media . '_icon'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('social_media.settings')
      ->set('facebook_url', $form_state->getValue('facebook'))
      ->set('facebook_icon', $form_state->getValue('facebook_icon'))
      ->set('twitter_url', $form_state->getValue('twitter'))
      ->set('twitter_icon', $form_state->getValue('twitter_icon'))
      ->set('instagram_url', $form_state->getValue('instagram'))
      ->set('instagram_icon', $form_state->getValue('instagram_icon'))
      ->set('linkedin_url', $form_state->getValue('linkedin'))
      ->set('linkedin_icon', $form_state->getValue('linkedin_icon'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
