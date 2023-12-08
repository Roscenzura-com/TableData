<?php

class FileData
{
	private $error=false;  
	private $errors=['no_file'=>'Файла %file% не существует', 'put'=>'Файл %file% не доступен для записи', 'get'=>'Файл %file% не доступен для чтения' ];
	
	private $fileTime;
	private $filename;
	private $fileContent=false;
	private $fileFolder;
	private $filePath;
	private $format;
	private $formatDefine=['json'=>'json', 'data'=>'json', 'csv'=>'csv'];
	private $fileContentSeparator="\n";
	
	
	public function setFile($filename, $format=false)
	{
		if ($this->filename==$filename) return true; 
		
		$this->setFilename($filename);

		if (!$format) $this->setFormat(); else $this->format=$format;
		
		if (!is_file($filename))
		{
			if ( !$this->createFile($filename, $this->format) ) return false;

			$this->fileTime=time();
		}
		else $this->fileTime=filemtime($filename);
		
		return true;
	}
	
	
	private function setFilename($filename)
	{
		$this->filePath=pathinfo($filename);	
		$this->filename=$filename;	
	}
	
	
	private function setFormat($filename=false)
	{
		if ($filename) $this->setFilename($filename);
		
		$ext=$this->filePath['extension'];
		
		$this->format=isset($this->formatDefine[$ext]) ? $this->formatDefine[$ext] : 'str';
	}
	
	
	public function createFile($filename, $format)
	{
		if ($format=='json') $write='[]'; else $write='';

		if (file_put_contents($filename, $write)===false)
		{
			return $this->fileError('put'); 
		}
		else
		{
			$this->fileContent=$write;
			return true;
		}
	}
	
	
	public function createFiles($dir, $format, ...$files)
	{
		if (is_array($files[0])) $files=$files[0];
		
		foreach ($files as $f) $this->createFile($dir.$f, $format);
	}
	
	
	public function getFile($filename, $format=false)
	{
		if ($this->setFile($filename, $format) )
		{
			if ($this->fileContent===false)
			{
				$this->fileContent=file_get_contents($filename);
				
				if ($this->fileContent===false) return $this->fileError('get'); 
			} 
			else return $this->fileContent;
		}
	}


	public function putFile($str, $filename=false)
	{
		if (!$filename = ($filename ?: $this->filename) ) exit('Не указано имя файла');
		
		if ($this->fileContent==$str) return true;	

		if ( file_put_contents( $filename, $str ) === false ) return $this->fileError('put'); else return true;
	}
	
	
	public function addFile($str, $filename=false, $separator=false)
	{
		if ($separator) $this->fileContentSeparator=$separator;
		if ($filename &&  !$this->setFile($filename)) return false;
		
		if ( file_put_contents($this->filename, $str.$this->fileContentSeparator, FILE_APPEND)===false ) return $this->fileError('put');
	}
	
		
	/*	
	public function addFileData($data, $filename=false) // ?? test
	{
		if ($filename &&  !$this->setFile($filename)) return false;
		
		$handle = @fopen($filename, 'r+');
		
		if ($handle === null)
		{
			$handle = fopen($filename, 'w+');
		}
		
		if ($handle)
		{
			fseek($handle, 0, SEEK_END);

			if (ftell($handle) > 0)
			{
				fseek($handle, -1, SEEK_END);
				fwrite($handle, ',', 1);
				fwrite($handle, json_encode($data) . ']');
			}
			else
			{
				fwrite($handle, json_encode(array($data)));
			}

			fclose($handle);
		}
	}	
	*/
	
	
	public function getFileData($filename, $default=false)
	{	
		if ( $this->getFile($filename)===false ) return exit( $this->fileError() );
		
		switch ($this->format) 
		{
			case "json":
				return $this->getJson($this->fileContent, $default);
			case "csv":
				return $this->getCSV($this->fileContent, $default); 
		}
	}
	
	
	private function getJson ($content, $default=false)
	{
		$data=json_decode( $content, true );
		
		if (is_null($data)) exit('Error format json data: '.$this->filename);
		
		if (empty($data) && is_array($default)) $data=$default;
		
		return $data;
	} 


	public function putFileData($data, $filename=false)
	{
		if ($filename) $this->setFormat($filename);
		
		switch ($this->format)
		{
			case "json":
				return $this->putFile(json_encode($data));  
			case "csv":
				return $this->saveCSV($data); 
		}
	}	
	
	
	private function getCSV($content, $default=false)
	{
		if ($content)
		{
			$data=explode($this->fileContentSeparator, $content);
			
			$data=array_map('str_getcsv', $data);
		}
		else
		{
			$data=$default ?: [];
		}
		
		return $data;
	}

	
	private function saveCSV($data)
	{
		$fp = fopen($this->filename, 'w');
		
		if (!is_array(current($data))) $data=[$data];

		foreach ($data as $arr) 
		{
			fputcsv($fp, $arr); // ",", "\"", "\\", $this->fileContentSeparator 
		}

		fclose($fp);
	}
 
	
	public function fileError($error=false)
	{	
		if ($error) $this->error=str_replace([ '%file%', '%folder%'], [$this->filePath['basename'], $this->filePath['dirname']], $this->errors[$error] ); else return $this->error;
	//	exit($error);
		var_dump($error);
	
		return false;
	}
  
} // class
?>
