
<?php
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
@ob_end_clean();
set_time_limit(0);

require "get_id.php";
require "update_stock.php";
	
$hostname='localhost';
$username='admin1';
$password='12345678';


    $dbh = new PDO("mysql:host=$hostname;dbname=wfirma",$username,$password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line

		$sql = "SELECT * FROM wfirma WHERE IN_XML IS NULL";
		$stm = $dbh->query($sql);
		// here you go:
		$users = $stm->fetchAll();

		foreach ($users as $row) {
			
		//sprawdzamy czy produkt o tym SKU istnieje w sklepie
			$id_inshoper = new get_idshoper(trim($row['SKU']));
			$id_inshop = $id_inshoper ->go_id();
			
				if($id_inshop != 31){				
				 $upshoper = new update_shoper($id_inshop, $row['STOCK']);
				 $upsh = $upshoper ->update();
				}
				
			echo str_repeat(' ',1024*64);
			ob_implicit_flush(1);	
		}		
		
		
				
	
 
	?>