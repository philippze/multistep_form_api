<?php

namespace Drupal\multistep_form_api;

use Drupal\Core\Form\FormBase;

/**
 * Provides a class which simplifies building a multi step forms.
 */
abstract class FormMultistepBase extends FormBase implements FormMultistepBaseInterface {
  use FormMultistepTrait;
}
