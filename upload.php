<HTML>
  <TITLE>
    File upload
  </TITLE>
<body>

<B>Image upload</b>



<FORM enctype="multipart/form-data" action="<?PHP echo $PHP_SELF ?>" method="post">

<!-- "MAX_FILE_SIZE" determines the biggest size an uploaded file can occupy -->

  <INPUT type="hidden" name="MAX_FILE_SIZE" value="10485760">
<FONT size=2 face="Tahoma">Send this file:</FONT>
  <INPUT name="userfile" type="file">
  <INPUT type="submit" name="submit" value="Send File">
</FORM>

</BODY>

<?PHP

// $userfile - The temporary filename in which the uploaded file was stored on the server machine.
// $userfile_name - The original name or path of the file on the senders system.
// $userfile_size - The size of the uploaded file in bytes.
// $userfile_type - The mime type of the file if the browser provided this information. An example would be "image/gif".

// copy to this directory

$dir="./upload/";


// copy the file to the server

if (isset($submit)){

copy($userfile,$dir.$userfile_name);

if (!is_uploaded_file ($userfile)){

echo "
<b>$userfile_name</b> couldn't be copied!";
}
}

// check whether it has been uploaded

if (is_uploaded_file ($userfile)){

echo ("
  <FONT size=2 face=\"Tahoma\">
  <b>$userfile_name</b> copied succesfully!
  url: <A href=\"http://backupteam.gamepoint.net/smartie/upload/$userfile_name\">http://backupteam.gamepoint.net/smartie/upload/$userfile_name</A>");
}

?>

</html>