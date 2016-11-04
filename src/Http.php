<?php

namespace yii\crossbar;

use yii\httpclient\Client;
use yii\base\Component;

class Http extends Component
{

	public $sslVerifyPeer = true;
	public $timeout				= 5;
	public $proxy					= null;

	public $url						= null;

	public $secret				= null;
	public $key						= null;
	
	private $client				= null;

	public function __construct() {

		$this->client = new Client([
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

	private function request($req, $url, $uri, $args = null, $kwargs = null) {

		$rq	= new \stdClass();

		if($req == 'call') {
			$rq->procedure	= $uri;
		} else {
			$rq->topic			= $uri;
		}

		if(!is_null($args)) {
				$rq->args			= [$args];
		}

		if(!is_null($kwargs)) {
				$rq->kwargs		= [$kwargs];
		}

		$body			= json_encode($rq);
		$params		= $this->sign($body);

		array_unshift($params, $this->url . $url);

		$options	= [
				'timeout'				=> $this->timeout,
				'sslVerifyPeer'	=> $this->sslVerifyPeer,
			];
		
		if($this->proxy != null) {
			$options['proxy']	= $this->proxy;
		}

		$rp	= $this->client->post($params, $body, ['content-type' => 'application/json'], $options)->send();

		if($rp->isOk) {
			$rq			= new WAMPReply($rp->data);
			return $rq;
		}

		return false;

	}

	public function publish($url, $topic, $args = null, $kwargs = null) {

		return $this->request('publish', $url, $topic, $args, $kwargs);

	}

	public function call($url, $procedure, $args = null, $kwargs = null) {

		return $this->request('call', $url, $procedure, $args, $kwargs);

	}

}

