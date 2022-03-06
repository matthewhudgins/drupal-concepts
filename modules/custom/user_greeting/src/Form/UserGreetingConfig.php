<?php

namespace Drupal\user_greeting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Config form for the User Greeting Block Plugin
*/
class UserGreetingConfig extends ConfigFormBase {
  /**
   * @return string
  */
  public function getFormId(){
    return 'user_greeting_config';
  }

  /**
   * @return array
  */
  protected function getEditableConfigNames() {
    return ['user_greeting.settings'];
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $config = $this->config('user_greeting.settings');

    $form['greeting'] = [
      '#type' => 'textarea',
      '#title' => $this->t('User Greeting'),
      '#description' => $this->t('This message will be displayed for logged in users underneath the View Profile link'),
      '#default_value' => $config->get('greeting'),
    ];
    return parent::buildForm($form, $form_state);
  }
  /**
   * @param array $form
   * @param FormStateInterface $form_state
  */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $config = $this->configFactory->getEditable('user_greeting.settings');
    $config->set('greeting', $form_state->getValue('greeting'))->save();
  }
}
