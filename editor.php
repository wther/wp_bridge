<?php

$seats = array('north', 'east', 'south', 'west');
$suits = array('s', 'h', 'd', 'c');

$vulnerabilities = array (
	'n' => 'N/S',
	'e' => 'E/W',
	'b' => 'Both',
	'-' => 'None'
);

/**
 * Generates HTML from suit code
 * @param $suit Suit code, e.g. s or c
 * @returns HTML Special Char code in <span> with style
 */
function htmlSuit($suit){
	$suit = strtolower(trim($suit));
	switch($suit){
		case 's': return '<span class="suit">&spades;</span>';
		case 'h': return '<span class="suit" style="color:red;">&hearts;</span>';
		case 'd': return '<span class="suit" style="color:red;">&diams;</span>';
		case 'c': return '<span class="suit">&clubs;</span>';
	}
	return $suit;
}

/**
  * Generate HTML table for editing hands
  * 
  * @param hands Dictionary containing seats and cards, 
  * @code hands['north'] = 'SASKH5H4...';
  * @returns HTML code of the assembled editor table
  */
 function htmlHandTable($hands){
	 global $seats, $suits;
	 
	 if(!is_array($hands)){
		 $hands = array();
	 }
	 
	 // Prepare array with the table's content
	 $cards = array();
	 for($j = 0; $j < count($seats); $j++){
		$seat = $seats[$j];
		$cards[$seat] = array();
		
		 if(isset($hands[$seat])){
			$suit = 's';
			$hand = strtolower($hands[$seat]);
			for($i = 0; $i < strlen($hand); $i++){
				if(in_array($hand[$i],$suits)){
					$suit = $hand[$i];
				} else {
					if(!isset($cards[$seat][$suit])){
						$cards[$seat][$suit] = "";
					} 
					$to_upper = strtoupper($hand);
					$cards[$seat][$suit] .= $to_upper[$i];
				}
			}			
		}
	 }

	 // Create table
	 $retval = '<table>';
	 foreach($seats as $seat){
		 $retval .= '<tr><td>' . strtoupper($seat) . '<td>';
		 foreach($suits as $suit){
			 $retval .= '<td>';
			 $name =  "seat_$seat[0]_$suit";
			 $value = isset($cards[$seat][$suit]) ? $cards[$seat][$suit] : '';
			 $retval .= htmlSuit($suit);
			 $retval .= '<input class="seat_input" name="' . $name . '" id="' . $name . '" value="' . $value . '" />';
			 $retval .= '</td>';
		 }
		 
		 $retval .= '</tr>';
	 }
	 
	 return $retval . '</table>';
 }
 
 /**
  * HTML input generator for selecting dealer position and vulnerability
  * @param $dealer May be n,s,e,w for appropriate seats
  * @param $vulnerability May be n for N/S, e for E/W, b for Both or - for none
  */
 function htmlDealerAndVulnerability($dealer, $vulnerability){
	 global $seats, $vulnerabilities;
	 
	 // Dealer
	 $html = '<div style="clear:both;"></div><div class="dealer-select"><span class="title">Dealer:</span><div style="float:right;">';
	 foreach($seats as $seat){
		 $selected = ($dealer == $seat[0]) ? 'checked="checked"' : '';
		 $name = "dealer";
		 $id = "dealer_$seat";
		 $html .= "<input type='radio' $selected value='$seat[0]' name='$name' id='$id' />";
		 $html .= "<label for='$id'>" . strtoupper($seat) . '</label>';
	 }
	 $html .= '</div></div><div style="clear:both;"></div>';
	 
	 // Vulnerability
	 $html .= '<div class="dealer-select"><span class="title">Vulnerability:</span><div style="float:right;">';
	 foreach($vulnerabilities as $v => $label){
		 $selected = ($v == $vulnerability) ? 'checked="checked"' : '';
		 $key = ($v == '-') ? 'none' : $v;
		 $name = "vulnerability";
		 $id = "vulnerability_$key";;
		 $html .= "<input type='radio' $selected value='$v' name='$name' id='$id' />";
		 $html .= "<label for='$id'>" . $label . '</label>';
	 }
	 $html .= '</div></div><div style="clear:both;"></div>';
	 return $html;
 }
 
 /**
  *  Generate auction table from Handviewer string
  */
 function htmlAuctionTable($auctionString){
	 $auctionString = $auctionString;
	 
	 // Generate table from string
	 $auction = array();
	 $buffer = '';
	 for($i = 0; $i < strlen($auctionString); $i++){
		 $c = $auctionString[$i];
		 
		 if($c ==  ' ' || $c == "\n" || $c == "\r" ){
			// do nothing
		 } else if($c == '(' && count($auction) != 0) {
			for(; $i < strlen($auctionString); $i++){
				$auction[count($auction)-1] .= $auctionString[$i];
				if($auctionString[$i] == ')') break;
			}
		 } else if ($buffer == ''){
			 switch($c){
				 case 'D':
				 case 'X':
					$auction[] = 'X';
					break;
				case 'R':
					$auction[] = 'XX';
					break;
				case 'P':
					$auction[] = 'P';
					break;
				case '?':
					$auction[] = '?';
					break;
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
					$buffer .= $c;
					break;
			 }
		 } else {
			$auction[] = $buffer . $c;	
			$buffer = '';	 
		 }
	 }
	 
	 // Create table
	 $html = '<table class="auction" style="width:100%;">';
	 
	 for($j = 0; $j < 6; $j++){
		$html .= '<tr>';
		for($i = 0; $i < 4; $i++){
			$n = $j * 4 + $i;
			$value = $auction[$n];
			$html .= "<td><input class='auction_entry' id='auction_$n' name='auction_$n' value='$value' /></td>";
		}
		if($j == 0){
			$html .= '<td rowspan="6" style="padding-right:50px;">';
			$html .= '<div style="padding:10px;">
			<ul>
			 <li>Select dealer using button,
				 then enter bids <br/> e.g., <b>3H</b>, <b>4N</b>, <b>2C</b>, <b>X</b>
			 </li><li><b>P</b> or empty box = pass</li>
			 <li><b>X</b> or <b>D</b> = double</li>
			 <li><b>XX</b> or <b>R</b> = redouble</li>
			 <li><b>[Tab key]</b> = next box</li>
			 <li><b>?</b> = What\'s your call?</li>
			 <li>Comments can be made in parentheses<br>
				 e.g., 1N(15-17)</li>
			 </ul>
			</div>';
			$html .= '</td>';
		}
		$html .= '</tr>';
	}
	 
	 return $html . '</table>';
 }

// Get the seat value from the $_GET parameter
$seatInput = array();
foreach(array('n' => 'north', 'e' => 'east', 'w' => 'west', 's' => 'south') as $key => $localKey){
	if(isset($_GET[$key])){
		$seatInput[$localKey] = $_GET[$key];
	}
}

$dealerInput = isset($_GET['d']) ? $_GET['d'] : '';
$vulInput = isset($_GET['v']) ? $_GET['v'] : '-';

?>
<html>
<head>
	<script type="text/javascript" src="../../../wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<title>Hand editor</title>
	<style type="text/css">
		body {
			font-size: 12px;
		}
		
		h1 {
			font-size: 18px;
		}
		
		input.seat_input{
			width: 120px;
		}
		
		input {
			font-size: 12px;
		}
		
		div.submitWrapper {
			float: right;
			padding-top: 15px;
			padding-right: 15px;
		}
		div.submitWrapper input{
			font-size: 20px;
		}
		
		div.editor_block {
			margin: 8px 5px;
			background-color: #D0D0D0;
			border: 10px #D0D0D0 solid;
			border-radius: 10px;
		}
		
		div.editor_block h2 {
			font-size: 16px;
			margin-bottom: 8px;
		}
		
		span.suit {
			font-size: 14px;
			padding: 3px;			
		}
		
		input.auction_entry {
			width: 60px;
		}
		
	</style>
</head>
<body>
	<h1>Hand editor</h1>
	<div class="submitWrapper">
	<input type="button" value="Save" onclick="submitDialog();" />
	</div>
	<div class="dimension editor_block">
		<h2>Setup apperance</h2>
		<table class="dimensionTable">
			<tr><td style="padding:0px;10px;">
				<div><label for="width" class="dim_title">Width:</label><br/><input type="text" name="width" id="width" class="dim_input" value="<?php print isset($_GET['width']) ? $_GET['width'] : '400'; ?>" size="4"/></div>
				<div><label for="height" class="dim_title">Height:</label><br/><input type="text" name="height" id="height" class="dim_input" value="<?php print isset($_GET['height']) ? $_GET['height'] : '400'; ?>" size="4"/></div>
			</td>
			<?php
				$sizes = array(
					array('x' => 128, 'y' => 128),
					array('x' => 128, 'y' => 256),
					array('x' => 400, 'y' => 400)
				);
				foreach($sizes as $value){
					print '<td style="padding:0px;10px;">';
					$height = min($value['y']/2, 100); //$value['y'] / 2;
					$width = $value['x'] * $height / $value['y']; //$value['x'] / 2;
					
					$height2 = $height / 2 - 8;
					print "<div style='width:{$width}px;height:{$height};background-color:grey;cursor:pointer;text-align:center;color:#EEE;' onclick=\"$('#width').val($value[x]);$('#height').val($value[y]);showPreview();\"><div style='padding-top:$height2;'>$value[x]x$value[y]</div></div>";
					print '</td>';
				}
			?>
			</tr>		
		</table>
	</div>
	<div class="dealer editor_block">
	<h2>Select deal properties</h2>
	<div class="boardNumber">
	<label for="board_number" class="title">Board no:</label><input type="text" name="board_number" id="board_number" style="float:right;" value="<?php print isset($_GET['b']) ? $_GET['b'] : ''; ?>" size="2"/></div>
	<?php print htmlDealerAndVulnerability($dealerInput, $vulInput); ?>
	</div>
	<div class="seats  editor_block">
	<h2>Enter cards for each seats</h2>
	<?php print htmlHandTable($seatInput); ?>
	</div>
	<div class="auction  editor_block">
	<h2>Specify auction</h2>
	<?php print htmlAuctionTable(isset($_GET['a']) ? $_GET['a'] : ''); ?>
	</div>
	<div class="submitWrapper">
	<input type="button" onclick="showPreview();" value="Show preview"/>
	<input type="button" value="Save" onclick="submitDialog();" />
	</div>
	<div class="preview editor_block">
	<h2>Preview</h2>
	<div id="preview_block"></div>
	<pre class="url" id="url"></pre>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	
	function getIframeHtml(){
		var width = parseInt($('#width').val());
		var height = parseInt($('#height').val());
		return '<iframe src="http://www.bridgebase.com/tools/handviewer.html' + getHandviewerUrl() + '" height="' + height + 'px" width="' + width + 'px"/>';
	}
	
	
	function submitDialog(){
		tinymce.activeEditor.selection.setContent(getIframeHtml());
		//tinyMCEPopup.execCommand('mceInsertContent', false, getIframeHtml());
		top.tinymce.activeEditor.windowManager.close();
	}
	
	function showPreview(){
		$('#url').html(getHandviewerUrl());
		$('#preview_block').html(getIframeHtml());
	}
	
	function getHandviewerUrl(){
		var url = {};
		var seats = ['n','e','s','w'];
		var suits = ['s', 'h', 'd', 'c'];
		for(var i = 0; i < seats.length; i++){
			var seat = seats[i];
			var value = '';
			for(var j = 0; j < suits.length; j++){
				var suit = suits[j];
				
				var text = $('#seat_' + seat + '_' + suit).val();
		
				if(text.length != 0){
					value += suit + text.toLowerCase();
				}
			}
			if(value.length != 0){
				url[seat] = value;
			}
		}
		
		var dealer = $("input[type='radio'][name='dealer']:checked");
		if(dealer.length > 0){
			url['d'] = dealer.val();
		}
		
		var vul = $("input[type='radio'][name='vulnerability']:checked");
		if(vul.length > 0){
			url['v'] = vul.val();
		}
		
		var passCount = 0;
		
		var auction = '';
		var j = -1 ;
		for(var i = 0;; i++){
			var entry = $('#auction_' + i);
			if(entry.length == 0) break;
			if(entry.val().length != 0) j = i;
		}
		
		for(var i = 0;i <= j; i++){
			var entry = $('#auction_' + i);
			if(entry.length != 0){
				var text = entry.val();
				var bid = '';
				var explain = '';
				if(text.indexOf('(') >= 0){
					var value = text.split('(');
					bid = value[0];
					explain = '(' + value[1];
				} else {
					bid = text;
				}
				
				switch(bid.toUpperCase()){
					case 'P':
					case 'PASS':
					case '':
						bid = 'p';
						break;
					case 'X':
					case 'D':
					case 'DOUBLE':
						bid = 'd';
						break;
					case 'XX':
					case 'R':
					case 'REDOUBLE':
						bid = 'r';
						break;
				}
				
				if(bid.toUpperCase().indexOf('NT') == 1){
					bid = bid.charAt(0) + 'n';
				}
				
				auction += bid.toLowerCase() + explain;				
			} else {
				break;
			}
		}
		if(auction != ''){
			url['a'] = auction;
		}
		
		var dealNumber = parseInt($('#board_number').val());
		if(dealNumber > 0){
			url['b'] = dealNumber;
		}
		
		var retval = '';
		for(key in url){
			if(retval != '') retval += '&';
			retval += key + '=' + url[key];
		}
		return '?' + retval;
	}
	//]]>
	</script>
</body>
</html>
