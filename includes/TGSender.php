<?php

/**
 * 
 * 
 */
namespace PDFSender;
class TGSender 
{

    private $botToken = "";
    private $documentPath = "";
    private $apiUrl = "";
    private $ch = '';

    function __construct() {

        $this->ch = curl_init();

    }
    function prepare_send_file($file) {
        global $botToken;
        $this->botToken = $botToken;
        $this->documentPath = $file;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/sendDocument";
 

    }
    
    function send($chatId) : array {

 

        $result = [ 'result'=> false, 'error' => '' ];
        curl_setopt($this->ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $chatId,
            'document' => new \CURLFile(realpath($this->documentPath))
        ]);
 
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        // Отправка запроса
        $response = curl_exec($this->ch);

        // Проверка на ошибки
        if (curl_error($this->ch)) {
            $result = [ 'result'=> false, 'response' => 'Ошибка: ' . curl_error($this->ch) ];
        } else {
            // Вывод ответа от Telegram
            $result = [ 'result'=> true, 'response' => $response ];
        }

        return $result;
    }
    function close() : void {
        // Закрытие соединения
        curl_close($this->ch);
    }
    

    
}

?>