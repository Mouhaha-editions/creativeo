import '../../css/form/recipe.scss';


jQuery(document).ready(function () {

    $(document).on('change', 'select[id$=_component]', function () {
        let unit = $(this).closest('tr').find('select[id$=_unit]');
        $.ajax({
            url: '/composants/ajax/units/' + $(this).val(),
            async: false,
            dataType: 'json',
            success: function (units) {
                unit.html("");
                for (let i = 0; i < units.length; i++) {
                    let val = units[i].value;
                    let name = units[i].name;
                    unit.prepend('<option value=' + val + '>' + name + '</option>');
                }
            }
        });
    });
    $('select[id$=_component]').each(function() {
        $(this).trigger('change');

    });
    $(document).on('addcomponent', function (evt, elt) {
        let unit = $(elt).find('select[id$=_unit]');
        $.ajax({
            url: '/composants/ajax/units/' + $(elt).find('select[id$=_component]').val(),
            async: false,
            dataType: 'json',
            success: function (units) {
                unit.html("");
                    for (let i = 0; i < units.length; i++) {
                        let val = units[i].value;
                        let name = units[i].name;
                        unit.prepend('<option value=' + val + '>' + name + '</option>');
                    }

            }
        });


    });

    jQuery('.add-another-collection-widget').click(function () {
        var list = jQuery(jQuery(this).attr('data-list-selector'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var newElem = jQuery(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list);
        $(document).trigger('addcomponent', newElem);
    });
});
$(document).on('click', '.remove-line', function () {
    if (confirm($(this).data('confirm'))) {
        $(this).closest('tr').remove();
    }
});



