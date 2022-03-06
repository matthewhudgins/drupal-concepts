<?php

namespace Drupal\user_greeting\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'User Greeting' Block.
 *
 * @Block(
 *   id = "greeting_block",
 *   admin_label = @Translation("Greeting block"),
 *   category = @Translation("Matt's Blocks"),
 * )
 */

 class Greeting extends BlockBase {
   /**
    *  {@inherit doc}
    */
    public function build() {

      //First check if the user is logged in
      if(\Drupal::currentUser()->isAuthenticated()){
        //Load the user entity using the currently active user's id
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

        //Get username and user id
        $name = $user->getDisplayName();
        $id = $user->id();

        //Get the timestamp of the last login and convert it to a readable date and time
        $lastLoginTimestamp = $user->getLastLoginTime();
        $loginDate = date('F jS, Y g:ia', $lastLoginTimestamp);

        //Grab the settings from the config form for the custom greeting
        $config = \Drupal::config('user_greeting.settings');
        $greeting = $config->get('greeting');

        //Return a render array with markup using the information above
        return [
          '#markup' => $this->t("<p>Hello <b>${name}</b>!</p><p>Your last log in was ${loginDate}</p><p><a href='/user/${id}'>Visit your profile</a></p><p>${greeting}</p>")
        ];
      }
    }

    // We need to turn off the caching of this block plugin since the greeting can be changed in the config
    public function getCacheMaxAge() {
      return 0;
  }
 }
