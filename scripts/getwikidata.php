<?php
/**
 * script for fetching a random fact from the "On this day..." page of wikipedia
 *
 */

setlocale(LC_ALL, 'de_DE.utf8');

const CAT_BORN = "Geboren";
const CAT_DIED = "Gestorben";

$arr_all_cats = array(
    "Politik und Weltgeschehen",
    "Wirtschaft",
    "Wissenschaft und Technik",
    "Kultur",
    "Gesellschaft",
    "Religion",
    "Katastrophen",
    "Natur und Umwelt",
    "Sport",
    CAT_BORN,
    CAT_DIED);

$arr_short_cats = array("politik",
    "wirtschaft",
    "wissenschaft",
    "kultur",
    "gesellschaft",
    "religion",
    "katastrophen",
    "natur",
    "sport",
    "geboren",
    "gestorben");

$out_put_cat	= parseArgs();
$arr_out 	    = array();
$date 		    = trim(strftime("%e._%B"));
$url          = "http://de.wikipedia.org/w/api.php?action=query&prop=extracts&titles=".$date."&format=json&continue=";

$regex_start  = "%<h[1234]>Ereignisse<\/h[1234]>%";
$regx_cat     = "%<h[234]>(.*?)<\/h[234]>%";
$regex_er     = "%<li>([0-9]{1,4}: .*?)<\/li>%";

$page         = file_get_contents($url);
$arr_data     = json_decode($page,true);
$data         = "";
$arr_cat      = array();

foreach($arr_data['query']['pages'] as $arr_page)
{
    $data = $arr_page["extract"];
}

$arr_cats = preg_split($regx_cat,$data,0,PREG_SPLIT_DELIM_CAPTURE);
$count    = count($arr_cats);
$act_cat  = "";

for($i = 0;$i<$count;$i++)
{
    $str_cat      = $arr_cats[$i];
    $str_cat_raw  = strip_tags($str_cat);
    $str_cat_raw  = str_ireplace(array('§'), '', $str_cat_raw);
    $arr_event    = array();

    if(in_array($str_cat_raw,$arr_all_cats))
    {
        $act_cat = $str_cat_raw;
        continue;
    }

    if(preg_match_all($regex_er ,$str_cat, $arr_event))
    {
        foreach ($arr_event[1] as $event) {
          $year_stop = strpos($event, ":");
          $year = substr($event, 0 ,$year_stop);
          $arr_out[$year][] = substr($event,$year_stop+2);
        }

    }
}

var_export($arr_out);

//cleanup
//$str_out.= strip_tags(htmlspecialchars_decode($arr_event[$event_rand_idx]));

/**
 * strips output string
 *
 * @param string $cat
 * @param array $arr_out
 * @return string
 */
function render($cat,$arr_out)
{
    $str_out = "";

    if(!empty($arr_out[$cat]))
    {
        $arr_event = $arr_out[$cat];
        $event_rand_idx = rand(0, count($arr_event)-1);
        if($cat == CAT_BORN)
        {
            $str_out = CAT_BORN." am ";
        }
        elseif($cat == CAT_DIED)
        {
            $str_out = CAT_DIED." am ";
        }

        $str_out.= date("d.m.");
        $str_out.= strip_tags(htmlspecialchars_decode($arr_event[$event_rand_idx]));
    }
    else
    {
        $str_out.= "nothing on in ".$cat."\n";
    }
    return $str_out;
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
