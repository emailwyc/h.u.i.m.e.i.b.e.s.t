<?php
/**
 *  图片处理类
 *
 * 使用用例
 *   try
 *   {
 *    $a = new ImageFactory('Imagick');
 *    $a->thumbnailImage('./a.jpg', './b.png', 400, 0);
 *   }
 *   catch(Exception $e)
 *   {
 *   	echo $e->getMessage();
 *   }
 */
class ImageFactory
{
	private $imgArrObj = array('Imagick', 'GD');
	private $curImgObjName;
	private $actImgObj;
	private $sourceImg;
	private $targetImg;
	private $imgTypeArr = array('jpg' => 6, 'png' => 7, 'gif' => 4);
	public $tImgHeight;
	public $tImgWidth;
	public $oriRange;

	public function __construct($imgObj = NULL)
	{
		if(!in_array($imgObj, $this->imgArrObj))
		{
			throw new Exception('Invalid Object Type!', '1001');
			return false;
		}

		$this->curImgObjName = $imgObj;
		$this->actImgObj = new $imgObj;
	}

	/**
	 * 设置源图片 
	 * 
	 * @param mixed $img 
	 * @access public
	 * @return void
	 */
	public function setSourceImg($img)
	{
		if(!is_file($img))
		{
			throw new Exception('the source image is not exist!', '1002');
			return false;
		}

		$this->sourceImg = $img;
	}

	/**
	 * 取得源图片 
	 * 
	 * @access public
	 * @return void
	 */
	public function getSourceImg()
	{
		return $this->sourceImg;
	}

	/**
	 * 设置目标图片 
	 * 
	 * @param mixed $img 
	 * @access public
	 * @return void
	 */
	public function setTargetImg($img)
	{
		$this->targetImg = $img;
	}

	/**
	 * 取得目标图片 
	 * 
	 * @access public
	 * @return void
	 */
	public function getTargetImg()
	{
		return $this->targetImg;
	}

	/**
	 * 取得图片的后缀 
	 * 
	 * @param mixed $img 
	 * @access public
	 * @return void
	 */
	public function getImageType($img)
	{
		if(!is_file($img))
			return false;

		switch($this->curImgObjName)
		{
			case $this->imgArrObj[0]:
				$this->actImgObj->readImage($img);
				return array_search($this->actImgObj->getImageType(), $this->imgTypeArr);
				break;
		}
	}

	/**
	 * 设置目标图片宽高 
	 * 
	 * @param mixed $width 
	 * @param mixed $height 
	 * @access public
	 * @return void
	 */
	public function setTargetSize($width, $height)
	{
		$this->tImgWidth = $width;
		$this->tImgHeight = $height;
	}

	/**
	 * 生成缩略图 
	 * 
	 * @param mixed $sourceImg 
	 * @param mixed $targetImg 
	 * @param mixed $maxWidth 
	 * @param int $maxHeight 
	 * @param int $force 
	 * @access public
	 * @return void
	 */
	public function thumbnailImage($sourceImg, $targetImg, $maxWidth, $maxHeight = 0, $force = 0, $isGif = 1)
	{
		$this->setSourceImg($sourceImg);
		$this->setTargetImg($targetImg);
		switch($this->curImgObjName)
		{
			case $this->imgArrObj[0]:
				$this->actImgObj->readImage($this->sourceImg);
				$this->oriRange = $this->getOriRange();
				$dstw = $maxWidth;
				$dsth = $maxHeight;
				if(!$force)
				{
					if($maxWidth > 0 && $maxHeight > 0)
					{
						//判断高度跟宽度 update by wangsc
						if($this->oriRange[0] > $maxWidth && $this->oriRange[1] > $maxHeight)
						{
							$rw = $this->oriRange[0] / $maxWidth;
							$rh = $this->oriRange[1] / $maxHeight;
							if($rw > $rh)
							{
								$dsth = 0;
								$dstw = $maxWidth;
							}
							else
							{
								$dstw = 0;
								$dsth = $maxHeight;
							}
						}
						elseif($this->oriRange[0] > $maxWidth)
						{
							$dstw = $maxWidth;
							$dsth = 0;
						}
						elseif($this->oriRange[1] > $maxHeight)
						{
							$dsth = $maxHeight;
							$dstw = 0;
						}
						else
						{
							$dstw = $this->oriRange[0];
							$dsth = $this->oriRange[1];
						}
					}
					else
					{
						if($maxWidth > 0 && $this->oriRange[0] < $maxWidth)
							$dstw= $this->oriRange[0];
						if($maxHeight > 0 && $this->oriRange[1] < $maxHeight)
							$dsth = $this->oriRange[1];
					}
				}
				$this->setTargetSize(intval($dstw), intval($dsth));
				if(strtolower(substr($this->sourceImg, strrpos($this->sourceImg, '.')+1)) == 'gif' && $this->actImgObj->getNumberImages() > 1)
				{
					$image = $this->actImgObj->coalesceImages();
					foreach($image as $fr)
					{
						$fr->thumbnailImage($this->tImgWidth, $this->tImgHeight, false);
						if($isGif != 1) return $fr->writeImage($this->targetImg);
					}

					//$this->actImgObj = $image->optimizeImageLayers();
					return $image->writeImages($this->targetImg, true);
				}
				else
				{
					$this->actImgObj->thumbnailImage($this->tImgWidth, $this->tImgHeight, false);
					$this->actImgObj->setImageFormat(substr($this->targetImg, strrpos($this->targetImg, '.')+1));
					return $this->actImgObj->writeImage($this->targetImg);
				}

				break;
		}
	}

	/**
	 * 截取图片 
	 * 
	 * @param mixed $sourceImg 
	 * @param mixed $targetImg 
	 * @param mixed $maxWidth 
	 * @param mixed $maxHeight 
	 * @param int $force 
	 * @access public
	 * @return void
	 */
	public function cutImage($sourceImg, $targetImg, $maxWidth, $maxHeight, $force = 0)
	{
		$this->setSourceImg($sourceImg);
		$this->setTargetImg($targetImg);
		switch($this->curImgObjName)
		{
			case $this->imgArrObj[0]:
				$this->actImgObj->readImage($this->sourceImg);
				$this->oriRange = $this->getOriRange();
				if($this->oriRange[0] >= $maxWidth && $this->oriRange[1] >= $maxHeight)
				{
					$x = floor(($this->oriRange[0]-$maxWidth)/2);
					$y = floor(($this->oriRange[1]-$maxHeight)/2);
					$this->setTargetSize(intval($maxWidth), intval($maxHeight));
					if(strtolower(substr($this->sourceImg, strrpos($this->sourceImg, '.')+1)) == 'gif' && $this->actImgObj->getNumberImages() > 1)
					{
						$image = $this->actImgObj->coalesceImages();
						foreach($image as $fr)
						{
							$fr->cropImage($this->tImgWidth, $this->tImgHeight, $x, $y);
							$fr->setImagePage($this->tImgWidth, $this->tImgHeight, 0, 0);
							return $fr->writeImage($this->targetImg);
						}
					}
					else
					{
						$this->actImgObj->cropImage($this->tImgWidth, $this->tImgHeight, $x, $y);
						$this->actImgObj->setImageFormat(substr($this->targetImg, strrpos($this->targetImg, '.')+1));
						return $this->actImgObj->writeImage($this->targetImg);
					}
				}
				else if($this->oriRange[0] < $maxWidth || $this->oriRange[1] < $maxHeight)
				{
					if(!$force)
					{
						$this->setTargetSize(intval($maxWidth), intval($maxHeight));
						if(strtolower(substr($this->sourceImg, strrpos($this->sourceImg, '.')+1)) == 'gif' && $this->actImgObj->getNumberImages() > 1)
						{
							$image = $this->actImgObj->coalesceImages();
							foreach($image as $fr)
							{
								$fr->cropThumbnailImage($this->tImgWidth, $this->tImgHeight);
								return $fr->writeImage($this->targetImg);
							}
						}
						else
						{
							$this->actImgObj->cropThumbnailImage($this->tImgWidth, $this->tImgHeight);
							$this->actImgObj->setImageFormat(substr($this->targetImg, strrpos($this->targetImg, '.')+1));
							return $this->actImgObj->writeImage($this->targetImg);
						}
					}
					else
					{
						if($this->oriRange[0] < $maxWidth && $this->oriRange[1] > $maxHeight)
						{
							$mHeight = 0;
							$mWidth = $maxWidth;
						}

						if($this->oriRange[0] > $maxWidth && $this->oriRange[1] < $maxHeight)
						{
							$mWidth = 0;
							$mHeight = $maxHeight;
						}

						if($this->oriRange[0] < $maxWidth && $this->oriRange[1] < $maxHeight)
						{
							if($maxWidth/$maxHeight > $this->oriRange[0]/$this->oriRange[1])
							{
								$mHeight = 0;
								$mWidth = $maxWidth;
							}
							else
							{
								$mWidth = 0;
								$mHeight = $maxHeight;
							}
						}

						if(strtolower(substr($this->sourceImg, strrpos($this->sourceImg, '.')+1)) == 'gif' && $this->actImgObj->getNumberImages() > 1)
						{
							$image = $this->actImgObj->coalesceImages();
							foreach($image as $fr)
							{
								$fr->thumbnailImage($mWidth, $mHeight, false);
								$this->oriRange = array();
								$x = floor(($fr->getImageWidth()-$maxWidth)/2);
								$y = floor(($fr->getImageHeight()-$maxHeight)/2);
								$fr->cropImage($maxWidth, $maxHeight, $x, $y);
								return $fr->writeImage($this->targetImg);
							}
						}
						else
						{

							$this->actImgObj->thumbnailImage($mWidth, $mHeight, false);
							$this->oriRange = $this->getOriRange();
							$x = floor(($this->oriRange[0]-$maxWidth)/2);
							$y = floor(($this->oriRange[1]-$maxHeight)/2);
							$this->actImgObj->cropImage($maxWidth, $maxHeight, $x, $y);
							$this->actImgObj->setImageFormat(substr($this->targetImg, strrpos($this->targetImg, '.')+1));
							return $this->actImgObj->writeImage($this->targetImg);
						}
					}
				}
				break;
		}
	}

	/**
	 * 取得图片宽和高的比例值
	 * 
	 * @param mixed $sourceImg 
	 * @access public
	 * @return void
	 */
	public function getPicRatio($sourceImg)
	{
		$this->setSourceImg($sourceImg);
		switch($this->curImgObjName)
		{
			case $this->imgArrObj[0]:
				$this->actImgObj->readImage($this->sourceImg);
				$this->oriRange = $this->getOriRange();
				return $this->oriRange[0]/$this->oriRange[1];
				break;
		}
	}

	/**
	 * 获取原始的图片处理对象 
	 * 
	 * @access public
	 * @return void
	 */
	public function getOriObj()
	{
		return $this->actImgObj;
	}

	/**
	 * 返回图片原始尺寸 
	 * 
	 * @access public
	 * @return void
	 */
	public function getOriRange()
	{
		switch($this->curImgObjName)
		{
			case $this->imgArrObj[0]:
				return array($this->actImgObj->getImageWidth(), $this->actImgObj->getImageHeight());
				break;
		}
		
	}

	/**
	 * __CALL 
	 * 
	 * @param mixed $name 
	 * @param mixed $args 
	 * @access public
	 * @return void
	 */
	public function __CALL($name, $args)
	{
		if(method_exists($this->actImgObj, $name))
		{
			return $this->actImgObj->$name($args);
		}
		else
		{
			throw new Exception('This Method ' . $name . ' is not exist!', '1003');
			return false;
		}
	}
}

