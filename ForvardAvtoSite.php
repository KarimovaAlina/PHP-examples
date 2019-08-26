<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 27.05.2019
 * Time: 13:57
 */
require_once(dirname(__FILE__)."/SiteBaseOem.php");
include_once(__DIR__ . '/../../lib/simple_html_dom.php');

class ForvardAvtoSite extends SiteBaseOem
{
    public function NoDetail($html, $url)
    {
        $alarm = trim(strip_tags($html->find('.m_auto h3', 0)->innertext));
        if ($alarm == 'К сожалению, по вашему запросу ничего не найдено.') {
            $html = false;
            LogParseTable::startParseLog("no details found", $this->r_id, $this->f_ch_id, $this->site_id, $url);
            $this->updateStatusNo(ParseTable::STATUS_NO_FOUND);
        }
        return ($html);
    }

    public function ParseDataPriceWAddress($html) {

        $table_rows = $html->find('.products-tile .products-tile__item');
        if ($table_rows) {
            $k = 0;
            for ($i = 0; $i < count($table_rows); $i++) {
                $row = $table_rows[$i];
                $detail_url = $this->site_url . $row->find('.product__border a', 0)->href;
                $detail_html = file_get_html($detail_url);

                $this->LogErrorDetHtml($detail_html, $detail_url);                    //проверка html детали

                $full_info = trim(strip_tags($detail_html->find('.page__header .header__title', 0)->innertext));
                $brand = trim(strip_tags($detail_html->find('.header__title a', 0)->innertext));
                $brand = strtoupper($brand);
                $cost_str = trim(strip_tags($detail_html->find('.price__val', 0)->innertext));
                $pos = strpos($cost_str, "&");
                $cost_str = substr($cost_str, 0, $pos);
                $cost_int = preg_replace("/[^0-9]/", "", $cost_str);

                $this->LogErrorCost($cost_str, $detail_url);                         //проверка наличия цены и запись в лог

                $cities = $detail_html->find('.product-card__availability .availability__item');
                for ($c = 0; $c < count($cities); $c++) {
                    if (trim(strip_tags($cities[$c]->find('.availability__region', 0)->innertext)) == 'Красноярск') {
                        $filials = $cities[$c]->find('.availability__point');
                        for ($j = 0; $j < count($filials); $j++) {
                            $filials[$j] = trim(strip_tags($filials[$j]->find('a', 0)->innertext));

                            $data[$k]['price_fiz'] = $cost_int;
                            $data[$k]['brand'] = $brand;
                            $data[$k]['address'] = $filials[$j];
                            $data[$k]['url_detail'] = $detail_url;
                            $data[$k]['full_info'] = $full_info;
                            $data[$k]['f_name'] = $this->f_name;
                            $data[$k]['f_id'] = $this->f_id;
                            $data[$k]['telefon'] = $this->getFilialPhone($this->f_ch_id, $filials[$j]);
                            $this->list[] = "$this->site_id $this->oem $detail_url";
                            $k++;
                        }
                    }
                }
                $detail_html->clear();
                unset($detail_html);
            }
            $data = array_values($data);
            return $data;
        }
    }
}