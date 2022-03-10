<?php

namespace Drupal\weather\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

class LocationEntry extends FormBase
{
  //This function generates a list of locations as form fields
  public function generateLocationList(array &$form)
  {
    //We only want the form to appear to users
    if (\Drupal::currentUser()->isAuthenticated()) {
      $uid = \Drupal::currentUser()->id();
      //Find all the locations that are attributed to this user
      $nids = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'location')
        ->condition('field_user', $uid)
        ->execute();
      if ($nids) {
        $locationNodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
      }
      $i = 0;
      //Make the form fields based on the nodes gathered and attach the ajax callbacks
      foreach ($locationNodes as $location) {
        $form['locations_fieldset'][$i]['location'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Location'),
          '#prefix' => "<div class='location-fieldset-wrapper' id='location-fieldset-wrapper-{$i}'>",
          '#suffix' => '</div>'
        ];
        $form['locations_fieldset'][$i]['location']['nickname'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Nickname'),
          '#nid' => $location->id(),
          '#attributes' => [
            'class' => ['nickname'],
          ],
          '#nid' => $location->id(),
          '#field' => 'title',
          '#default_value' => $location->title->value,
          '#index' => $i,
          '#ajax' => [
            'callback' => '::updateLocation',
            'event' => 'change',
          ],
        ];
        $form['locations_fieldset'][$i]['location']['zip'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Zip Code'),
          '#nid' => $location->id(),
          '#index' => $i,
          '#field' => 'field_zip_code',
          '#attributes' => [
            'class' => ['zip'],
          ],
          '#ajax' => [
            'callback' => '::updateLocation',
            'event' => 'change',
            'wrapper' => "location-fieldset-wrapper-{$i}",
          ],
          '#default_value' => $location->field_zip_code->value
        ];
        $form['locations_fieldset'][$i]['location']['lat'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Latitude'),
          '#nid' => $location->id(),
          '#index' => $i,

          '#field' => 'field_latitude',
          '#attributes' => [
            'class' => ['lat'],
          ],
          '#ajax' => [
            'callback' => '::updateLocation',
            'event' => 'change',
          ],
          '#default_value' => $location->field_latitude->value
        ];
        $form['locations_fieldset'][$i]['location']['lng'] = [
          '#type' => 'textfield',
          '#attributes' => [
            'class' => ['lng'],
          ],
          '#index' => $i,
          '#nid' => $location->id(),
          '#field' => 'field_longitude',
          '#ajax' => [
            'callback' => '::updateLocation',
            'event' => 'change',
          ],
          '#title' => $this->t('Longitude'),
          '#default_value' => $location->field_longitude->value
        ];
        $form['locations_fieldset'][$i]['location']['button_remove'] = [
          '#type' => 'submit',
          '#value' => $this->t('Remove this location'),
          '#nid' => $location->id(),
          '#index' => $i,
          '#submit' => array('::removeLocation'),
          '#ajax' => [
            'callback' => '::removeLocationCallback',
            'wrapper' => "locations-fieldset-wrapper",
          ]
        ];
        $i++;
      }
    }
  }
  //This is called when updates to the locations are needed after the user has changed text in the fields
  public function updateLocation(array &$form, FormStateInterface $form_state)
  {
    //This will be necessary to update the specific node
    $nid = $form_state->getTriggeringElement()['#nid'];
    //Here we load the node
    $location = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    //This is the value of the field that was just edited
    $value = $form_state->getTriggeringElement()['#value'];
    //This is the name of the field just edited
    $field = $form_state->getTriggeringElement()['#field'];
    //This is the index pertaining to where it is in the render array
    //Useful for making sure we only rerender what's necessary
    $i = $form_state->getTriggeringElement()['#index'];
    if ($field == 'field_zip_code') {
      //If the zip code changed make a request to grab the lat and long from it
      //Get API Key for Google Geocoding
      $config = \Drupal::config('weather.settings');
      $google_api_key = $config->get('google_api_key');
      $url     = "https://maps.googleapis.com/maps/api/geocode/json?address={$value}&key={$google_api_key}";
      $resp    = json_decode(file_get_contents($url), true);

      $lat    = $resp['results'][0]['geometry']['location']['lat'] ?? '';
      $lng   = $resp['results'][0]['geometry']['location']['lng'] ?? '';
      if ($lat && $lng) {
        $location->field_latitude->value = $lat;
        $location->field_longitude->value = $lng;
        $form['locations_fieldset'][$i]['location']['lat']['#value'] = $lat;
        $form['locations_fieldset'][$i]['location']['lng']['#value'] = $lng;
      }
    }
    $location->$field->value = $value;
    $location->save();

    $form_state->setRebuild();
    return $form['locations_fieldset'][$i];
  }
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['description'] = array(
      '#markup' => '<div>' . $this->t('Add Locations.') . '</div>',
    );

    $form['#tree'] = TRUE;
    $form['locations_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Locations'),
      '#prefix' => '<div id="locations-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    if (\Drupal::currentUser()->isAuthenticated()) {
      $this->generateLocationList($form);
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['locations_fieldset']['actions']['add_location'] = [
      '#type' => 'submit',
      '#name' => 'add-location',
      '#value' => $this->t('Add Location'),
      '#submit' => array('::addOne'),
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'locations-fieldset-wrapper',
      ],
    ];

    $form['#attached']['library'][] = 'weather/main';
    $form_state->setCached(FALSE);

    return $form;
  }

  public function getFormId()
  {
    return 'location_entry';
  }

  public function addOne(array &$form, FormStateInterface $form_state)
  {
    //When the user clicks the add button let's actually generate a node right then and there
    //and assign it to this user.
    $node = \Drupal\node\Entity\Node::create([
      'type' => 'location',
      'title' => 'Loading Location Data Via IP',
      'uid' => \Drupal::currentUser()->id(),
      'field_user' => \Drupal::currentUser()->id(),
      'status' => 1,
    ]);
    $node->save();
    //Let's regenerate that list now that we have a new node
    $this->generateLocationList($form);


    $form_state->setRebuild();
    return $form['locations_fieldset'];
  }

  public function addmoreCallback(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRebuild();
    return $form['locations_fieldset'];
  }
  public function removeLocation(array &$form, FormStateInterface $form_state)
  {
    // Grab the nid from the triggering button and delete the node with that id
    $nidToDelete = $form_state->getTriggeringElement()['#nid'];
    $locationToDelete = \Drupal::entityTypeManager()->getStorage('node')->load($nidToDelete);
    $locationToDelete->delete();
    $form_state->setRebuild();
  }
  public function removeLocationCallback(array &$form, FormStateInterface $form_state)
  {
    return $form['locations_fieldset'];
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    //This never gets run because this form was built with live editing in mind
    $output = t(
      'These are the locations @locations'
    );
  }
}
