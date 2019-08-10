<?php

namespace Drupal\multistep_form_api;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a class which simplifies building a multi step forms.
 */
abstract class FormMultistepBase extends FormBase implements FormMultistepBaseInterface {

  /**
   * Storage of the multistep form.
   *
   * @var array
   */
  protected $storage;

  /**
   * All steps of the multistep form.
   *
   * @var array
   */
  protected $steps;

  /**
   * Indicates if this form will use an ajax.
   *
   * @var bool
   */
  protected $use_ajax;

  /**
   * Current step.
   *
   * @var integer
   */
  protected $current_step;

  /**
   * Returns wrapper for the form.
   */
  public function getFormWrapper() {
    $form_id = $this->getFormId();
    if ($this->use_ajax) {
      $form_id = 'ajax_' . $form_id;
    }
    return str_replace('_', '-', $form_id);
  }

  /**
   * Steps for the multistep form.
   *
   * The class FormMultistepBase provide two steps,
   * considering that the multi step form must consists minimum
   * from two steps. In class the callbacks of the steps are an
   * abstract methods, which means required to implemantation in child class.
   * If you need more then two steps inherit and implement this method
   * in your child class like it done in example module and implement
   * the callbacks defined in this method.
   *
   * @return array
   *   An array of elements (steps), where key of element is
   *   a numeric representation of the step and value is a callback
   *   which will be called to return a $form by the numeric represantation.
   */
  public function getSteps() {
    $steps = ['1' => 'firstStepForm', '2' => 'secondStepForm'];
    return $steps;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (is_null($this->current_step)) {
      // Initialize multistep form.
      $this->initMultistepForm($form, $form_state);
    }

    // Build form for the spesific step.
    $form = $this->stepForm($form, $form_state);

    $form['#prefix'] = '<div id=' . $this->getFormWrapper() . '>';
    $form['#suffix'] = '</div>';

    // Retrieve and add the form actions array.
    $actions = $this->actionsElement($form, $form_state);
    if (!empty($actions)) {
      $form['actions'] = $actions;
    }

    return $form;
  }

  /**
   * Initialize multistep form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function initMultistepForm(array $form, FormStateInterface $form_state) {
    $this->current_step = 1;
    $this->steps = $this->getSteps();
    $this->storage = array();
  }

  /**
   * The form for the specific step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function stepForm(array &$form, FormStateInterface $form_state) {
    $form = call_user_func_array([$this, $this->steps[$this->current_step]], [&$form, $form_state]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function previousStepSubmit(array &$form, FormStateInterface $form_state) {
    $this->copyFormValuesToStorage($form, $form_state);
    $this->current_step -= 1;
    $form_state
      ->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function nextStepSubmit(array &$form, FormStateInterface $form_state) {
    $this->copyFormValuesToStorage($form, $form_state);
    $this->current_step += 1;
    $form_state
      ->setRebuild(TRUE);
  }

  /**
   * Switches to the specific step.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param numeric $step
   *   The numeric representation of the step.
   */
  public function switchStep(FormStateInterface $form_state, $step) {
    $this->current_step = $step;
    $form_state
      ->setRebuild(TRUE);
  }

  /**
   * Returns the actions form element for the specific step.
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = $this->stepActions($form, $form_state);

    if (isset($element['submit'])) {
      // Give the primary submit button a #button_type of primary.
      $element['submit']['#button_type'] = 'primary';
    }

    $count = 0;
    foreach (Element::children($element) as $action) {
      $element[$action] += [
        '#weight' => ++$count * 5,
      ];

      if ($this->use_ajax && $action != 'submit') {
        $element[$action] += [
          '#ajax' => [
            'wrapper' => $this->getFormWrapper(),
          ],
        ];
      }
    }

    if (!empty($element)) {
      $element['#type'] = 'actions';
    }

    return $element;
  }

  /**
   * Returns an array of supported actions for the specific step form.
   */
  protected function stepActions(array $form, FormStateInterface $form_state) {
    // Do not show 'back' button on the first step.
    if (!$this->isCurrentStepFirst()) {
      $actions['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#submit' => ['::previousStepSubmit'],
      ];
    }

    // Do not show 'next' button on the last step.
    if (!$this->isCurrentStepLast()) {
      $actions['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#submit' => ['::nextStepSubmit'],
      ];
    }

    // Show submit button on the last step.
    if ($this->isCurrentStepLast()) {
      $actions['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t("Submit information"),
      ];
    }

    return $actions;
  }

  /**
   * Checks if the current step is the first step.
   */
  protected function isCurrentStepFirst() {
    return $this->current_step == 1 ? TRUE : FALSE;
  }

  /**
   * Checks if the current step is the last step.
   */
  protected function isCurrentStepLast() {
    return $this->current_step == $this->amountSteps() ? TRUE : FALSE;
  }

  /**
   * Returns an amount of the all steps.
   */
  protected function amountSteps() {
    return count($this->steps);
  }

  /**
   * Returns current step.
   */
  protected function getCurrentStep() {
    return $this->current_step;
  }

  /**
   * Copies field values to storage of the class.
   *
   * @param array $form
   *   A nested array of form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function copyFormValuesToStorage(array $form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = $form_state->getValues();

    foreach ($values as $field_name => $value) {
      // If field is already stored in storage
      // check if it was changed, if so rewrite value.
      if ((isset($this->storage[$field_name]) && $this->storage[$field_name] != $value) || !isset($this->storage[$field_name])) {
        $this->storage[$field_name] = $value;
      }
    }
  }

  /**
   * Gets the value of the specific field from storage of the class.
   *
   * @param string $field_name
   *   A name of the field.
   * @param $empty_value
   *   The value which will be returned if $field_name is not stored in storage.
   *
   * @return
   *   A field value.
   */
  protected function getFieldValueFromStorage($field_name, $empty_value = NULL) {
    if (isset($this->storage[$field_name])) {
      return $this->storage[$field_name];
    }
    else {
      return $empty_value;
    }
  }

  /**
   * First step form builder.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  abstract protected function firstStepForm(array &$form, FormStateInterface $form_state);

  /**
   * Second step form builder.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  abstract protected function secondStepForm(array &$form, FormStateInterface $form_state);

}
