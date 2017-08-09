<?php
/**
 * Created by PhpStorm.
 * User: sa
 * Date: 7/6/16
 * Time: 10:02 AM
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Patarticle_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    //添加tags
    public function addTags($tags)
	{
        $where['name'] = array('$in'=>$tags);
        $tagsInfo = $this->getInfoAll('pat_article_tags',$where);//检验tags是否存在 不存在添加
        $tagsInfo_name = array_column($tagsInfo,'name' );//存在的tags
        $tags = array_unique($tags);
        $newTags = array_diff($tags, $tagsInfo_name); //需要添加的tags
        if(!empty($newTags)){
            $newTags = array_values($newTags);
            for($i=0; $i<count($newTags); $i++){
                $data[]['name'] = $newTags[$i];
            }
            $insert = $this->batchInsert('pat_article_tags', $data);
        }
	$tagIds = $this->getInfoAll('pat_article_tags', $where);
        return $tagIds;

    }

    public function getInfoAll($table,$where,$sort="",$fields=array(),$idIsStr = false) {
        $info = $this->mdb->where($where)->order_by($sort)->select($fields)->get($table,$idIsStr);
        if(!empty($info)){
            return $info;
        }else{
            return array();
        }
    }

    //批量插入
    public function batchInsert($table,$date)
    {
        $result = $this->mdb->batchinsert($table,$date);
        if($result){return true;}
        else{return false;}
    }
}
