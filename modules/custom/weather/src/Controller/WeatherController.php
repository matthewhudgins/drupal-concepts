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

    //Get API Key for Weather
    $config = \Drupal::config('weather.settings');
    $openweather_api_key = $config->get('openweather_api_key');

    $weatherLocations = [];
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
      //Add the weather data to an array that will be used by the twig template
      array_push($weatherLocations,['name' => $location->title->value, 'temp' => $location->field_temperature->value, 'lat' => $location->field_latitude->value, 'lng' => $location->field_longitude->value]);
    }


    $build['page'] = [
      '#theme' => 'map',
      '#title' => 'Weather Map',
      '#locations' => $weatherLocations
    ];
    $build['#cache'] = ['max-age' => 0];
    $build['page']['#attached']['library'][] = 'weather/map';
    return $build;
  }
}
