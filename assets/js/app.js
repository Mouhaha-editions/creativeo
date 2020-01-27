/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
const $ = require('jquery');
const jQuery = require('jquery');
require('bootstrap');
require('admin-lte/build/js/AdminLTE');
require("@fortawesome/fontawesome-free");
require("select2");
import 'bootstrap/js/dist/popover'

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const Swal = require('sweetalert2');
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
$('.popover, .help').popover({
    container: 'body'
});
$(".select2").select2({
    tags: true
});
