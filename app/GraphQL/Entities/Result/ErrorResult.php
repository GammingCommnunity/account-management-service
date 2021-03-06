<?php

namespace App\GraphQL\Entities\Result;

class ErrorResult
{
	/**
	 * @var ErrorInfo
	 */
	public $error;

	/**
	 * @param string $describe
	 */
	public function __construct(string $describe)
	{
		$this->error = new ErrorInfo($describe);
	}

	public static function exit(string $text){
		header('Content-Type: application/json');
		exit(json_encode(new ErrorResult($text)));
	}
}

class ErrorInfo{
	/**
	 * @var string
	 */
	public $describe;

	/**
	 * @param string $describe
	 */
	public function __construct(string $describe)
	{
		$this->describe = $describe;
	}
}
