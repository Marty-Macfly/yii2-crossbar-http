<?php

namespace yii\crossbar;

use GuzzleHttp\Client;
use yii\base\Component;

class Http extends Component
{

	public $url			= null;
	public $publish = null;
	public $call		= null;
	public $secret	= null;
	public $key			= null;
	
	private $client	= null;

	public function connect() {

		if($this->client != null) {
			return;
		}

		$this->client = new Client([
					'base_uri'  => $this->url,
					'request.options' => [
						'headers'       => ['Content-Type' => 'application/json'],
						'exceptions'    => false,
				],
			]);

	}

	private function sign($body) {

		$time				= new \DateTime('now', new \DateTimeZone('UTC'));
		$rq					= array(
			'seq'				=> rand(0, pow(2,12)),
			'timestamp'	=> $time->format('Y-m-d\TH:i:s.u\Z'),
			);

		if($this->key != null && $this->secret != null) {

			$rq['nonce']			= rand(0, pow(2, 12));
			$signature				= hash_hmac('sha256',
					$this->key . $rq['timestamp'] . $rq['seq'] . $rq['nonce'] . $body,
					$this->secret,
					true
				);
			$encodedsign			= strtr(base64_encode($signature), '+/', '-_');

			$rq['key']				= $this->key;
			$rq['signature']	= $encodedsign;

		}

		return $rq;

	}

	public function request($req, $uri, $args = null, $kwargs = null) {

		$this->connect();

		$rq							= new \stdClass();

		if($req == 'call') {
			$rq->procedure	= $uri;
		} else {
			$rq->topic			= $uri;
		}

		$rq->args				= [$msg];
		$body						= json_encode($rq);
		$rp							= $this->client->post(($req == 'call') ? $this->call : $this->publish, [
				'body'	=> $body,
				'query' => $this->sign($body),
			]);

		if($rp->getStatusCode() == 200) {
			$body		= $rp->getBody();
			$rq			= new WAMPReply(json_decode($body));
		}

		return $rq;

	}

	public function publish($agent, \Matrix\Message $msg) {

		return $this->request('publish', $agent, $msg);

	}

	public function call($agent, \Matrix\Message $msg) {

		return $this->request('call', $agent, $msg);

	}

}

