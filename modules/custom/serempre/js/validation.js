/**
 * @file
 * JavaScript para validar que el campo sea requrerido y solo alfabetico.
 */
(function ($) {
  Drupal.behaviors.myFormValidation = {
    attach:function() {

      $('#serempre-agregar-form').on('form-pre-serialize', function(event, form, options, veto){
        $("#serempre-agregar-form").validate({
          rules: {
            nombre: {
              required: true,
              lettersonly: true
            }
          },
          messages: {
            nombre: {
              required: "Por favor ingrese un nombre de usuario.",
              lettersonly: "Por favor ingrese solo letras y sin espacios."
            }
          }
        });  
                
        if(!$(form).valid())
        {
            veto.veto = true;
        }
      })

    }
  };
})(jQuery);



 