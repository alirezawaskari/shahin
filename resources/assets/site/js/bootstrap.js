try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
    require('lightgallery.js');
    require('isotope-layout/dist/isotope.pkgd');
    window.swal = require('sweetalert');
    window.iziToast = require('izitoast');
} catch (e) {}
