<?php

namespace Drupal\weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Config form for the User Greeting Block Plugin
*/
class WeatherConfig extends ConfigFormBase {
  /**
   * @return string
  */
  public function getFormId(){
    return 'weather_config';
  }

  /**
   * @return array
  */
  protected function getEditableConfigNames() {
    return ['weather.settings'];
  }

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    $config = $this->config('weather.settings');

    $form['google_api_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Google GeoCoding API Key'),
      '#description' => $this->t('Provide the API key to be used with geocoding'),
      '#default_value' => $config->get('google_api_key'),
    ];
    $form['openweather_api_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('openweathermap API Key'),
      '#description' => $this->t('Provide the API key to be used to gather weather data'),
      '#default_value' => $config->get('openweather_api_key'),
    ];
    return parent::buildForm($form, $form_state);
  }
  /**
   * @param array $form
   * @param FormStateInterface $form_state
  */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $config = $this->configFactory->getEditable('weather.settings');
    $config->set('google_api_key', $form_state->getValue('google_api_key'))->save();
    $config->set('openweather_api_key', $form_state->getValue('openweather_api_key'))->save();
  }
}
