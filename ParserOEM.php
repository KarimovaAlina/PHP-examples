<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 30.08.2018
 * Time: 23:23
 */

ini_set('max_execution_time', '10000');

include_once(__DIR__ . '/../lib/simple_html_dom.php');
use JonnyW\PhantomJs\Client;

/**
 * Парсер сайтов
 * тестировать работу скрипта можно через файл /parse/Parser.php
 */
class ParserOEM implements iJob
{
    use Queueable;

    public $s_id = 0;
    public $r_id = 0;
    public $f_id = 0;
    public $oem = 0;
    public $f_ch_id = 0;

    public $debug = false;
    public $debug_url = '';
    public $debug_list = [];


    public function __construct($oem, $s_id, $f_id, $f_ch_id, $r_id)
    {
        $this->oem = $oem;
        $this->s_id = $s_id;
        $this->r_id = $r_id;
        $this->f_ch_id = $f_ch_id;
        $this->f_id = $f_id;
    }

    /**
     * метод выполнится в job()
     */
    public function handle()
    {
        try {
            if ($this->s_id == 29) $arr = $this->parseAutotradePrice($this->oem);
            if ($this->s_id == 180) $arr = $this->parseAutoLeaderPrice($this->oem);
            if ($this->s_id == 181) $arr = $this->parseForvardAvtoPrice($this->oem);
//            if ($this->s_id == 187) $arr = $this->parseANovostPrice($this->oem);
//            if ($this->s_id == 200) $arr = $this->parseBoostMastersPrice($this->oem);
//            if ($this->s_id == 201) $arr = $this->parseRotorPrice($this->oem);
//            if ($this->s_id == 203) $arr = $this->parseUgKoreaPrice($this->oem);
//            if ($this->s_id == 204) $arr = $this->parseAvtoMPrice($this->oem);
//            if ($this->s_id == 205) $arr = $this->parseAutoKoreaPrice($this->oem);
//            if ($this->s_id == 206) $arr = $this->parseKorea124Price($this->oem);

            if ($this->debug) {
                $this->debug_list = $arr;
                echo "Результат: ".count($this->debug_list);
                echo "<br>урл: ".$this->debug_url;
                var_dump($this->debug_list);
            } else {
                if (empty($arr)) {
                    //запись результата в бд
                    db::insert('parse_oem_result', [
                        'parse_request_id' => $this->r_id,
                        'f_id' => $this->f_id,
                        'full_name' => 'нет',
                        'cost' => 0,
                        'brand' => 'нет',
                        'url_detail' => 'нет',
                        'created_at' => 'NOW()',
                    ])->execute();
                } else {
                    for ($i = 0; $i < count($arr); $i++) {
                        //запись результата в бд
                        db::insert('parse_oem_result', [
                            'parse_request_id' => $this->r_id,
                            'f_id' => $this->f_id,
                            'full_name' => $arr[$i]['full_info'],
                            'cost' => $arr[$i]['price_fiz'],
                            'brand' => $arr[$i]['brand'],
                            'address' => $arr[$i]['address'],
                            'telefon' => $arr[$i]['telefon'],
                            'url_detail' => $arr[$i]['url_detail'],
                            'created_at' => 'NOW()',
                        ])->execute();
                    }
                }
            }

        } catch (Exception $e) {
            echo "Error" . $e->getMessage();
            throw $e;
        }

    }


    public function parseAutotradePrice($oem)
    {
        try {
            require_once(dirname(__FILE__) . "/../parse/sites/AutotradeSite.php");

            $autotradeSite = new AutotradeSite($oem, 0);
            $site = $autotradeSite->getSite(29);                         //получение сайта
            $autotradeSite->getFirmName($site['firm_id']);               //получение имени фирмы из бд
            $url = $site['url'] . '/krasnoyarsk/autopart/' . $oem;
            $this->debug_url = $url;
            $html = file_get_html($url);
            if (!$html) {
                throw new Exception();
            }
            if ($html) {
                $data = $autotradeSite->ParseDataPriceWAddress($html);       //получение данных по найденным деталям
                $this->list = $autotradeSite->list;
                $html->clear();
                unset($html);
            }

        } catch (Exception $e) {
            echo "Error" . $e->getMessage();
            throw $e;
        }
        return($data);
    }


    public function parseAutoLeaderPrice($oem)
    {
        try {
            require_once(dirname(__FILE__) . "/../parse/sites/AutoLeaderSite.php");

            $this->status_no_id = 0;                                                   // вычисляем максимально достигнутый $status_no_id для сайта
            $autoLeaderSite = new AutoLeaderSite($oem, 0);
            $site = $autoLeaderSite->getSite(180);                                     //получение сайта
            $autoLeaderSite->getFirmName($site['firm_id']);                            //получение имени фирмы из бд
            $url = $site['url'] . '/catalog/search/?query=' . $oem;
            $cookie_fiz = '_identity=49fe6...';
//            $cookie_ur = '_identity=ef042ce55...';
            $html = $autoLeaderSite->getUrlContent($url, $cookie_fiz);
//            $htmlUr = $autoLeaderSite->getUrlContent($url, $cookie_ur);

            $new_html = str_get_html($html);
//            $new_htmlUr = str_get_html($htmlUr);

            if (!$html) {
                throw new Exception();
            }

            if ($new_html) {
//                $dataUr = $autoLeaderSite->ParseDataUr($new_htmlUr);
                $data = $autoLeaderSite->ParseDataPriceWAddress($new_html);     //получение данных по найденным деталям
                $this->list = $autoLeaderSite->list;
                $new_html->clear();
                unset($new_html);
            }
        } catch (Exception $e) {
            echo "Error" . $e->getMessage();
            throw $e;
        }
        return($data);
    }


    public function parseForvardAvtoPrice($oem)
    {
        try {
            require_once(dirname(__FILE__) . "/../parse/sites/ForvardAvtoSite.php");

            $forvardAvtoSite = new ForvardAvtoSite($oem, 0);
            $site = $forvardAvtoSite->getSite(181);                                      //получение сайта
            $forvardAvtoSite->getFirmName($site['firm_id']);                             //получение имени фирмы из бд
            $url = $site['url'] . '/catalog/search/?q=' . $oem . '&how=r';
            $this->debug_url = $url;

            $html = file_get_html($url);

            if (!$html) {
                throw new Exception();
            }

            if ($html) {
                $data = $forvardAvtoSite->ParseDataPriceWAddress($html);       //получение данных по найденным деталям
                $this->list = $forvardAvtoSite->list;
                unset($html);
            }


        } catch (Exception $e) {
            echo "Error" . $e->getMessage();
            throw $e;
        }
//        var_dump($data);
        return($data);

    }
}
