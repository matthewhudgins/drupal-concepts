<?php
namespace Drupal\matts_module\Plugins\Node;
use Drupal\node\Entity\Node as BaseNode;

class MattsNode extends BaseNode {
  public function getSimilar(){
    // Returns all nids of nodes with the same name regardless of type
    $query = \Drupal::entityQuery('node')
    ->condition('title', $this->title->value)
    ->condition('nid',$this->id(), '<>');
     $resultingNids = $query->execute();
    if($resultingNids){
      //Take the nids and load the nodes that they are related to and store them in an array to be returned
      $nodes = BaseNode::loadMultiple($resultingNids);
      return $nodes;
    } else {
      return false;
    }
  }
}
