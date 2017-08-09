<?php
/**
 *  文件上传处理
 * 
 *	使用用例
 *	try//多文件上传
 *	{
 *		$f = new UploadFile('original');
 *		$f->setAllowType(array('jpg', 'png'));
 *		$f->setMaxSize(1024*8);
 *		if($f->upload('newsimg', array('./test.jpg', './test1.jpg', './test2.png', './dfasd')))
 *		{
 *	 		var_dump($f->getSuccessFile());
 *		}
 *	}
 *	catch(Exception $e)
 *	{
 *		var_dump($e->getMessage());
 *		var_dump($e->getCode());
 *	}
 *
 *  try
 *  {
 *  	$f = new UploadFile('stream');
 *  	$f->setAllowType(array('jpg', 'png', 'gif', 'doc'));
 *  	$f->setMaxSize(1024*8);
 *  	var_dump($f->upload('newsimg', 'xxxxx.png'));
 *  	exit;
 *  }
 *  catch(Exception $e)
 *  {
 *  	var_dump($e->getMessage());
 *  	var_dump($e->getCode());
 *  }
 */

class UploadFile
{
	/**
	 * 上传接收类型 
	 * 
	 * @var string 
	 * 	stream 是php:input协议上传
	 *	original 是原始的上传类型		
	 * @access private
	 */
	private $upType = array('stream', 'original');
	public $curUpType;
	private $saveFile;
	private $saveDir;
	private $upFileName;
	private $allowType;
	private $tmpFile;
	private $maxFileSize;//单位是K
	private $randCount = 0;//多文件上传计数器
	private $tempRandName;//多文件上传临时名字
	private $successFile;//上传成功后的文件名称
	private $originalFile;//上传后可取得的原文件名称
	private $synUpload = 1;//当多个文件上传的时候，一个失败全部失败，设置为0的时候，则不会
	private $mimeTypes = array(
		'ez'         => 'application/andrew-inset',   
		'hqx'        => 'application/mac-binhex40',   
		'cpt'        => 'application/mac-compactpro',   
		'doc'        => 'application/msword',   
		'bin'        => 'application/octet-stream',   
		'dms'        => 'application/octet-stream',   
		'lha'        => 'application/octet-stream',   
		'lzh'        => 'application/octet-stream',   
		'exe'        => 'application/octet-stream',   
		'class'      => 'application/octet-stream',   
		'so'         => 'application/octet-stream',   
		'dll'        => 'application/octet-stream',   
		'oda'        => 'application/oda',   
		'pdf'        => 'application/pdf',   
		'ai'         => 'application/postscript',   
		'eps'        => 'application/postscript',   
		'ps'         => 'application/postscript',   
		'smi'        => 'application/smil',   
		'smil'       => 'application/smil',   
		'mif'        => 'application/vnd.mif',   
		'xls'        => 'application/vnd.ms-excel',   
		'ppt'        => 'application/vnd.ms-powerpoint',   
		'wbxml'      => 'application/vnd.wap.wbxml',   
		'wmlc'       => 'application/vnd.wap.wmlc',   
		'wmlsc'      => 'application/vnd.wap.wmlscriptc',   
		'bcpio'      => 'application/x-bcpio',   
		'vcd'        => 'application/x-cdlink',   
		'pgn'        => 'application/x-chess-pgn',   
		'cpio'       => 'application/x-cpio',   
		'csh'        => 'application/x-csh',   
		'dcr'        => 'application/x-director',   
		'dir'        => 'application/x-director',   
		'dxr'        => 'application/x-director',   
		'dvi'        => 'application/x-dvi',   
		'spl'        => 'application/x-futuresplash',   
		'gtar'       => 'application/x-gtar',   
		'hdf'        => 'application/x-hdf',   
		'js'         => 'application/x-javascript',   
		'skp'        => 'application/x-koan',   
		'skd'        => 'application/x-koan',   
		'skt'        => 'application/x-koan',   
		'skm'        => 'application/x-koan',   
		'latex'      => 'application/x-latex',   
		'nc'         => 'application/x-netcdf',   
		'cdf'        => 'application/x-netcdf',   
		'sh'         => 'application/x-sh',   
		'shar'       => 'application/x-shar',   
		'swf'        => 'application/x-shockwave-flash',   
		'sit'        => 'application/x-stuffit',   
		'sv4cpio'    => 'application/x-sv4cpio',   
		'sv4crc'     => 'application/x-sv4crc',   
		'tar'        => 'application/x-tar',   
		'tcl'        => 'application/x-tcl',   
		'tex'        => 'application/x-tex',   
		'texinfo'    => 'application/x-texinfo',   
		'texi'       => 'application/x-texinfo',   
		't'          => 'application/x-troff',   
		'tr'         => 'application/x-troff',   
		'roff'       => 'application/x-troff',   
		'man'        => 'application/x-troff-man',   
		'me'         => 'application/x-troff-me',   
		'ms'         => 'application/x-troff-ms',   
		'ustar'      => 'application/x-ustar',   
		'src'        => 'application/x-wais-source',   
		'xhtml'      => 'application/xhtml+xml',   
		'xht'        => 'application/xhtml+xml',   
		'zip'        => 'application/zip',   
		'au'         => 'audio/basic',   
		'snd'        => 'audio/basic',   
		'mid'        => 'audio/midi',   
		'midi'       => 'audio/midi',   
		'kar'        => 'audio/midi',   
		'mpga'       => 'audio/mpeg',   
		'mp2'        => 'audio/mpeg',   
		'mp3'        => 'audio/mpeg',   
		'aif'        => 'audio/x-aiff',   
		'aiff'       => 'audio/x-aiff',   
		'aifc'       => 'audio/x-aiff',   
		'm3u'        => 'audio/x-mpegurl',   
		'ram'        => 'audio/x-pn-realaudio',   
		'rm'         => 'audio/x-pn-realaudio',   
		'rpm'        => 'audio/x-pn-realaudio-plugin',   
		'ra'         => 'audio/x-realaudio',   
		'wav'        => 'audio/x-wav',   
		'pdb'        => 'chemical/x-pdb',   
		'xyz'        => 'chemical/x-xyz',   
		'bmp'        => 'image/bmp',   
		'gif'        => 'image/gif',   
		'ief'        => 'image/ief',   
		'jpeg'       => 'image/jpeg',   
		'jpg'        => 'image/jpeg',   
		//'jpg'        => 'image/pjpeg',   
		'jpe'        => 'image/jpeg',   
		'png'        => 'image/png',   
		'tiff'       => 'image/tiff',   
		'tif'        => 'image/tiff',   
		'djvu'       => 'image/vnd.djvu',   
		'djv'        => 'image/vnd.djvu',   
		'wbmp'       => 'image/vnd.wap.wbmp',   
		'ras'        => 'image/x-cmu-raster',   
		'pnm'        => 'image/x-portable-anymap',   
		'pbm'        => 'image/x-portable-bitmap',   
		'pgm'        => 'image/x-portable-graymap',   
		'ppm'        => 'image/x-portable-pixmap',   
		'rgb'        => 'image/x-rgb',   
		'xbm'        => 'image/x-xbitmap',   
		'xpm'        => 'image/x-xpixmap',   
		'xwd'        => 'image/x-xwindowdump',   
		'igs'        => 'model/iges',   
		'iges'       => 'model/iges',   
		'msh'        => 'model/mesh',   
		'mesh'       => 'model/mesh',   
		'silo'       => 'model/mesh',   
		'wrl'        => 'model/vrml',   
		'vrml'       => 'model/vrml',   
		'css'        => 'text/css',   
		'html'       => 'text/html',   
		'htm'        => 'text/html',   
		'asc'        => 'text/plain',   
		'txt'        => 'text/plain',   
		'rtx'        => 'text/richtext',   
		'rtf'        => 'text/rtf',   
		'sgml'       => 'text/sgml',   
		'sgm'        => 'text/sgml',   
		'tsv'        => 'text/tab-separated-values',   
		'wml'        => 'text/vnd.wap.wml',   
		'wmls'       => 'text/vnd.wap.wmlscript',   
		'etx'        => 'text/x-setext',   
		'xsl'        => 'text/xml',   
		'xml'        => 'text/xml',   
		'mpeg'       => 'video/mpeg',   
		'mpg'        => 'video/mpeg',   
		'mpe'        => 'video/mpeg',   
		'qt'         => 'video/quicktime',   
		'mov'        => 'video/quicktime',   
		'mxu'        => 'video/vnd.mpegurl',   
		'avi'        => 'video/x-msvideo',   
		'movie'      => 'video/x-sgi-movie',   
		'ice'        => 'x-conference/x-cooltalk',
		'docx'       => 'application/msword',
		'xlsx'       => 'application/x-zip',
		'pptx'       => 'application/x-zip',
		'rar'        => 'application/x-rar'
		);

	public function __construct($upType)
	{
		$this->setUpType($upType);
	}

	/**
	 * 多文件上传设置 
	 * 
	 * @param int $type 
	 * @access public
	 * @return void
	 */
	public function setSynUpload($type = 0)
	{
		if(in_array($type, array(1, 0)))
			$this->synUpload = $type;
	}

	/**
	 * 设置上传的类型 
	 * 
	 * @param mixed $upType 
	 * @access public
	 * @return void
	 */
	public function setUpType($upType)
	{
		if(!in_array($upType, $this->upType))
		{
			throw new Exception('Invalid Upload Type', '2001');
			return false;
		}

		$this->curUpType = $upType;
	}

	/**
	 * 设置上传后保存的位置 
	 * 
	 * @param mixed $saveFile 
	 * @access public
	 * @return void
	 */
	public function setSaveDir($saveDir)
	{
		if(is_dir($saveDir) && is_writeable($saveDir))
			$this->saveDir = $saveDir;
		else
		{
			throw Exception('The Save Dir is unwriteable', '2002');
			return false;
		}
	}

	/**
	 * 取得保存的位置 
	 * 
	 * @access public
	 * @return void
	 */
	public function getSaveDir()
	{
		return $this->saveDir;
	}

	/**
	 * 设置保存文件名 
	 * 
	 * @param mixed $fileName 
	 * @access public
	 * @return void
	 */
	public function setSaveFile($fileName)
	{
		$this->saveFile = $fileName;
	}

	/**
	 * 取得保存的文件名
	 * 
	 * @access public
	 * @return void
	 */
	public function getSaveFile()
	{
		return $this->saveFile;
	}

	/**
	 * 设置上传的文件标识名 
	 * 
	 * @param mixed $upFileName 
	 * @access public
	 * @return void
	 */
	public function setUpFileName($upFileName)
	{
		if($this->curUpType == $this->upType[1])
		{
			if(!isset($_FILES[$upFileName]))
			{
				throw new Exception('Invalid Upload File Name', '2003');
				return false;
			}

			$this->upFileName = $upFileName;
		}
		else
			return true;
	}

	/**
	 * 设置允许上传的文件类型 
	 * 
	 * @param (array) $type 
	 * @access public
	 * @return void
	 */
	public function setAllowType(array $type)
	{
		$this->allowType = $type;
	}

	/**
	 * 取得允许上传的文件类型 
	 * 
	 * @access public
	 * @return void
	 */
	public function getAllowType()
	{
		return $this->allowType;
	}

	/**
	 * 取得文件可能的类型 
	 * 
	 * @param mixed $file 
	 * @access public
	 * @return void
	 */
	public function getFileType($file)
	{
		$f = new finfo(FILEINFO_MIME);
		$ft = $f->file($file);
		$ftArr = explode(';', $ft);
		return array_keys($this->mimeTypes, $ftArr[0]);
	}

	/**
	 * 判断文件类型是否是被允许的类型 
	 * 
	 * @param mixed $file 
	 * @access public
	 * @return void
	 */
	public function checkFileType($file)
	{
		if(($fileType = $this->getFileType($file)) !== false && count(array_intersect($this->allowType, $fileType)) > 0)
			return true;
		else
		{
			throw new Exception('The type of file is not allow to upload', 2011);
			return false;
		}
	}

	/**
	 * 设置单个文件大小
	 * 
	 * @param mixed $size 
	 * @access public
	 * @return void
	 */
	public function setMaxSize($size)
	{
		$this->maxFileSize = $size;
	}

	/**
	 * 取得设置的单个文件大小 
	 * 
	 * @access public
	 * @return void
	 */
	public function getMaxSize()
	{
		return $this->maxFileSize;
	}

	/**
	 * 验证文件大小 
	 * 
	 * @param mixed $file 
	 * @access public
	 * @return void
	 */
	public function checkFileSize($file)
	{
		if(is_file($file))
			$file = filesize($file);

		if($file > $this->maxFileSize*1024)
		{
			throw new Exception('The file is too large', 2007);
			return false;
		}

		return true;
	}

	/**
	 * 上传文件 
	 * 
	 * @param mixed $upFileName 
	 * @param mixed $saveFileName 
	 * @param mixed $saveFileDir 
	 * @access public
	 * @return void
	 */
	public function upload($upFileName, $saveFileName, $saveFileDir = NULL)
	{
		$this->setUpFileName($upFileName);
		$this->setSaveFile($saveFileName);
		$saveFileDir ? $this->setSaveDir($saveFileDir) : '';

		switch($this->curUpType)
		{
			case $this->upType[0]:
				$stream = file_get_contents('php://input');
				if(!$stream)
				{
					throw new Exception('Invalid file stream', 2005);
					return false;
				}

				$tmpName = $this->randName();
				$fStream = fopen($tmpName, 'wb');
				if(!$fStream)
				{
					throw new Exception('Can not create temp file', 2006);
					return false;
				}

				fwrite($fStream, $stream);
				fclose($fStream);
				if($this->checkFileType($tmpName) && $this->checkFileSize($tmpName))
				//if($this->checkFileSize($tmpName))
				{
					if(copy($tmpName, $this->saveDir . $this->saveFile))
					{
						$this->successFile = $this->saveFile;
						return true;
					}
					else
					{
						throw new Exception('Upload Error', 2009);
						return false;
					}
				}
				else
				{
					throw new Exception('The file type is forbid', 2007);
					return false;
				}
				break;

			case $this->upType[1]:
				if(!is_array($this->saveFile))
					return $this->singleUpload($_FILES[$this->upFileName], $this->saveDir . $this->saveFile);
				else
				{
					$revName = $this->revMutFiles($_FILES[$this->upFileName]);
					$j = count($this->saveFile);
					$s = count($revName);
					if($j > $s)
						$j = $s;

					for($i = 0; $i < $j; $i++)
					{
						if(!$this->singleUpload($revName[$i], $this->saveFile[$i]) && $this->synUpload)
							return false;
					}

					return true;
				}

				break;
		}
	}

	/**
	 * 多文件格式转换
	 * 
	 * @param mixed $files 
	 * @access public
	 * @return void
	 */
	public function revMutFiles($files)
	{
		$res = array();
		for($i = 0, $j = count($files['tmp_name']); $i < $j; $i++)
		{
			$res[$i] = array(
				'tmp_name' => $files['tmp_name'][$i],
				'name' => $files['name'][$i],
				'type' => $files['type'][$i],
				'size' => $files['size'][$i],
				'error' => $files['error'][$i]
			);
		}

		return $res;
	}

	/**
	 * 产生随机文件名 
	 * 
	 * @access public
	 * @return void
	 */
	public function randName()
	{
		!$this->tempRandName ? $this->tempRandName = microtime(true)*10000 : '';
		++$this->randCount;
		$randName = $this->tempRandName . $this->randCount;
		$rand = getcwd() . '/' . $randName;
		$this->tmpFile[] = $rand;
		return $rand;
	}

	/**
	 * 单个文件上传 
	 * 
	 * @param mixed $upFileName 
	 * @param mixed $saveFileName 
	 * @access public
	 * @return void
	 */
	public function singleUpload($upFileName, $saveFileName)
	{
		if($upFileName['error'] != UPLOAD_ERR_OK)
		{
			$msg = 'Error';
			switch($upFileName['error'])
			{
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$msg = 'The file is too large';
					break;

				case UPLOAD_ERR_PARTIAL:
					$msg = 'Part of the file upload failed';
					break;

				case UPLOAD_ERR_NO_FILE:
					$msg = 'File is not upload';
					break;

				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$msg = 'System error';
					break;
			}
			
			if($msg && $this->synUpload)
				throw new Exception($msg, 2010);

			return false;
		}

		//判断大小
		if($this->checkFileSize($upFileName['size']))
		{
			//产生随机临时文件名
			$tmpName = $this->randName();
			if(!move_uploaded_file($upFileName['tmp_name'], $tmpName))
			{
				throw new Exception('Upload Error', 2008);
				return false;
			}

			//判断后缀
			if(strrpos($saveFileName, '.') == false)
				$saveFileName .= substr($upFileName['name'], strrpos($upFileName['name'], '.'));

			//判断格式 
			//if($this->checkFileType($upFileName['type']) && copy($tmpName, $saveFileName))
			if($this->checkFileType($tmpName) && copy($tmpName, $saveFileName))
			{
				$this->successFile[] = $saveFileName;
				$this->originalFile[] = $upFileName['name'];
				return true;
			}
			else
			{
				throw new Exception('Upload Error', 2009);
				return false;
			}
		}
	}

	/**
	 * 返回上传成功的文件名 
	 * 
	 * @access public
	 * @return void
	 */
	public function getSuccessFile()
	{
		return $this->successFile;
	}

	/**
	 * 取得上传后的原始图片名称 
	 * 
	 * @access public
	 * @return void
	 */
	public function getOriginalFile()
	{
		return $this->originalFile;
	}

	/**
	 * 析构函数
	 *
	 * 清除临时文件 
	 * @access public
	 * @return void
	 */
	public function __destruct()
	{
		if(is_array($this->tmpFile))
		{
			foreach($this->tmpFile as $tf)
			{
				is_file($tf) ? unlink($tf) : '';
			}
		}
	}
}
