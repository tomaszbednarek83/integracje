<?php
set_time_limit(0);
session_start();
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();


if($_GET['run'] != 'yes') {exit;}

	$xml = '<?xml version="1.0"?><offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1">';
	file_put_contents('producty.xml',$xml);
	$xml = null;
	/**
		* Logowanie do API
		* 
		* @param resource $c cURL resource handle
		* @param string $login Login użytkownika
		* @param string $password Hasło użytkownika
		* @return string Indentyfikatorr sesji użytkownika
	*/
	function login($c, $login, $password) {
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
	
	//dzielenie tablicy na partie
	function partition( $list, $p ) {
		$listlen = count( $list );
		$partlen = floor( $listlen / $p );
		$partrem = $listlen % $p;
		$partition = array();
		$mark = 0;
		for ($px = 0; $px < $p; $px++) {
			$incr = ($px < $partrem) ? $partlen + 1 : $partlen;
			$partition[$px] = array_slice( $list, $mark, $incr );
			$mark += $incr;
		}
		return $partition;
	}
	/**
		* Pobranie błędów
		* 
		* @param resource $c cURL resource handle
		* @param string $session Indentyfikatorr sesji użytkownika
	*/
	function getError($c, $session){
		$params = Array(
        "method" => "call",
        "params" => Array($session, 'internals.validation.errors', null)
		);
		curl_setopt($c, CURLOPT_POSTFIELDS, "json=" . json_encode($params));
		$result = (Array) json_decode(curl_exec($c));
		return $result;
	}
	//////
	function api_shop($co, $ids){
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'http://tv-zakupy.pl/webapi/json/');
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		
		// zalogowanie użytkownika i pobranie identyfikatora sesji
		$session = login($c, "api", "ApiAku12");
		
		if ($session != null) {
			
			//pobieranie dokładnych danych po id produktu
			if($co == 'productid'){
				$params = Array(
				"method" => "call",		
				"params" => Array($session, "product.list", array($extended = true, $translations = true, $options = false, $gfx = true, $attributes = false, $products = $ids, $related = true, $mainGfx = false))
				
			);}
			//pobieranie wszystkich id produktów
			elseif($co == 'allproductid'){
				$params = Array(
				"method" => "call",		
				"params" => Array($session, "product.list", array($extended = false, $translations = false, $options = false, $gfx = false, $attributes = false, $products = null, $related = false, $mainGfx = false))
				
			);}
			//pobieranie id kategorii
			elseif($co == 'kategoriaid'){
				$params = Array(
				"method" => "call",		
				"params" => Array($session, "product.categories", array($ids)))
				
			;}			
			//pobieranie nazwy kategorii
			elseif($co == 'kategorianazwy'){
				$params = Array(
				"method" => "call",		
				"params" => Array($session, "category.list", array($extended = true, $translations = true, $categories = $ids)))
				
			;}
			//pobieranie nazwy kategorii
			elseif($co == 'producentlist'){
				$params = Array(
				"method" => "call",		
				"params" => Array($session, "producer.info", $ids))
				
			;}				
			// zakodowanie parametrów dla metody POST
			$postParams = "json=" . json_encode($params);
			curl_setopt($c, CURLOPT_POSTFIELDS, $postParams);
			
			// dekodowanie rezultatu w formacie JSON do tablicy result
			$data = curl_exec($c);
			$result = (Array)json_decode($data);
			
			return $result;
			
		}
		
	}
	if (isset($result['error'])) {
	echo "Wystąpił błąd: " . $result['error'] . ", kod: " . $result['code'];}
	

	
	
	$go2 = api_shop('allproductid', null);
	
	
	$go = partition( $go2, 10);	
	
	//partie
	foreach($go as $value){
		//pobieranie partiami
		$value5 = api_shop('productid', $value);		
		
		
			foreach ($value5 as $item){
			
				$product = (Array)$item;
				$stock = (Array)$product['stock'];
				//print_r($product['ean']);
				echo "</br>";
				echo "produkt: </br>";
				//print_r($item);
				
				if($stock['stock'] > 0 && $stock['active'] == 1)
				//if($product['ean'] > 0 && $stock['stock'] > 0)
				{
					echo "produkt yes</br>";
			
					$translations = (Array)$product['translations'];
					$translPL = (Array)$translations['pl_PL'];
					
					//print_r($translations);
					
					$stock = (Array)$product['stock'];
					/*echo '<pre>';
					print_r($stock);
					echo '</pre>';*/
					
					$xml .= '<o id="'. $product['product_id'] .'" url="'. $translPL['permalink'] .'" price="'. $stock['comp_promo_price'] .'" avail="1" set="0" stock="'. $stock['stock'] .'" >';
										
	
					$xml .= '<cat><![CDATA[Produkty z TV]]></cat>';
					$xml .= '<name><![CDATA['. $translPL['name'] .']]></name>';					
						
					$xml .=	'<imgs>';
					$gfx = (Array)$product['images'];
					//print_r($gfx);
					$im=0;
					foreach ($gfx as $g) {
						$image = (Array)$g;
						
						if($im ==0)
						{$xml .= '<main url="https://tv-zakupy.pl/environment/cache/images/500_500_productGfx_' . $image['gfx_id'] . '/' . $image['name'] . '.jpg"/>';}
						else
						{$xml .= '<i url="https://tv-zakupy.pl/environment/cache/images/500_500_productGfx_' . $image['gfx_id'] . '/' . $image['name'] . '.jpg"/>';}
						
						$im++;
						
					}
					$xml .=	'</imgs>';
					$xml .=	'<desc><![CDATA['.$translPL['description'].']]></desc>';	
					
					
					//pobieramt producenta producer_id
					$go5 = api_shop('producentlist', $product['producer_id']);		
					$go5 = (Array)$go5;
					
					$xml .=	'<attrs><a name="Producent"><![CDATA[ '. $go5['name'] .' ]]></a>';
					$xml .=	'<a name="Kod_producenta"><![CDATA[ '. $product['code'] .' ]]></a></attrs>';
					$xml .=	'</o>';
					
					
					if(simplexml_load_string($xml)){
						echo 'dodajemy do xml';
						echo str_repeat(' ',1024*64);
						ob_implicit_flush(1);
						file_put_contents('producty.xml',$xml, FILE_APPEND);
					}else{
						echo '<b>error:</b> '.$xml.'</br></br></br></br>' ;   }
					
					//print_r($xml);
					
					$xml = null;
					$xmlobj = null;
					
					
				} //end stock and ean 
					$product = null;
					$stock = null;
				
			}
			
			
		
	} 
	//$xml .=	'</offers>';
	
	
	file_put_contents('producty.xml', '</offers>', FILE_APPEND);
		
	
	
?>