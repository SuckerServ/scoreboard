<?php

class buf {
	public $stack = array();
	function getc() { 
		return array_shift($this->stack);
	}
	function getint() {  
		$c = $this->getc();
		if ($c == 0x80) { 
			$n = $this->getc(); 
			$n |= $this->getc() << 8; 
			return $n;
		}
		else if ($c == 0x81) {
			$n = $this->getc();
			$n |= $this->getc() << 8;
			$n |= $this->getc() << 16;
			$n |= $this->getc() << 24;
			return $n;
		}
		return $c;
	}
	function getstring($len=100) {
		$r = ""; $i = 0; 
		while (true) { 
			$c = $this->getint();
			if ($c == 0) return $r;
			$r .= chr($c);
		} 
	}
}

function get_mode($int) {
	switch($int) {
		case 0: return 'ffa/default';
		case 1: return 'coop edit';
		case 2: return 'teamplay';

		case 3: return 'instagib';
		case 4: return 'instagib team';

		case 5: return 'efficiency';
		case 6: return 'efficiency team';

		case 7: return 'tactics';
		case 8: return 'tactics team';

		case 9: return 'capture';
		case 10: return 'regen capture';

		case 11: return 'ctf';
		case 12: return 'insta ctf';

		case 13: return 'protect';
		case 14: return 'insta protect';

		case 15: return 'hold';
		case 16: return 'insta hold';

		case 17: return 'efficiency ctf';
		case 18: return 'efficiency protect';
		case 19: return 'efficiency hold';
		
		case 20: return 'collect';
		case 21: return 'insta collect';
		case 22: return 'efficiency collect';

		default: return 'unknown';
	}
}

function get_mastermode($state) {
	switch($state) {
		case 255: return "auth";
		case 0: return "open";
		case 1: return "veto";
		case 2: return "locked";
		case 3: return "private";
		case 4: return "password";
		default: return "unknown";
	}
}

function get_protocol_name($p) {
	switch($p) {
		case 256: return "CTF";
		case 257: return "Trooper";
		case 258: return "Justice";
		case 259: return "Collect";
		default: return $p;
	}
}

function GetHop($serverhost, $serverport, $command, $bufl) {
	$s = stream_socket_client("udp://".$serverhost.":".$serverport, $errno, $errstr, 10);
	stream_set_timeout($s, 10);
	fwrite($s, $command);
	$b = new buf();
	$g = fread($s, $bufl);
	$b->stack = unpack("C*", $g);
	return $b;
}

function get_info($serverhost, $serverport) {
	$b = GetHop($serverhost, $serverport+1, chr(0x19).chr(0x01), 4096);
	$b->getint();
	$b->getint();
	$se['players'] = $b->getint();
	$b->getint();
	$se['protocol'] = $b->getint();
	$se['version'] = get_protocol_name($se['protocol']);
	$se['mode_int'] = $b->getint();
	$se['mode'] = get_mode($se['mode_int']);
	$se['time'] = $b->getint();
	if ($se['protocol'] > 257) {
		$se['time_seconds'] = $se['time'];
		$se['time'] = round($se['time'] / 60);
		if ($se['time_seconds']) $se['time']++;
	}
	$se['slots'] = $b->getint();
	$se['mastermode_int'] = $b->getint();
	$se['mastermode'] = get_mastermode($se['mastermode_int']);
	$se['map'] = $b->getstring();
	if (!$se['map']) $se['map'] = '';
	$se['server'] = $b->getstring();
	if (!$se['server']) $se['server'] = '';

	return $se;
}
?>
