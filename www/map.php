<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Warsaw'); 


$servername = "localhost";
$username = "";
$password = "";

if(isset($_GET["id"])) {
	if(file_exists("img/".intval($_GET["id"]).".png") && !isset($_GET["save"])) {
		echo "<center><img src='https://map.fiszu.ovh/img/".intval($_GET["id"]).".png'></center>";
	} else {

		$db = new mysqli($servername, $username, $password);

		$gd = imagecreatetruecolor(1000, 1000);
		$default = imagecolorallocate($gd, 120,100,100); 
		$back = imagecolorallocate($gd, 100,100,100); 
		$white = imagecolorallocate($gd,255,255,255);
		$black = imagecolorallocate($gd,0,0,0);
		$red = imagecolorallocate($gd,255,0,0);
		$blue = imagecolorallocate($gd,0,0,255);
		$green = imagecolorallocate($gd,0,255,0);
		
		$font_height=imagefontheight(5);
		$font_width=imagefontwidth(5);

		imagefill($gd, 0, 0, $back);
		imagestring($gd, 5, 970, 20, intval($_GET["id"]), $white);
			
		if($_GET["id"] != 0) {
			$stmt = $db->prepare("SELECT * FROM `plemiona`.`data_pl140` WHERE `id` = ?");
			$stmt->bind_param('i', $_GET["id"]);
			$stmt->execute();
			$result = $stmt->get_result();
			$row = $result ->fetch_assoc();
			$timestamp = $row["timestamp"];
			imagestring($gd, 2, 10, 980, 'https://map.fiszu.ovh/map.php?big=1&id='.intval($_GET["id"]), $white);
			imagestring($gd, 2, 750, 980, 'by Fiszu(lechita323) '.date('H:i:s d/m/Y', $timestamp), $white);
					
			
			$id_prev = $_GET["id"]-1;
			
			$result = $db->query("SELECT * FROM `pl140_".$timestamp."`.`owners`");
			while($row = $result->fetch_assoc()) {
				$owners[$row["id"]]["name"] = $row["name"];
				$owners[$row["id"]]["points"] = $row["points"];
				$owners[$row["id"]]["allie"] = $row["allie"];
			}
			
			$result = $db->query("SELECT * FROM `pl140_".$timestamp."`.`allies`");
			while($row = $result->fetch_assoc()) {
				$allies[$row["id"]]["name"] = $row["name"];
				$allies[$row["id"]]["short"] = $row["short"];
				$allies[$row["id"]]["points"] = $row["points"];
				$allies[$row["id"]]["count"] = 0;
				$allies[$row["id"]]["count_prev"] = 0;
			}
			
			if($_GET["id"] > 1) {
				$stmt = $db->prepare("SELECT * FROM `plemiona`.`data_pl140` WHERE `id` = ?");
				$stmt->bind_param('i', $id_prev);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result ->fetch_assoc();
				$timestamp_prev = $row["timestamp"];
				
				$result = $db->query("SELECT * FROM `pl140_".$timestamp_prev."`.`owners`");
				while($row = $result->fetch_assoc()) {
					$owners_prev[$row["id"]]["name"] = $row["name"];
					$owners_prev[$row["id"]]["points"] = $row["points"];
					$owners_prev[$row["id"]]["allie"] = $row["allie"];
				}
				
				$result = $db->query("SELECT * FROM `pl140_".$timestamp_prev."`.`allies`");
				while($row = $result->fetch_assoc()) {
					$allies_prev[$row["id"]]["name"] = $row["name"];
					$allies_prev[$row["id"]]["short"] = $row["short"];
					$allies_prev[$row["id"]]["points"] = $row["points"];
					$allies_prev[$row["id"]]["count"] = 0;
					$allies_prev[$row["id"]]["count_prev"] = 0;
				}
				
				$result = $db->query("SELECT * FROM `pl140_".$timestamp_prev."`.`villages`");
				while($row = $result->fetch_assoc()) {	
					if($row["owner"] != 0) {
						if(isset($owners_prev[$row["owner"]])) {
							$allie = $owners_prev[$row["owner"]]["allie"];
							if($allie != 0) {
								$allies_prev[$allie]["count_prev"]++;
							}
						}
					}
				}
			}
			
			$result = $db->query("SELECT * FROM `plemiona`.`colors`");
			while($row = $result->fetch_assoc()) {
				$color[$row["allie"]] = imagecolorallocate($gd, $row["r"], $row["g"], $row["b"]);
			}
				
			//PINKTY
			$result = $db->query("SELECT * FROM `pl140_".$timestamp."`.`villages`");
			while($row = $result->fetch_assoc()) {		
				if($row["owner"] != 0) {
					$allie = $owners[$row["owner"]]["allie"];
					if($allie != 0) {
						$allies[$allie]["count"]++;
					}
					if(isset($color[$allie])) {
						$rgb = $color[$allie];
					} else {
						$rgb = $default;
					}
					
					$x = $row["x"];
					$y = $row["y"];
					
					if(isset($_GET["big"]) && $_GET["big"] == 1) {
						imagesetpixel($gd, $x-1, $y-1, $rgb);
						imagesetpixel($gd, $x-1, $y, $rgb);
						imagesetpixel($gd, $x, $y-1, $rgb);
						imagesetpixel($gd, $x, $y, $rgb);
					} else if(isset($_GET["big"]) && $_GET["big"] == 2) {
						imagesetpixel($gd, $x-1, $y-1, $rgb);
						imagesetpixel($gd, $x-1, $y, $rgb);
						imagesetpixel($gd, $x-1, $y+1, $rgb);
						imagesetpixel($gd, $x, $y-1, $rgb);
						imagesetpixel($gd, $x, $y, $rgb);
						imagesetpixel($gd, $x, $y+1, $rgb);
						imagesetpixel($gd, $x+1, $y-1, $rgb);
						imagesetpixel($gd, $x+1, $y, $rgb);
						imagesetpixel($gd, $x+1, $y+1, $rgb);
					} else {
						imagesetpixel($gd, $x, $y, $rgb);
					}
				}
			}
			
			//OPISY
			$sum = 0;
			$row = $db->query("SELECT MAX(`group`) AS max FROM `plemiona`.`colors`")->fetch_assoc();
			$max = $row["max"];
			$iw = 1;
			for($i=1; $i<=$max; $i++) {
				$result = $db->query("SELECT * FROM `plemiona`.`colors` WHERE `group` = ".$i);
				$text = "";
				$count = 0;
				$count_prev = 0;
				while($row = $result->fetch_assoc()) {
					if(isset($allies[$row["allie"]])) {
						$text .= " ".$allies[$row["allie"]]["short"];
						$c = $color[$row["allie"]];
						$count += $allies[$row["allie"]]["count"];
					}
					if($_GET["id"] > 1) {
						if(isset($allies_prev[$row["allie"]])) {
							$count_prev += $allies_prev[$row["allie"]]["count_prev"];
						}
					}
				}
				
				if($count > 0) {
					$iw++;
					if($_GET["id"] > 1) {
						$delta = $count-$count_prev;
						if($delta < 0) {
							imagestring($gd, 5, 55-(strlen($delta)*10), $iw*15, $delta, $red);
						} else if($delta == 0) {
							imagestring($gd, 5, 55-(strlen($delta)*10), $iw*15, $delta, $blue);
						} else if($delta > 0) {
							imagestring($gd, 5, 55-(strlen($delta)*10), $iw*15, $delta, $green);
						} 
					}
					imagestring($gd, 5, 100-(strlen($count)*10), $iw*15, $count, $black);
					imagestring($gd, 5, 95, $iw*15, $text, $c);
				}
				$groupcount[$i]["color"] = $c;
				$groupcount[$i]["count"] = $count;
				$sum += $count;
			}
			imagestring($gd, 5, 95, ($max+1)*15, " INNE/BARBY", $default);
			
			$starty = 0;
			for($i=1; $i<=$max; $i++) {
				$c = $groupcount[$i]["color"];
				$count = $groupcount[$i]["count"];
				$stopy=round($count/$sum*1000);
				imagefilledrectangle($gd, $starty, 0, $starty+$stopy, 7, $c);
				$starty+=$stopy;
			}
		}
		imagepng($gd, "img/".intval($_GET["id"]).".png", 0);
		if(!isset($_GET["noprint"])) {
			echo "<center><img src='https://map.fiszu.ovh/img/".intval($_GET["id"]).".png'></center>";
		}
	}
} else {
	$db = new mysqli($servername, $username, $password);
	$result = $db->query('SELECT * FROM `plemiona`.`data_pl140`');

	while($row = $result->fetch_assoc()) {
		echo "<a href=?big=1&id=".$row["id"].">Mapa z dnia ".date('H:i:s d/m/Y', $row["timestamp"])."</a>";
		echo "<br>";
	}
}

?>

<!-- Matomo -->
<script type="text/javascript">
  var _paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="https://mapfiszuovh.matomo.cloud/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src='//cdn.matomo.cloud/mapfiszuovh.matomo.cloud/matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->