<?php 

namespace Drupal\webo_social\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SocialMediaConfigForm extends ConfigFormBase {

public function getFormId() {
    return 'social_media_config_form';
}

protected function getEditableConfigNames() {
    return ['webo_social.settings'];
}

public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('webo_social.settings');

    $form['facebook'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Facebook URL'),
        '#default_value' => $config->get('facebook'),
    ];

    $form['twitter'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Twitter URL'),
        '#default_value' => $config->get('twitter'),
    ];

    $form['linkedin'] = [
        '#type' => 'textfield',
        '#title' => $this->t('LinkedIn URL'),
        '#default_value' => $config->get('linkedin'),
    ];

    $form['instagram'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Instagram URL'),
        '#default_value' => $config->get('instagram'),
    ];

    return parent::buildForm($form, $form_state);
}

public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('webo_social.settings')
        ->set('facebook', $form_state->getValue('facebook'))
        ->set('twitter', $form_state->getValue('twitter'))
        ->set('linkedin', $form_state->getValue('linkedin'))
        ->set('instagram', $form_state->getValue('instagram'))
        ->save();

    parent::submitForm($form, $form_state);
}
}