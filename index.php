<?php

require_once '..\MyProjects\src\Interfaces\TokenProviderInterface.php';
require_once '..\MyProjects\src\Interfaces\SearchPerformerInterface.php';
require_once '..\MyProjects\src\Interfaces\ResultFormatterInterface.php';
require_once '..\MyProjects\src\Controllers\SearchController.php';

if ($argc < 2) {
    die("Usage: php index.php <search_keyword>\n");
}

$searchKeyword = $argv[1];

$searchObj = new SearchController();
$searchResult = $searchObj->performSearch($searchKeyword);
$searchObj->formatResults($searchResult);