<?php

namespace App\Util;

class Environment{

    /**
     * @dir string - Diretorio absoluto do arquivo
     * @param string $dir
     * @return bool
     */
	public static function load(string $dir = "/"):bool
    {

		$envFile = "{$dir}/.env";
		
		if(!file_exists($envFile)){
			throw new \Exception("Error: {$envFile} not found", 404);
		}

		$lines = file($envFile);

		foreach($lines as $line){
			putenv(trim($line));
		}

		return true;
	}
}