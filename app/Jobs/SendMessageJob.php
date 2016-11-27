<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;
    protected $message;
    protected $retry;
    protected $token;
    public function __construct($ids, $message, $token, $retry = 0)
    {
        $this->ids = $ids;
        $this->message = $message;
        $this->retry = $retry;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->retry > 25) {
            \Log::warning('Drop message', [
                'ids' => $this->ids,
                'message' => $this->message,
                'retry' => $this->retry
            ]);
            return;
        }

        $execute = "";
        $execute .= "var r = {};\n";
        $body = $this->message['body'];
        $execute .= "var b = '".addslashes($body)."';\n";
        foreach ($this->ids as $id) {
            $params = [
                'peer_id' => $id,
                "message" => 'b',
                'random_id' => $id.rand(10,1000),
                'v' => '5.60',
            ];
            $params = json_encode($params);
            $execute .= sprintf("r.r%s = API.messages.send(%s);\n", $id, $params);
        }

        $execute .= "return r;\n";

        $url = "https://api.vk.com/method/execute?v=5.60&access_token=".$this->token;

        $p = [
            'code' => $execute
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($p));
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data) {
            $json = json_decode($data, true);
            if ($json) {
                if (isset($json['response'])) {
                    \Log::debug("Send", $json);
                    return;
                }
            }
        }
        \Log::debug("Fail", ['data'=>$data]);
        sleep(1);
        dispatch( new self($this->ids, $this->message, $this->token, $this->retry + 1) );
    }
}
