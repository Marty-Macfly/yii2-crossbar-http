<?php

namespace yii\crossbar;

class WAMPReply 
{

	private      $kwargs;
	private      $args;
	private      $error;

	public function __construct($data = array()) {

		if(is_array($data)) {

			$this->kwargs   = array_key_exists('kwargs',	$data) ? $data['kwargs'] : null;
			$this->error    = array_key_exists('error',	$data) ? $data['error']	 : null;
			$this->args     = array_key_exists('args',	$data) ? $data['args']	 : null;

		} else {

			$this->kwargs   = property_exists($data, 'kwargs') ? $data->kwargs	 : null;
			$this->error    = property_exists($data, 'error') ? $data->error	 : null;
			$this->args     = property_exists($data, 'args') ? $data->args	 : null;

		}

	}

	public function isError() {

		return $this->error != null;

	}

	public function __get($property) {

		if (property_exists($this, $property)) {

			return $this->$property;

		}

	}

	public function __toString() {

		return sprintf("kwargs: %s\nerror: %s\n args: %s", $this->kwargs, $this->error, json_encode($this->args));

	}

}
