<?php
namespace App\Models;

class Utilities extends Model {

	public function getCURLData($url) {

		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);

		return $data;

	}

	public function drawArray($d) {

		$aux = array();
 	 	foreach ($d as $arr) {
		  foreach ($arr as $k) array_push($aux, $k);
 		}

		if(is_array($aux[0])) {
			$seeker = $aux[0];
			$len = count($aux[0]);
		}else {
			$seeker = $aux;
			$len = count($aux);
		}

		$i = 0;
		$out = '[';
		foreach ($seeker as $k){
				if($i < $len - 1) $out .= '"'.$k.'",';
				else $out .= '"'.$k.'"';
				$i ++;
		}
		$out .= ']';

		return $out;

	}

	public function downloadFile($file) {
		
  	if (file_exists($file)) {
 
 	    $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $file);
      finfo_close($finfo);
  
      header('Content-Description: File Transfer');
      header('Content-Type: '.$mime);
      header("Content-Transfer-Encoding: Binary");
      header('Content-Disposition: attachment; filename="'.basename($file).'"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);
      exit;

  	}

	}

	public function downloadFolder($source, $destination) {

		$name = pathinfo($source)['basename'];
		
		/*if($name == '_uploads') $file = 'uploads.tar';
		else*/ $file = $name.'.tar.gz';

		$val = shell_exec("tar -zcvf $destination/$file -C $source/../ $name 2>&1 --exclude '.*'");

		if(!isset($val)) return false;

		$path = $destination.'/'.$file;

		header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header("Content-Transfer-Encoding: Binary");
    header('Content-Disposition: attachment; filename="'.$file.'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    ob_clean();
    flush();
    readfile($path);
    unlink($path);
		exit;
		
	}

	public function downloadFromApi($destination, $id, $type, $name, $pdb) {

		set_time_limit(0);
		$pdbdownloaded = fopen ($destination.'/'.$name, 'w+');

		if(isset($pdb)) $ch = curl_init(sprintf($this->global['api']['ligfile'], $type, $id, $pdb));
		else $ch = curl_init(sprintf($this->global['api']['pdbfile'], $type, $id));

		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FILE, $pdbdownloaded); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch); 
		curl_close($ch);
		return fclose($pdbdownloaded);

	}

	public function getMoment() {
  	
		return date("Y/m/d*H:i:s");
 	
	}

	public function startsWith($haystack, $needle) {

     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
	
	}

	public function getExtension($filename) {
	
		return pathinfo($filename, PATHINFO_EXTENSION);	

	}

	public function getHumanDate($date) {
	
		return date("Y/m/d H:i" , $date);
	}

	public function getSize($bytes) {

		if ($bytes >= 1073741824) {
			$bytes = (number_format($bytes / 1073741824, 2) + 0). ' GB';
		}
		elseif ($bytes >= 1048576) {
			$bytes = (number_format($bytes / 1048576, 2) + 0) . ' MB';
		}
		elseif ($bytes >= 1024) {
			$bytes = (number_format($bytes / 1024, 2) + 0). ' KB';
		}
		elseif ($bytes >= 0) {
			$bytes = ($bytes + 0). ' B';
		}
		
		return $bytes;

	}

	public function downloadXML($rest) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $rest);
		$fileContents = curl_exec($ch);
		curl_close($ch);

		$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);

		$fileContents = trim(str_replace('"', "'", $fileContents));

		$simpleXml = simplexml_load_string($fileContents);

		$json = json_encode($simpleXml);

		$json = str_replace("@","",$json);

		$json = json_decode($json);

		return $json;

	}

	
}

