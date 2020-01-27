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
    $('#modal-pay-ovo').on('hide.bs.modal', function () {
        alert('Tunggu sampai transaksi selesai.')
        return false;
    });
    $.getJSON('pay-ovo', {invoice: invoice, phone: phone}, function (res) {
        if(res.status){
            window.location.reload();
        }else{
            alert('Terjadi kesalahan saat membua tagihan, ulangi beberapa saat lagi.')
        }
    }).done(function(){
        el.html('BAYAR').removeAttr('disabled');
        $('#modal-pay-ovo').off('hide.bs.modal');
    });
});
