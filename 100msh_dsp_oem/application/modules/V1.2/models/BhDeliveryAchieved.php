<?php

/**
 *
 */
class BhDeliveryAchieved extends Model
{

    public function getAdvertisedList($where)
    {
        $rel = $this->db->bh->table('bh_ad_stat')
        ->field('sum(click) as click,sum(view) as view, sum(reportSpend) as reportSpend, the_date')
        ->where($where)
        ->group('the_date')
        ->select();
        return $rel;
    }

    public function getAdvertisedTotal($where)
    {
        $rel = $this->db->bh->table('bh_ad_stat')
        ->field('sum(click) as click,sum(view) as view, sum(reportSpend) as reportSpend, "总计"')
        ->where($where)
        ->find();
        return $rel;
    }


    public function getAdvertAreaInfo($where)
    {
        $rel = $this->db->bh->table('bh_ad_city_stat')
        ->field('sum(click) as click,sum(view) as view, sum(reportSpend) as reportSpend, cityId')
        ->where($where)
        ->group('cityId')
        ->select();
        return $rel;
    }

    public function getAdvertAreaInfoTotal($where)
    {
        $rel = $this->db->bh->table('bh_ad_city_stat')
        ->field('sum(click) as click,sum(view) as view, sum(reportSpend) as reportSpend,"总计"')
        ->where($where)
        ->find();
        return $rel;
    }



}
