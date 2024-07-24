<?php
namespace api\modules\vp\controllers;

use yii\rest\ActiveController;

class VerificationController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
       
        return $behaviors;
    }
    private function der2pem($der_data)
    {
       $pem = chunk_split(base64_encode($der_data), 64, "\n");
       $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
       return $pem;
    }
    public function actionCheck(){
       
  


        $data = [
            "publicKeyUrl" => "https%3a%2f%2fstatic.gc.apple.com%2fpublic-key%2fgc-prod-10.cer",
            "signature" => "ZGSMDFVRCZsZdrwK9lBfjypo3sSrateCIETsZFSmLI4W6vqappEcy5sCzTnBf1zyEkok8cHzhhzRbj%2bC6AbI9mayu7BefU1bmlVpcLpHOE53Kk7pStaTSAsCorSPPdvy1BkgGkoyd9gu4ALwHvf%2bJoT6aGhySHqSj6Ao1qi%2fxar3Ur32LKNq1FaDbCBEf%2f7Zgx2uwqGWQi%2bkJPhlQierNFm0d1uiquPRYYR2rhrOFjU0QULCWvXETODbkKyUsXmnooSd%2bkeLiqL%2b32gjoEP8U%2bYb%2bdakjEBZaONfYDzmL8d%2biBvI90suDKBalax6IBPtItSgKOMU5RfxKmrqO0zZ0V9E4A8zisjk7TlrA3NKBL5C2KXMuWh0CMUqYGAOEy2SXuDSSx6%2fCXtPWRtPD9yXKml%2fzU7pN1EMyRsmJFL0E58TtxabdhgD%2bKG%2bDrSVQbPr%2blBIjiQVgnBuZti7DvG1cUlBAPLqM96Nikt7ZEyPVSZR0Hje%2f2f6wUv0exGIxqU19CaiIciHMuMRAwDzCwq4AaQGtvWMjGH2ZdB2OU%2fdyF1ZqYcr%2f87v7odO6eLNDxyXtQeNiDJB6gl9rb8oKyDNnEU5%2fTNm0Igv%2fgsmzVuxOC6kwzhLs0XCGI%2fWvIjLJum%2bD%2fuFbk6Vd7mNCjegM6u5KWW%2fwd%2b5AUoRvMzRnlBlKww%3d",
            "salt" => "llLBIA%3d%3d",
            "timestamp" => "1721799800593",
            "teamPlayerId" => "T%3a_117db69b8df505c850cfda303378c2e7",
            "bundleId" => "com.NoOverwork.VoxelParty"
        ];
        $publicKeyUrl = urldecode($data['publicKeyUrl']);
        $signature = base64_decode(urldecode($data['signature']));
       
       
        $salt = base64_decode(urldecode($data['salt']));
        $timestamp = pack("J",$data['timestamp']);
        $teamPlayerId =  urldecode($data['teamPlayerId']);
        $bundleId =  urldecode($data['bundleId']);
       

        $dataBuffer = $teamPlayerId . $bundleId . $timestamp . $salt;
        
        $pkURL =  urldecode($publicKeyUrl);
        $certificate = file_get_contents ($pkURL);
        $pemData = $this->der2pem($certificate);
        $pubkeyid = openssl_pkey_get_public($pemData);
        
        $ok = openssl_verify(
                    $dataBuffer, 
                    $signature, 
                    $pubkeyid, 
                    OPENSSL_ALGO_SHA256
                );
        return [
            "ok" => $ok
        ];
        
    }
}
