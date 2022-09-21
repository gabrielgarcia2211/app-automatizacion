//Adiciones y detalles
$('.nav-item .nav-link').on('click', function() {
    $(".nav-item .nav-link").removeClass('active');
    $(this).addClass('active');
 });