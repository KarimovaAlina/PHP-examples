<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 23.05.2019
 * Time: 17:05
 */

require_once(dirname(__FILE__)."/SiteBaseOem.php");
include_once(__DIR__ . '/../../lib/simple_html_dom.php');

class AutotradeSite extends SiteBaseOem
{
    public function NoDetail($html, $url)
    {
        $alarm = trim(strip_tags($html->find('.m-widget5 .text-center', 0)->innertext));
        if ($alarm == 'По вашему запросу ничего не найдено, попробуйте изменить условия поиска.') {
            $html = false;
            LogParseTable::startParseLog("no details found", $this->r_id, $this->f_ch_id, $this->site_id, $url);
            $this->updateStatusNo(ParseTable::STATUS_NO_FOUND);
        }
        return ($html);
    }

    public function ParseDataPriceWAddress($html) {

        $table_rows = $html->find('.m-widget5 .m-widget5__title');
        if ($table_rows) {
            $k = 0;
            for ($i = 0; $i < count($table_rows); $i++) {
                $row = $table_rows[$i];
                $detail_url = $this->site_url . $row->find('a', 0)->href;
//            echo $detail_url . "<br>\r\n";
                $html_detail = file_get_html($detail_url);

                $this->LogErrorDetHtml($html_detail, $detail_url);                         //проверка html детали
                $isAvail = trim(strip_tags($html_detail->find('.at--left-bordered .m-badge--wide', 0)->innertext));
                if ($isAvail == 'В наличии') {
                    $full_info = trim(strip_tags($row->find('a', 0)->innertext));
                    $brand = trim(strip_tags($html_detail->find('.col-6 strong', 1)->innertext));
                    $brand = strtoupper($brand);
                    $cost_str = trim(strip_tags($html_detail->find('.detail__cost', 0)->innertext));

                    $this->LogErrorCost($cost_str, $detail_url);                               //проверка наличия цены и запись в лог

                    $filials = $html_detail->find('.table--item-avails .pl-3');
                    $addresses = '';
                    for ($j = 0; $j < count($filials); $j++) {
                        $shit = trim(strip_tags($html_detail->find('.table--item-avails .pl-3', $j)->innertext));
                        $shit_arr = explode("-", $shit);
                        $present = trim($shit_arr[0]);
                        $check = trim($shit_arr[1]);
//                echo $check . "<br>\r\n";

                        if (stristr($check, 'в наличии')) {
                            $data[$k]['price_fiz'] = F::clearCode($cost_str);
                            $data[$k]['brand'] = $brand;
                            $data[$k]['address'] = $present;
                            $data[$k]['url_detail'] = $detail_url;
                            $data[$k]['full_info'] = $full_info;
                            $data[$k]['f_name'] = $this->f_name;
                            $data[$k]['f_id'] = $this->f_id;
                            $data[$k]['telefon'] = $this->getFilialPhone($this->f_ch_id, $present);
                            $this->list[] = "$this->site_id $this->oem $detail_url";
                            $k++;
                        }
                    }
                }
                $html_detail->clear();
                unset($html_detail);
            }
            $data = array_values($data);
            return $data;
        }
    }
}