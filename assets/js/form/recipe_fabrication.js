import '../../css/form/recipe_fabrication.scss';
import Swal from 'sweetalert2'


$(".submit").on('click', function (e) {
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
                $("#recipe_fabrication_end").val("1");
                $t.closest('form').submit();
                return true;
            } else {
                return false;
            }

        })
    } else {
        return false;
    }
});

jQuery(document).ready(function () {
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
                    let selected = data.options[i].selected;

                    option = option === null ? "sans déclinaison" : option;
                    html += "<option "+(selected ? "selected='selected'":'')+" data-price='" + price + "' value='" + option + "'>" + option + "</option>";
                    count++;
                }
                html += "</select>";
                if (count === 1) {
                    let option = data.options[0].label;
                    option = option === null ? "sans déclinaison" : option;
                    $t.html(option);
                    let price = data.options[0].price;
                    $t.closest('tr').find('td.price').html(price);
                } else {
                    $t.html(html);
                    let $select = $("[name='options[" + val + "]']");
                    $select.on('change', function () {
                        let price = $(this).find('option:selected').data('price');
                        $t.closest('tr').find('td.price').html(price);
                    });
                    $select.trigger('change');
                }
            }
        })
    });
});

