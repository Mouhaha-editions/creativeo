import '../../css/form/recipe_fabrication.scss';
import Swal from 'sweetalert2'


$(".submit").on('click', function (e) {

    if ($t.val() !== "estimate") {
        e.preventDefault();
        let $t = $(this);
        if ($t.val() === "start") {
            $t.closest('form').submit();
            return true;
        } else if ($t.val() === "stop") {
            Swal.fire({
                title: $t.data('title'),
                input: 'text',
                inputValue: $t.data('hours')
            }).then((result) => {
                if (result.value) {
                    $("#recipe_fabrication_hours").val(result.value);
                    $("#recipe_fabrication_ended").val("1");
                    $t.closest('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        } else {
            return false;
        }
    }
});

jQuery(document).ready(function () {

    $(document).on('verif.enougth', function () {
        let $buttons = $("form button");
        if ($("#components-fields-list").find('.fa-times').length !== 0) {
            $buttons.attr('disabled', 'disabled');
            $buttons.attr('type', 'button');
            $buttons.attr('title', "Vous n'avez pas tous les composants nécéssaires.");
        } else {
            $buttons.removeAttr('disable');
            $buttons.removeAttr('title');
            $buttons.attr('type', 'submit');
        }
    });

    $("[data-component]").each(function () {
        let $t = $(this);
        let val = $t.data('component');
        let type = $t.data('type');
        $.ajax({
            url: '/composants/ajax/options/' + type + "/" + val,
            dataType: 'json',
            success: function (data) {
                let html = "<select class='form-control form-control-sm' name='options[" + val + "]'>";
                let count = 0;
                for (let i = 0; i < data.options.length; i++) {
                    let option = data.options[i].label;
                    let price = data.options[i].price;
                    let enougth = data.options[i].enougth;
                    let selected = data.options[i].selected;
                    option = option === null ? "sans déclinaison" : option;
                    html += "<option " + (selected ? "selected='selected'" : '') + " data-price='" + price + "' data-enougth='" + enougth + "'  value='" + option + "'>" + option + "</option>";
                    count++;
                }
                html += "</select>";
                if (count === 1) {
                    let option = data.options[0].label;
                    option = option === null ? "sans déclinaison" : option;
                    $t.html(option);
                    let price = data.options[0].price.toFixed(4);
                    let enougth = data.options[0].enougth;
                    $t.closest('tr').find('td.price').html(price + " &euro;<input type='hidden' name=\"options[" + val + "]\" value=\"" + option + "\">" + "<input type='hidden' name=\"prices[" + val + "]\" value=\"" + price + "\">");
                    $t.closest('tr').find('td.enougth').html(enougth);
                } else {
                    $t.html(html);
                    let $select = $("[name='options[" + val + "]']");
                    $select.on('change', function () {
                        let price = parseFloat($(this).find('option:selected').data('price')).toFixed(4);
                        let enougth = $(this).find('option:selected').data('enougth');
                        $t.closest('tr').find('td.price').html(price + " &euro;<input type='hidden' name=\"prices[" + val + "]\" value=\"" + price + "\">");
                        $t.closest('tr').find('td.enougth').html(enougth);
                    });
                    $select.trigger('change');
                }
            $(document).trigger('verif.enougth');
            }
        })
    });
});

