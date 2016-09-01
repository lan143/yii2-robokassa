<?php

namespace robokassa;

use Yii;
use yii\base\Object;

class Merchant extends Object
{
    public $sMerchantLogin;

    public $sMerchantPass1;
    public $sMerchantPass2;

    public $baseUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    public function payment($nOutSum, $nInvId, $sInvDesc = null, $sIncCurrLabel=null, $sEmail = null, $sCulture = null, $shp = [], $returnLink = false)
    {
        $url = $this->baseUrl;

        $signature = "{$this->sMerchantLogin}:{$nOutSum}:{$nInvId}:{$this->sMerchantPass1}";
        if (!empty($shp)) {
            $signature .= ':' . $this->implodeShp($shp);
        }
        $sSignatureValue = md5($signature);

        $url .= '?' . http_build_query([
            'MrchLogin' => $this->sMerchantLogin,
            'OutSum' => $nOutSum,
            'InvId' => $nInvId,
            'Desc' => $sInvDesc,
            'SignatureValue' => $sSignatureValue,
            'IncCurrLabel' => $sIncCurrLabel,
            'Email' => $sEmail,
            'Culture' => $sCulture,
            'IsTest' => $this->IsTest,
        ]);

        if (!empty($shp) && ($query = http_build_query($shp)) !== '') {
            $url .= '&' . $query;
        }
        
        if ( !$returnLink ){
            Yii::$app->user->setReturnUrl(Yii::$app->request->getUrl());
            return Yii::$app->response->redirect($url);
        } else {
            return $url;
        }
    }

    private function implodeShp($shp)
    {
        ksort($shp);
        foreach($shp as $key => $value) {
            $shp[$key] = $key . '=' . $value;
        }

        return implode(':', $shp);
    }

    public  function checkSignature($sSignatureValue, $nOutSum, $nInvId, $sMerchantPass, $shp)
    {
        $signature = "{$nOutSum}:{$nInvId}:{$sMerchantPass}";
        if (!empty($shp)) {
            $signature .= ':' . $this->implodeShp($shp);
        }
        return strtolower(md5($signature)) === strtolower($sSignatureValue);

    }
} 
