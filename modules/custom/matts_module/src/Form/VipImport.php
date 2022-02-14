<?php

namespace Drupal\matts_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class VipImport extends FormBase{
  // This form is used to allow admins to import CSVs to convert to VIP nodes

    public function getFormId(){
        return 'vipimportform';
    }
    public function buildForm(array $form, FormStateInterface $form_state){
        $form['description'] = array(
            '#markup' => '<h3>Import csv file of vips.</h3>'
        );
        $form['import_csv'] = array(
            '#type' => 'managed_file',
            '#title' => 'Upload file here',
            '#upload_location' => 'public://importvip',
            "#upload_validators"  => array("file_validate_extensions" => array("csv")),
            '#default_value' => '',
            '#states' => array(
                'visible' => array(
                  ':input[name="File_type"]' => array('value' => 'Upload Your File'),
                ),
            )
        );
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Upload CSV',
            '#button_type' => 'primary',
        );
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state){
        // This is the order the columns are excepted to ge in.
        $csvIndices = [
          'prefix' => 0,
          'fname' => 1,
          'mname' => 2,
          'lname' => 3,
          'suffix' => 4,
          'fullname' => 5,
          'name_on_card' => 6,
          'association' => 7,
          'email' => 8,
          'code' => 9
        ];
        $csv_file = $form_state->getValue('import_csv');

        // Load the csv file and save it
        $file = File::load($csv_file[0]);
        $file->setPermanent();
        $file->save();
        $file = fopen($file->getFileUri(), "r");

        $i = 0;
        // We are going to import these in batches of 20
        $batchSize = 20;
        $currentBatch = array();
        while(!feof($file)){
            if($i <= $batchSize){
              $row = fgetcsv($file);
              if(empty($row[$csvIndices['name_on_card']])){
                \Drupal::logger('ke_vip')->warning("no name on card ".implode(",", $row));
                  $i++;
                  continue;
              } else {

                // Formulate an array with the contents of a row of the provided csv to turn into a node.
                  $vip = array(
                    'title' =>$row[$csvIndices['fullname']],
                    'field_prefix' => $row[$csvIndices['prefix']],
                    'field_first_name' => $row[$csvIndices['fname']],
                    'field_last_name' => $row[$csvIndices['lname']],
                    'field_email' => $row[$csvIndices['email']],
                    'field_name_on_card' => $row[$csvIndices['name_on_card']],
                    'field_code' => $row[$csvIndices['code']],
                );
                $currentBatch[] = $vip;
                $i++;
              }
            } else {
                // If we're finished creating the batch array call the addImportContentItem to process it
                $operations[] = ['\Drupal\matts_module\addImportContent::addImportContentItem',[$currentBatch]];
                $i = 0;
                $currentBatch = array();
            }

        }
        $batch = array(
            'title' => 'Importing Data...',
            'operations' => $operations,
            'init_message' => 'Import is starting',
            'finished' => '\Drupal\matts_module\addImportContent::addImportContentItemCallback'
        );
        batch_set($batch);
    }
    public function csvtoarray($filename='',$delimiter=','){
        // Takes the CSV and returns the data as an array
        if(!file_exists($filename) || !is_readable($filename)) return FALSE;
        $header = NULL;
        $data = array();
        if(($handle = fopen($filename, 'r')) !== FALSE){
            while(($row = fgetcsv($handle,1000, $delimiter)) !== FALSE){
                if(!$header){
                    $header = $row;
                } else {
                    $data[] = array_combine($header,$row);
                }
                fclose($handle);
            }

            return $data;
        }

    }
}
