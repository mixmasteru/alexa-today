<?php

/**
 * Export wiki articles to json
 *
 */
class WikiExport
{
    const CAT_POWE = "Politik und Weltgeschehen";
    const CAT_WIRT = "Wirtschaft";
    const CAT_WITE = "Wissenschaft und Technik";
    const CAT_KULT = "Kultur";
    const CAT_GESE = "Gesellschaft";
    const CAT_RELI = "Religion";
    const CAT_KATA = "Katastrophen";
    const CAT_NAUM = "Natur und Umwelt";
    const CAT_SPOR = "Sport";
    const CAT_BORN = "Geboren";
    const CAT_DIED = "Gestorben";

    /**
     * all categories of wiki
     * @var array
     */
    protected $arr_all_cats = array(self::CAT_POWE, self::CAT_WIRT, self::CAT_WIRT, self::CAT_WITE, self::CAT_KULT,
                                    self::CAT_GESE, self::CAT_RELI, self::CAT_KATA, self::CAT_NAUM, self::CAT_SPOR,
                                    self::CAT_BORN, self::CAT_DIED);
    /**
     * all categories to export
     * @var array
     */
    protected $arr_exp_cats = array(self::CAT_POWE, self::CAT_WIRT, self::CAT_WIRT, self::CAT_WITE, self::CAT_KULT,
                                    self::CAT_GESE, self::CAT_RELI, self::CAT_KATA, self::CAT_NAUM, self::CAT_SPOR);
    
    //https://de.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&explaintext=&exsectionformat=plain&redirects=&titles=22._August
    const URL_WIKI      = "http://de.wikipedia.org/w/api.php?action=query&prop=extracts&titles=#DATE#&format=json&continue=";
    const REGEX_START   = "%<h[1234]>Ereignisse<\/h[1234]>%";
    const REGEX_CAT     = "%<h[234]>(.*?)<\/h[234]>%";
    const REGEX_ER      = "%<li>([0-9]{1,4}: .*?)<\/li>%";
    const REGEX_ER_MORE = "%<li>(.*[0-9]{1,4}( v. Chr.)?: .*?)<\/li>%";
    const STR_VCHR      = " v. Chr.";

    /**
     * @param $date
     * @return array
     */
    public function exportEvents($date){

        $arr_out	= array();
        $date_str 	= trim(strftime("%e._%B",strtotime($date)));
        $url		= str_ireplace("#DATE#",$date_str,self::URL_WIKI);

        $page		= file_get_contents($url);
        $arr_data	= json_decode($page,true);
        $data       = array_pop($arr_data['query']['pages']);
        $data       = $data['extract'];

        $arr_cats = preg_split(self::REGEX_CAT,$data,0,PREG_SPLIT_DELIM_CAPTURE);
        $count    = count($arr_cats);
        $act_cat  = "";

        for ($i = 0;$i<$count;$i++) {
            $str_cat      = $arr_cats[$i];
            $str_cat_raw  = strip_tags($str_cat);
            $str_cat_raw  = str_ireplace(array('§'), '', $str_cat_raw);
            $arr_event    = array();

            if (in_array($str_cat_raw,$this->arr_all_cats)) {
                $act_cat = $str_cat_raw;
                continue;
            }
            if (!in_array($act_cat,$this->arr_exp_cats)) {
                continue;
            }

            if (preg_match_all(self::REGEX_ER_MORE, $str_cat, $arr_event)) {
                foreach ($arr_event[1] as $event) {
                    $event =  $this->cleanEvent($event);

                    $year_stop = strpos($event, ":");
                    $year = substr($event, 0 ,$year_stop);

                    $event = substr($event,$year_stop+2); //cut year
                    $arr_out[$year][] = $event;
                }

            }
        }

        ksort($arr_out);
        return $arr_out;
    }


    /**
     * @param $event
     * @return mixed|string
     */
    protected function cleanEvent($event){
        $event = trim($event);
        $event = str_ireplace("<span>0</span>","",$event);
        if(strpos($event,self::STR_VCHR))
        {
            $event = "-".str_ireplace(self::STR_VCHR,"",$event);
        }
        
        $event = strip_tags(htmlspecialchars_decode($event));
        return $event;
    }

    /**
     * parses cli arguments
     *
     * @return string
     */
    function parseArgs()
    {
        global $arr_all_cats;
        global $arr_short_cats;
        global $argv;
        $cat = "";

        if(!empty($argv[1]))
        {
            $arg = $argv[1];
            if(in_array($arg, $arr_all_cats))
            {
                $cat = $arg;
            }
            elseif(is_int(array_search($arg, $arr_short_cats)))
            {
                $cat = $arr_all_cats[array_search($arg, $arr_short_cats)];
            }
            elseif($arg == 'help')
            {
                echo "Usage: !today [category]\n";
                echo "categories: ".implode(",",$arr_short_cats)."\n";
                die;
            }
            else
            {
                echo "unknown category '".$arg."'\n";
                die;
            }
        }
        return $cat;
    }
}