<?php

namespace App\SystemDataProviders;

use GuzzleHttp;

class VirtualminDataProvider implements DataProvider {

	// TODO: create a standard model for each related object (e.g user),
	// preferably being able to save/load through a single interface (like Eloquent).

	protected $base_url;
	protected $system_user;
	protected $system_pass;
	protected $verify_peer;

	protected $client;

	public function __construct() {
		$this->base_url = config('data_provider.virtualmin.base_url');
		$this->system_user = config('data_provider.virtualmin.username');
		$this->system_pass = config('data_provider.virtualmin.password');
		$this->verify_peer = config('data_provider.verify_peer', TRUE);

		$this->client = new GuzzleHttp\Client([
			'base_uri'  => $this->base_url,
		]);
	}


	// A general method to fetch the API
	protected function _api($api, $data, $method = 'GET') {
		// Hardcode some params to get expected response format
		$defaults = [
			'program' => $api,
			'json' => 1,
			'multiline' => '',
		];

		$params = [
			'auth'	=> [$this->system_user, $this->system_pass],
			// 'debug' => fopen('a.txt', 'w'),
		];
		if(!$this->verify_peer) {
			$params['verify'] = FALSE;
		}
		try {
			if($method === 'GET') {
				$params['query'] = array_merge($data, $defaults);
				$res = $this->client->request('GET', '', $params);
			} elseif($method === 'POST') {
				$params['form_params'] = $data;
				$res = $this->client->request('POST', '?'.http_build_query($defaults), $params);
			} else {
				// Allow other methods or not?
				throw new \UnexpectedValueException('Invalid $method: '.$method.'.');
				return FALSE;
			}
		} catch(\GuzzleHttp\Exception\RequestException $e) {
			// Sometimes the server closed the connection in such a way that cURL
			// would throw an error, when it's actually just 401 Unauthorized or
			// something. If response is available (which means the request didn't
			// get dropped) we would know what happened.
			if($e->hasResponse()) {
				$res = $e->getResponse();
			} else {
				// Propagate the error
				throw $e;
			}
		}

		$code = $res->getStatusCode();
		if($code === 200) {
			$body = (string) $res->getBody();
			$json = json_decode($body);
			return $json;
		} else {
			$reason = $res->getReasonPhrase();
			throw new \UnexpectedValueException('Failed to get API response, reason: '.$code.' '.$reason.'.');
			return FALSE;
		}
	}

	protected function _apiAction($api, $params) {
		$data = $this->_api($api, $params, 'POST');
		$success = $data->status == 'success';
		$result = [
			'status'	=> $success,
			'message'	=> $success ? ($data->output ?? '') : ($data->error ?? ''),
		];

		return $result;
	}

	public function listUsers($domain = NULL) {
		$params = array();
		if($domain) {
			$params['domain'] = $domain;
		} else {
			$params['all-domains'] = '';
		}

		return $this->_api('list-users', $params);
	}

	public function createUser($domain, $username, $password, $realname) {
		$params = [
			'domain'	=> $domain,
			'user'		=> $username,
			'pass'		=> $password,
			'real'		=> $realname,
		];

		return $this->_apiAction('create-user', $params);
	}

	public function getUser($domain, $username) {
		$params = [
			'domain'	=> $domain,
			'user'		=> $username,
		];

		$data = $this->_api('list-users', $params);
		return $data->status == 'success' ? $data->data[0] : NULL;
	}

}
