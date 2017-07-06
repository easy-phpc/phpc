<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

class FileSystem
{
  function normalize(&$filename)
  {
    $filename=str_replace("\\","/",$filename);
    if($filename=="" || $filename[0]!="/") return;
    if(defined("PhpcRoot")) $filename=PhpcRoot.substr($filename,1);
    $filename=$_SERVER["DOCUMENT_ROOT"].$filename;
  }

  function localize(&$filename)
  {
    $filename=str_replace("\\","/",$filename);
    $path=str_replace("\\","/",$_SERVER["DOCUMENT_ROOT"]);
    if(defined("PhpcRoot")) $path.=rtrim(PhpcRoot,"/");
    if(!phpcstrncasecmp($filename,$path,phpcstrlen($path)))
      $filename=phpcsubstr($filename,phpcstrlen($path));
  }

  function validateFilename($filename)
  {
    $extension=phpcstrtolower($this->getFileExtension($filename));
    $filename=str_replace("'","",$this->getFileName($filename));
    $filename=preg_replace("{[^A-Za-z\d\-]}"," ",$filename);
    $filename=preg_replace("{ +}","_",trim($filename));
    if($filename=="") $filename="noname";
    if(!preg_match("{^\.[A-Za-z\d]+\$}",$extension)) $extension="";
    return $filename.$extension;
  }

  function incrementFilename($filename)
  {
    $filename=rtrim($filename,"/");
    $extension=$this->getFileExtension($filename);
    $filename=substr($filename,0,strlen($filename)-strlen($extension));
    return incrementIdentifier($filename).$extension;
  }

  function openFile($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return (string)@file_get_contents($filename);
  }

  function openFileTimeout($filename, $timeout=FileSystemTimeout, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return (string)@file_get_contents_timeout($filename,$timeout);
  }

  function saveFile($filename, $content, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return @file_put_contents($filename,$content)==strlen($content);
  }

  function renameFile($oldfilename, $newfilename, $normalize=true)
  {
    if($normalize) $this->normalize($oldfilename);
    if($normalize) $this->normalize($newfilename);
    return @rename($oldfilename,$newfilename);
  }

  function deleteFile($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return @unlink($filename);
  }

  function isFileExists($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return file_exists(rtrim($filename,"/"));
  }

  function getFileName($filename)
  {
    $filename=basename(rtrim($filename,"/"));
    $index=strrpos($filename,".");
    return $index!==false?substr($filename,0,$index):$filename;
  }

  function getFileExtension($filename)
  {
    $filename=basename(rtrim($filename,"/"));
    $index=strrpos($filename,".");
    return $index!==false?substr($filename,$index):"";
  }

  function getFileHash($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return phpchash($filename,false,false,true);
  }

  function getFileSize($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    return (int)@filesize($filename);
  }

  function getImageSize($filename, $normalize=true)
  {
    if($normalize) $this->normalize($filename);
    $size=@getimagesize($filename);
    if(!is_array($size) || !isset($size[0]) || !isset($size[1])) return false;
    return array("width"=>$size[0],"height"=>$size[1]);
  }

  function createFolder($folder, $attrs=FolderCreateAttributes, $normalize=true)
  {
    if($normalize) $this->normalize($folder);
    $umask=@umask(0);
    $result=@mkdir(rtrim($folder,"/"),$attrs);
    @umask($umask);
    return $result;
  }

  function deleteFolder($folder, $normalize=true)
  {
    if($normalize) $this->normalize($folder);
    return @rmdir(rtrim($folder,"/"));
  }

  function getFolder($folder, $extensions=false, $normalize=true)
  {
    if($normalize) $this->normalize($folder);
    $folder=rtrim($folder,"/")."/";
    if($extensions!==false) {
      if(!is_array($extensions)) $extensions=explodeSmart(",",$extensions);
      $extensions=array_map("phpcstrtolower",$extensions);
    }
    $result=array();
    if(!$resource=@opendir($folder)) return $result;
    while(($filename=@readdir($resource))!==false) {
      if(!is_file($folder.$filename)) continue;
      $extension=phpcstrtolower($this->getFileExtension($filename));
      $match=$extensions===false || in_array($extension,$extensions);
      if($match) $result[]=$filename;
    }
    @closedir($resource);
    sort($result);
    return $result;
  }

  function getSubfolders($folder, $normalize=true)
  {
    if($normalize) $this->normalize($folder);
    $folder=rtrim($folder,"/")."/";
    $result=array();
    if(!$resource=@opendir($folder)) return $result;
    while(($filename=@readdir($resource))!==false) {
      if(trim($filename,".")=="" || !is_dir($folder.$filename)) continue;
      $result[]=$filename;
    }
    @closedir($resource);
    sort($result);
    return $result;
  }

  function sanitizeFilesRecursive($files, $fragment, $fields, $path=array())
  {
    $result=array();
    if(is_array($fragment)) foreach($fragment as $key=>$value) {
      $newpath=$path;
      $newpath[]=$key;
      $result[$key]=$this->sanitizeFilesRecursive($files,$value,$fields,$newpath);
    }
    else foreach($fields as $field) {
      $value=$files[$field];
      foreach($path as $step) if(is_array($value)) $value=ifset($value[$step]);
      $result[$field]=$value;
    }
    return $result;
  }

  function sanitizeFiles($files)
  {
    if(!is_array($files)) return $files;
    $keys=array("name","type","tmp_name","error","size");
    if(count(array_diff($keys,array_keys($files)))) {
      foreach($files as $key=>$value) $files[$key]=$this->sanitizeFiles($value);
      return $files;
    }
    $fields=array_keys($files);
    return $this->sanitizeFilesRecursive($files,$files[$fields[0]],$fields);
  }

  function getUploadInformation($name)
  {
    $result=$this->sanitizeFiles($_FILES);
    if(!is_array($name)) $name=array($name);
    foreach($name as $part) {
      if(!is_array($result) || !isset($result[$part])) return false;
      $result=$result[$part];
    }
    if(!is_array($result)) return false;
    foreach($result as $key=>$value) if(is_array($value)) return false;
    $keys=array("name","type","tmp_name","error","size");
    if(count(array_diff($keys,array_keys($result)))) return false;
    return $result;
  }

  function isUploadAttempt($name)
  {
    $upload=$this->getUploadInformation($name);
    return $upload && $upload["error"]!=UPLOAD_ERR_NO_FILE;
  }

  function getUploadedFile($name)
  {
    $upload=$this->getUploadInformation($name);
    if(!$upload || $upload["error"]) return false;
    if(!is_uploaded_file($upload["tmp_name"])) return false;
    $filename=trim(stripSlashesSmart($upload["name"]));
    if(defined("PhpcUploadFolder")) {
      $tempname=PhpcUploadFolder.basename($upload["tmp_name"]);
      $this->normalize($tempname);
      if(!@move_uploaded_file($upload["tmp_name"],$tempname)) return false;
      $content=$this->openFile($tempname,false);
      $this->deleteFile($tempname,false);
    }
    else $content=$this->openFile($upload["tmp_name"],false);
    $size=strlen($content);
    return $size?compact("filename","size","content"):false;
  }

  function processUploadedFile($name, $folder, $format=false, $extensions=UploadSafeExtensions, $convert=array(), $maxsize=false, $normalize=true)
  {
    $folder=rtrim($folder,"/")."/";
    $upload=$this->getUploadInformation($name);
    if(!$upload || $upload["error"]) return false;
    if(!is_uploaded_file($upload["tmp_name"])) return false;
    if(!$upload["size"] || ($maxsize!==false && $upload["size"]>$maxsize)) return false;
    $localname=trim(stripSlashesSmart($upload["name"]));
    $extension=phpcstrtolower($this->getFileExtension($localname));
    if($extensions!==false) {
      if(!is_array($extensions)) $extensions=explodeSmart(",",$extensions);
      $extensions=array_map("phpcstrtolower",$extensions);
      if(!in_array($extension,$extensions)) return false;
    }
    if(isset($convert[$extension])) $extension=$convert[$extension];
    if($format) {
      $index=$format["start"];
      do {
        $filename=phpcpad($index++,$format["digits"]);
        $filename=$format["prepend"].$filename.$format["append"].$extension;
      } while($this->isFileExists($folder.$filename,$normalize));
    }
    else {
      $filename=$this->validateFilename($localname);
      while($this->isFileExists($folder.$filename,$normalize))
        $filename=$this->incrementFilename($filename);
    }
    $target=$folder.$filename;
    if($normalize) $this->normalize($target);
    if(!@move_uploaded_file($upload["tmp_name"],$target)) return false;
    return $folder.$filename;
  }
}

?>
