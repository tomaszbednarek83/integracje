<?php
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);

require "wfirma.class.php";
	
$hostname='localhost';
$username='admin1';
$password='12345678';


    $dbh = new PDO("mysql:host=$hostname;dbname=wfirma",$username,$password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line
	$dbh->query('DELETE FROM wfirma');
	$dbh->query('ALTER TABLE wfirma AUTO_INCREMENT = 1');
	
		$it = new wfirmaQuery('goods', 'find');   
		$it->setParameter("page", "1"); 
		$it->setParameter("limit", "3000");
		$result_wfirma = $it->execute();
		
		$sku_price_all = $result_wfirma['goods'];
		
		foreach($sku_price_all as $produkty)
		{
			echo "<pre>";
				print_r($produkty);
			echo "</pre>";
			
			if(empty($produkty['total'])){
				
				//ustalenie min i max
				$minimalna = null;
				$maksymalna = null;
				foreach($produkty['good']['good_prices'] as $price)
				{		
					if($price['good_price']['name'] == 'CENY MINIMALNE')
					{
						$minimalna = $price['good_price']['brutto'];
						}
					elseif($price['good_price']['name'] == 'CENY MAKSYMALNE')
					{
						$maksymalna = $price['good_price']['brutto'];
						}				
							
				}				
					$stock = round($produkty['good']['count']);
					if($produkty['good']['code']){
						$stmt = $dbh->prepare("INSERT INTO wfirma(SKU, CENA_MAX, CENA_MIN, STOCK) VALUES(:sku, :max, :min, :stock)");
						$stmt->bindParam(":sku",trim($produkty['good']['code']));
						$stmt->bindParam(":min",$minimalna);
						$stmt->bindParam(":max",$maksymalna);
						$stmt->bindParam(":stock",$stock);
						if($stmt->execute())
						{	
							echo "dodano<br>";
						}
					
					}
			}	
		}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?> 