<?php

namespace backend\models;

use common\components\NodeLogger;
use Yii;
use \backend\models\base\Checkout as BaseCheckout;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "checkout".
 */
class Checkout extends BaseCheckout
{
    public function fields()
    {
        return [
            "id",
            "konsumen",
            "konsumenAlamat",
            "total_biaya",
            "kode_unik",
            "grand_total",
            "checkoutLenders",
            "is_lunas" => function () {
                return false;
            }
        ];
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return Invoice::find()->where(["ref_id" => "Checkout::" . $this->id])->orderBy("id DESC")->one();
    }

    public function createInvoice($metode_pembayaran)
    {
        $inv = new Invoice();
        $inv->no_transaksi = $this->generateNoInvoiceCheckout();
        $inv->ref_id = "Checkout::" . $this->id;
        $inv->user_id = $this->konsumen_id;
        $inv->grand_total = $this->grand_total;
        $inv->created_at = date("Y-m-d H:i:s");
        $inv->due_date = date("Y-m-d H:i:s", strtotime("1 day"));
        if ($inv->save()) {
//            $secureDeposit = 0;
            $jmlBukuSewa = 0;
            $totalHarga = 0;
            $totalOngkir = 0;
            foreach ($this->checkoutLenders as $checkoutLender) {
                foreach ($checkoutLender->checkoutLenderBukus as $buku) {
                    $hargaBuku = $buku->harga;
                    if ($this->konsumen->getStatusBerlangganan() == "YES" && $buku->jenis_transaksi == JenisTransaksi::SEWA) {
                        $hargaBuku = 0;
                    }

                    $detail = new InvoiceDetail();
                    $detail->invoice_id = $inv->id;
                    $detail->nama_item = "(" . ($buku->jenis_transaksi == JenisTransaksi::BELI ? "Beli" : "Sewa") . ")" . $buku->buku->judul;
                    $detail->harga_satuan = $hargaBuku;
                    $detail->quantity = $buku->jumlah;
                    $detail->subtotal = $hargaBuku * $buku->jumlah;
                    $detail->save();

                    $totalHarga += $detail->subtotal;



                    if ($buku->jenis_transaksi == JenisTransaksi::SEWA) {
//                        $secureDeposit += 150000;
                        $jmlBukuSewa += 1;
                    }
                }

                if($checkoutLender->ongkir != 0) {
                    $totalOngkir += $checkoutLender->ongkir;
                }
            }

            if($totalOngkir > 0){
                $detail = new InvoiceDetail();
                $detail->invoice_id = $inv->id;
                $detail->nama_item = "Ongkos Kirim";
                $detail->harga_satuan = $totalOngkir;
                $detail->quantity = 1;
                $detail->subtotal = $totalOngkir;
                $detail->save();
                $totalHarga += $totalOngkir;
            }
            Yii::error($metode_pembayaran);
            if (in_array($metode_pembayaran,[Invoice::METODE_PEMBAYARAN_TRANSFER,Invoice::METODE_PEMBAYARAN_GOPAY,Invoice::METODE_PEMBAYARAN_OVO])) {
                if($metode_pembayaran == Invoice::METODE_PEMBAYARAN_OVO){
                    Yii::error('ovo payment');
                    $inv->kode_pembayaran ='';
                    $inv->jenis_pembayaran ='ovo';
                }

                if($metode_pembayaran == Invoice::METODE_PEMBAYARAN_GOPAY){
                    Yii::error('gopay payment');
                    $inv->kode_pembayaran ='';
                    $inv->jenis_pembayaran ='gopay';
                }

                if ($totalHarga != 0) {
                    $detail = new InvoiceDetail();
                    $detail->invoice_id = $inv->id;
                    $detail->nama_item = "Kode Unik";
                    $detail->harga_satuan = $this->kode_unik;
                    $detail->quantity = 1;
                    $detail->subtotal = $this->kode_unik;
                    if ($detail->save()) {
                        $totalHarga += $detail->subtotal;
                    }
                }
            }

            if ($metode_pembayaran == Invoice::METODE_PEMBAYARAN_SALDO) {
                $depositTertahan = 0;
                $user = $inv->user;
                if ($totalHarga > $user->regular_deposit) {
                    $depositTertahan = $user->regular_deposit;
                } else if ($totalHarga <= $user->regular_deposit) {
                    $depositTertahan = $totalHarga; //200.000 - 190.000
                }
                $user->regular_deposit -= $depositTertahan;
                $user->save();

                $inv->deposit_tertahan = $depositTertahan;
                if($inv->deposit_tertahan !=0){
                    $detail = new InvoiceDetail();
                    $detail->invoice_id = $inv->id;
                    $detail->nama_item = "Pembayaran dari Saldo";
                    $detail->harga_satuan = -$inv->deposit_tertahan;
                    $detail->quantity = 1;
                    $detail->subtotal = -$inv->deposit_tertahan;
                    if ($detail->save()) {
                        $totalHarga += $detail->subtotal;
                    }
                }
            }

            $inv->grand_total = $totalHarga - floatval($this->voucher_nominal);
            Yii::error($inv);
            if ($inv->save()) {

                if($inv->grand_total == 0){
                    $inv->tandaiLunas();
                    $inv->sendEmail();
//                    NodeLogger::sendLog('grandtotal 0');
                }else if ($metode_pembayaran == Invoice::METODE_PEMBAYARAN_SALDO) {
                    //jika saldo lebih besar dari tagihan, maka dianggap lunas
                    if ($inv->grand_total <= $inv->user->regular_deposit) {
                        $inv->tandaiLunas();
                        $inv->sendEmail();
//                        NodeLogger::sendLog('pembayaran saldo');
                    }
                }else{
                    $inv->sendEmail();
                }
            }
//
//            //tambah secure deposit
//            $konsumen = $this->konsumen;
//            if ($secureDeposit > 0) {
//                $sisa = $secureDeposit - $konsumen->regular_deposit;
//
//                $detail = new InvoiceDetail();
//                $detail->invoice_id = $inv->id;
//                $detail->nama_item = "Kekurangan Uang Jaminan x" . $jmlBukuSewa . " buku";
//                $detail->harga_satuan = $sisa > 0 ? $sisa : $secureDeposit;
//                $detail->quantity = 1;
//                $detail->subtotal = $detail->harga_satuan;
//                if ($detail->save()) {
//                    $inv->grand_total += $detail->subtotal;
//                    $inv->save();
//                }
//            }
        } else {
            NodeLogger::sendLog($inv->errors);
        }

        return $inv;
    }

    public function generateNoInvoiceCheckout()
    {
        return "INVBKU" . date("Ymd") . str_pad(intval(Checkout::find()->count()), 6, "0", STR_PAD_LEFT);
    }

    public function isLunas()
    {
        $lunas = false;

        /** @var Invoice $invoice */
        $invoice = Invoice::find()->where(["ref_id" => "Checkout::" . $this->id])->one();
        if ($invoice && $invoice->status_pembayaran == 1) {
            $lunas = true;
        }

        return $lunas;
    }
}
