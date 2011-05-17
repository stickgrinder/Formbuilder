<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Formbuilder {

  private $_config;

  public function __construct( $config = array() )
  {
    $this->_config = $this->_initialize_config($config);

    // set values if not present

  }

  private function _initialize_config( $config = array() )
  {

    $defaults = array(
      'formbuilder_general' => array(
      ),
      'formbuilder_creator' => array(
      ),
      'formbuilder_validator' => array(
        'error_delimiter_tag' => 'div',
        'error_delimiter_classes' => 'errors',
      ),
      'formbuilder_renderer' => array(
        'field_wrapper_tag' => 'div',
        'field_wrapper_classes' => 'field-wrapper',
        'error_show' => FB_R_ERROR_SHOW_FIELD,
        'error_position' => FB_R_ERRPOS_BEFORECLOSE,
      ),
    );

    if ( ! is_array($config) ) $config = array();

    return $this->_merge_config($defaults, $config);

  }

  private function _merge_config ($defaults, $config)
  {
    foreach($config as $key => $value)
    {
      if(array_key_exists($key, $defaults) && is_array($value))
        $defaults[$key] = $this->_merge_config($defaults[$key], $config[$key]);

      else
        $defaults[$key] = $value;

    }

    return $defaults;

  }

  public function new_form($validator_class = 'FB_Validator', $renderer_class = 'FB_Renderer')
  {

    // these lines is not strictly necessary but it's here just in case
    // you would like to use this stuff outside of a spark (say, as a library)
    $CI =& get_instance();
    $CI->load->library('form_validation');

    require_once("classes/fb_creator.php");
    require_once("classes/".strtolower($validator_class).".php");
    require_once("classes/".strtolower($renderer_class).".php");

    $new_form = new FB_Creator($this->_config['formbuilder_creator']);
    $new_form->set_validator(new $validator_class($this->_config['formbuilder_validator'], $new_form));
    $new_form->set_renderer(new $renderer_class($this->_config['formbuilder_renderer'], $new_form));

    return $new_form;
  }

}





