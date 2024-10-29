<?php
namespace Spaceinvoices;

abstract class ApiResource {
	public static function _POST($url,$body = '{}') {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		$response = \Httpful\Request::post($real_url)
			->sendsJson()
			->body($body)
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}
	public static function _GET($url,$params = null) {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		if (!is_null($params)) {
			$query_string = http_build_query($params);
			$real_url = $real_url.'?'.$query_string;
		}

		$response = \Httpful\Request::get($real_url)
			->expectsJson()
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}
	public static function _PUT($url,$body = '{}') {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		$response = \Httpful\Request::put($real_url)
			->sendsJson()
			->body($body)
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}
	public static function _DELETE($url) {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		$response = \Httpful\Request::delete($real_url)
			->expectsJson()
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}
	public static function _PDF($url) {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		$response = \Httpful\Request::get($real_url)
			->sendsJson()
			->body('{}')
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}
	public static function _IMG($url,$image,$type) {
		$real_url = \Spaceinvoices\Spaceinvoices::$apiBaseUrl.$url;

		$response = \Httpful\Request::post($real_url)
			->attach(array($image))
			->addHeader('Authorization', \Spaceinvoices\Spaceinvoices::getAccessToken())
			->send();

		$response = ApiResource::errorHandler($response);
		return $response;
	}

	public static function errorHandler($response) {
		$code = $response->code;
		if ($code === 200) {
			return $response;
		}

		if (isset($response->body->error)) {
			$response->body->error->name = ApiResource::responseCodeName($code);
			$response->body->error->text = ApiResource::responseCodeText($code);
			$response->body->error->message = $response->body->error->message;
		}

		return $response;
	}

	public static function responseCodeName($code) {
		switch ($code) {
			case 400: return 'Bad Request';
			case 401: return 'Unauthorized';
			case 404: return 'Not Found';
			case 422: return 'Unprecessable entity';
			case 500: return 'Internal Server Error';
			case 503: return 'Service Unavailable';
			default:	return 'Error';
		}
	}

	public static function responseCodeText($code) {
		switch ($code) {
			case 400: return 'Your request is invalid.';
			case 401: return 'Either you are not allowed to access the route or are not authorised for the resource with the given id.';
			case 404: return 'The specified route could not be found.';
			case 422: return 'The provided data is invalid, usualy that means a validation error. Details about the error are visible in the server response body.';
			case 500: return 'We had a problem with our server. Try again later.';
			case 503: return 'We\'re temporarily offline for maintenance. Please try again later';
			default:	return '';
		}
	}
}

?>