<?php

/**
 * @file
 * Contains Drupal/matts_module/form/VipAjax
 */

 namespace Drupal\matts_module\Form;

 use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

// Ajax form to perform quick lookups based on their ticket code
class VipAjax extends FormBase {
  public function getFormId(){
    return 'vip_ajax_form';
  }
  public function buildForm(array $form, FormStateInterface $form_state){
    //This text field performs the checkVipCallback function whenever the field is changed
    $form['code'] = [
      '#type' => 'textfield',
      '#title' => 'Ticket Code',
      '#description' => 'Enter in a ticket code',
      '#ajax' => [
        'callback' => '::checkVipCallback',
        'effect' => 'fade',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL
        ]
      ]
    ];
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state){

  }
  public function getVip($code){
    //Returns the node based on the code given
    $nid = \Drupal::entityQuery('node')
    ->condition('status',1)
    ->condition('type','vip')
    ->condition('field_code',$code)
    ->execute();
    $nid = reset($nid);
    if($nid){
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        return $node;
    } else {
        return false;
    }
}
  public function checkVipCallback(array &$form, FormStateInterface $form_state){
    // Generate an ajax response that takes the code, grabs the node based on that code and outputs some basic information on the page about that node
    $ajax_response  = new AjaxResponse();
    $code = $form_state->getValue('code');
    $text = "Ajax Response: ".$form_state->getValue('code');

    if($code){
        $vip = $this->getVip($code);
        $text = '<div class="info-field"><span class="label">Name:</span>'.$vip->title->value.'</div>';
        $text = '<div class="info-field"><span class="label">Email:</span>'.$vip->field_email->value.'</div>';
    }
    $ajax_response->addCommand(new HtmlCommand('#edit-code--description', $text));
    return $ajax_response;
  }
}
