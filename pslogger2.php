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


$FORM['loc']=$_SERVER["REQUEST_URI"];
$FORM['ref']=$_SERVER["HTTP_REFERER"];

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



$fd = fopen ($logfile, "a+");
flock($fd, LOCK_EX);  # Lock
if(!$fd)
{
	if ($fdb)
	{
		fputs($fdb, "Could not open $logfile for writing\n");
		fputs($fdb, $_SERVER["REMOTE_ADDR"]." - - $time \"GET ".$FORM['loc']." ".$_SERVER["SERVER_PROTOCOL"]."\" 200 - \"".$FORM['ref']."\" \"".$_SERVER["HTTP_USER_AGENT"]."\"\n");
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
	fputs($out, $_SERVER["REMOTE_ADDR"]." - - $time \"GET ".$FORM['loc']." ".$_SERVER["SERVER_PROTOCOL"]."\" 200 - \"".$FORM['ref']."\" \"".$_SERVER["HTTP_USER_AGENT"]."\"\n");
}


flock($fd, LOCK_UN);  # Unlock
fclose($fd);
fclose($fdb);

?>
