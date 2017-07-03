<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Goutte\Client;
use Illuminate\Support\Facades\DB;

class ParseController extends Controller
{
    /**
     * Parser
     * @return array
     */
    public function getData()
    {
        $link = "http://www.bills.ru/";
        $client = new Client();
        $crawler = $client->request('GET', $link);
        $result = array();
        $container = $crawler->filter('#bizon_api_news_list')->first();
        $container->filter('tr')->each(function ($node) use (&$result) {
            $title = $node->filter('a')->text();
            $date = $node->filter('.news_date')->text();
            // date processing
            $search  = array('янв' , 'фев' , 'мар' , 'апр' , 'мая' , 'июн' , 'июл' , 'авг' , 'сен' , 'окт' , 'ноя' , 'дек', ' ');
            $replace = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '.');
            $news_date = trim($date).date(" Y");
            $news_date = str_replace($search, $replace, $news_date);
            $news_date = strtotime($news_date);
            if ($news_date > time()) {
                $news_date = strtotime("-1 year", $news_date);
            }
            $news_date = date("Y-m-d H:i:s", $news_date);

            $url = trim($node->filter('a')->attr('href'));
            $element = DB::table('bills_ru_events')->where('url', $url)->first();
            if ( ! $element) {
                $item = array(
                    'title' => trim($title),
                    'date' => $news_date,
                    'url' => $url
                );
                $result[] = $item;
            }
        });
        // dd($result);
        if ($result) {
            DB::table('bills_ru_events')->insert($result);
        }
        return $result;
    }
}
