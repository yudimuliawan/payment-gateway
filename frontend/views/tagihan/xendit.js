$(document).on('click', '.xendit-pay', function () {
    console.log('xx');
    var phone = $('.ovo-account-id').val();
    if(phone == ''){
        alert('Telepon / Akun OVO harus diisi');
        return false;
    }
    var invoice = '<?=$invoice?>';
    var el = $(this);
    el.html('Membuat tagihan...').attr('disabled','disabled');
    $.getJSON('pay-ovo', {invoice: invoice, phone: phone}, function (res) {
        if(res.status){
            window.location.reload();
        }else{
            alert('Terjadi kesalahan saat membua tagihan, ulangi beberapa saat lagi.')
        }
    }).complete(function(){
        el.html('BAYAR').removeAttr('disabled');
    });
});
