<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Constants definition
 */
define('FB_R_CRB_LABEL_NONE', FALSE);
define('FB_R_CRB_LABEL_BEFORE', 11);
define('FB_R_CRB_LABEL_AFTER', 21);

define('FB_R_ERROR_HIDE', -1);
define('FB_R_ERROR_SHOW_FORM', 0);
define('FB_R_ERROR_SHOW_FIELD', 1);

define('FB_R_ERRPOS_BEFOREOPEN', 3);
define('FB_R_ERRPOS_AFTEROPEN', 13);
define('FB_R_ERRPOS_BEFORECLOSE', 23);
define('FB_R_ERRPOS_AFTERCLOSE', 33);


$config['formbuilder_validator']['error_delimiter_tag'] = 'p';
$config['formbuilder_validator']['error_delimiter_classes'] = 'errors global message';

$config['formbuilder_renderer']['field_wrapper_tag'] = 'div';
$config['formbuilder_renderer']['field_wrapper_classes'] = 'field-wrapper';
$config['formbuilder_renderer']['error_show'] = FB_R_ERROR_SHOW_FORM;
$config['formbuilder_renderer']['error_position'] = FB_R_ERRPOS_AFTEROPEN;
