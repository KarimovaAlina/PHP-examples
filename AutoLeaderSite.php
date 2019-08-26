<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 27.05.2019
 * Time: 11:39
 */
require_once(dirname(__FILE__)."/SiteBaseOem.php");
include_once(__DIR__ . '/../../lib/simple_html_dom.php');

class AutoLeaderSite extends SiteBaseOem
{
    public function NoDetail($html, $url)
    {
        $alarm = trim(strip_tags($html->find('.section-page p', 0)->innertext));
        if ($alarm == 'По вашему запросу ничего не найдено, попробуйте переформулировать или упростить запрос') {
            $html = false;
            LogParseTable::startParseLog("no details found", $this->r_id, $this->f_ch_id, $this->site_id, $url);
            $this->updateStatusNo(ParseTable::STATUS_NO_FOUND);
        }
        return ($html);
    }

    public function ParseDataPriceWAddress($html)
    {
        $table_rows = $html->find('.catalog-products article');
        if ($table_rows) {
//            echo 'count(table_rows): ' . count($table_rows) . "<br>\r\n";;
            $j = 0;

            for ($i = 0; $i < count($table_rows); $i++) {
                $addresses = '';
                $row = $table_rows[$i];
//            if ($row->find('.available-warehouses .active', 1)) {
                $cities = $row->find('.available-warehouses thead');

                foreach ($cities as $index => $city) {
                    if (trim(strip_tags($city->find('td', 0)->innertext)) == 'Красноярск') {

                        $detail_url = $row->find('.content a', 0)->href;
//                        echo 'detail_url: ' . $detail_url . "<br>\r\n";;
                        $full_info = trim(strip_tags($row->find('.content a', 0)->innertext));
                        $brand = trim(strip_tags($row->find('.brand p', 0)->innertext));
                        $brand = strtoupper($brand);
//                $cities = $row->find('.available-warehouses thead');

//                    $filials = $row->find('.available-warehouses .active', 1);
                        $filials = $city->next_sibling();
                        $filial_rows = $filials->find('tr');

                        foreach ($filial_rows as $f_row) {

                            $present = trim(strip_tags($f_row->find('td', 0)->innertext));
                            $avail = trim(strip_tags($f_row->find('td', 1)->innertext));
                            $cost_str = trim(strip_tags($f_row->find('td', 2)->innertext));
                            $cost_str = preg_replace("/[^0-9]/", "", $cost_str);
                            $amount = trim(strip_tags($f_row->find('td', 3)->innertext));
                            $amount = preg_replace("/[^0-9]/", "", $amount);

                            $this->LogErrorAddress($present, $detail_url);                    //проверка наличия адреса и запись в лог
                            $this->LogErrorCost($cost_str, $detail_url);                      //проверка наличия цены и запись в лог

                            if (stristr($avail, 'на скл.')) {
                                $data[$j]['price_fiz'] = F::clearCode($cost_str);
                                $data[$j]['address'] = $present;
                                $data[$j]['brand'] = $brand;
                                $data[$j]['full_info'] = $full_info;
                                $data[$j]['url_detail'] = $detail_url;
                                $data[$j]['amount'] = $amount;
                                $data[$j]['f_name'] = $this->f_name;
                                $data[$j]['f_id'] = $this->f_id;
                                $data[$j]['telefon'] = $this->getFilialPhone($this->f_ch_id, $present);
                                $this->list[] = "$this->site_id $this->oem $detail_url";
                                $j++;
                            }
                        }
                    }
                }
            }
//        var_dump($data);
            $html->clear();
            $data = array_values($data);
            return $data;
        }
    }
}

