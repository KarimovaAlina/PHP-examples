<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 23.05.2019
 * Time: 15:34
 */

class SiteBaseOem
{
    public $oem = '';
    public $c_id = '';
    public $r_id = '';
    public $list = [];
    public $f_id = '';
    public $f_name = '';
    public $site_id = '';
    public $f_ch_id = '';
    public $site_url = '';
    public $detail_list = [];
    public $site_city_name = '';
    private $status_no_id = 0; // при проходке сайта, вычисляется, почему не нашлась там запчасть


    public function __construct ($oem, $r_id)
    {
        $this->oem = $oem;
        $this->r_id = $r_id;
    }


    public function checkOem ($oem) {
        if ($oem) {
            $oem = preg_replace("/[^A-Za-z0-9-]/", "", $oem);
        }
        return $oem;
    }


    public function LogErrorHtml ($html, $url) {
        if (!$html) {
            $this->updateStatusNo(ParseTable::STATUS_NO_SITE);
            LogParseTable::startParseLog("wrong main url", $this->r_id, $this->f_ch_id, $this->site_id, $url);
            return true;
        } else return false;
    }

    public function LogErrorSite ($site, $city_id) {
        if (empty($site)) {
            LogParseTable::startParseLog("site_no_found", $this->r_id, $this->f_ch_id, $city_id, '');
            return true;
        } else return false;
    }


    public function LogErrorDetHtml ($html_detail, $detail_url) {
        if (!$html_detail) {
            LogParseTable::startParseLog("no detail html", $this->r_id, $this->f_ch_id, $this->site_id, $detail_url);
//            throw new Exception(); //????????
            return false;
        }
        return true;
    }


    public function LogErrorCost ($cost, $detail_url) {
        if (!$cost) {
            LogParseTable::startParseLog("no cost", $this->r_id, $this->f_ch_id, $this->site_id, $detail_url);
        }
        return;
    }

    public function LogErrorAddress ($present, $detail_url) {   //поменять или добавить в файл getBestAnswerChain
        if (!$present) {
            $this->updateStatusNo(ParseTable::STATUS_NO_SITE);
            LogParseTable::startParseLog("no address", $this->r_id, $this->f_ch_id, $this->site_id, $detail_url);
        }
        return;
    }

    public function updateStatusNo($new) {
        if ($new === ParseTable::STATUS_NO_SITE || $this->status_no_id === ParseTable::STATUS_NO_SITE) {
            $this->status_no_id = ParseTable::STATUS_NO_SITE;
        }
        elseif ($new > $this->status_no_id) {
            $this->status_no_id = $new;
        }
    }


    public function getSite($id) {
        $site_for_parse = SiteTable::getSiteForParse($id);
        if (empty($site_for_parse)) {
            return;
        }
        $site = $site_for_parse;
        $this->site_id = $site['id'];
        $this->site_url = $site['url'];
        $this->f_ch_id = $site['firm_chain_id'];
        $this->f_id = $site['firm_id'];
        $this->site_city_name = $site['name_city'];
        $this->c_id = $site['city'];
        return $site;
    }

    public function getFirmName($id) {
        $res  = db::select('f.name_commercial')
            ->from('firms f')
            ->where('f.id', '=', $id)
            ->execute();
        $this->f_name = $res['name_commercial'];
        return $res['name_commercial'];
    }

    public function insertDb ($data, $parse_status_id)
    {
        $count_insert = 0;
        for ($i = 0; $i < count($data); $i++) {
            db::insert('parse_result', [
                'request_id' => $this->r_id,
                'site_id' => $this->site_id,
                'parse_status_id' => $parse_status_id,
                'fullname' => '',
                'body' => '',
                'engine' => '',
                'marking' => '',
                'cost' => $data[$i]['price_fiz'],
                'cost_int' => F::clearCode($data[$i]['price_fiz']),
                'present' => $data[$i]['address'],
                'note' => $data[$i]['full_info'],
                'url' => $data[$i]['url_detail'],
                'brand' => $data[$i]['brand'],
            ])->execute();

            $count_insert++;
            $temp = $data[$i]['url_detail'];
            $this->list[] = "$this->site_id $this->oem $temp";

        }
        return ($count_insert);
    }

    public function getUrlContent($url, $cookie)
{
//        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
//        $agent = 'Opera/9.80 (Windows NT 6.0; U; en) Presto/2.8.99 Version/11.10';
    $agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    return $result;
}

    public function getUrlContentTitan($url, $cookie)
    {
        $agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Accept-Encoding: gzip',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'Cookie: lor_ok=5bd12d34dd8d41bc651907e0b7135ab1; PHPSESSID=2d17cc0a67c3d40be537928d9fefba43; default=9a4771aa69376756641fdd83c6f9da6d; language=ru-ru; currency=RUB; clbvid=5cf63ea0ca11532b73d82ebb; _ym_uid=1559885623681268709; _ym_d=1559885623; _ga=GA1.2.1245764482.1559885623; _gid=GA1.2.1704776888.1559885623; _ym_isad=2',
            'Host: titanavto.com',
            'Pragma: no-cache',
            'Referer: http://titanavto.com/search/?search=14400p7j004&sub_category=true&description=true',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $result = curl_exec($ch);
        return $result;
    }

    public function getFilialId($f_ch_id, $address) {
        $res = db::select('f.id')
            ->from('firms f')
            ->where('f.firm_chain_id', '=', $f_ch_id)
            ->where('f.address', '=', $address)
            ->execute();
        return ($res['id']);
    }

    public function getFilialPhone($f_ch_id, $address) {
        $fil_id = $this->getFilialId($f_ch_id, $address);
        $res = db::select('fp.country, fp.public_format, fp.id')
            ->from('firm_phones fp')
            ->join('phone_to_firm ptf ON ptf.id_phone = fp.id')
            ->where('ptf.id_firm', '=', $fil_id)
            ->executeArr();
        for($i=0; $i<count($res); $i++) {
            $result[$i] = $res[$i]['country'].$res[$i]['public_format'];
        }
        if (count($result) > 1) {
            $final = '';
            foreach ($result as $r) {
                $final = $final . $r . "<br>";
            }
            $final = substr($final, 0, -4);
        } else $final = $result[0];
        return $final;
    }

    public function getFirmAddress() {
        $res = db::select('f.address')
            ->from('firms f')
            ->where('f.id', '=', $this->f_id)
            ->execute();
        return ($res['address']);
    }

    public function getFirmPhone() {
        $res = db::select('fp.country, fp.public_format, fp.id')
            ->from('firm_phones fp')
            ->join('phone_to_firm ptf ON ptf.id_phone = fp.id')
            ->where('ptf.id_firm', '=', $this->f_id)
            ->executeArr();
        for($i=0; $i<count($res); $i++) {
            $result[$i] = $res[$i]['country'].$res[$i]['public_format'];
        }
        if (count($result) > 1) {
            $final = '';
            foreach ($result as $r) {
                $final = $final . $r . "<br>";
            }
            $final = substr($final, 0, -4);
        } else $final = $result[0];
        return $final;
    }
}
