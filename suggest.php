<HTML>
<HEAD>
  <title>LCD Smartie - by BasieP - suggest.php</title>
  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
</HEAD>

<BODY background="white.jpg" bgcolor="#DEDEDE" text="#000000" link="#006000" alink="#006000" vlink="#003000">

<B><U><P><BR><FONT FACE=Tahoma SIZE=4 COLOR="#ff0000">Suggestions</P></B></U></FONT>

<CENTER>
<?php
  $file = fopen ("suggestions.dat", "r");
  while (feof ($file) == 0) {
    $nrtopics ++;
    $author[$nrtopics] = fgets ($file, 4096);
    $message[$nrtopics] = fgets ($file, 4096);
    $postdate[$nrtopics] = fgets ($file, 4096);
    $message[$nrtopics] = stripslashes($message[$nrtopics]);
    $author[$nrtopics] = stripslashes($author[$nrtopics]);
  }
  fclose($file);
  if ($send) {
    $message1 = preg_replace ("'\n'", "...0return0...", $message1);
    $search = array ("'<script[^>]*?>.*?</script>'si",                // Strip out scripts
                     "'<[\/\!]*?[^<>]*?>'si",                         // Strip out html tags
                     "'([\r\n])[\s]+'",                               // Strip out white space
                     "'&(quot|#34);'i",                               // Replace html entities
                     "'&(amp|#38);'i", 
                     "'&(lt|#60);'i",
                     "'&(gt|#62);'i",
                     "'&(nbsp|#160);'i",
                     "'&(iexcl|#161);'i",
                     "'&(cent|#162);'i",
                     "'&(pound|#163);'i",
                     "'&(copy|#169);'i",
                     "'  'i",
                     "'&#(\d+);'e");                                  // evaluate as php
    $replace = array ("",
                      "",
                      "\\1",
                      "\"",
                      "&",
                      "<",
                      ">",
                      " ",
                      chr(161),
                      chr(162),
                      chr(163),
                      chr(169),
                      " ",
                      "chr(\\1)");
    $author1 = preg_replace ($search, $replace, $author1);
    $message1 = preg_replace ($search, $replace, $message1);
    $message1 = preg_replace ("'...0return0...'", "<br>", $message1);

    if (($author1 == "") and ($passwd == "")) {
      echo ("<br><center><font size=3 color=#FF0000 face=Tahoma>please give yourself a nickname!<BR><BR></font></center>");
      $spam = 1;
    }
    if (($message1 == "") and ($passwd == "")) {
      echo ("<br><center><font size=3 color=#FF0000 face=Tahoma>You have to fill in a suggestion!<BR><BR></font></center>");
      $spam = 1;
    }
    if (($delete > 0) and ($passwd != "")) {
      $spam = 1;
      if ($passwd == "xxqjbasie23") {
        echo ('
          <font size=3 face="Tahoma">
        ');
        echo("  
            suggestion nr $delete is deleted.<BR>
            number of suggestions = $nrtopics-1<BR> 
          </FONT>
        ");
        $file = fopen ("suggestions.dat", "w");
        for ($j = 1; $j <= $delete-1; $j++) {
          fputs ($file, "$author[$j]");
          fputs ($file, "$message[$j]");
          fputs ($file, "$postdate[$j]");
        }
        for ($j = $delete+1; $j <= $nrtopics-1; $j++) {
          fputs ($file, "$author[$j]");
          fputs ($file, "$message[$j]");
          fputs ($file, "$postdate[$j]");
        }
        fclose($file);
        $nrtopics =0;
        $file = fopen ("suggestions.dat", "r");
        while (feof ($file) == 0) {
          $nrtopics ++;
          $author[$nrtopics] = fgets ($file, 4096);
          $message[$nrtopics] = fgets ($file, 4096);
          $postdate[$nrtopics] = fgets ($file, 4096);
          $message[$nrtopics] = stripslashes($message[$nrtopics]);
          $author[$nrtopics] = stripslashes($author[$nrtopics]);
        }
        fclose($file);
        $delete = 0;
      } else {
        echo ("<br><center><font size=3 color=#FF0000 face=Tahoma>password incorrect!<BR><BR></font></center>");
      }
      $passwd = "";
    }
    if ($spam == 0) {
      $file = fopen ("suggestions.dat", "a");
      fputs ($file, "$author1\n");
      fputs ($file, "$message1\n");
      fputs ($file, date("d-m-y G:i")."\n");
      fclose($file);

      $nrtopics =0;
      $file = fopen ("suggestions.dat", "r");
      while (feof ($file) == 0) {
        $nrtopics ++;
        $author[$nrtopics] = fgets ($file, 4096);
        $message[$nrtopics] = fgets ($file, 4096);
        $postdate[$nrtopics] = fgets ($file, 4096);
        $message[$nrtopics] = stripslashes($message[$nrtopics]);
        $author[$nrtopics] = stripslashes($author[$nrtopics]);
      }
      fclose($file);
    }
  }
    for ($i = 1; $i <= $nrtopics-1; $i++) {
    echo ('
   <table width="80%" border=1 cellspacing=1 cellpadding=0 bordercolor="#006000">
     <tr> 
        <td bgcolor="#003000">
          <div align="center">
            <font color="#FFFFFF" face=Arial>
    ');
    echo ("
              $author[$i] wrote this on $postdate[$i]<BR>
    ");
    echo ('

            </font>
          </div>
        </TD>
      </TR>
      <TR>
        <TD bgcolor="#FFFFFF">
          <div align="center">
            <font color="#000000" face=Arial>
    ');
    echo ("
              $message[$i]<BR>
              <FONT size=1><A href=suggest.php?delete=$i>delete</A></FONT><BR>
    ");
    echo ('
            </font>
          </div>
        </td>
      </tr>
    </table>
    <BR><BR>
    ');
    }

    echo ("
    <form action=suggest.php method=post>
    ");
    if ($delete > 0) {
      echo ("
      <input type=hidden name=send value=yes>
      <input type=hidden name=delete value=$delete>
      <font face=Tahoma color=#000000>Password:<br></font><input type=password name=passwd><br><br>
      ");
    } else {
      echo ("
      <font face=Tahoma color=#000000>Name:<br></font><input type=Text name=author1><br><br>
      <font face=Tahoma color=#000000>Suggestion:<br></font><textarea name=message1 rows=8% cols=40% wrap=PHYSICAL></textarea><br><br>
      <input type=hidden name=send value=yes>
      <input type=hidden name=delete value=yes>
      <input type=hidden name=passwd value=>
      ");
    }
    echo ("
      <input type=submit value='Post'>
    </form>
    ");
?>
  </CENTER>
</BODY>
</HTML>