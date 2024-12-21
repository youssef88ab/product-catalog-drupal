<?php

namespace Drupal\social_media_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SocialMediaConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_media_config.settings'];
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
    $config = $this->config('social_media_config.settings');

    $form['facebook_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Facebook URL'),
      '#default_value' => $config->get('facebook_url'),
    ];

    $form['facebook_icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Icon Class'),
      '#default_value' => $config->get('facebook_icon'),
      '#description' => $this->t('Enter the icon class, e.g., fab fa-facebook-f'),
    ];

    $form['twitter_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Twitter URL'),
      '#default_value' => $config->get('twitter_url'),
    ];

    $form['twitter_icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter Icon Class'),
      '#default_value' => $config->get('twitter_icon'),
      '#description' => $this->t('Enter the icon class, e.g., fab fa-twitter'),
    ];

    // Add more social media platforms as needed.

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('social_media_config.settings')
      ->set('facebook_url', $form_state->getValue('facebook_url'))
      ->set('facebook_icon', $form_state->getValue('facebook_icon'))
      ->set('twitter_url', $form_state->getValue('twitter_url'))
      ->set('twitter_icon', $form_state->getValue('twitter_icon'))
      // Save more social media platforms as needed.
      ->save();

    parent::submitForm($form, $form_state);
  }
}
