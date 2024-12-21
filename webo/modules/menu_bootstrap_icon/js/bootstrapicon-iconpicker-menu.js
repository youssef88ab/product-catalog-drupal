(function ($, Drupal, drupalSettings) {
  $(function () {
    let iconFile = drupalSettings.menu_bootstrap_icon.icons;
    fetch(iconFile)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        var icons = data;

        $('.iconpicker').attr('autocomplete', 'off').iconpicker({
          hideOnSelect: true,
          icons: icons
        });
      })
      .catch(error => console.error('Error:', error));
    // Bind iconpicker events to the element
    $('.iconpicker').on('iconpickerSelected', function(event){
      $('.icon-preview').removeClass().addClass('icon-preview ' + event.iconpickerValue);
    });
  });
})(jQuery, Drupal, drupalSettings);
