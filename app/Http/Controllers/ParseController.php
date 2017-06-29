<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Goutte\Client;
use Illuminate\Support\Facades\DB;

class ParseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Parse data
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
            $url = $node->filter('a')->attr('href');
            $item = array(
                'title' => trim($title),
                // 'date' => trim($date),
                'date' => date("Y-m-d H:i:s"),
                'url' => trim($url)
            );
            $result[] = $item;
        });
        DB::table('bills_ru_events')->insert($result);
        return $result;
    }
}
