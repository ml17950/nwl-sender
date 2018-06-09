<?php
class clsStatistics {
	var $db;

	function __construct(&$db) {
		//echo "<!--",__CLASS__,":",__FUNCTION__,"-->\n";
		$this->db = $db;
	}

	function add_view($bid, $lsid) {
		$sql = "UPDATE `letterit_views` SET `VIEWS` = `VIEWS` + 1, `LAST_VIEW` = ".time()." WHERE `BID` = ".$bid." AND `LS_ID` = ".$lsid.";";
		$this->db->query($sql);
	}

	function add_click($bid, $lsid) {
		$sql = "UPDATE `letterit_views` SET `CLICKS` = `CLICKS` + 1, `LAST_CLICK` = ".time()." WHERE `BID` = ".$bid." AND `LS_ID` = ".$lsid.";";
		$this->db->query($sql);
	}

	function display($bid = 0) {
		if ($bid == 0) {
			echo "<h1>",LNG_STATS1,"</h1>";
			$sql = "SELECT `month`, `year`,SUM(`registered`) AS `registered`, SUM(`deregistered`) AS `deregistered` FROM `letterit_stats` GROUP BY `year`, `month` ORDER BY `year` DESC, `month` DESC;";
		}
		else {
			echo "<h1>",LNG_STATS2," ",zname($bid),"</h1>";
			$sql = "SELECT * FROM `letterit_stats` WHERE `BID` = ".$bid." ORDER BY `year` DESC, `month` DESC;";
		}

		$stats = $this->db->fetch_assoc_array($sql);

		if (is_array($stats) && (count($stats) > 0)) {
			$maxAdd = 0;
			$maxRem = 0;

			foreach ($stats as $stat) {
				if ($stat['registered'] > $maxAdd)
					$maxAdd = $stat['registered'];
				if ($stat['deregistered'] > $maxRem)
					$maxRem = $stat['deregistered'];
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
				if ($maxAdd > 0)
					$widthAdd = round(($maxWidth / $maxAdd) * $stat['registered'], 0);
				else
					$widthAdd = 0;
				if ($maxRem > 0)
					$widthRem = round(($maxWidth / $maxRem) * $stat['deregistered'], 0);
				else
					$widthRem = 0;

				echo "<tr>";
				echo "<td class='t-left'>",str_pad($stat['month'], 2, '0', STR_PAD_LEFT)," / ",$stat['year'],"</td>";
				echo "<td class='t-right'><img src='images/bar_red_light.png' height='14' width='",$widthRem,"' title='",$stat['deregistered']," ",LNG_STATS4,"'></td>";
				echo "<td class='t-left'>",$stat['deregistered'],"</td>";
				echo "<td class='t-right'>",$stat['registered'],"</td>";
				echo "<td class='t-left'><img src='images/bar_green_light.png' height='14' width='",$widthAdd,"' title='",$stat['registered']," ",LNG_STATS5,"'></td>";
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
// 		$sql = "SELECT `BID` , `RegisterDT` , `OptOutDT` FROM `letterit_abonnenten` ";
// 		$xxx = $this->db->fetch_assoc_array($sql);
// 		foreach ($xxx as $xx) {
// 			$key1 = $xx['BID'];
// 			
// 			if ($xx['OptOutDT'] == 0) {
// 				$key2 = date('Y-n', $xx['RegisterDT']);
// 				$ddd[$key1][$key2]['registered']++;
// 			}
// 			else {
// 				$key2 = date('Y-n', $xx['OptOutDT']);
// 				$ddd[$key1][$key2]['deregistered']++;
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
// 				$sql = "UPDATE `letterit_stats` SET `registered` = ".intval($bbb['registered']).", `deregistered` = ".intval($bbb['deregistered'])." WHERE `BID` = ".$aid." AND `month` = ".$monat." AND `year` = ".$jahr.";";
// 				//$sql = "INSERT INTO `letterit_stats` (`BID`, `month`, `year`, `registered`, `deregistered`) VALUES ('".$aid."', '".$monat."', '".$jahr."', '99', '99');";
// 				echo $sql,"<br>";
// 			}
// 		}
	}
}
?>