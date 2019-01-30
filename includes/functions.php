<?php
/**

  HC-Media Universal Functions  v 0.1 - 05.01.2011
  ----------------------------
  
  selectDB($values, $table, $where = null, $order = null, $limit = null)  										//F&uuml;hrt eine Datenbankabfrage aus
  insertDB($table, $felder, $values)                                      										//F&uuml;hrt einen Datenbankinsert aus
  deleteDB($table, $where)                                                										//L&ouml;scht Datens&auml;tze aus der Datenbank
  updateDB($table, $values, $where)                                       										//F&uuml;hrt ein Update auf eine Tabelle aus
  isActive($menupunkt);                                                   										//Überpr&uuml;fen ob ein Men&uuml;punkt aktiv ist
  ftp_upload($server, $user, $pw, $remote_file, $local_file);             										//Datei auf FTP-Server laden
  zip_entpacken($file, $extractTo);                                       										//zip Datei entpacken
  check_email($email)                                                     										//BOOLEAN; &uuml;berpr&uuml;ft eine E-Mail Adresse auf Richtigkeit
  include_analytics($tracking_id)                                         										//Einbinden von Google Analytics
  remove_folder($dir)													  										//Löscht einen Ordner inkl. aller Unterordner und Unterdateien
  mkthumb($img_src, $img_side  = "h", $img_px = "100", $folder_scr = "pictures", $des_src = "thumbs")			//Erzeugt ein proportionales Thumbnail
  echoInput($type, $name, $class = NULL, $label = NULL, $label_class = NULL, $id = NULL, $minlength = NULL, $maxlength = NULL) //Erzeugt ein Form-Input

  
  
*/

function selectDB($values, $table, $where=NULL, $order=NULL, $limit=NULL, $debug=FALSE)
{
  $sql = "SELECT $values FROM $table";
  if($where != NULL) $sql .= " WHERE $where";
  if($order != NULL) $sql .= " ORDER BY $order";
  if($limit != NULL) $sql .= " LIMIT $limit";
  if($debug != FALSE) echo $sql."<br />\n";
  $output = $sql;
  $sql = mysql_query($sql) or die("Datenbankabfrage konnte nicht ausgef&uuml;hrt werden. <br /> $output");
  
  if($sql) return $sql;
}
//----------------------------------------------------------------------------------------------------------------

function insertDB($table, $felder, $values, $debug=FALSE)
{
  $sql = "INSERT INTO $table ($felder) VALUES ($values)";
  if($debug != FALSE) echo $sql."<br />\n";
  $sql = mysql_query(utf8_decode($sql)) or die("Datenbankinsert konnte nicht ausgef&uuml;hrt werden.");
  
  if($sql) $output = "Datenbankinsert wurde ausgef&uuml;hrt.";
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function deleteDB($table, $where, $debug=FALSE)
{
  $sql = "DELETE FROM $table WHERE $where";
  if($debug != FALSE) echo $sql."<br />\n";
  $sql = mysql_query($sql) or die("Datensatz konnte nicht gel&ouml;scht werden.");
  
  if($sql) $output = "Datensatz wurde gel&ouml;scht.";
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function updateDB($table, $values, $where, $debug=FALSE)
{
  $sql = "UPDATE $table SET $values WHERE $where";
  if($debug != FALSE) echo $sql."<br />\n";
  $sql = mysql_query(utf8_decode($sql)) or die("Datensatz konnte nicht aktualisiert werden.");
  
  if($sql) $output = "Datensatz wurde aktualisiert.";
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function isActive($menupunkt)
{
	if(basename($_SERVER['PHP_SELF'], ".php") == $menupunkt) return true;
	return false;
}
//----------------------------------------------------------------------------------------------------------------

function ftp_upload($server, $user, $pw, $remote_file, $local_file)
{
  $conn_id = ftp_connect($server);
  $login_result = ftp_login($conn_id, $user, $pw);
	if ((!$conn_id) || (!$login_result)) die("Fehler beim herstellen der FTP-Verbindung.");

  if (ftp_put($conn_id, $remote_file, $local_file, FTP_BINARY)) $output = "Erfolgreich hochgeladen.\n";
  else $output = "Ein Fehler trat beim Hochladen von $remote_file.\n";

  ftp_close($conn_id);
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function zip_entpacken($file, $extractTo, $delete = TRUE)
{
  $zip = new ZipArchive;

  if ($zip->open($file) === TRUE) {
    $num = $zip->numFiles;
    $zip->extractTo($extractTo);
    $zip->close();

    $n_num = $num-$zip->numFiles;
    $output = "Es wurden ".$n_num." Files entpackt.\n";
    if($delete) unlink($file);
  } 
  else $output = "Beim entpacken des Files $file ist ein Fehler aufgetreten.\n";
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function check_email($email) 
{
  if( (preg_match('/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/', $email)) || (preg_match('/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/',$email)) ) {
    $host = explode('@', $email);
    if (!function_exists('checkdnsrr')) {
      function checkdnsrr($host, $type = '') {
        if(!empty($host)) {
          if($type == '') $type = "MX";
          @exec("nslookup -type=$type $host", $output);
          while(list($k, $line) = each($output)) {
            if(eregi("^$host", $line)) {
                return true;
            }
          }
          return false;
        }
      }
    }

    if(checkdnsrr($host[1].'.', 'MX') ) return true;
    if(checkdnsrr($host[1].'.', 'A') ) return true;
    if(checkdnsrr($host[1].'.', 'CNAME') ) return true;
  }
  return false;
}
//----------------------------------------------------------------------------------------------------------------

function include_analytics($tracking_id)
{
  $output ="
  <script type=\"text/javascript\">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '$tracking_id']);
    _gaq.push(['_setDomainName', 'none']);
    _gaq.push(['_setAllowLinker', true]);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>";
  return $output;
}
//----------------------------------------------------------------------------------------------------------------

function remove_folder($dir) {
    foreach (glob($dir) as $file) {
        if (is_dir($file)) {
            remove_folder("$file/*");
            rmdir($file);
        } else {
            unlink($file);
        }
    }
	return true;
}
//----------------------------------------------------------------------------------------------------------------

function mkthumb($img_src, $img_side  = "h", $img_px = "100", $folder_scr = "pictures", $des_src = "thumbs")
{
	list($src_width, $src_height, $src_typ) = getimagesize($folder_scr."/".$img_src);
	$pic_pfad = explode("/", $img_src);
	$only_picname = $pic_pfad[sizeof($pic_pfad)-1];

	$faktor = $src_width / $src_height;
	if($img_side = "h") //Querformat od. Quadrat
	{
	  $new_image_height = $img_px * $faktor;
	  $new_image_width = $img_px;
	}
	if($img_side = "w") //Hochformat
	{
	  $new_image_width = $img_px * $faktor;
	  $new_image_height = $img_px;
	}
	
	if($src_typ == 1)     // GIF
	{
	  $image = imagecreatefromgif($folder_scr."/".$img_src);
	  $new_image = imagecreate($new_image_width, $new_image_height);
	  imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width,$new_image_height, $src_width, $src_height);
	  imagegif($new_image, $des_src."/".$img_src, 100);
	  imagedestroy($image);
	  imagedestroy($new_image);
	  return true;
	}
	elseif($src_typ == 2) // JPG
	{
	  $image = imagecreatefromjpeg($folder_scr."/".$img_src);
	  $new_image = imagecreatetruecolor($new_image_width, $new_image_height);
	  imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width,$new_image_height, $src_width, $src_height);
	  imagejpeg($new_image, $des_src."/".$only_picname, 100);
	  imagedestroy($image);
	  imagedestroy($new_image);
	  return true;
	}
	elseif($src_typ == 3) // PNG
	{
	  $image = imagecreatefrompng($folder_scr."/".$img_src);
	  $new_image = imagecreatetruecolor($new_image_width, $new_image_height);
	  imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_image_width,$new_image_height, $src_width, $src_height);
	  imagepng($new_image, $des_src."/".$img_src);
	  imagedestroy($image);
	  imagedestroy($new_image);
	  return true;
	}
	else
	{
	  return false;
	}
}
//----------------------------------------------------------------------------------------------------------------

function echoInput($type, $name, $value = NULL, $class = NULL, $label = NULL, $label_class = NULL, $newline = TRUE, $id = NULL, $minlength = NULL, $maxlength = NULL)
{
	$input = "";
	if($label_class != NULL) $lclass = " class='".$label_class."'";
	else $lclass = "";
	
	if($class != NULL) $class = " class='".$class."'";
	else $class = "";
	
	if($id != NULL) $id = " id='".$id."'";
	else $id = "";
	
	if($minlength != NULL) $minlength = " minlength='".$minlength."'";
	else $minlength = "";
	
	if($maxlength != NULL) $maxlength = " maxlength='".$maxlength."'";
	else $maxlength = "";
	
	if($value != NULL) $value = " value='".$value."'";
	else $value = "";
	
	if($label != NULL) $input .= "<label".$lclass.">".$label."</label>";
	$input .=	"<input type='".$type."' name='".$name."'".$value.$class.$id.$minlength.$maxlength." />";
	if($newline) $input .= "<br />";
	$input .= "\n";
	return $input;
}
//----------------------------------------------------------------------------------------------------------------

function echoTextarea($name, $value = NULL, $class = NULL, $label = NULL, $label_class = NULL, $newline = TRUE, $id = NULL, $minlength = NULL, $maxlength = NULL)
{
	$textarea = "";
	if($label_class != NULL) $lclass = " class='".$label_class."'";
	else $lclass = "";
	
	if($class != NULL) $class = " class='".$class."'";
	else $class = "";
	
	if($id != NULL) $id = " id='".$id."'";
	else $id = "";
	
	if($minlength != NULL) $minlength = " minlength='".$minlength."'";
	else $minlength = "";
	
	if($maxlength != NULL) $maxlength = " maxlength='".$maxlength."'";
	else $maxlength = "";
	
	if($value != NULL) $value = $value;
	else $value = "";
	
	if($label != NULL) $textarea .= "<label".$lclass.">".$label."</label>";
	$textarea .=	"<textarea name='".$name."'".$class.$id.$minlength.$maxlength.">".$value."</textarea>";
	if($newline) $textarea .= "<br />";
	$textarea .= "\n";
	return $textarea;
}
//----------------------------------------------------------------------------------------------------------------

function echoSelect($name, $class = NULL, $table, $values, $where=NULL, $order=NULL, $limit=NULL, $selected = NULL, $multiple = FALSE, $label = NULL, $label_class = NULL, $newline = TRUE, $id = NULL)
{
	$options	= selectDB($values, $table, $where, $order, $limit);
	$select 	= "";
	
	if($label_class != NULL) $lclass = " class='".$label_class."'";
	else $lclass = "";
	
	if($class != NULL) $class = " class='".$class."'";
	else $class = "";
	
	if($id != NULL) $id = " id='".$id."'";
	else $id = "";
	
	//if($selected != NULL) $selected = "'".$selected."'";
	
	if($multiple) 
	{
		$multiple = " multiple='multiple' size='5'";
		$name = $name."[]";
	}
	else $multiple = "";
	
	if($label != NULL) $select .= "<label".$lclass.">".$label."</label>";
	$select .= "<select name='".$name."'".$class.$id.$multiple." />\n";
	$select .= "<option value='0'>---</option>";
	
	while($option = mysql_fetch_array($options))
	{
		$value = $option[0];
		$name = $option[1];
		$sel = "";
		if(is_array($selected)){
			if(in_array($value, $selected)){
				$sel = " selected";
			}
		} elseif($selected == $value) {
			$sel = " selected";
		}
		
		$select .= "<option value='".$value."'".$sel.">".$name."</option>"."\n";
	}
	
	if($newline) $select .= "</select><br />\n";
	else $select .= "</select>\n";
	return $select;
}
//----------------------------------------------------------------------------------------------------------------

function echoYesNoRadio($name, $class = NULL, $label = NULL, $label_class = NULL, $newline = TRUE, $select = NULL)
{
	$radio = "";
	if($label_class != NULL) $lclass = " class='".$label_class."'";
	else $lclass = "";
	
	if($class != NULL) $class = " class='".$class."'";
	else $class = "";
	
	if($select == 1) {
		$checkedY = " checked";
		$checkedN = "";
	} else {
		$checkedY = "";
		$checkedN = " checked";
	}
	
	if($label != NULL) $radio .= "<label".$lclass.">".$label."</label>";
	$radio .=	"<input type='radio' value='1' name='".$name."'".$class.$checkedY." > Yes <input type='radio' value='0' name='".$name."'".$class.$checkedN." > No";
	if($newline) $radio .= "<br />";
	$radio .= "\n";
	return $radio;
}

//----------------------------------------------------------------------------------------------------------------

function echoYesNo($i)
{
	if($i == 1){
		return "Yes";
	} else {
		return "No";
	}
}
//----------------------------------------------------------------------------------------------------------------

// to change the text of the table field e.g. AT, DE to ("AT", "DE")
// for easy comparison in sql statements
function addQuotesForSQL($array)
{
  $output_string = "";
  for($i = 0; $i < sizeof($array); $i++)
  {
    $output_string .= '"'.trim($array[$i]).'"';
    if($i < sizeof($array)-1) $output_string .= ",";
  }
  return $output_string;
}

//----------------------------------------------------------------------------------------------------------------

function calcBruttoPreis($preis, $steuer)
{
	return round(($preis/100)*(100+$steuer), 2);
}

?>