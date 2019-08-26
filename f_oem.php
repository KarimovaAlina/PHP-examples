<?php
defined('_E92EXEC') or die('Restricted access');
ini_set('max_execution_time','3000');


class f_oem extends basePage
{

    // Конструктор
    public function __construct ($p, $params) {
        parent::__construct($p, $params);
    }

    public  function getContent()
    {
        if ($this->p == "f_oem" && $_GET['req']) $content = $this->getListAjaxPrice();
        elseif ($this->p == "f_oem") $content = $this->getListAjax();
        else return $this->restrict();
        return $content;
    }

    public function getListAjaxPrice()
    {
        try{
            $result = [];
            if (isset($_POST['oem'])){
                $oem = preg_replace("/[^A-Za-z0-9-]/", "", $_POST['oem']);
            }
            if (isset($_POST['city_id'])){
                $city_id = $_POST['city_id'];
            }

            if (empty($oem)) {
                throw new Exception('Вы не ввели OEM');
            }

            //запись запроса в бд
            db::insert('parse_request', [
                'request_id' => '',
                'firm_id' => $this->a->company_id,
                'oem' => $oem,
                'manager_id' => $this->a->company_m_id,
                'user_id' => $this->a->id,
                'emex_status_id' => '',
                'created_at' => 'NOW()',
            ])->execute();
            $last_parse_req_id = db::getInstance()->last_inserted_id();

            //выбрать сайты для парсинга
            $res  = db::select('s.firm_id, s.firm_chain_id, s.id')
                ->from('site s')
                ->where('s.city', '=', 1)
                ->where('s.oem_flag', '=', 1)
                ->where('s.id', '!=', 61)
                ->where('s.id', '!=', 184)
                ->where('s.id', '!=', 202)
                ->executeArr();


            for ($i = 0; $i < count($res); $i++) {
                F::job(new ParserOEM($oem, $res[$i]['id'], $res[$i]['firm_id'], $res[$i]['firm_chain_id'], $last_parse_req_id));
//                (new ParserOEM($oem, $res[$i]['id'], $res[$i]['firm_id'], $res[$i]['firm_chain_id'], $last_parse_req_id))->handle();
////                if (time() > ($startTime + 40)) break;
//                $query = "SELECT COUNT(*) as count FROM parse_oem_result WHERE parse_request_id = '{$last_parse_req_id}' GROUP BY f_id;";
//                $row = db::executeRawArr($query);
//                if (count($row) == count($res)) break;
            }

//             ждать результата
            $startTime = time();
            while (1){
                if (time() > ($startTime + 40)) break;
                $query = "SELECT COUNT(*) as count FROM parse_oem_result WHERE parse_request_id = '{$last_parse_req_id}' GROUP BY f_id;";
                $row = db::executeRawArr($query);
                if (count($row) == count($res)) break;
            }

            //селект результатов из бд
            for ($i = 0; $i < count($res); $i++) {
                $temp[$i] = db::select('por.f_id, por.full_name, por.cost, por.brand, por.address, por.telefon, por.url_detail, f.name_commercial, p.party')
                    ->from('parse_oem_result por')
                    ->join('firms f ON f.id = "'.$res[$i]['firm_id'].'"')
                    ->join('packet_1 p ON p.id_firms = "'.$res[$i]['firm_id'].'"')
                    ->where('por.parse_request_id', '=', $last_parse_req_id)
                    ->where('por.f_id', '=', $res[$i]['firm_id'])
                ->execute();
            }

            $j = 0;
            $k = 0;
            for ($i = 0; $i < count($temp); $i++) {
                if ($temp[$i]['cost'] != null) {
                    if ($temp[$i]['cost'] == 0) {
                        $result['no_detail'][$j]['name_commercial'] = $temp[$i]['name_commercial'];
                        $result['no_detail'][$j]['f_id'] = $temp[$i]['f_id'];
                        $j++;
                    } else {
                        $result['list'][$k] = $temp[$i];
                        $k++;
                    }
                    continue;
                }

                for ($n = 0; $n < count($temp[$i]); $n++) {
                    if ($temp[$i][$n]['cost'] != null) {
                        $result['list'][$k] = $temp[$i][$n];
                        $k++;
                    }
                }
            }

            if (empty($result['list'])) {
                $result['result'] = 0;

            } else {
                $result['result'] = 1;
            }


        } catch (Exception $e) {
            $result['result'] = 0;
            $result['message'] = $e->getMessage();
        }
        $this->renderText( json_encode($result,JSON_UNESCAPED_UNICODE), true, 'application/json' );
    }
}
