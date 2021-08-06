<?php session_start(); global $filecount; ?>
<!DOCTYPE html>
<html dir=rtl>
<head>
<title>مسودة اونلاين</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link rel="stylesheet" href="style.css" >
<script src="nicEdit.js" type="text/javascript"></script>
<script type="text/javascript">
bkLib.onDomLoaded(nicEditors.allTextAreas);
</script>

</head>
<body><center><div style="margin:6px">
<?php

mb_internal_encoding('UTF-8'); 
//--------------------- setting up ----
if (!is_dir("files")){ mkdir("files",0777,true);	}
if (!is_dir("publish")){ mkdir("publish",0777,true);	}
if (isset($_GET['action']) && $_GET['action']=="exit") { unset($_SESSION['userfolder']); 
echo "<script>close();</script>";  } 

//------------ publish ------------
if (isset($_GET['publish'])) { 	publish($_GET['publish']); }
	function publish($thefile){
		deleteoldfiles(60);
		$ourfile = $_SESSION['path'].$thefile;	
		
		if (file_exists($ourfile)) {	
      $file_content .= file_get_contents($ourfile); }
		 $file_content = bzdecompress($file_content);
		 $file_content ='<link rel="stylesheet" href="../print.css" >'.$file_content;
		$file_content .= '<center><div class="A4">';
		$file_content .= '</div>';
		//$file_content=lighten($file_content);
			$thefile = md5($thefile);
		$thefile  = $thefile.".html"; 
		$thefile = "publish/".$thefile;
	$file_handle = fopen($thefile,"w");
	 fwrite($file_handle,"\xEF\xBB\xBF".$file_content);
	
	fclose($file_handle);
	if (!headers_sent()) {
	header("location:$thefile");}}
	////-------------------------------- end publish ------------
	if (isset($_POST['myfolder'])) {
			RemoveEmptySubFolders("files/");
if(!empty($_POST['uzr'])){
	unset($_SESSION['userfolder']);
	$userfolder = $_POST['uzr'];} else {$userfolder = $_POST['myfolder'];}
			
	if (!is_dir($userfolder)){ mkdir("files/".$userfolder,0777,true);	}	
	$_SESSION['userfolder']=$userfolder;
	$_SESSION['path']= "files/".$_SESSION['userfolder']."/";
	header("location:index.php"); }
///--------------------------------------------
if (!isset($_SESSION['userfolder'])) { ?>
	<div class="well well-sm" style="width:32%; margin-top: 65px;">
	<form action="" METHOD="POST">
<br><input type=text name="myfolder" pattern="[A-Za-z0-9_]{6,}"  required autocomplete="off" >
<br><br><input type=submit value="Open/Create document"></form></div>
	
<?php  } else { ?>
	
	<a href=?action=exit style="float:left" class="xzt"><img src="images/qt.png"></a> 
	  <div class='menu' id=menu ><table><tr><td><br><a href='index.php'>
	  <img src='images/new.png'> جديد </a><br><br></td></tr>
 <?php 

$files = "files/".$_SESSION['userfolder']."/*"; 
$filecount = 0;
foreach (glob($files ) as $filename) { 
$filecount++;
$basefilename = basename($filename);
//$nicename = substr(basename($basefilename), 0, -5);	
$nicename  = str_replace('_', ' ', $basefilename );
 
  echo "<tr><td><a href='index.php?file=$basefilename' >$nicename</a></td>";
   //echo "<td><a href='a4.php?file=$basefilename' >print</a></td>";
	echo "<td><a href='index.php?killthis=$basefilename' onclick=\"javasciprt: return confirm('تأكيد الحذف?')\"><img src='images/delete.png'></a></td>";
  echo "<td><a href='index.php?publish=$basefilename' target='_blank' title='انشاء رابط مشاركة'> <img src='images/share.png'> </a></td></tr>";
  }   
 echo "</table></div>";

   	if (!isset($_GET['file'])) { 
	if ($filecount > 49){ echo "<h4>نأسف ، لا يمكنك انشاء ملف جديد ، تجاوزت عدد الملفات المسموح به  في هذه الخدمة وهو 50 ملف</h4>"; exit();}
	?>
		<div class=editor1>
	    <form action="" method="POST">
        <input type="text" name="filename" value="" autofocus  required pattern="[ ء-يA-Za-z0-6]{4,}" placeholder="اسم الملف" maxlength=60>
		<input type="submit" value="حفظ واغلاق" name=savefile ><br>
       <textarea    name="notes" id=notes style='width:100%;height:auto'></textarea>    
    </form></div>
		<?php 	} else {			
			{ 			
		$ourfile  = $_GET['file'];
	$ourfile = $_SESSION['path'].$ourfile;
		editfile($ourfile); } }
//-----------working with user data-------------------------
if (isset($_SESSION['userfolder'])) 
	{
		if (isset($_GET['killthis']) ) { 
	unlink ($_SESSION['path'].$_GET['killthis']); 
	 header("location:index");}	}
	//------------------------------
	   
if (isset($_SESSION['userfolder']) && isset($_POST['notes'])) {
	$ourfile = $_POST['filename'];
	$content = lighten($_POST['notes']);	
$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);

$content = str_replace('</w:r></w:p>', "\r\n", $content);
	savefile ($ourfile ,$content);
	   } }
function savefile($thefile,$content){
		$thefile  = trim($thefile );
	$thefile = str_replace(' ', '_', $thefile);	
	$thefile = mb_strtolower($thefile);

		$thefile = $_SESSION['path'].$thefile;
	$file_handle = fopen($thefile,"w");
	$hastr = stristr($content, '</tr>');
$hastbl = stristr($content, '</table>');
if(!empty($hastbl)) { $content = str_replace('<table ', '<table class=innertable ', $content);}
if(!empty($hastr) && empty($hastbl)) {$content= "<table class=innertable>".$content."</table>";}
		$content = bzcompress($content);
	fwrite($file_handle,$content);
	fclose($file_handle);
	$thefile= basename($thefile);
	 echo "<script>history.back();</script>";	}

function editfile ($thefile){
	    if (file_exists($thefile)) {	
        $file_handle = fopen($thefile, "r");
        $file_content = fread($file_handle, filesize($thefile));
        fclose($file_handle); $file_content = bzdecompress($file_content);  ?>
	<div class="editor"> 
    <form action="" method="POST">
	<?php 
		$nicename  = str_replace('_', ' ', $thefile);
		echo basename($nicename); ?>
        <input type="hidden" name="filename" value="<?php echo basename($thefile); ?>">
		<input type="submit" value="حفظ التعديل"  class="svch" >
       <center> <textarea   style='width:100%;height:auto' id=notes  name="notes" ><?php echo $file_content; ?></textarea>     
		</form>
		</div>

		<?php 		} 	}
//-------------------
function deleteoldfiles($days){
$path = 'publish/';  
// Open the directory  
if ($handle = opendir($path))  
{  
    // Loop through the directory  
    while (false !== ($file = readdir($handle)))  
    {  
        // Check the file we're doing is actually a file  
        if (is_file($path.$file))  
        {  
            // Check if the file is older than X days old  
            if (filemtime($path.$file) < ( time() - ( $days * 24 * 60 * 60 ) ) )  
            {  
		
                // Do the deletion  
                unlink($path.$file);  
            }  
        }  
    }  
} 
}
//-----
function RemoveEmptySubFolders($path)
{
  $empty=true;
  foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file)
  {
     if (is_dir($file))
     {
        if (!RemoveEmptySubFolders($file)) $empty=false;
     }
     else
     {
        $empty=false;
     }
  }
  if ($empty) rmdir($path);
  return $empty;
}
//---------------
function lighten($html){
	
$html = preg_replace('<style(\w+).*?>', '<$1>', $html); 
$html  = str_replace('Arial', 'amiri', $html);
	return $html;
}

?>

</div>
</body></html>