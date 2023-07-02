<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $input = $request->all();
        $message = $input['message'];
        $chat_id = $message['chat']['id'];
        $text = $message['text'];
        
        if($text == '/start')
        {
            $text = "hello";
            $this->call('sendMessage',[
                'chat_id' => $chat_id,
                'text' => $this->sendtext($text)
            ]);
        }else{
            $this->call('sendMessage',[
                'chat_id' => $chat_id,
                'text' => $this->sendtext($text)
            ]);
        }
    }
    private function call( string $method, $params = [])

    {
        $url = "https://api.telegram.org/bot" . config('services.telegram.api_key') . "/" . $method;
        $response = Http::post($url,$params);
        return $response->json();
    }

    private function sendtext($text)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://openai80.p.rapidapi.com/chat/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r
            \"model\": \"gpt-3.5-turbo\",\r
            \"messages\": [\r
                {\r
                    \"role\": \"user\",\r
                    \"content\": \"$text\"\r
                }\r
            ]\r
        }",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: openai80.p.rapidapi.com",
                "X-RapidAPI-Key: 8e2c6b7796msh234603408b6569ep1c50bfjsna2e7e81079ff",
                "content-type: application/json"
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) 
        {
            echo "cURL Error #:" . $err;
        }else 
        {
            $json_response = $response;
            $decoded_response = json_decode($json_response, true);
            $message = $decoded_response['choices'][0]['message']['content'];
            
            if(empty($message)){
                $message = "The server is busy now, try later!";
            }
            
            return $message;

        }
    }
}
