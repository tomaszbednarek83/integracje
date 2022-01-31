<?php

class get_idshoper {
	public $codesku;
	
	public function __construct($codesku) {
		$this->codesku = $codesku;				
	}
	
public function login($c, $login, $password) {
    $params = Array(
        "method" => "login",
        "params" => Array($login, $password)
    );
    curl_setopt($c, CURLOPT_POSTFIELDS, "json=" . json_encode($params));
    $result = (Array) json_decode(curl_exec($c));
    if (isset($result['error'])) {
        return null;
    } else {
        return $result[0];
    }
}

/**
 * Pobranie b³êdów
 * 
 * @param resource $c cURL resource handle
 * @param string $session Indentyfikatorr sesji u¿ytkownika
 */
public function getError($c, $session){
	$params = Array(
        "method" => "call",
        "params" => Array($session, 'internals.validation.errors', null)
    );
    curl_setopt($c, CURLOPT_POSTFIELDS, "json=" . json_encode($params));
    $result = (Array) json_decode(curl_exec($c));
    return $result;
}

public function go_id(){
	
$c = curl_init();
curl_setopt($c, CURLOPT_URL, 'http://fitandstrong.pl/webapi/json/');
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		
		// zalogowanie uzytkownika i pobranie identyfikatora sesji
		$session = $this->login($c, "APIAku", "AkuAku12");

if ($session != null) {
    $conditions = Array(
        "stock.code" => $this->codesku             
    );
    
    $params = Array(
        "method" => "call",
        "params" => Array($session, "product.list.filter", 
                Array($conditions, "product_id", 1) // warunki, kolumna do sortowania, limit
            )
    );

    // zakodowanie parametrów dla metody POST
    $postParams = "json=" . json_encode($params);
    curl_setopt($c, CURLOPT_POSTFIELDS, $postParams);

    // dekodowanie rezultatu w formacie JSON do tablicy result
    $data = curl_exec($c);
    $result = (Array)json_decode($data);

    // sprawdzenie, czy wyst¹pi³ b³¹d
    if (isset($result['error'])) {
        //echo "Wystapil blad: " . $result['error'] . ", kod: " . $result['code'];
		return $result['code'];
    } else {
		
        return $result[0];
    }
} else {
    echo "Wyst¹pi³ b³¹d logowania";
}

curl_close($c);
}

}


?>