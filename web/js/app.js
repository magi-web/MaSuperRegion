$(document).on('change', '#appbundle_medecin_region, #appbundle_medecin_departement', function () {
    var $field = $(this);
    var $regionField = $('#appbundle_medecin_region');
    var $form = $field.closest('form');
    var target = '#' + $field.attr('id').replace('departement', 'ville').replace('region', 'departement');
    // Les données à envoyer en Ajax
    var data = {};
    data[$regionField.attr('name')] = $regionField.val();
    data[$field.attr('name')] = $field.val();
    // On soumet les données
    $.post($form.attr('action'), data).then(function (data) {
        // On récupère le nouveau <select>
        var $input = $(data).find(target);
        // On remplace notre <select> actuel
        $(target).replaceWith($input);
    })
});