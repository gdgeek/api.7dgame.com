<?php
namespace frontend\controllers;

use common\models\ResendVerificationEmailForm;
use common\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\PasswordResetRequestForm;
use common\models\ResetPasswordForm;
use common\models\SignupForm;
use frontend\models\ContactForm;
use Da\QrCode\QrCode;
/**
 * Site controller
 */
class QrcodeController extends Controller
{
   
   


    public function actionQrcode()
    {

		$qrCode = (new QrCode('This is my text'))
			->setSize(250)
			->setMargin(5)
			->useForegroundColor(51, 153, 255);

		// now we can display the qrcode in many ways
		// saving the result to a file:

		$qrCode->writeFile(__DIR__ . '/code.png'); // writer defaults to PNG when none is specified

		// display directly to the browser 
		header('Content-Type: '.$qrCode->getContentType());
		echo $qrCode->writeString();
    }
	
    public function actionIndex()
    {
        return $this->render('index');
    }

    
}
