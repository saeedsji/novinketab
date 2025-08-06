import './bootstrap';

import Swal from 'sweetalert2';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
import './alpine-ui-cdn.min.js'

window.addEventListener('toast', function (event) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-right",
        showConfirmButton: false,
        timer: 3000,
        width: 450,
        timerProgressBar: true,
        showCloseButton: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    setTimeout(() => {
        Toast.fire(event.detail);
    }, event.detail.timeout);

});
window.addEventListener('alert', function (event) {
    const Toast = Swal.mixin({
        position: "center",
        showConfirmButton: true,
        timer: 3000
    });
    Toast.fire(event.detail);
});

window.addEventListener('toastr', function (event) {
    const {type, message, delay} = event.detail;
    toastr.options = {
        "positionClass": "toast-top-center",
        "closeButton": true,
        "debug": true,
        "newestOnTop": false,
        "progressBar": true,
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut",
        "rtl": true,
    }
    setTimeout(() => {
        toastr[type](message);
    }, delay || 0); // Default delay to 0 if not provided


});


