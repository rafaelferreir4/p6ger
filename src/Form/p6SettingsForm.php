<?php

/**
 * @file
 * Contains \Drupal\example\Form\exampleSettingsForm
 */
namespace Drupal\p6\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class p6SettingsForm extends ConfigFormBase {
  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'p6_config_form';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'p6.settings',
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('p6.settings');
    $form = parent::buildForm($form, $form_state);

    $form['p6_next_prev'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('thing'),
      '#default_value' => $config->get('things'),
    );  

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('p6.settings');
    $config->set('things', $form_state->getValue('p6_next_prev'))->save();

    parent::submitForm($form, $form_state);
  }
}
