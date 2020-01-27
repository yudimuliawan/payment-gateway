<?php

namespace backend\models;

use common\components\MailSender;
use common\components\NodeLogger;
use Yii;
use \backend\models\base\Invoice as BaseInvoice;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "invoice".
 */
class Invoice extends BaseInvoice
{
    const METODE_PEMBAYARAN_TRANSFER = 1;
    const METODE_PEMBAYARAN_BOBA = 2;
    const METODE_PEMBAYARAN_SALDO = 3;
    const METODE_PEMBAYARAN_OVO = 4;
    const METODE_PEMBAYARAN_GOPAY = 5;

    public function fields()
    {
        return [
            "id",
            "no_transaksi",
            "user",
            "grand_total",
            "status_pembayaran",
            "created_at",
            "due_date",
            "invoiceDetails",
        ];
    }

    public function getJenis()
    {
        $arr = explode("::", $this->ref_id);
        $jenis = $arr[0];
        if ($jenis == "Langganan") {
            return "Langganan";
        } else if ($jenis == "Topup") {
            return "Topup";
        } else if ($jenis == "Checkout") {
            return "Sewa/Jual Buku";
        }
    }

    /**
     * @return Langganan
     */
    public function getLangganan()
    {
        if ($this->ref_id) {
            $arr = explode("::", $this->ref_id);
            if ($arr[0] == "Langganan") {
                return Langganan::find()->where(["id" => $arr[1]])->one();
            }
        }
        return null;
    }

    /**
     * @return Checkout
     */
    public function getCheckout()
    {
        if ($this->ref_id) {
            $arr = explode("::", $this->ref_id);
            if ($arr[0] == "Checkout") {
                return Checkout::find()->where(["id" => $arr[1]])->one();
            }
        }
        return null;
    }

    /**
     * @return Topup
     */
    public function getTopup()
    {
        if ($this->ref_id) {
            $arr = explode("::", $this->ref_id);
            if ($arr[0] == "Topup") {
                return Topup::find()->where(["id" => $arr[1]])->one();
            }
        }
        return null;
    }

    public function tandaiLunas()
    {
        $this->status_pembayaran = 1;
        $this->save();

        // langganan
        $langganan = $this->getLangganan();
        if ($langganan) {
//            NodeLogger::sendLog('invoice langganan');
            $user = $this->user;
            $user->paket_langganan_id = $langganan->paket_langganan_id;
            $user->langganan_aktif = 1;

            $langgananHistory = new LanggananHistory();
            $langgananHistory->user_id = $langganan->user_id;
            $langgananHistory->langganan_id = $langganan->id;
            $langgananHistory->jenis_langganan_id = $langganan->paket_langganan_id;
            $langgananHistory->status = 1;
//            $langganan_history = langgananHistory::find()->where(["langganan_id" => $langganan->id])->one();
//            $langganan_history->status = 1;
//            $langganan_history->save();

            if ($user->mulai_sewa > 0) {
                /**  @var LanggananHistory $langgananHistory */
                $langgananHistoryLast = LanggananHistory::find()
                    ->where(['user_id' => $langganan->user_id])
                    ->andWhere(["status" => 1])
                    ->orderBy("id DESC")
                    ->one();
                $langgananHistory->start_time = $langgananHistoryLast->end_time;
                $langgananHistory->end_time = date("Y-m-d H:i:s", strtotime($langgananHistory->start_time . "+" . ($langgananHistory->langganan->lama_berlangganan) . " month"));
                $langgananHistory->save();
                $user->save();
//                if (strtotime($user->batas_akhir_langganan) < strtotime('now')) {

//                    $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime("+" . ($langganan->lama_berlangganan) . " month"));
//                    $langgananHistory->start_time = date("Y-m-d H:i:s");
//                    $langgananHistory->save();
//                    $user->save();
//                } else {
//                    $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime($user->batas_akhir_langganan . " +" . ($langganan->lama_berlangganan) . " month"));
//                    $langgananHistory->save();
//                    $user->save();
//                }
//                $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime($user->batas_akhir_langganan . " +" . ($langganan->lama_berlangganan) . " month"));
//                $user->save();
            } else {
//                if (strtotime($user->batas_akhir_langganan) < strtotime('now')) {
//                    $user->batas_akhir_langganan = null;
//                    $langgananHistory->save();
//                    $user->save();
//                } else {
                    $langgananHistory->save();
                    $user->save();
//                }
//                $user->save();
            }

//        $langganan = $this->getLangganan();
//        if ($langganan) {
//            $user = $this->user;
//            $user->paket_langganan_id = $langganan->paket_langganan_id;
//            if ($user->batas_akhir_langganan) {
//                if (strtotime($user->batas_akhir_langganan) < strtotime('now')) {
//                    $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime("+" . ($langganan->lama_berlangganan) . " month"));
//                    $user->save();
//                } else {
//                    $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime($user->batas_akhir_langganan . " +" . ($langganan->lama_berlangganan) . " month"));
//                    $user->save();
//                }
//            } else {
//                $user->batas_akhir_langganan = date("Y-m-d H:i:s", strtotime("+" . ($langganan->lama_berlangganan) . " month"));
//                $user->save();
//            }

            //cek apakah secure deposit cukup ?
            foreach ($this->invoiceDetails as $detail) {
                if ($detail->nama_item == "Secure Deposit" || $detail->nama_item == "Uang Jaminan") {
                    $user->secure_deposit += $detail->subtotal;
                    $user->save();
                }
            }
        }
        //beli buku
        $checkout = $this->getCheckout();
        if ($checkout) {
//            NodeLogger::sendLog('invoice checkout beli buku');
            foreach ($checkout->checkoutLenders as $checkoutLender) {
                $checkoutLender->checkout_status_id = CheckoutStatus::SUDAH_DIBAYAR;
                $checkoutLender->save();
//                NodeLogger::sendLog('invoice checkout beli buku $checkoutLender');
                foreach ($checkoutLender->checkoutLenderBukus as $checkoutLenderBuku) {
                    $checkoutLenderBuku->checkout_status_id = CheckoutStatus::SUDAH_DIBAYAR;
                    $checkoutLenderBuku->save();
//                    NodeLogger::sendLog('invoice checkout beli buku checkoutLenderBukus');
                }
            }
//            $this->sendEmail();
        }
        //topup saldo
        $topup = $this->getTopup();
        if ($topup) {
            if ($topup->status_proses == 0) {
                $topup->status_proses = 1;
                if ($topup->save()) {
                    //tambah deposit
                    $user = $topup->user;
                    if ($topup->jenis == "regular") {
                        $user->regular_deposit += $topup->nominal;
                    } else {
                        $user->secure_deposit += $topup->nominal;
                    }
                    $user->save();
                }
            }
        }

        $this->kirimNotifKeLender();
    }

    public function sendEmail()
    {
//        NodeLogger::sendLog($this->id);
//        NodeLogger::sendLog(InvoiceDetail::find()->where(['invoice_id' => $this->id])->all());
        MailSender::sendEmail(Yii::$app->view->render('@backend/views/emails/invoice', ["model" => $this]),
            $this->user->email,
            $this->user->nama_depan . ' ' . $this->user->nama_belakang,
            "Tagihan " . $this->no_transaksi, "Tagihan " . $this->no_transaksi);
    }

    public function kirimNotifKeLender()
    {
        $checkout = $this->getCheckout();
        if ($checkout) {
            $nama = $checkout->konsumen->nama_depan . " " . $checkout->konsumen->nama_belakang;
            foreach ($checkout->checkoutLenders as $checkoutLender) {
                Notifikasi::tambah("Pesanan Baru", "Pesanan Baru oleh " . $nama . " masuk.", $checkoutLender->lender_id);
            }
        }
    }

    public function afterSave($insert, $oldAttribute)
    {
//        NodeLogger::sendLog('aftersave');
        if ($insert == false) {
//            NodeLogger::sendLog('aftersave $insert == false');
//            NodeLogger::sendLog($oldAttribute);
//            NodeLogger::sendLog($this->status_pembayaran);
//            if (isset($oldAttribute["status_pembayaran"]) && $this->status_pembayaran == 1) {
            if ( $this->status_pembayaran == 1) {

                Notifikasi::tambah("Info Tagihan", "Tagihan Anda " . $this->no_transaksi . " sudah lunas.", $this->user_id);

//                $this->kirimNotifKeLender();
//                NodeLogger::sendLog('aftersave $insert == false pembayaran 1');
            }
        } else {
//            NodeLogger::sendLog('aftersave else');
//            Notifikasi::tambah("Info Tagihan", "Tagihan Baru Anda Sebesar" .$this->grand_total . ".", $this->user_id);
//            $this->sendEmail();
        }
    }


}
