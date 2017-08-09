<?php
/**
 * 文件生成基类
 */
defined('DEBUG') ? '' : define('DEBUG', 1);
defined('ERROR_500') ? '' : define('ERROR_500', '/50x.html');
class MkFile
{		
    /**
     * 文件要保存的路径 
     * 
     * @var string
     * @access private
     */
    private $fileDir = '';

    /**
     * 文件名 
     * 
     * @var string
     * @access private
     */
    private $fileName = '';

    /**
     * 文件内容 
     * 
     * @var string
     * @access private
     */
    private $fileText = '';

	/**
	 * 创建模式 
	 * 
	 * @var string
	 * @access private
	 */
	private $mode = '';

    /**
     * 析构函数 
     * 
     * @param string $fileName 
     * @param string $fileText 
     * @param string $fileDir 
     * @access public
     * @return void
     */
    public function __construct($fileName = '', $fileText = '', $fileDir = '', $mode = 'w+')
    {
   		$this->setFileDir($fileDir);
   		$this->setFileName($fileName);
   		$this->setFileText($fileText);
		$this->setMode($mode);
    }

    /**
     * 设置文件名 
     * 
     * @param mixed $fileName 
     * @access public
     * @return void
     */
    public function setFileName($fileName)
    {
   		$this->fileName = $fileName;
    }

    /**
     * 获取文件名 
     * 
     * @access public
     * @return void
     */
    public function getFileName()
    {
   		return $this->fileName;
    }
    
    /**
     * 设置文件内容 
     * 
     * @param mixed $fileText 
     * @access public
     * @return void
     */
    public function setFileText($fileText)
    {
   		$this->fileText = $fileText;
    }

    /**
     * 获取文件内容
     * 
     * @access public
     * @return void
     */
    public function getFileText()
    {
   	 	return $this->fileText;
    }

    /**
     * 设置文件保存路径
     * 
     * @param mixed $fileDir 
     * @access public
     * @return void
     */
    public function setFileDir($fileDir)
    {
   	 	$this->fileDir = $fileDir;
    }

    /**
     * 获取文件保存路径
     * 
     * @access public
     * @return void
     */
    public function getFileDir()
    {
   	 	return $this->fileDir;
    }

	/**
	 * 设置文件创建模式 
	 * 
	 * @param mixed $mode 
	 * @access public
	 * @return void
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}
	
	/**
	 * 获取文件创建模式 
	 * 
	 * @access public
	 * @return void
	 */
	public function getMode()
	{
		return $this->mode;
	}

    /**
     * 文件保存
     * 
     * @access public
     * @return bool
     */
    public function save()
    {
   	 	$ret = false;
   	 	if($this->getFileName() === '')
			$this->halt('保存文件时，文件名不能为空.');
   		else
   		{
   			try
   			{
   				$f = fopen($this->getFileDir() . $this->getFileName(), $this->getMode());
				if(!$f)
				{
					throw new Exception('Error');
					return false;
				}

   				fwrite($f,$this->getFileText());
   				fclose($f);
   				$ret = true;
   			}
   			catch(Exception $e)
   			{
				$this->halt('文件没有写入权限,请检查对应到目录是否有写权限.(' . $this->getFileDir() . ')');
   			}
   		}
   		return $ret;
    }
	/**
	 * 错误报告
	 * 
	 * @param string $message 
	 * @access public
	 * @return void
	 */
	public function halt($message = '')
	{
		if(DEBUG == 1)
		{
			header('Content-type:text/html;charset=utf8');
			echo "<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">
					<b>File Error</b><br>
					<b>Message</b>: $message<br />
					</div>";
			exit();
		}
		else
		{
			//记录缓存服务器错误

			//跳转到500页面
			header('HTTP/1.0 500 Internal Server Error');
			header('Location:' . ERROR_500);
		}
	}
}
