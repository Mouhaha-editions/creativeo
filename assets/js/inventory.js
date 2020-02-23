import Swal from 'sweetalert2'

$(function () {
    $(document).on('click', '.edit-inventory', function () {
        let tr = $(this).closest('tr');
        let url = $(this).attr('href');
        let title = $(this).data('title');
        let text = $(this).data('text');
        Swal.fire({
            title: title,
            input: 'text',
            text: text,
            inputValue: $(this).data('current')
        }).then((result) => {
            if (result.value !== undefined) {
                $.ajax({
                    url: url,
                    data: {
                        value: result.value
                    },
                    dataType: 'html',
                    type: 'post',
                    success: function (data) {
                        tr.replaceWith(data);
                    }
                })
            }
        });
        return false;
    });
    let ajaxRq = null;
    $(document).on('keyup change', '#inventory_productLabel', function () {
        let unit = $(this).val();
        if (ajaxRq !== null) {
            ajaxRq.abort();
        }
        ajaxRq = $.ajax({
            url: '/composants/ajax/text/units/' + unit,
            async: false,
            dataType: 'json',
            success: function (data) {
                $("#inventory_unit option").removeAttr('selected');
                $("#inventory_unit option[value=" + data.unit + "]").attr('selected', 'selected');
            }
        });
    });


    $(document).on('click', '.delete-inventory', function () {
        let tr = $(this).closest('tr');
        let url = $(this).attr('href');
        let text = $(this).data('text');
        let confirm = $(this).data('confirm');
        let cancel = $(this).data('cancel');
        Swal.fire({
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirm,
            cancelButtonText: cancel
        }).then((result) => {
            if (result.value !== undefined) {
                $.ajax({
                    url: url,
                    dataType: 'html',
                    type: 'get',
                    success: function (data) {
                        tr.remove();
                    }
                })
            }
        });
        return false;
    });

    let $productLabel = $("#inventory_productLabel");
    let parentProductLabel = $productLabel.parent();
    $productLabel.remove();
    parentProductLabel.append('<select id="inventory_productLabel" class="form-control form-control-sm" name="inventory[productLabel]"></select>');
    $productLabel = $("#inventory_productLabel");
    $productLabel.select2({
        tags: true,
        delay: 250,
        ajax: {
            url: '/composants/ajax/list',
            dataType: 'json',
            // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
        }
    });
    let $optionLabel = $("#inventory_optionLabel");
    let parentOptionLabel = $optionLabel.parent();
    $optionLabel.remove();
    parentOptionLabel.append('<select id="inventory_optionLabel" class="form-control form-control-sm" name="inventory[optionLabel]"></select>');
    $optionLabel = $("#inventory_optionLabel");
    $optionLabel.select2({
        tags: true,
        delay: 250,
        ajax: {
            url: function (params) {
                return '/composants/ajax/list-option/'+$("#inventory_productLabel").val();
            },
            dataType: 'json',
            // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
        }
    });

});
