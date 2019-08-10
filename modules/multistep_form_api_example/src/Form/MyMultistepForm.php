<?php

namespace Drupal\multistep_form_api_example\Form;

use Drupal\multistep_form_api\FormMultistepBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An ajax multi step form builded by the FormMultistepBase class.
 */
class MyMultistepForm extends FormMultistepBase {

  /**
   * {@inheritdoc}
   *
   * If you do not need an ajax multistep form
   * then do not implement this property.
   */
  protected $use_ajax = TRUE;

  /**
   * {@inheritdoc}
   *
   * The class FormMultistepBase extends \Drupal\Core\Form\FormBase class
   * which implements FormInterface interface,
   * so you need implement method getFormId().
   */
  public function getFormId() {
    return 'my_multistep_form';
  }

  /**
   * {inheritdoc}
   */
  public function getSteps() {
    // Gets the parent two steps (['1' => 'firstStepForm', '2' => 'secondStepForm']).
    // Do not forget implement these callbacks.
    $steps = parent::getSteps();
    // Add third step.
    // You may add as many steps as you need.
    $steps += ['3' => 'thirdStepForm'];
    return $steps;
  }

  /**
   * {inheritdoc}
   */
  protected function firstStepForm(array &$form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Step 1: Personal details'),
    ];
    $form['name']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#default_value' => $this->getFieldValueFromStorage('name'),
    ];

    return $form;
  }

  /**
   * {inheritdoc}
   */
  protected function secondStepForm(array &$form, FormStateInterface $form_state) {
    $form['adress'] = [
      '#type' => 'fieldset',
      '#title' => t('Step 2: Street address info'),
    ];
    $form['adress']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your street address'),
      '#default_value' => $this->getFieldValueFromStorage('address'),
    ];

    return $form;
  }

  /**
   * Third step form builder.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  protected function thirdStepForm(array &$form, FormStateInterface $form_state) {
    $form['city'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Step 3: City info'),
    ];
    $form['city']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your city'),
      '#default_value' => $this->getFieldValueFromStorage('city'),
    ];

    return $form;
  }

  /**
   * Submiting information of the multistep form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The values of fields stored in $this->storage array and $form_state (last step).
  }

}
