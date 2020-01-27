<?php
namespace frontend\controllers;

use backend\components\XenditApi;
use backend\components\MidtransApi;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;

use backend\models\Invoice;
use backend\models\InvoiceDetail;

/**
 * Tagihan controller
 */
class TagihanController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'detail','pay','pay-ovo','status'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $tagihan_belum_dibayars = Invoice::find()->where(["user_id" => Yii::$app->user->id, "status_pembayaran" => "0"])->all();
        $tagihan_sudah_dibayars = Invoice::find()->where(["user_id" => Yii::$app->user->id, "status_pembayaran" => "1"])->all();

        return $this->render('index', [
            'tagihan_belum_dibayars' => $tagihan_belum_dibayars,
            'tagihan_sudah_dibayars' => $tagihan_sudah_dibayars,
        ]);
    }

    public function actionDetail($no)
    {
        $tagihan = Invoice::find()->where(["no_transaksi" => $no, "user_id" => Yii::$app->user->id])->one();
        $detail_tagihans = InvoiceDetail::find()->where(["invoice_id" => $tagihan->id])->andWhere(['!=', 'nama_item', "Kode Unik"])->all();
        /** @var $midtran MidtransApi */
        $midtran = Yii::$app->midtrans;
        $pembayaran = [
            'status' => false
        ];
        if(!empty($tagihan->kode_pembayaran) && $tagihan->jenis_pembayaran == 'gopay'){
            $statusBayar = $midtran->getStatus($tagihan->kode_pembayaran);
            if($statusBayar->transaction_status == 'settlement'){
                $status = 'success';
            }elseif($statusBayar->transaction_status == 'pending'){
                $status = 'pending';
            }else{
                $status = 'failure';
            }
            Yii::error($statusBayar);
            $pembayaran = [
                'status' => $status
            ];
        }elseif(!empty($tagihan->kode_pembayaran) && $tagihan->jenis_pembayaran == 'ovo'){
            /** @var $xendit XenditApi */
            $xendit = Yii::$app->xendit;
            $statusBayar = $xendit->getStatus($tagihan->kode_pembayaran);

            if($statusBayar->status == 'COMPLETED'){
                $status = 'success';
            }elseif($statusBayar->status == 'FAILED'){
                $status = 'failure';
            }else{
                $status = 'pending';
            }
            $pembayaran = [
                'status' => $status
            ];

            Yii::error($statusBayar);

        }

        if($pembayaran['status'] == 'success'){
            $tagihan->status_pembayaran = 1;
            $tagihan->save(false);
        }

        return $this->render('detail', [
            'tagihan' => $tagihan,
            'pembayaran' => $pembayaran,
            'detail_tagihans' => $detail_tagihans,
        ]);
    }

    public function actionPayOvo($invoice, $phone){
        /** @var $xendit XenditApi */
        $xendit = Yii::$app->xendit;
        $tagihan = Invoice::findOne(["no_transaksi" => $invoice, "user_id" => Yii::$app->user->id]);
        if($tagihan != null) {
            $tagihan->kode_pembayaran = $tagihan->no_transaksi . '-' . time();
            if($tagihan->save()) {
                $data = $xendit->create($tagihan->kode_pembayaran, $tagihan->grand_total,$phone);
                Yii::error($data);
                $token = ArrayHelper::getValue($data, 'transaction_date');
                if ($token != null) {
                    return $this->asJson(['status' => true, 'date' => $token]);
                }
            }else{
                Yii::error($tagihan->errors);
            }
        }
        return $this->asJson(['status' => false]);
    }

    public function actionPay($invoice){
        /** @var $midtran MidtransApi */
        $midtran = Yii::$app->midtrans;
        $tagihan = Invoice::findOne(["no_transaksi" => $invoice, "user_id" => Yii::$app->user->id]);
        if($tagihan != null) {
            $tagihan->kode_pembayaran = $tagihan->no_transaksi . '-' . time();
            if($tagihan->save()) {
                $data = json_decode($midtran->createToken($tagihan->kode_pembayaran, $tagihan->grand_total, [
                    'first_name' => Yii::$app->user->identity->nama_depan,
                    'last_name' => Yii::$app->user->identity->nama_belakang,
                    'email' => Yii::$app->user->identity->email
                ]));
                $token = ArrayHelper::getValue($data, 'token');
                if ($token != null) {
                    return $this->asJson(['status' => true, 'token' => $token]);
                }
            }else{
                Yii::error($tagihan->errors);
            }
        }
        return $this->asJson(['status' => false]);
    }

    public function actionStatus(){
        /** @var $midtran MidtransApi */
        $midtran = Yii::$app->midtrans;
        var_dump($midtran->getStatus('SANDBOX-M118963-231'));
    }
}
