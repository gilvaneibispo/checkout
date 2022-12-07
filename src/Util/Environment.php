<?php

namespace App\Util;

class Environment{

    /**
     * @param string $key
     * @return string
     * @throws \Exception
     */
	public static function load(string $key):string
    {

		$envArr = $_ENV;

		if(!is_array($envArr) || !isset($envArr[$key])){
			throw new \Exception("Error: {$key} is not defined!", 404);
		}

		return $envArr[$key];
	}
}