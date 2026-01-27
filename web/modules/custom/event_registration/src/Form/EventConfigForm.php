<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Event Configuration Form.
 */
class EventConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
    ];

    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of Event'),
      '#options' => [
        '' => $this->t('- Select -'),
        'online_workshop' => $this->t('Online Workshop'),
        'hackathon' => $this->t('Hackathon'),
        'conference' => $this->t('Conference'),
        'oneday_workshop' => $this->t('One-day Workshop'),
      ],
      '#required' => TRUE,
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
    ];

    $form['registration_start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration Start Date'),
      '#required' => TRUE,
    ];

    $form['registration_end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Registration End Date'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $event_date = $form_state->getValue('event_date');
    $start_date = $form_state->getValue('registration_start_date');
    $end_date = $form_state->getValue('registration_end_date');

    // Registration end date must be after start date.
    if ($start_date > $end_date) {
      $form_state->setErrorByName(
        'registration_end_date',
        $this->t('Registration end date must be after start date.')
      );
    }

    // Event date must be after registration end date.
    if ($event_date < $end_date) {
      $form_state->setErrorByName(
        'event_date',
        $this->t('Event date must be after registration end date.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) 
  {
     // Insert data into database.
     \Drupal::database()->insert('event_config')
    ->fields([
      'event_name' => $form_state->getValue('event_name'),
      'event_category' => $form_state->getValue('event_category'),
      'event_date' => $form_state->getValue('event_date'),
      'registration_start_date' => $form_state->getValue('registration_start_date'),
      'registration_end_date' => $form_state->getValue('registration_end_date'),
      'created' => time(),
    ])
    ->execute();
    $this->messenger()->addMessage($this->t('Event saved successfully to database!'));  
  }

}