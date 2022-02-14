<?php
namespace Drupal\matts_module;

class addImportContent{
    public static function addImportContentItem($vips, &$context){
      // This function takes the array of vip fields and values, converts them to nodes and adds message for the batch process.
        $context['sandbox']['current_item'] = $vips;
        $message = "Creating ".json_encode($vips);

        $addContentObj = new addImportContent();
        foreach($vips as $vip){
            $addContentObj->createVip($vip);
            $context['results'][] = $vip;

        }
        $context['message'] = $message;
    }
    function addImportContentItemCallback($success,$results,$operations){
        // If the batch was sucessful display a message that tells how many items have been processed
        if($success){
            $message = \Drupal::translation()->formatPlural(
                count($results),
                'One item processed.', '@count items processed.'
            );
        }
        else {
            $message = t('Finished with an error.');
        }
        drupal_set_message($message);
    }
    public function createVip($vip){
      //Function that takes an array of fields and values and creates nodes based on them.
      if(empty($vip['field_email'])){
        \Drupal::logger('matts_module')->warning("No email for vip: ".$vip['title']);
        return;
      }
        $query = \Drupal::entityQuery('node')
        ->condition('title', $vip['title'])
        ->condition('field_email',$vip['field_email'])
        ->condition('type','vip')
        ->execute();
        //If there's already vip with this title skip it
        if(count($query) > 0){
        \Drupal::logger('matts_module')->warning("Already vip with email: ".$vip['title'].':'.$vip['field_email']);
          return;
        }
        $vip['field_code'] = empty($vip['field_code']) ? generate_string("23456789abcdefghijkmnpqrstuvwxyz") : $vip['field_code'];
        $node = \Drupal\node\Entity\Node::create([
            'type' => 'vip',
            'title'=> $vip['title'],
            'field_first_name' => $vip['field_first_name'],
            'field_last_name' => $vip['field_last_name'],
            'field_prefix' => $vip['field_prefix'],
            'field_email' => $vip['field_email'],
            'field_name_on_card' => $vip['field_name_on_card'],
            'field_code' => $vip['field_code']
        ]);
        $node->save();
    }
}
