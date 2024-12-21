/* global a2a*/
(function ($, Drupal, drupalSettings) {
  'use strict';

  var isFunctionExecuted = 0;
  Drupal.behaviors.addToAny = {
    attach: function (context, settings) {
      jQuery(document).ready(function ($) {

        $(document).off('click', '.popup-trigger').on('click', '.popup-trigger', function (e) {
          var popupBlock = $('#better-social-share-popup-reg');
          popupBlock.addClass('active')
            .find('.fade-out').click(function () {

              popupBlock.css('opacity', '0').find('.better-social-share-popup-content').css('margin-top', '350px');
              setTimeout(function () {
                $('.better-social-share-popup').removeClass('active');
                popupBlock.css('opacity', '').find('.better-social-share-popup-content').css('margin-top', '');
              }, 600);
            });
        });

        $(document).off('click', '.better-social-share-popup-close').on('click', '.better-social-share-popup-close', function (e) {
          hide_popup();
        });

        $(document).keyup(function (event) {
          if (event.which == '27') {
            hide_popup();
          }
        });

        $(document).off('click', '#better-social-share-popup-reg').on('click', '#better-social-share-popup-reg', function (event) {
          if ($(event.target).is('.better-social-share-popup-close') || $(event.target).is('#better-social-share-popup-reg')) {
            event.preventDefault();
            hide_popup();
            reset_search();
          }
        });

        function hide_popup() {
          var popupBlock = $('#better-social-share-popup-reg');

          popupBlock.css('opacity', '0').find('.better-social-share-popup-content').css('margin-top', '350px');
          setTimeout(function () {
            $('.better-social-share-popup').removeClass('active');
            popupBlock.css('opacity', '').find('.better-social-share-popup-content').css('margin-top', '');
          }, 600);
        }

        //open popup
        $('.popup-trigger').on('click', function (event) {
          var parentAttr = $(this).closest('.social-share-btns');
          var idAttr = $(this).closest('.social-share-btns');
          var entity_url = parentAttr.data('entity_url');
          var entity_title = parentAttr.data('entity_title');

          $('.better-social-share-popup-container').data('entity_url', entity_url);
          $('.better-social-share-popup-container').data('entity_title', entity_title);

          event.preventDefault();
          $('.better-social-share-popup').addClass('is-visible');
        });

        //close popup
        $('.better-social-share-popup').on('click', function (event) {
          if ($(event.target).is('.better-social-share-popup-close') || $(event.target).is('.better-social-share-popup')) {
            event.preventDefault();
            $(this).removeClass('is-visible');
            reset_search();
          }
        });

        $('.better-social-share-popup-close').on('click', function (event) {
          event.preventDefault();
          $('.better-social-share-popup').removeClass('is-visible');
          reset_search();

        });
        //close popup when clicking the esc keyboard button
        $(document).keyup(function (event) {
          if (event.which == '27') {
            $('.better-social-share-popup').removeClass('is-visible');
          }
        });
      });

      if (!isFunctionExecuted) {
        var ajaxUrl = drupalSettings.base_url;
        (function ($) {
          $.ajax({
            url: ajaxUrl,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
              // Append the content to the end of the body
              $('body').append(response.content);
            }
          });
        })(jQuery);

        // Set the flag to true
        isFunctionExecuted = 1;
      }

      $(document).off('click', '.social-link').on('click', '.social-link', function (e) {

        e.preventDefault();
        if ($('.better-social-share-popup').hasClass('is-visible')) {

          var entity_url = $('.better-social-share-popup-container').data('entity_url');
          var entity_title = $('.better-social-share-popup-container').data('entity_title');

          if ($(this).data('link')) {
            var data_link = $(this).data('link');

            console.log('drupalSettings.current_url', drupalSettings.current_url);
            console.log('drupalSettings.current_title', drupalSettings.current_title);

            if(entity_url == '') {
              entity_url = drupalSettings.current_url;
              entity_title = drupalSettings.current_title;
            }

            var replacedUrl = data_link
              .replace('$share_link', encodeURIComponent(entity_url))
              .replace('$title', encodeURIComponent(entity_title));
            window.open(replacedUrl, "popUpWindow", "height=400,width=600,left=400,top=100,resizable,scrollbars,toolbar=0,personalbar=0,menubar=no,location=no,directories=no,status");
          } else if ($(this).hasClass('copy-link')) {

            // Create a temporary textarea element
            var $tempTextarea = $("<textarea>");

            // Set its value to the custom text you want to copy
            $tempTextarea.val(entity_url);

            // Append the textarea to the document
            $("body").append($tempTextarea);

            // Select the text in the textarea
            $tempTextarea.select();

            // Copy the selected text to the clipboard
            document.execCommand("copy");

            alert('Text copied to clipboard!');
            $('.better-social-share-popup').removeClass('is-visible');

            // Remove the temporary textarea from the document
            $tempTextarea.remove();

            reset_search();
          } else if ($(this).attr('href')) {
            e.preventDefault();
            var data_link = $(this).attr('href');
            var replacedUrl = data_link
              .replace('$share_link', encodeURIComponent(entity_url))
              .replace('$title', encodeURIComponent(entity_title));

            window.location.href = replacedUrl;

          }
        } else {
          window.open($(this).data('link'), "popUpWindow", "height=400,width=600,left=400,top=100,resizable,scrollbars,toolbar=0,personalbar=0,menubar=no,location=no,directories=no,status");

        }
      });
    }
  };

  function reset_search() {
    setTimeout(function () {
      $('input.social-share-search').val('').trigger('keyup');

    }, 1000);
  }

}(jQuery, Drupal, drupalSettings));


function heateorSsspLoadEvent(e) {
  var t = window.onload;
  if (typeof window.onload != "function") { window.onload = e } else {
    window.onload = function () {
      t();
      e()
    }
  }
};

/**
 * Search sharing services
 */
function heateorSsspFilterSharing(val) {
  jQuery('ul.better-social-share-mini li a').each(function () {
    if (jQuery(this).text().toLowerCase().indexOf(val.toLowerCase()) != -1) {
      jQuery(this).parent().css('display', 'block');
    } else {
      jQuery(this).parent().css('display', 'none');
    }
  });
};
