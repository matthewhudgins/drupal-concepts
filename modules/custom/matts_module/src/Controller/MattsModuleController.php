<?php
namespace Drupal\matts_module\Controller;
use Drupal\Core\Controller\ControllerBase;

class MattsModuleController extends ControllerBase{
  public function getVip($code){
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
  public function importPage(){
    $form = \Drupal::formBuilder()->getForm('Drupal\ke_vip\Form\VipImport');
    return $form;
}

//This function takes the code used in the route, loads the node that has a field equal to that and displays further information onto the page using a twig template
  public function ticket($code){
    $vip = $this->getVip($code);
    if(!$vip){
      return [
        '#markup' => '<h1>VIP Does not exist</h1>'
      ];
    }

    //Build the render array
    $build['page'] = [
      '#theme' => 'ticket',
      '#title' => 'Ticket',
      '#first_name' => $vip->field_first_name->value,
      '#last_name' => $vip->field_last_name->value,
      '#full_name' => $vip->field_name_on_card->value,
      '#show_apple_wallet' => !$vip->field_is_not_generated->value,
      '#code' => $vip->field_code->value,
    ];

    $build['page']['#attached']['library'][] = 'matts_module/ticket';
    return $build;
  }
}
