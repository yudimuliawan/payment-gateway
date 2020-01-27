<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use backend\models\UserAlamat;
use backend\models\Cart;
use backend\models\UserBuku;
use common\components\NodeLogger;

?>
<div class="container">
<div class="site-loader" style="display: none;">
    <div class="loader"></div>
    <!-- <p class="loader-text" style="text-align: center;">Loading...</p> -->
</div>
<div class="site-faq">
<form class="form-checkout" method="post">
    <div class="row" id="content">
        <div class="col-md-8">

            <?php //var_dump($_SESSION["buku_cart"]); ?>

            <!-- Pemeritahuan -->
            <div class="alert alert-info alert-dismissable">Data diri Anda hanya akan dibagikan kepada Owner untuk keperluan pengiriman buku ke alamat Anda</div>
            <!-- End of Pemberitahuan -->

            <!-- Alamat -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Alamat Pengiriman
                </div>
                <div class="panel-body">

                    <div id="alamat-pengiriman">

                    </div>

                    <div id="pilih-alamat" style="display:none;">
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: space-between; align-items: center;">
                            <select id="alamat_dipilih" class="form-control">
                                <option value="">Pilih Alamat</option>
                                <?php foreach ($alamats as $pilih_alamat) { ?>
                                    <option
                                    value="<?= $pilih_alamat->id ?>" style="font-weight: ;"><?= $pilih_alamat->alamat_detail.', '.$pilih_alamat->wilayahKecamatan->nama.', '.$pilih_alamat->wilayahPropinsi->nama ?></option>
                                <?php } ?>
                            </select>
                            &nbsp;
                            <button id="tutup-pilih-alamat" class="btn btn-danger btn-block form-control" style="width: 20%;">Tutup</button>
                        </div>
                        <br>
                    </div>

                    <div id="tambah-alamat" class="panel panel-primary" style="display: none;">
                        <div class="panel-heading">
                            Tambah Alamat
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Atas Nama" id="ta-atasnama">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="No. Telepon" id="ta-notelp">
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" rows="8" cols="80" placeholder="Detail Alamat" id="ta-detailalamat"></textarea>
                            </div>
                            <div class="form-group">
                                <?= Html::dropDownList(null, null, [], ["class" => "form-control", "id"=>"propinsi_id"]) ?>
                            </div>
                            <div class="form-group">
                                <?= Html::dropDownList(null, null, [], ["class" => "form-control", "id"=>"kabupaten_id"]) ?>
                            </div>
                            <div class="form-group">
                                <?= Html::dropDownList(null, null, [], ["class" => "form-control", "id"=>"kecamatan_id"]) ?>
                            </div>
                            <div class="form-group">
                                <?= Html::dropDownList(null, null, [], ["class" => "form-control", "id"=>"desa_id"]) ?>
                            </div>
                            <div class="form-group" style="text-align: center">
                                <button id="button-simpan-tambah-alamat" type="button" class="btn btn-success" name="button">Simpan</button>
                                <button id="button-tutup-tambah-alamat" type="button" class="btn btn-danger" name="button">Tutup</button>
                            </div>
                        </div>
                    </div>

                    <p>
                        <div class="pull-left">
                            <button id="pilih-alamat-lain" type="button" class="btn btn-success btn-block" name="button">Pilih Alamat Lain</button>
                        </div>
                        <div class="pull-right">
                            <button id="button-tambah-alamat" type="button" class="btn btn-primary btn-block" name="button">Tambah Alamat</button>
                        </div>
                    </p>
                </div>
            </div>
            <!-- End of Alamat -->

            <!-- Daftar Belanja -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Daftar Belanja
                </div>
                <div class="panel-body">
                    <?php
                    $kueri = UserBuku::find()->where(["id" => $_SESSION["buku_cart"]])->groupBy(["user_id"])->all();
                    NodeLogger::sendLog("asu");
                    foreach ($kueri as $buku_cart) {
                        $owners = UserBuku::find()->where(["id" => $_SESSION["buku_cart"], "user_id" => $buku_cart->user_id])->all();
                        // $bukunyas = Cart::find()
                    ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <span class="fa fa-archive"></span> <?php echo $buku_cart->user->nama_depan.' '.$buku_cart->user->nama_belakang ?>
                            </div>
                            <div class="panel-body">
                                <?php
                                foreach ($owners as $bukunya) {
                                ?>
                                <!-- <div class="col-md-12 text-center">
                                    <img src="https://images.bolehbaca.com/<?= $bukunya->foto_depan ?>" style="font-size: 75%; /*width: 78px;*/ width: 100%; ">
                                </div> -->

                                <div class="list-group-item" style="display: flex; flex-direction: row; align-items: center;">
                                    <img src="https://images.bolehbaca.com/<?= $bukunya->foto_depan ?>" style="font-size: 75%; /*width: 78px;*/ width: 20%; float: left; margin-right: 15px;">
                                    <div style="display: flex; flex-grow: 1; flex-direction: column;">
                                        <!-- <p class="list-group-item-text">
                                            <span class="label label-primary" style="font-size: 12px;">Beli</span>
                                        </p> -->
                                        <p></p>
                                        <input style="display: none;" type="text" name="id[]" value="<?= $bukunya->id ?>">

                                        <h4 class="list-group-item-heading" style="border-bottom: 1px solid rgb(213, 213, 213); padding-top: 10px; padding-bottom: 10px; font-size: 20px;"><?= $bukunya->buku->judul ?></h4>
                                        <p class="list-group-item-text" style="padding-top: 5px;">
                                            <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: space-between;">
                                                <div style="font-weight: 300;">Harga per Item </div><div style="font-weight: 300;color: #949494;">Rp <?= number_format($bukunya->harga_jual,2,',','.') ?></div>
                                            </div>
                                        </p>
                                        <p class="list-group-item-text" style="padding-top: 5px;">
                                            <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: space-between;">
                                                <div style="font-weight: 300;">Qty </div><div style="font-weight: 300;color: #000;">×&nbsp;1</div>
                                            </div>
                                        </p>
                                        <p class="list-group-item-text" style="padding-top: 5px;">
                                            <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: space-between;">
                                                <div style="font-weight: 300;">Berat </div><div style="font-weight: 300;color: #949494;">
                                                    <?php
                                                    if ($bukunya->buku_berat) {
                                                        echo $bukunya->buku_berat / 1000 . " kg";
                                                    } else {
                                                        echo "1 kg";
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </p>
                                        <p class="list-group-item-text" style="padding-top: 5px;">
                                            <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: space-between;">
                                                <div style="font-weight: 300;">Total Harga </div><div style="font-weight: bold;">Rp <?= number_format($bukunya->harga_jual,2,',','.') ?></div>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                                <?php
                                }
                                ?>

                                <br>
                                <div>
                                    <label>Opsi Pengiriman: </label>
                                    <select class="form-control pengirimans" id="opsi-pengiriman<?= $buku_cart->user_id ?>" name="jenis_kirim[]">
                                        <option value="">Pilih Opsi</option>
                                        <option value="1">Ambil di Rumah Owner</option>
                                        <option value="2">Dikirim via Ekspedisi</option>
                                    </select>
                                    <input type="hidden" name="jenis_kirim[]" value="1">
                                </div>
                                <br>

                                <div id="catatan-ambil-dirumah-owner<?= $buku_cart->user_id ?>" class="alert alert-info alert-dismissable" style="display: none;">
                                    <b>Catatan</b><br>Mohon kontak Owner terlebih dahulu untuk mengatur jadwal (hari dan jam) pengambilan buku ke Rumah Owner
                                </div>

                                <div id="ambil-dirumah-owner<?= $buku_cart->user_id ?>" class="alert alert-info" style="display: none;">
                                    <p><b>Nama Owner:</b></p>
                                    <p>a/n <?= $buku_cart->user->nama_depan.' '.$buku_cart->user->nama_belakang ?></p>
                                    <br>
                                    <p><b>Alamat Owner:</b></p>
                                    <p><?= $buku_cart->user->userAlamat->alamat_detail.', Kelurahan/Desa '.$buku_cart->user->userAlamat->wilayahDesa->nama ?></p>
                                    <!-- <p>Jl. Joyosuko Metro No.42 A, Kelurahan/Desa Merjosari</p> -->
                                    <p><?= 'Kecamatan '.$buku_cart->user->userAlamat->wilayahKecamatan->nama ?></p>
                                    <!-- <p>Kecamatan Lowokwaru, Malang</p> -->
                                    <p><?= $buku_cart->user->userAlamat->wilayahPropinsi->nama ?></p>
                                    <!-- <p>Jawa Timur</p> -->
                                    <p><?= $buku_cart->user->userAlamat->wilayahDesa->kodepos ?></p>
                                    <!-- <p>65144</p> -->
                                    <br>
                                    <p><b>No Telpon / WA Owner:</b></p>
                                    <p><?= $buku_cart->user->userAlamat->telp ?></p>
                                    <br>
                                </div>

                                <div id="pilih-paket<?= $buku_cart->user_id ?>" style="display: none;">
                                    <label>Pilih Paket: </label>
                                    <select class="form-control" name="ongkir[]">
                                        <option value=""></option>
                                    </select>
                                    <br>
                                </div>

                                <div id="free-pickup<?= $buku_cart->user_id ?>" style="display: none;">
                                    <label>Pilih Paket: </label>
                                    <select class="form-control" name="tanggal_free_pickup[]">
                                        <option value=""></option>
                                    </select>
                                    <br>
                                </div>

                                <div>
                                    <label>Pesan Tambahan: </label>
                                    <input type="text" name="catatan[]" class="form-control" value="" placeholder="Pesan untuk Owner">
                                    <input type="hidden" name="catatan[]" class="form-control" value="" placeholder="Pesan untuk Owner">
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                        $(document).ready(function(){
                            $("#opsi-pengiriman<?= $buku_cart->user_id ?>").change(function(){
                                var opsiPengiriman = parseInt($("#opsi-pengiriman<?= $buku_cart->user_id ?>").val());
                                if (opsiPengiriman == 1) {
                                    $("#ambil-dirumah-owner<?= $buku_cart->user_id ?>").show();
                                    $("#catatan-ambil-dirumah-owner<?= $buku_cart->user_id ?>").show();
                                    $("#pilih-paket<?= $buku_cart->user_id ?>").hide();

                                } else if (opsiPengiriman == 2) {
                                    $("#ambil-dirumah-owner<?= $buku_cart->user_id ?>").hide();
                                    $("#catatan-ambil-dirumah-owner<?= $buku_cart->user_id ?>").hide();
                                    $("#pilih-paket<?= $buku_cart->user_id ?>").show();

                                } else {
                                    $("#ambil-dirumah-owner<?= $buku_cart->user_id ?>").hide();
                                    $("#catatan-ambil-dirumah-owner<?= $buku_cart->user_id ?>").hide();
                                    $("#pilih-paket<?= $buku_cart->user_id ?>").hide();
                                }
                            });




                        });


                        </script>
                    <?php } ?>
                </div>
            </div>
            <!-- End of Daftar Belanja -->
        </div>
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Metode Pembayaran
                </div>
                <div class="panel-body">
                    <select class="form-control" id="metode-pembayaran" name="metode_pembayaran">
                        <option value="1">Transfer</option>
                        <option value="2">Bayar di Boba Library</option>
                        <!-- <option value="3">Bayar dengan Saldo</option> -->
                        <option value="4">Bayar dengan OVO</option>
                        <option value="5">Bayar dengan GOPAY</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-info" id="transfer">
                <?php foreach ($banks as $bank) { ?>
                    <div class="color: white" id="<?= $bank->nama_bank ?>">
                        <p>
                            <img src="https://images.bolehbaca.com/<?= $bank->icon ?>" style="width: 50px;">
                        </p>
                        <p>No Rekening</p>
                        <p><b><?= $bank->no_rek ?></b></p>
                        <p>Nama Rekening</p>
                        <p><b><?= $bank->atas_nama ?></b></p>
                        <br><br>
                    </div>
                <?php } ?>
            </div>

            <div class="alert alert-info" style="display: none;" id="bayar-diboba">
                <p>Alamat</p>
                <p><b>Jl. Sukarno Hatta PTP II No 26, Kota Malang.</b></p>
                <p>No Telp</p>
                <p><b>0813 5361 6733</b></p>
            </div>

            <!-- <div class="panel panel-primary" style="display: none;" id="bayar-saldo">
                <div class="panel-heading">
                    Saldo Deposit Anda
                </div>
                <div class="panel-body">
                    <p style="font-size: 20px;font-weight: bold;" class="pull-right">
                        Rp 178000
                    </p>
                </div>
            </div> -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Kupon
                </div>
                <div class="voucher-exists panel-body" style="display: none;">

                </div>
                <div class="panel-body form-voucher">
                    <input type="text" class="form-control input-voucher" placeholder="Masukan Kupon">
                    <div style="color:red; display: none" class="voucher-not-exists">Maaf kupon tidak tersedia</div>
                </div>
                <div class="panel-footer form-voucher">
                    <button type="button" class="btn btn-sm btn-info">GUNAKAN</button>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    Ringkasan Belanja
                </div>
                <div class="panel-body">
                    <!-- <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center; border: 1px solid rgb(238, 238, 238);"> -->
                    <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center;">
                        <label class="control-label" style="font-weight: bold;">Subtotal</label>
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <label class="control-label" style="font-weight: 300;color: #949494;">Rp <?php echo number_format($total,2,',','.') ?></label>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center;">
                        <label class="control-label" style="font-weight: bold;">Ongkir</label>
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <label class="control-label" style="font-weight: 300;color: #949494;">Rp 0</label>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center;">
                        <label class="control-label" style="font-weight: bold;">Kode Unik</label>
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <label class="control-label" style="font-weight: 300;color: #949494;">Rp <?= number_format($kode_unik,2,',','.') ?></label>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center;">
                        <label class="control-label" style="font-weight: bold;">Diskon Kupon</label>
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <label class="control-label total-discount-voucher" style="font-weight: 300;color: #949494;">Rp 0</label>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: row; margin: 5px; padding: 5px; align-items: center;">
                        <label class="control-label" style="font-weight: bold;font-size: 22px;">TOTAL</label>
                        <div style="display: flex; flex: 1 1 0%; flex-direction: row; justify-content: flex-end; align-items: center;">
                            <label class="control-label total-pembayaran" data-total-pembayaran="<?=$total+$kode_unik?>" style="font-weight: bold;color: #cf000f;font-size: 22px;">Rp <?php echo number_format($total+$kode_unik,2,',','.') ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <button id="submit-checkout" type="button" class="btn btn-success btn-block" name="button" style="display: none;">Lanjut</button>
            <button id="submit-checkout-n" type="button" class="btn btn-success btn-block" name="button">Lanjut</button>

            <a href="<?= Url::to(['cart/index']) ?>" class="btn btn-danger btn-block" name="button">Kembali</a>

        </div>
    </div>

</div>
</div>
</form>

<style>
.panel {
    border:1px solid #ececec;
}
.panel-body {
    border:1px solid #ececec;
}
.panel a:hover {
    color: #000;
    text-decoration: none;
}
.panel-body.tab:hover {
    background: #E1F5FE;
    color: #000;
}
.panel-body.active {
    background: #E1F5FE;
    color: #000;
}
.nav-tabs.nav-justified > li > a {
    border-bottom: 0px solid #E1F5FE;
    border-radius: 0px 0px 0 0;
}
.wishlist-saya-list {
    padding: 10px;
    color: #434a54;
    border-top: 1px solid #ddd;
}
.wishlist-saya-list:hover {
    background: #E1F5FE;
}
.wishlist-saya-title {
    font-weight: bold;
}
.wishlist-saya-status {
    font-size: 12px;
    background-color: #37bc9b;
}
.wishlist-saya-image {
    width: 50px;
    height: 100%;
    float: left;
    margin-right: 10px;
}
.list-group-item {
    border: 0px;
}
.loader {
    position: absolute;
    left: 50%;
    top: 50%;
    z-index: 1;
    margin: -75px 0 0 -75px;
    border: 16px solid #f3f3f3;
    border-radius: 50%;
    border-top: 16px solid #3498db;
    width: 120px;
    height: 120px;
    -webkit-animation: spin 1s linear infinite; /* Safari */
    animation: spin 1s linear infinite;
}
.loader-text {
    position: absolute;
    left: 52.5%;
    top: 70%;
    z-index: 1;
    margin: -75px 0 0 -75px;
}
/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
@media only screen and (min-width: 600px) {
    #content {
        padding: 20px;
    }
}
@media only screen and (max-width: 600px) {
    #content {
        padding: 0px;
        padding-top: 50px;
    }
}
</style>

<?php
// $kueris = UserBuku::find()->where(["id" => $_SESSION["buku_cart"]])->groupBy(["user_id"])->all();
//
// foreach ($kueris as $values) {
?>

<script type="text/javascript">
    $(document).ready(function(){
        function calculatePrice(discount){
            var curentTotal = $('.total-pembayaran').data('total-pembayaran');
            var totalDisplay = (curentTotal - discount).toString();
            totalDisplay= totalDisplay.replace(/\d{1,3}(?=(\d{3})+(?!\d))/g, '$&.')
            $('.total-pembayaran').html('Rp '+ totalDisplay+',00');
        }
        var voucher = '';
        $(document).on('click','.form-voucher button[type=button]',function () {
            var getVoucherCode = $(".form-voucher .input-voucher").val();
            if(getVoucherCode == ""){
                alert('Masukan kupon.');
                return;
            }
            var el = $(this);
            var text = el.html();
            el.html('Mengecek kupon...');
            $.getJSON('<?=Url::to(['cek-voucher'])?>',{voucher: getVoucherCode}, function (res) {
                if(res.status == 'success'){

                    $(".voucher-exists")
                        .html('Kupon <strong>'+getVoucherCode+'</strong>, dapat digunakan. <a href="javascript:void(0)" class="open-form-voucher">Ganti</a>')
                        .show();
                    $('.total-discount-voucher').html(res.discountView);
                    calculatePrice(res.discount);
                    $(".form-voucher").hide();
                    voucher = getVoucherCode;
                }else{
                    $(".voucher-not-exists").show();
                }
            }).error(function () {
                $(".voucher-not-exists").show();
            }).complete(function () {
                el.html(text);
            })
        });

        $(document).on('click', '.open-form-voucher', function () {
            voucher = '';
            calculatePrice(0);
            $('.total-discount-voucher').html('Rp 0');
            $(".form-voucher").show();
            $(".voucher-exists").hide();
        });

        var kodeUnik = <?= $kode_unik ?>;
        // alert(kodeUnik);

        $("#submit-checkout-n").click(function() {
        	var myarray = [];
            <?php
            $kueris = UserBuku::find()->where(["id" => $_SESSION["buku_cart"]])->groupBy(["user_id"])->all();
            foreach ($kueris as $a) {
            ?>
            myarray.push($("#opsi-pengiriman<?= $a->user_id ?>").val());
            <?php } ?>

            var t = myarray.includes("");
            if(t) {
                alert("Mohon pilih Opsi Pengiriman terlebih dahulu");
            } else {
                $(".site-loader").show();
                $(".site-faq").hide();

                var idAlamat = $("#id-alamat-pengiriman").val();
                var checkout = $(".form-checkout").serialize() + "&user_alamat=" + idAlamat + "&kode_unik=" + kodeUnik;

                $.ajax({
                    type: "get",
                    url: "<?= Url::to(["checkout/submit"]) ?>",
                    data: $(".form-checkout").serialize() + "&user_alamat=" + idAlamat + "&kode_unik=" + kodeUnik+'&voucher='+voucher,
                    success: function(data){
                        if(data == 'voucher_invalid'){
                            alert('Kupon yang Anda masukan sudah tidak valid.');
                            return;
                        }
                        if (data) {
                            $(".site-loader").hide();
                            window.location="<?= Url::to(["tagihan/detail", "no" => "" ]) ?>" + data;
                            console.log(data);
                        }
                    },
                });

                // console.log(checkout);
            }
        });
    });
</script>
<?php
// }

?>

<?php

$this->registerJs('
    // $("#submit-checkout-n").click(function(){
    //     var peng = $(".pengirimans").val();
    //     alert(peng);
    //     if (peng = 1) {
    //         $("submit-checkout").show();
    //         $("submit-checkout-n").remove();
    //
    //     } else {
    //         alert("Mohon masukkan opsi pengiriman");
    //         return false;
    //     }
    // });

    // $("#submit-checkout-n").click(function(){
    //     alert("Mohon pilih Opsi Pengiriman terlebih dahulu");
    //     return false;
    // });

    $("#button-simpan-tambah-alamat").click(function(){
        var atas_nama = $("#ta-atasnama").val();
        var no_telp = $("#ta-notelp").val();
        var detail_alamat = $("#ta-detailalamat").val();
        var propinsi_id = $("#propinsi_id").val();
        var kabupaten_id = $("#kabupaten_id").val();
        var kecamatan_id = $("#kecamatan_id").val();
        var desa_id = $("#desa_id").val();

        $.ajax({
            type: "POST",
            url: "'.Url::to(["checkout/tambah-alamat"]).'",
            data: {
                atas_nama:atas_nama,
                no_telp:no_telp,
                detail_alamat:detail_alamat,
                propinsi_id:propinsi_id,
                kabupaten_id:kabupaten_id,
                kecamatan_id:kecamatan_id,
                desa_id:desa_id,
            },
            success: function(data) {
                if (data == 1) {
                    $("#tambah-alamat").hide();
                    window.location="";
                    $("#button-tambah-alamat").show();

                    console.log("sukses tambah");
                } else {
                    console.log("gagal");
                }
            },
        });
    });

    $("#alamat-pengiriman").load("'.Url::to(["checkout/alamat"]).'");

    $("#alamat_dipilih").change(function(){
        $("#alamat-pengiriman").load("'.Url::to(["checkout/alamat"]).'?id=" + $(this).val());
    });

    // $("#submit-checkout").click(function(){
    //     $(".site-loader").show();
    //     $(".site-faq").hide();
    //
    //     var idAlamat = $("#id-alamat-pengiriman").val();
    //
    //     var checkout = $(".form-checkout").serialize() + "&user_alamat=" + idAlamat;
    //
    //     $.ajax({
    //         type: "get",
    //         url: "'.Url::to(["checkout/submit"]).'",
    //         data: $(".form-checkout").serialize() + "&user_alamat=" + idAlamat,
    //         success: function(data){
    //             if (data) {
    //                 $(".site-loader").hide();
    //                 window.location="'.Url::to(["tagihan/detail", "no" => "" ]).'" + data;
    //                 console.log(data);
    //             }
    //         },
    //     });
    //
    //     console.log(checkout);
    //
    // });

    $("#kabupaten_id").hide();
    $("#kecamatan_id").hide();
    $("#desa_id").hide();

    $("#propinsi_id").load("'.Url::to(["propinsilist"]).'");

    $("#propinsi_id").change(function(){
        $("#kabupaten_id").load("'.Url::to(["kabupatenlist"]).'?id=" + $(this).val(), function(){
            $("#kabupaten_id").show();
        });
    });

    $("#kabupaten_id").change(function(){
        $("#kecamatan_id").load("'.Url::to(["kecamatanlist"]).'?id=" + $(this).val(), function(){
            $("#kecamatan_id").show();
        });
    })

    $("#kecamatan_id").change(function(){
        $("#desa_id").load("'.Url::to(["desalist"]).'?id=" + $(this).val());
        $("#desa_id").show();
    });

    $("#pilih-alamat-lain").click(function(){
        $("#pilih-alamat").show();
        $("#tambah-alamat").hide();
    });

    $("#tutup-pilih-alamat").click(function(){
        $("#pilih-alamat").hide();
        $("#button-tambah-alamat").show();
    });

    $("#metode-pembayaran").change(function(){
        var hasil = parseInt($("#metode-pembayaran").val());
        if (hasil == 1) {
            $("#transfer").show();
            $("#bayar-diboba").hide();
            $("#bayar-saldo").hide();
        } else if (hasil == 2) {
            $("#transfer").hide();
            $("#bayar-diboba").show();
            $("#bayar-saldo").hide();
        } else if (hasil == 3) {
            $("#transfer").hide();
            $("#bayar-diboba").hide();
            $("#bayar-saldo").show();
        }else{
            $("#transfer").hide();
            $("#bayar-diboba").hide();
            $("#bayar-saldo").hide();
        }
    });

    $("#button-tambah-alamat").click(function(){
        $("#tambah-alamat").show();
        $("#button-tambah-alamat").hide();
        $("#pilih-alamat").hide();
    });

    $("#button-tutup-tambah-alamat").click(function(){
        $("#tambah-alamat").hide();
        $("#button-tambah-alamat").show();
    });

    // $("#opsi-pengiriman")

')

?>
