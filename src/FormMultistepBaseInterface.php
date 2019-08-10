<?php

namespace Drupal\multistep_form_api;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an interface for multistep form class.
 */
interface FormMultistepBaseInterface {

  /**
   * Submission handler for the next step action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function nextStepSubmit(array &$form, FormStateInterface $form_state);

  /**
   * Submission handler for the previous step action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function previousStepSubmit(array &$form, FormStateInterface $form_state);

}
