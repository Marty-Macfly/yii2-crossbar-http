<?php

namespace yii\crossbar;

use yii\httpclient\Client;
use yii\base\Component;

class Http extends Component
{

	public $url			= null;
	public $publish = null;
	public $call		= null;
	public $secret	= null;
	public $key			= null;
	
	private $client	= null;

	public function __construct() {

		$this->client = new Client([
				'base_url'				=> $this->url,
				'responseConfig'	=> ['format' => Client::FORMAT_JSON],
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

		$rq							= new \stdClass();

		if($req == 'call') {
			$rq->procedure	= $uri;
		} else {
			$rq->topic			= $uri;
		}

		$rq->args				= [$msg];
		$body						= json_encode($rq);
		$params					= array_unshift($this->sign($body), ($req == 'call') ? $this->call : $this->publish);
		$rp							= $this->client->post($params, $body)->send();

		if($rp->isOk()) {
			$rq			= new WAMPReply($rp->data);
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

