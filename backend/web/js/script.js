function alertMessage(title,msg,tag='success'){
    if(title && msg){
        swal(title,msg,tag );
    }
}

$('#verify_method :radio').change(function(){
    var value = $(this).val();
    if(value == 'otp') {
      $('#otp-div').removeClass('hide');
    } else {
      $('#otp-div').addClass('hide');
    }
});

$('#generate_otp').click(function () {
  $.get(window.BASEURL + "/vendor/redeem/generate-otp?mobile=" + $('#mobile').val());
});

$(document).ready(function(){
    $( document ).on( 'focus', ':input', function(){
        $( this ).attr( 'autocomplete', 'off' );
    });
});
