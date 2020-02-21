/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
// require("select2");
import 'bootstrap/js/dist/popover'
import 'bootstrap/js/dist/tooltip'
import Swal from 'sweetalert2'

const $ = require('jquery');
require('bootstrap');
require('admin-lte/build/js/AdminLTE');
require("@fortawesome/fontawesome-free");
const introJs = require('intro.js/intro');
require('select2/dist/js/select2.full.min');
// import 'intro.js/themes/introjs-nassim.css';

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const Swal = require('sweetalert2');
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
$('.popover, .help').popover({
    container: 'body'
});
$('[title]').tooltip();
$(document).on("change", "[type=file]", (function() {
    var c = $(this).val().replace("C:\\fakepath\\", "").trim();
    $(this).closest("div").find(".custom-file-label").text("" !== c ? c : $(this).attr("placeholder"))
}));
$(document).on('click', 'a[data-toggle=modal]', function () {
    let $t = $(this);
    let modal = $t.data('target');
    let url = $t.data('href');
    // $(modal).find('.modal-title').html('Contacter '+ $t.text());
    $.ajax({
        url: url,
        dataType: 'html',
        success: function (data) {
            $(modal).find('.modal-body').html(data);
        },
        error: function () {
            $(modal).find('.modal-body').html("J'ai une erreur de calcul, envois moi un message pour me prevenir stp ");
        }
    });
    return false;
});
$('.tuto-start').on('click', function () {
    if($("[data-intro]").length === 0){
        Swal.fire({
            title: "Pas d'aide disponible",
            html: "Désolé, il n'y a pas d'aide disponible pour cette page.<br/>Contacte nous pour nous signaler que ce serait appreciable.",
            icon:'error'
        })
    }

    introJs().setOptions({
        'skipLabel': 'Stop',
        'nextLabel': '>',
        'prevLabel': '<',
        'doneLabel': 'Stop',
        'tooltipPosition': 'right'
    }).start();
    return false;
});


