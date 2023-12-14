<?php

class SearchController implements TokenProviderInterface, SearchPerformerInterface, ResultFormatterInterface
{
    private $accessToken;

    public function __construct()
    {
        $this->accessToken = $this->getToken();
        if (!$this->accessToken) {
            die('Failed to get access_token');
        }
    }

    public function getToken()
    {
        $tokenUrl = 'https://production.api.ipaustralia.gov.au/public/external-token-api/v1/access_token';
        $tokenData = [
            'grant_type' => 'client_credentials',
            'client_id' => 'yt9BqH3mSEMtfCnlRnRZoxOCMo9GZqjg',
            'client_secret' => 'iNVQlcbDSk__VktP5xXg3Fmz8_lPo-V2yqOayI9AZIXQ2b4yu-tFjTu-SnIbdf23'
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($tokenData)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($tokenUrl, false, $context);

        return json_decode($response, true)['access_token'] ?? null;
    }

    private function performSearchRequest($searchUrl, $searchData)
    {
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ];

        $ch = curl_init($searchUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($searchData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function performSearch($searchKeyword)
    {
        $searchUrl = 'https://production.api.ipaustralia.gov.au/public/australian-trade-mark-search-api/v1/search/advanced';

        $searchData = [
            'changedSinceDate' => '',
            'rows' => [
                [
                    'op' => 'AND',
                    'query' => [
                        'word' => [
                            'text' => $searchKeyword,
                            'type' => 'PART'
                        ],
                        'wordPhrase' => ''
                    ]
                ]
            ],
            'sort' => [
                'field' => 'NUMBER',
                'direction' => 'ASCENDING',
            ]
        ];

        return $this->performSearchRequest($searchUrl, $searchData);
    }

    public function formatResults($searchResult)
    {
        if (!isset($searchResult['count'])) {
            die("Invalid response format. No count field found.\n" . json_encode($searchResult, JSON_PRETTY_PRINT));
        }

        $totalCount = $searchResult['count'];

        if ($totalCount > 0) {
            echo "Results: $totalCount\n";

            $formattedResults = array_map(function ($trademarkId) {
                return ['number' => $trademarkId];
            }, $searchResult['trademarkIds'] ?? []);

            echo json_encode($formattedResults, JSON_PRETTY_PRINT);
        } else {
            echo "No results found.\n";
        }
    }
}
