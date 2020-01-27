<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

?>
<div class="container">
    <div class="site-faq">

        <div class="row" id="content">
            <div class="col-md-12">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="pull-left">
                            <h3><b>Tagihan</b></h3>
                        </div>
                    </div>
                </div>

                <nav aria-label="breadcrumb" style="background:#e6e9ed;border-radius:4px;">
                    <ol class="breadcrumb" style="background:#e6e9ed;">
                        <li class="breadcrumb-item"><a href="<?= Url::to(['/']); ?>">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="<?= Url::to(['profil/index']); ?>">Profil Saya</a></li>
                        <li class="breadcrumb-item"><a href="<?= Url::to(['tagihan/index']); ?>">Tagihan Saya</a></li>
                        <li class="breadcrumb-item active" aria-current="page" style="color: #434a54;">Detail Tagihan
                        </li>
                    </ol>
                </nav>

                <div class="row">

                    <div class="col-md-12">
                        <p>No. Invoice: <?= $tagihan->no_transaksi ?></p>
                        <p>Status Pembayaran:</p>
                        <?php if ($pembayaran['status'] == 'pending'): ?>
                            <div class="alert alert-info">
                                Pembayaran Anda sedang dalam proses verifikasi.
                            </div>
                        <?php else: ?>
                            <p>
                                <?php

                                if ($tagihan->status_pembayaran == 0) {
                                    echo '<span class="label label-danger" style="font-size: 16px;">Belum Dibayar</span>';
                                } else {
                                    echo '<span class="label label-success" style="font-size: 16px;">Sudah Dibayar</span>';
                                }

                                ?>
                            </p>
                        <?php endif ?>
                        <?php if ($pembayaran['status'] == 'failure'): ?>
                            <div class="alert alert-danger">
                                Pembayaran Anda mengalami kegagalan, silahkan bayar kembali.
                            </div>
                        <?php endif ?>
                    </div>

                    <div class="col-md-12">
                        <p>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                Daftar Belanja dan Pengiriman
                            </div>
                            <div class="panel-body">
                                <table class="table table-bordered table-hovered table-striped">
                                    <thead>
                                    <tr>
                                        <th style="text-align: center;">No</th>
                                        <th>Nama Barang</th>
                                        <th style="width: 120px; text-align: center;">Harga Satuan</th>
                                        <th style="text-align: center;">Jumlah</th>
                                        <th style="width: 120px; text-align: center;">Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($detail_tagihans as $detail_tagihan) { ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= $detail_tagihan->nama_item ?></td>
                                            <td style="text-align: right;">
                                                Rp <?= number_format($detail_tagihan->harga_satuan, 0, '', '.') ?></td>
                                            <td style="text-align: center;"><?= $detail_tagihan->quantity ?></td>
                                            <td style="text-align: right;">
                                                Rp <?= number_format($detail_tagihan->subtotal, 0, '', '.') ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="4">Potongan Kupon</td>
                                        <td style="text-align: right;">
                                            -
                                            Rp <?= number_format(ArrayHelper::getValue($tagihan->checkout, 'voucher_nominal', 0), 0, '', '.') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">Total & kode Unik</td>
                                        <td style="text-align: right;">
                                            Rp <?= number_format($tagihan->grand_total, 0, '', '.') ?>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        </p>
                    </div>
                    <?php if ($tagihan->status_pembayaran == 0) : ?>
                        <?php if ($pembayaran['status'] == 'pending'): ?>
                            <div class="text-center">
                                <button class="btn btn-info" onclick="window.location.reload()">Cek Pembayaran</button>
                            </div>
                        <?php elseif ($tagihan->jenis_pembayaran == 'ovo'): ?>
                            <?= $this->registerJs($this->render('xendit.js', ['invoice' => $tagihan->no_transaksi])) ?>
                            <div class="text-center">
                                <?php
                                Modal::begin([
                                    'header' => '<h2>Bayar dengan OVO</h2>',
                                    'toggleButton' => ['label' => 'BAYAR DENGAN OVO','class' => 'btn btn-primary'],
                                ]);
                                ?>
                                <div class="alert alert-info">
                                    Masukan nomor telepon yang telah terdaftar di OVO, dan kami akan mengirimkan penaginakan di akun tersebut.
                                </div>
                                <input type="text" class="form-control ovo-account-id" placeholder="No. Telepon / Akun Ovo">
                                <hr>
                                <button class="btn btn-primary xendit-pay">BAYAR</button>
                                <?php Modal::end(); ?>

                            </div>
                        <?php elseif ($tagihan->jenis_pembayaran == 'gopay'): ?>
                            <div class="text-center">
                                <button class="midtrans-pay btn btn-success">Bayar Dengan GOPAY</button>
                            </div>
                            <?= $this->registerJsFile(Yii::$app->midtrans->getSnapJs(), [
                                'data-client-key' => Yii::$app->midtrans->getClientKey()
                            ]) ?>
                            <?= $this->registerJs($this->render('midtrans.js', ['invoice' => $tagihan->no_transaksi])) ?>
                        <?php else: ?>
                            <?php
                            if ($tagihan->status_pembayaran == 0) {
                                ?>
                                <div class="col-md-12">
                                    <p>Untuk melakukan konfirmasi pembayaran, klik di sini :</p>
                                    <p class="text-center">
                                        <a href="<?= Url::to(["konfirmasi-pembayaran/", "no" => $detail_tagihan->invoice->no_transaksi]) ?>"
                                           class="btn btn-success">konfirmasi Pembayaran</a>
                                    </p>
                                </div>
                            <?php } else {
                                "";
                            }; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>


            </div>
        </div>

    </div>
</div>

<style>
    .panel {
        border: 1px solid #ececec;
    }

    .panel-body {
        border: 1px solid #ececec;
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

    @media only screen and (min-width: 600px) {
        #content {
            padding: 20px;
            padding-top: 0px;
        }
    }

    @media only screen and (max-width: 600px) {
        #content {
            padding: 0px;
            padding-top: 20px;
        }
    }
</style>
