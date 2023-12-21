jQuery(document).ready(function($) {
    $('#date_reservation').change(function() {
        var dateChoisie = $(this).val();
        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                'action': 'charger_creneaux',
                'date': dateChoisie
            },
            success: function(response) {
                $('#heure_reservation').html(response);
            }
        });
    });
});
