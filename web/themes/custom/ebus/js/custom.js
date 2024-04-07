(function ($, Drupal, window, document, undefined) {
    $(document).ready(function(){
        if ($('body.path-seat-available').length){
            setTimeout(function () {
                location.reload(true);
            }, 3000);
        }
    });
})(jQuery, Drupal, this, this.document);
