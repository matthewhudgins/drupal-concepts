<?php

namespace Drupal\weather\Controller;

use Drupal\Core\Controller\ControllerBase;

class WeatherController extends ControllerBase
{
  public function viewAndUpdateAllLocations()
  {
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'location')
      ->execute();
    if ($nids) {
      $locationNodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
    }
    $weatherMarkup = '<h2>All Weather Locations</h2>';

    //Get API Key for Weather
    $config = \Drupal::config('weather.settings');
    $openweather_api_key = $config->get('openweather_api_key');

    // Loop through every location and update their weather data
    foreach ($locationNodes as $location) {
      $lat = $location->field_latitude->value;
      $lng = $location->field_longitude->value;
      //If a longitude and lat are present use the openweathermap api to grab the temp and store it in the node.
      if (!empty($lat) && !empty($lng)) {
        $url     = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lng}&appid={$openweather_api_key}&units=imperial";
        $resp    = json_decode(file_get_contents($url), true);
        $temp    = $resp['main']['temp'] ?? '';
        if (!empty($temp)) {
          $location->field_temperature->value = $temp;
          $location->save();
        }
      }
      //Add the weather data to the markup that will displayed on the page.
      $weatherMarkup .= "<div class='location'><p>Location Name: {$location->title->value}<br/>Temperature: {$location->field_temperature->value}</p></div>";
    }
    return ['#markup' => $weatherMarkup, '#cache' => ['max-age' => 0]];
  }
}
