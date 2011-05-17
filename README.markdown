# Formbuilder 2.0

This 2.0 version is a from-scratch rewrite of original @dperrymorrow's Formbuilder.
Its goal is to create a full-fledged form-factory able to define, render and validate a form, providing at the very same time a fluent interface so that form creation is easy to write and read.
Full doc will be available soon, in the meantime here is a simple example.

Create a controller named at your wish, and add the following code to it:

    $this->load->spark('formbuilder/2.0');

    $form = $this->formbuilder->new_form();

    $form->set_action('page/home')
    ->add_file_field('image','Upload image')
    ->add_group('account', 'Account')
      ->add_text_field('username', 'Username', '', 'class="username red" id="username"', 'trim|required|minlength[5]|xss_clean')
      ->add_password_field('password', 'Password', '', 'id="password"', 'trim|minlength[5]|xss_clean')
      ->add_email_field('email', 'E-Mail', 'example@example.com', array(), TRUE)
    ->add_group('profile', 'Profile')
      ->add_text_field('name', 'Name')
      ->add_checkbox('happy', 'I am happy', TRUE, array('class'=>array('check', 'happy'), 'id'=>'mycheck'))
    ->close_group()
    ->add_checkboxes('hero', 'My favorite comic hero:', FB_R_CRB_LABEL_BEFORE, array(
      array (
        'label' => 'Superman',
        'value' => 'superman',
        'checked' => FALSE,
      ),
      array (
        'label' => 'Goofy',
        'value' => 'goofy',
        'checked' => FALSE,
      ),
      array (
        'label' => 'Conan the Barbarian',
        'value' => 'conan',
        'checked' => FALSE,
      )))
    ->add_submit('submit', 'Save')
    ->add_reset('reset', 'Cancel');

    if (!$form->validate())
    {
      echo $form; // magic method to print the form out straight away, or
      //echo $myform->render_form();
    }
    else
    {
      echo "Nice form!";
    }

Some nice feature that will soon be documented in further details:

- Validator extends standard CI one so that you could put your custom validation callbacks in the model. Just add `"callback_myfunction_model[mymodel]"` into validation rules to invoke `$this->mymodel->myfunction()` as a validation method.
- Renderer and validator could be rewritten if necessary: I plan to provide an interface to inherit from withh the stable release. The goal is to allow anybody to implement other renderers (for integration with template engines and such) and maybe more advanced validators. Even if decoupling is not perfect yet, a couple of public functions are already available to inject your custom objects:

    $form = $this->formbuilder->new_form();

    $form->set_validator(new Custom_validator($form));
    $form_>set_renderer(new Custom_renderer($form));

- You could print out the whole form just echoing the object or treating it as a string; still you could access following methods:

    echo $form->render_form(); // echoes the form, equals echo $form;
    echo $form->render_group('profile'); // echoes specified fieldset;
    echo $form->render_field('name'); // echoes specified field, wrapped by chosen wrapper tags (specified in config file)
    echo $form->render_raw_field('name'); //echoes specified field, without wrapper but full with label and error

- In config file you could chose label positions for checkboxed and radiobuttons (before/after/none), validation error method (general/field-by-field) and error messages positions (before/after form open; before/after form close OR before field label/between label and field/after field). You could also configure error wrapper tags and classes.

More to come in a short while.

# Contacts

- [Log Issues or Suggestions](https://github.com/stickgrinder/formbuilder/issues)
- [Follow me on Twitter](http://twitter.com/stickgrinder)

Many thanks to @dperrymorrow for his first version of Formbuilder! :) Kudos!
