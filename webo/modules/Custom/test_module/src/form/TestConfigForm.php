<?php

namespace Drupal\test_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TestConfigForm extends ConfigFormBase {

protected function getEditableConfigNames() {
    return ['test_module.settings'];
}

  public function getFormId() {
    return 'test_module_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('test_module.settings');

    $form['sample_setting'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sample Setting'),
      '#default_value' => $config->get('sample_setting'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('test_module.settings')
      ->set('sample_setting', $form_state->getValue('sample_setting'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
