<?php

namespace App\Services\Data\News;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\Data\Contracts\NewsProviderInterface;

class ImdbNewsProvider implements NewsProviderInterface
{
    /**
     * @var Client
     */
    private $http;

    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function getArticles()
    {
        $compiledNews = [];

        $html = $this->http->get('https://www.imdb.com/news/top')->getBody()->getContents();
        $strippedHtml = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $html);

        $crawler = new Crawler($strippedHtml);

        // grab every news article on the page
        foreach ($crawler->filter('#news-article-list .news-article') as $k => $node) {
            $articleCrawler = new Crawler($node);

            $fullUrl = head($articleCrawler->filter('.news-content__offsite-link')->extract(['href']));
            if ( ! isset(parse_url($fullUrl)['scheme'])) {
                $fullUrl = 'http://imdb.com' . $fullUrl;
            }
            $fullUrlText = head($articleCrawler->filter('.news-content__offsite-link')->extract(['_text']));
            $img = head($articleCrawler->filter('img')->extract(['src']));
            $body = trim(head($articleCrawler->filter('.news-article__content')->extract(['_text']))) . '...';
            $body .= '<p><a target="_blank" href='.$fullUrl.'>'.$fullUrlText.'</a></p>';

            if ( ! $img) continue;

            // transform each news article into array
            $compiledNews[$k] = [
                'title' => trim(head($articleCrawler->filter('h2')->extract(['_text']))),
                'body' => $body,
                'meta' => [
                    'source'   => trim(head($articleCrawler->filter('.news-article__source')->extract(['_text']))),
                    'image'	   => preg_replace("/([A-Z]+)([0-9]+)_CR([0-9]+),([0-9]+),100,150/", '${1}400_CR$3,$4,270,400', $img),
                ]
            ];
        }

        return collect($compiledNews);
    }
}
