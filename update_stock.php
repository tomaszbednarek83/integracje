<?php

class update_shoper {
	public $ids;
	
	public $stock_count;
	
	public function __construct($ids, $stock_count) {
		$this->ids = $ids;
		
		$this->stock_count = $stock_count;
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

public function update(){
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, 'http://fitandstrong.pl/webapi/json/');
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		
		// zalogowanie uzytkownika i pobranie identyfikatora sesji
		$session = $this->login($c, "APIAku", "AkuAku12");

	if ($session != null) {
		$product = Array( 			
			"stock" => Array(				
				"stock" => $this->stock_count
			)	

		);
		
		$params = Array(
			"method" => "call",
			"params" => Array($session, "product.save", Array($this->ids, $product, true)) 
				// id produktu, dane, force
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
			if ($result[0] == -1) {
				echo "Podane dane s¹ nieprawid³owe i nie spe³niaj¹ wymagañ walidacji";
				$err = $this->getError($c, $session);
					foreach($err as $error){
						echo PHP_EOL.$error;
					}
			} else if ($result[0] == 0) {
				echo "Operacja siê nie uda³a";
				$err = $this->getError($c, $session);
					foreach($err as $error){
						echo PHP_EOL.$error;
					}
			} else if ($result[0] == 1) {
				echo "<br>Produkt zostal uaktualniony o ilosc:".$this->stock_count;
			} else if ($result[0] == 2) {
				echo "Operacja siê nie uda³a - obiekt jest zablokowany przez innego administratora";
			}
		}
	} else {
		echo "Wyst¹pi³ b³¹d logowania";
	}

	curl_close($c);

	}
}

			
?>