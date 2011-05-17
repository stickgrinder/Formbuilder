<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FB_Renderer
{

  private $_form_structure;
  private $_classes_on_wrapper;
  private $_wrapper_open;
  private $_wrapper_close;
  private $_holder;

  private $_config; // configuration options (from config file, "renderer" key)


  public function __construct( $config = array(), FB_Creator $holder = NULL )
  {
    // load form helper so that they're available to perform
    // form rendering
    $CI =& get_instance();
    $CI->load->helper('form');

    $this->_holder = $holder;

    // set configuration
    $this->_config = $config;

    $this->_wrapper_open = '<'.$this->_config['field_wrapper_tag'].' class="'.$this->_config['field_wrapper_classes'].'">';
    $this->_wrapper_close = '</'.$this->_config['field_wrapper_tag'].'>';
  }

  private function _stringify_attributes(array $attributes = array())
  {
    // build attributes string
    $attributes_string = '';
    foreach ($attributes as $name => $value)
    {
      $attributes_string .= $name.'="'.$value.'" ';
    }

    return $attributes_string;
  }

  // error related helpers are useless since they rely on CI_form_validation
  // following functions are rewritten versions for these ones.
  public function form_error($field = '', $prefix = '', $suffix = '')
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      return '';
    }

    return $OBJ->error($field, $prefix, $suffix);
  }

  public function validation_errors($prefix = '', $suffix = '')
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      return '';
    }

    return $OBJ->error_string($prefix, $suffix);
  }

  public function set_value($field = '', $default = '')
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      if ( ! isset($_POST[$field]))
      {
        return $default;
      }

      return form_prep($_POST[$field], $field);
    }

    return form_prep($OBJ->set_value($field, $default), $field);
  }

  public function set_select($field = '', $value = '', $default = FALSE)
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      if ( ! isset($_POST[$field]))
      {
        if (count($_POST) === 0 AND $default == TRUE)
        {
          return ' selected="selected"';
        }
        return '';
      }

      $field = $_POST[$field];

      if (is_array($field))
      {
        if ( ! in_array($value, $field))
        {
          return '';
        }
      }
      else
      {
        if (($field == '' OR $value == '') OR ($field != $value))
        {
          return '';
        }
      }

      return ' selected="selected"';
    }

    return $OBJ->set_select($field, $value, $default);
  }

  public function set_checkbox($field = '', $value = '', $default = FALSE)
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      if ( ! isset($_POST[$field]))
      {
        if (count($_POST) === 0 AND $default == TRUE)
        {
          return ' checked="checked"';
        }
        return '';
      }

      $field = $_POST[$field];

      if (is_array($field))
      {
        if ( ! in_array($value, $field))
        {
          return '';
        }
      }
      else
      {
        if (($field == '' OR $value == '') OR ($field != $value))
        {
          return '';
        }
      }

      return ' checked="checked"';
    }

    return $OBJ->set_checkbox($field, $value, $default);
  }

  public function set_radio($field = '', $value = '', $default = FALSE)
  {
    if (FALSE === ($OBJ =& $this->_holder->get_validator()))
    {
      if ( ! isset($_POST[$field]))
      {
        if (count($_POST) === 0 AND $default == TRUE)
        {
          return ' checked="checked"';
        }
        return '';
      }

      $field = $_POST[$field];

      if (is_array($field))
      {
        if ( ! in_array($value, $field))
        {
          return '';
        }
      }
      else
      {
        if (($field == '' OR $value == '') OR ($field != $value))
        {
          return '';
        }
      }

      return ' checked="checked"';
    }

    return $OBJ->set_radio($field, $value, $default);
   }

  public function set_wrappers( $wrapper_open = '<div>', $wrapper_close='</div>')
  {
    $this->_wrapper_open  = $wrapper_open;
    $this->_wrapper_close = $wrapper_close;
  }

  public function set_form_structure(array $form_structure)
  {
    if (is_array($form_structure))
      $this->_form_structure = $form_structure;
  }

  public function render_form()
  {
    if (!isset($this->_form_structure) || !is_array($this->_form_structure)) return FALSE;

    //return '<pre>'.print_r($this->_form_structure, TRUE);

    $output[10] =
      $this->_form_structure['properties']['is_multipart']
      ?
      form_open_multipart($this->_form_structure['properties']['action'])
      :
      form_open($this->_form_structure['properties']['action']);

    $output[20] = '';

    foreach ($this->_form_structure['items'] as $name => $item)
    {
      // is this item a group?
      if (substr($name, -6, 6) === '_group')
        $output[20] .= $this->render_group(substr($name, 0, -6));
      else
        $output[20] .= $this->render_field($name);
    }

    $output[30] = form_close();

    // check for errors
    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FORM )
    {
      $output[$this->_config['error_position']] = $this->validation_errors();
    }

    ksort($output);
    return implode('', $output);
  }


  public function render_group($group_name = FALSE, $wrapped = TRUE)
  {

    if (! $group_name || ! isset($this->_form_structure['items'][$group_name.'_group'])) return FALSE;

    $group = $this->_form_structure['items'][$group_name.'_group'];

    $output = form_fieldset($group['legend'], $group['attributes']);

    foreach($this->_form_structure['items'][$group_name.'_group']['items'] as $field_name => $field)
    {
      $output .= $this->render_field($group_name.'/'.$field_name, $wrapped);
    }

    $output .= form_fieldset_close();

    return $output;
  }

  public function render_field($field_name, $wrapped = TRUE)
  {

    // find field definition and check if renderer is able to render
    // the field

    // first of all, is the field inside any group?
    if (strpos($field_name, '/'))
    {
      $steps = explode('/', $field_name);
      $group_name = $steps[0];
      $field_name = $steps[1];
    }

    if (! empty($group_name) )
      $field = $this->_form_structure['items'][$group_name.'_group']['items'][$field_name];

    else
      $field = $this->_form_structure['items'][$field_name];

    // ok, now let's see if we could render this stuff!
    $rendering_method = '_render_'.$field['type'].'_field';
    if (method_exists($this, $rendering_method))
    {

      $output = $this->$rendering_method($field);

      // last but not least, wrap output with
      if ($wrapped) $output = $this->_wrapper_open . $output . $this->_wrapper_close;

      return $output."\n";
    }
    else
    {

      log_message('error', 'Formbuilder: No rendering method found for field of type '.$field['type']);

      return FALSE;
    }

  }

  private function _render_button_field($field)
  {
    extract($field);
    // ouput a simple button
    return form_button($name, $value, $this->_stringify_attributes($attributes));
  }

  private function _render_reset_field($field)
  {
    extract($field);
    // ouput a simple button
    return form_reset($name, $value, $this->_stringify_attributes($attributes));
  }

  private function _render_submit_field($field)
  {
    extract($field);
    // ouput a simple button
    return form_submit($name, $value, $this->_stringify_attributes($attributes));
  }

  private function _render_checkbox_field($field)
  {
    extract($field);

    if ($label_position !== FB_R_CRB_LABEL_NONE)
      $output[$label_position] = form_label($label, $attributes['id']);


    $output[20] = form_checkbox(
      $name,
      $value,
      $this->set_checkbox($name, $checked),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);

  }

  private function _render_checkboxes_field($field)
  {
    extract($field);

    $output = form_fieldset($label, array('id' => $attributes['id'], 'class' => 'checkboxes fieldset'));

    $i = 0;
    foreach ($options as $option)
    {
      $subfield = array(
        'name' => $name,
        'label' => $option['label'],
        'label_position' => $label_position,
        'value' => $option['value'],
        'checked' => $option['checked'],
        'attributes' => $attributes,
      );
      $subfield['attributes']['id'] .= '_'.$i++;

      $output .= $this->_render_checkbox_field($subfield);
    }

    $output .= form_fieldset_close();

    return $output;

  }

  private function _render_radiobutton_field($field)
  {
    extract($field);

    if ($label_position !== FB_R_CRB_LABEL_NONE)
      $output[$label_position] = form_label($label, $attributes['id']);


    $output[20] = form_radio(
      $name,
      $value,
      $this->set_radio($name, $checked),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);

  }

  private function _render_radiobuttons_field($field)
  {
    extract($field);

    $output = form_fieldset($label, array('id' => $attributes['id'], 'class' => 'radiobuttons fieldset'));

    $i = 0;
    foreach ($options as $option)
    {
      $subfield = array(
        'name' => $name,
        'label' => $option['label'],
        'label_position' => $label_position,
        'value' => $option['value'],
        'checked' => $option['checked'],
        'attributes' => $attributes,
      );
      $subfield['attributes']['id'] .= '_'.$i++;

      $output .= $this->_render_radiobutton_field($subfield);
    }

    $output .= form_fieldset_close();

    return $output;
  }

  private function _render_dropdown_field($field)
  {
    // ouput a simple text field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_dropdown(
      $name,
      $options,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);

  }

  private function _render_multiselect_field($field)
  {
    // ouput a simple text field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_multiselect(
      $name,
      $options,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);
  }

  private function _render_text_field($field)
  {
    // ouput a simple text field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_input(
      $name,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);
  }

  private function _render_password_field($field)
  {
    // ouput a simple password field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_password(
      $name,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);
  }

  private function _render_file_field($field)
  {
    // ouput a simple file upload field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_upload(
      $name,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);
  }

  private function _render_textarea_field($field)
  {
    // ouput a simple textarea field
    extract($field);
    $output[10] = form_label($label, $attributes['id']);

    $output[20] = form_textarea(
      $name,
      $this->set_value($name, $value),
      $this->_stringify_attributes($attributes)
    );

    if ( $this->_config['error_show'] === FB_R_ERROR_SHOW_FIELD )
    {
      $output[$this->_config['error_position']] = $this->form_error($name);
    }

    ksort($output);
    return implode('', $output);

  }



}
// --------------------------------------------------------------------
/**
 * End of FB_renderer
 */