<?php

/**
 *
 */
class Portraits extends Model
{

    public function createPortraits($data)
    {
        $id = $this->db->dsp->table('dsp_individual_portraits')->data($data)->insert();
        return $id;
    }

    public function updatePortraits($where,$data)
    {
        $line = $this->db->dsp->table('dsp_individual_portraits')->where($where)->data($data)->update();
        return $line;
    }

    public function findPortraits($where)
    {
        $rel = $this->db->dsp->table('dsp_individual_portraits')->where($where)->find();
        return $rel;
    }

    public function createPortraitsTab($data)
    {
        $id = $this->db->dsp->table('dsp_individual_portrait_tabs')->data($data)->insert();
        return $id;
    }

    public function updatePortraitsTab($where,$data)
    {
        $line = $this->db->dsp->table('dsp_individual_portrait_tabs')->where($where)->data($data)->update();
        return $line;
    }

    public function findPortraitsTab($where)
    {
        $rel = $this->db->dsp->table('dsp_individual_portrait_tabs')->where($where)->find();
        return $rel;
    }
    public function selectPortraitsTab($where)
    {
        $rel = $this->db->dsp->table('dsp_individual_portrait_tabs')->where($where)->select();
        return $rel;
    }
    public function delPortraitsTab($where)
    {
        $rel = $this->db->dsp->table('dsp_individual_portrait_tabs')->where($where)->delete();
        return $rel;
    }

    /**
     * [resourceTransferUpdate 资源转移更新]
     * @param  [type] $comp_id [description]
     * @param  [type] $data    [description]
     * @return [type]          [description]
     */
    public function resourceTransferUpdate($comp_id,$data)
    {

        $rel = $this->db->dsp->table('dsp_comp_poster')->where('comp_id='.$comp_id)->data($data)->update();
        return $rel;

    }

    /**
     * [getServerSceneResource 得到服务商场景资源]
     * @param  [type] $comp_id [服务商id]
     * @return [type]          [description]
     */
    public function getServerSceneResource($comp_id)
    {
        $rel = $this->db->dsp->table('dsp_comp_poster')->field('high_num,app_num,one_num')->where('comp_id='.$comp_id)->find();

        return $rel;
    }

    /**
     * [recordTransferCreate 记录创建]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function recordTransferCreate($data)
    {
        $rel = $this->db->dsp->table('dsp_poster_record')->data($data)->insert();
        return $rel;
    }


    public function getPortraitsList($where,$limit = '')
    {
        $rel = $this->db->dsp->table('dsp_individual_portraits')
        ->field('individual_portraits_id,user_mac,query_time')
        ->where($where)
        ->limit($limit)
        ->select();

        return $rel;
    }


    public function startTrans()
    {
        $this->db->dsp->startTrans();
    }
    public function commit()
    {
        $this->db->dsp->commit();
    }
    public function rollback()
    {
        $this->db->dsp->rollback();
    }
}
