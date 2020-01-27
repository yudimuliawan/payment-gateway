$(document).on('click', '.midtrans-pay', function () {
    var options = {
        onSuccess: function(result){window.location.reload();},
        onPending: function(result){window.location.reload();},
        onError: function(result){window.location.reload();},
        onClose: function(){console.log('customer closed the popup without finishing the payment');},
        skipCustomerDetails: true,
        skipOrderSummary: true,
        selectedPaymentType: 'gopay'
    };

    snap.show();
    $.getJSON('pay', {invoice: '<?=$invoice?>'}, function (res) {
        if(res.status){
            snap.pay(res.token, options);
        }else{
            snap.hide();
        }
    })
        .error(function () {
            snap.hide();
        })
        .complete(function () {

    });
});
