<?php
#-----------------------------------------------------------------------------
# This script based on pslogger.pl was build and modified by Florent CHANTRET
# It is translated from perl to PHP, and add  new features :
#
# - hosts and IP blocking for not logging some hosts.
# - Support for awstats_misc_tracker.js (awstats.sourceforge.net)
#
# pslogger.pl was itself build from original file pslogger.cgi 
# and modified by Laurent Destailleur.
# It corrects some bugs and add improvments and new features.
# Because pslogger.pl is GPL, this tool is also GPL.
#-----------------------------------------------------------------------------

#-----------------------------------------------------------------------------
# Defines
#-----------------------------------------------------------------------------
$REVISION='1.7b';
$VERSION="1.0 (build $REVISION)";
$FORM = array();

# log file
$logfile  = "../logmy/websitephp.txt";
$baklogfile  = "../logmy/websitephp.bak.txt";

umask(0);
$fdb = fopen ($baklogfile, "a+");
#if ($fdb){ fputs($fdb, "started\n"); }

$itsUs = 0;

# Password to reset logs
$password = "pslogger";

# Offset from GMT time
$timezone = "+0100";

# Those IPs and hosts won't be logged
# (useful for the webmaster of the site that don't want to keep track of its visits)
$ipBlocking = array("127.0.0.1");
$hostBlocking = array("clansley.dnsalias.org"); # Perfect for dynamic DNS ! 
# $hostBlocking = array(""); # Perfect for dynamic DNS ! 

###############################
# Script                      #
# Do not edit below this line #
###############################

$MonthLib = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"); # MonthLib must be in english because used to translate log date in apache log files

if(! isset($_SERVER["QUERY_STRING"]) && (! isset($_SERVER{"REDIRECT_STATUS"}) || $_SERVER{"REDIRECT_STATUS"} < 400))
{
	 
	# If run from command line, return the syntax
	print "----- pslogger script $VERSION  -----\n";
	print "To use this web logger you must:\n";
	print "0- Edit the \$logfile variable of this script.\n";
	print "1- Put this script into your web document directory.\n";
	print "2- Add the following code at end of each html pages (before the /body) :\n";
	print "\n";
	print <<< EOF
<script language="javascript">
var awdoc=document.location.href;
if (awdoc.match(/^http/i)!=null) {
document.write('<scr' + 'ipt language="javascript" src="/pslogger.php?loc='+escape(document.location)+'&ref='+escape(document.referrer));
if(document.all) { document.write('&size='+document.fileSize); }
document.write('"></scr' + 'ipt>'); }
</script>
<noscript><img src="/pslogger.php?/js/awstats_misc_tracker.js%3Fnojs%3Dy" height=0 width=0 border=0 style="display: none"></noscript>
EOF;
	print "\n";
	exit;	
}

# Block some hosts
foreach($hostBlocking as $hostname)
{
	$ip = gethostbyname($hostname);
	if($ip == $_SERVER["REMOTE_ADDR"])
	{
		$itsUs = 1;
	}
}

# Block some IPs
foreach($ipBlocking as $ip)
{
	if($ip == $_SERVER["REMOTE_ADDR"])
	{
		$itsUs = 1;
	}
}



if($_SERVER{"REDIRECT_STATUS"} >= 400)
{
	$httpcode = $_SERVER{"REDIRECT_STATUS"};
	$_SERVER{'DOCUMENT_URI'} = $_SERVER{'REDIRECT_URL'};
	$_SERVER{'DOCUMENT_URI'} .= "?$_SERVER{'REDIRECT_QUERY_STRING'}";
	print "<HTML>\n<head>\n<title>$httpcode Error</title>\n</head>\n<body>\n";
	print "<H1>$httpcode Error</h1><BR>\n";
	if ($_SERVER{"REDIRECT_STATUS"} == 404) { print "The requested page does not exist.\n"; }
	print "</BODY>\n<HTML>\n";
	
	$lt = localtime(time());
	$time = sprintf("[%02d/%s/%04d:%02d:%02d:%02d $timezone]",$lt[3],$MonthLib[$lt[4]],$lt[5]+1900,$lt[2],$lt[1],$lt[0]);
	
	if ($fdb) 
	{ 
		fputs($fdb, "redirect\n". $_SERVER["REMOTE_ADDR"]." - - $time \"GET ".$_SERVER{'REQUEST_URI'}." ".$_SERVER["SERVER_PROTOCOL"]."\" ".$_SERVER{'REDIRECT_STATUS'}." - \"".($_SERVER{'HTTP_REFERER'}?$_SERVER{'HTTP_REFERER'}:'-')."\" \"".($_SERVER{'HTTP_USER_AGENT'}?$_SERVER{'HTTP_USER_AGENT'}:'-')."\"\n\n"); 
		fclose($fdb);
	}
	
	exit;
}

$str =$_SERVER["QUERY_STRING"];
$i=strpos($str, "&ref=");

if ($i === false)
{
	foreach(split("&", $_SERVER["QUERY_STRING"]) as $pair)
	{
		$namval = split("=", $pair);
		$FORM[$namval[0]] = preg_replace("/ /","+",URLdecode($namval[1]));
	}
}
else
{
        $FORM['ref']=rawurldecode(substr($str, $i+5));
        $FORM['loc']=rawurldecode(substr($str, 4, $i-4));
}

# if(isset($FORM['reset']) && $FORM['reset'] > 0)
# {
	# reset_log();
	# exit;
# }

# Check and/or Define value for time, FORM{'loc'}, FORM{'size'}, FORM{'ref'}
$lt = localtime(time());
$time = sprintf("[%02d/%s/%04d:%02d:%02d:%02d $timezone]",$lt[3],$MonthLib[$lt[4]],$lt[5]+1900,$lt[2],$lt[1],$lt[0]);
if(!isset($FORM['loc'])) # Should not happen
{
	$FORM['loc'] = '(undefined)';
}
else
{
	$FORM['loc'] = preg_replace("/^http:\/\/.*?\//", "/", $FORM['loc']);
}

if((!isset($FORM['ref'])) || $FORM['ref']=='')
{
	$FORM['ref'] = '-';	
}

if((!isset($FORM['size'])) || $FORM['size']=='' || $FORM['size']=='undefined')
{
	$FORM['size'] = '-';	
}



# Example of $_SERVER["QUERY_STRING"] here:
# loc=/js/awstats_misc_tracker.js
# loc=/index.php
$location = "(unknown)";
$posAwStats = strpos($_SERVER["QUERY_STRING"], "awstats_misc_tracker.js");
if (! ($posAwStats === false))
{
	# Log for awstats_misc_tracker.js

	# $awsMiscTracker = substr($_SERVER["QUERY_STRING"], $posAwStats);
	$awsMiscTracker = ereg_replace("loc=","",$_SERVER["QUERY_STRING"]);

	# $awsMiscTracker = preg_replace("/%([a-fA-F0-9][a-fA-F0-9])/e", pack("C", chr(hexdec("$1"))), $awsMiscTracker);
	$location = rawurldecode($awsMiscTracker);
}
else
{
	$location = $FORM['loc'];
}

$fd = fopen ($logfile, "a+");
flock($fd, LOCK_EX);  # Lock
if(!$fd)
{
	if ($fdb)
	{
		fputs($fdb, "Could not open $logfile for writing\n");
		fputs($fdb, $_SERVER["REMOTE_ADDR"]." - - $time \"GET ".$location." ".$_SERVER["SERVER_PROTOCOL"]."\" 200 ".$FORM['size']." \"".$FORM['ref']."\" \"".$_SERVER["HTTP_USER_AGENT"]."\"\n");
		fclose($fdb);
	}
	exit;
}

$out=$fd;
if ($itsUs) 
{ 
	#fputs($fdb, "its us\n");
	$out = $fdb; 
}
else
{
  fputs($out, $_SERVER["REMOTE_ADDR"]." - - $time \"GET ".$location." ".$_SERVER["SERVER_PROTOCOL"]."\" 200 ".$FORM['size']." \"".$FORM['ref']."\" \"".$_SERVER["HTTP_USER_AGENT"]."\"\n");
}

flock($fd, LOCK_UN);  # Unlock
fclose($fd);
fclose($fdb);


setcookie("palmorbtest", "test", time()+60*60*24*30); 
header("Location: http://palmorb.sourceforge.net/imgs/vcss.png");
# header("Location: imgs/vcss.png");

function reset_log()
{
	if(isset($FORM['password']) && $FORM['password']==$password)
	{
		$fd = fopen($logfile, "w+");
		if(!$fd)
		{
			print "Could not reset $logfile";
			exit;
		}
		fclose($fd);
		print "LOG FILE CLEARED!";
	}
	else
	{
		print "INVALID PASSWORD";
	}
}
?>
