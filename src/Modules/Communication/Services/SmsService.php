<?php
namespace LightWine\Modules\Communication\Services;

use LightWine\Modules\Communication\Models\SmsMessageModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Communication\Interfaces\ISmsService;

class SmsService implements ISmsService
{
    private ConfigurationManagerService $settings;

    Public function __construct(){
        $this->settings = new ConfigurationManagerService();
    }

    /**
     * Send the sms message using MessageBird service: https://www.messagebird.com
     * @param string $recipients The receiver (phonenumber)
     * @param string $originator Your name
     * @param string $body The text of the message to send
     */
    private function SendSmsUsingMessageBird(string $recipients, string $originator, string $body){
        $key = $this->settings->GetAppSetting("messagebird")["api_key"];

        $curl = curl_init();

        // Add headers
        $headers = [
            'Authorization: AccessKey '.$key,
        ];

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://rest.messagebird.com/messages",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_POSTFIELDS => "recipients=".$recipients."&originator=".$originator."&body=".$body
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * Send a sms message using the specified SmsMessageModel
     * @param SmsMessageModel $sms The SmsMessageModel containing all the details of the sms message
     */
    public function SendSms(SmsMessageModel $sms){
        $this->SendSmsUsingMessageBird($sms->PhoneNumber, $sms->FromName, $sms->Message);
    }
}