<?php
namespace Drupal\matts_module\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\webformSubmissionInterface;
use Drupal\node\Entity\Node;
/**
 * Form submission handler.
 *
 * @WebformHandler(
 *   id = "matts_form_handler",
 *   label = @Translation("Matt's form handler"),
 *   category = @Translation("Form Handler"),
 *   description = @Translation("Turn submissions into nodes"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */

 class MattsSignupHandler extends WebformHandlerBase {
       /**
       * {@inheritdoc}
       */
        public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
            // In this hypothetical, this handler is applied to forms that are expecting a custom composite multifield with "owners" as the machine id
            // This handler the values put into the 'owners' multifield and turns them into actual nodes
            // Each individual owner added to the form has a name and email but all owners entered share the same agency name.

            //Get values from the submission
            $values = $webform_submission->getData();

            //If owners were entered
            if($values['owners']){
                foreach($values['owners'] as $owner){
                    //First check if an owner already exists with these values
                    $query = \Drupal::entityQuery('node');
                    $query->condition('type', 'owner');
                    $query->condition('title', $owner['owner_name']);
                    $query->condition('field_email', $owner['owner_email']);
                    $query->condition('field_agency', $values['agency_name']);
                    $resultingNids = $query->execute();

                    //If the query returned no existing nodes then go ahead and create the owner nodes
                    if(empty($resultingNids)){
                        $node =Node::create(['type' => 'owner']);
                        $node->title = $owner['owner_name'];
                        $node->field_email = $owner['owner_email'];
                        $node->field_agency = $values['agency_name'];
                        $node->save();
                    }
                }
            }

            return true;
        }

 }
