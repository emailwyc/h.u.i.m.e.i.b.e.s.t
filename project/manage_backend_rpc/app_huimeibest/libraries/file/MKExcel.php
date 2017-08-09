<?php
/**
 * 通用EXCEL文件导出
 * 
 * @author soone
 * 2009-09-15
 */

class ExportExcel {

    /**
     * 导出文件名
     */
    private $fileName = "export.xls";


    /**
     * 数据原始编码
     */
    private $srcCharset = "UTF-8";


    /**
     * 数据目标编码
     */
    private $objCharset = "GBK";


    /**
     * 是否转换数据编码
     */
    private $covCharset = true;


    /**
     * 是否导出过文件头
     */
    private $isHeader = false;


    /**
     * 将数据以Excel文件形式导出
     * 
     * @param string $s 需要被转换的字符串
     * @return string 转换后的字符串
     */
    private function str2csv($s) {
        $s = str_replace('"', '""', $s);
        return '"'.$s.'"';
    }


    /**
     * 构造函数
     * (设置数据编码转换参数)
     * 
     * @param string $fileName 导出文件名
     * @param string $srcCharset 数据原始编码值
     */
    public function __construct($fileName = "export.xls", $srcCharset = "UTF-8") {
        $fileName = strtoupper(trim($fileName));
        $srcCharset = strtoupper(trim($srcCharset));

        if ($fileName != "") {
            $this->fileName = $fileName;
        }

        if ($srcCharset != "") {
            $this->srcCharset = $srcCharset;
        }

        if ($this->srcCharset != $this->objCharset) {
           $this->covCharset = true;
        } else {
            $this->covCharset = false;
        }
    }


    /**
     * 获取数据，并输出
     * 
     * @param array $dataArray 主体数据
     * 主体数据必须为二维数组或一维数组
     * @return boolean 为true，数据获取成功，否则没有获取数据
     */
    public function addArray($dataArray) {
        if (!is_array($dataArray)) {
            return false;
        }
        
        if (!$this->isHeader) {
            $this->exportHeader();
        }
        
        foreach ($dataArray as $val) {
            $method = is_array($val); // 二（及以上）维数组
            break;
        }
        
        if (!$method) { // 一维数组转成二维数组处理
            $tmpArray = $dataArray;
            $dataArray = array();
            $dataArray[0] = $tmpArray;
        } 
        foreach ($dataArray as $key => $val) {
            $lineStr = "";
            foreach ($val as $k => $v) {
                if ($v == '') {
                    $lineStr .= "\"\"\t";  
                } else {
                    if ($this->covCharset)
                        $lineStr .= $this->str2csv(iconv($this->srcCharset, $this->objCharset, $v))."\t";
                    else
                        $lineStr .= $this->str2csv($v)."\t";
                }
            }
            $lineStr = substr($lineStr, 0, -1);
            echo $lineStr."\n";
            flush();
			
        }
        
        return true;
    }


    /**
     * 将数据以Excel文件形式导出(文件头)
     */
    private function exportHeader() {
        // 文件头部信息
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment; filename=" . $this->fileName);
        header("Content-Type: application/vnd.ms-excel; charset=" . $this->objCharset);
        $this->isHeader = true;
    }
}
?>
