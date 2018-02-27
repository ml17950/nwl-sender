<?php
class clsStatistics {
	var $db;

	function __construct(&$db) {
		//echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		$this->db = $db;
	}

	function display($bid = 0) {
		if ($bid == 0) {
			echo "<h1>",LNG_STATS1,"</h1>";
			$sql = "SELECT `Monat`, `Jahr`,SUM(`Zugang`) AS `Zugang`, SUM(`Abgang`) AS `Abgang` FROM `letterit_stats` GROUP BY `Jahr`, `Monat` ORDER BY `Jahr` DESC, `Monat` DESC;";
		}
		else {
			echo "<h1>",LNG_STATS2," ",zname($bid),"</h1>";
			$sql = "SELECT * FROM `letterit_stats` WHERE `BID` = ".$bid." ORDER BY `Jahr` DESC, `Monat` DESC;";
		}

		$stats = $this->db->fetch_assoc_array($sql);

		if (count($stats) > 0) {
			$maxAdd = 0;
			$maxRem = 0;

			foreach ($stats as $stat) {
				if ($stat['Zugang'] > $maxAdd)
					$maxAdd = $stat['Zugang'];
				if ($stat['Abgang'] > $maxRem)
					$maxRem = $stat['Abgang'];
			}

			echo "<table border='0' width='100%' cellpadding='2' cellspacing='0'>";
			echo "<tr>";
			echo "<th class='t-left'>",LNG_STATS3,"</th>";
			echo "<th class='t-right' width='320'>",LNG_STATS4,"</th>";
			echo "<th class='t-left' width='40'>&nbsp;</th>";
			echo "<th class='t-right' width='40'>&nbsp;</th>";
			echo "<th class='t-left' width='320'>",LNG_STATS5,"</th>";
			echo "</tr>";

			$maxWidth = 310; // = 100%
			$maxRows = 24; // last 24 months
			$rowCnt = 0;

			foreach ($stats as $stat) {
				$widthAdd = round(($maxWidth / $maxAdd) * $stat['Zugang'], 0);
				$widthRem = round(($maxWidth / $maxRem) * $stat['Abgang'], 0);

				echo "<tr>";
				echo "<td class='t-left'>",str_pad($stat['Monat'], 2, '0', STR_PAD_LEFT)," / ",$stat['Jahr'],"</td>";
				echo "<td class='t-right'><img src='images/bar_red_light.png' height='14' width='",$widthRem,"' title='",$stat['Abgang']," ",LNG_STATS4,"'></td>";
				echo "<td class='t-left'>",$stat['Abgang'],"</td>";
				echo "<td class='t-right'>",$stat['Zugang'],"</td>";
				echo "<td class='t-left'><img src='images/bar_green_light.png' height='14' width='",$widthAdd,"' title='",$stat['Zugang']," ",LNG_STATS5,"'></td>";
				echo "</tr>";

				$rowCnt++;
				if ($rowCnt >= $maxRows)
					break;
			}

			echo "</table>";
		}
		else
			msg(LNG_STATS6, "info");




// 		$ddd = array();
// 		$sql = "SELECT `BID` , `Datum` , `Abmeldezeit` FROM `letterit_abonnenten` ";
// 		$xxx = $this->db->fetch_assoc_array($sql);
// 		foreach ($xxx as $xx) {
// 			$key1 = $xx['BID'];
// 			
// 			if ($xx['Abmeldezeit'] == 0) {
// 				$key2 = date('Y-n', $xx['Datum']);
// 				$ddd[$key1][$key2]['Zugang']++;
// 			}
// 			else {
// 				$key2 = date('Y-n', $xx['Abmeldezeit']);
// 				$ddd[$key1][$key2]['Abgang']++;
// 			}
// 		}
// 		
// // 		debugarr($ddd);
// 		
// 		foreach ($ddd as $aid => $aaa) {
// 			foreach ($aaa as $bdate => $bbb) {
// 				
// // 				debugarr($bbb);
// 				
// 				$monat = substr($bdate, 5, 1);
// 				$jahr = substr($bdate, 0, 4);
// 				
// 				$sql = "UPDATE `letterit_stats` SET `Zugang` = ".intval($bbb['Zugang']).", `Abgang` = ".intval($bbb['Abgang'])." WHERE `BID` = ".$aid." AND `Monat` = ".$monat." AND `Jahr` = ".$jahr.";";
// 				//$sql = "INSERT INTO `letterit_stats` (`BID`, `Monat`, `Jahr`, `Zugang`, `Abgang`) VALUES ('".$aid."', '".$monat."', '".$jahr."', '99', '99');";
// 				echo $sql,"<br>";
// 			}
// 		}
	}
}
?>