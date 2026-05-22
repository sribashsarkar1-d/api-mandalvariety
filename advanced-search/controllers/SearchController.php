<?php
namespace Controllers;
use Core\Request;
use Core\Response;
use Models\Search;
use Helpers\SearchHelper;

class SearchController {
    private $searchModel;

    public function __construct() {
        $this->searchModel = new Search();
    }

    private function getFilters() {
        return [
            'category_id' => Request::getQuery('category_id'),
            'min_price' => Request::getQuery('min_price'),
            'max_price' => Request::getQuery('max_price'),
            'in_stock' => Request::getQuery('in_stock'),
            'sort' => Request::getQuery('sort', 'relevance'),
            'page' => max(1, (int)Request::getQuery('page', 1)),
            'limit' => min(100, max(1, (int)Request::getQuery('limit', 20)))
        ];
    }

    public function globalSearch() {
        $start_time = microtime(true);
        $raw_q = Request::getQuery('q', '');
        $q = SearchHelper::normalize($raw_q);
        $filters = $this->getFilters();
        $offset = ($filters['page'] - 1) * $filters['limit'];

        $products = [];
        $categories = [];
        $related = [];
        $did_you_mean = null;

        if (!empty($q)) {
            $products = $this->searchModel->globalSearch($raw_q, $filters['limit'], $offset, $filters, $filters['sort']);
            $categories = $this->searchModel->searchCategories($raw_q, 5);

            if (empty($products) && empty($categories)) {
                $did_you_mean = $this->searchModel->getClosestMatch($q);
                if ($did_you_mean) {
                    $related = $this->searchModel->globalSearch($did_you_mean, 5, 0, [], 'relevance');
                } else {
                    $related = $this->searchModel->getRelatedProductsByCategoryFallback(5);
                }
            }
        } else {
            $products = $this->searchModel->globalSearch('', $filters['limit'], $offset, $filters, $filters['sort']);
        }

        $total_results = count($products) + count($categories);
        $this->searchModel->logSearch($raw_q, $q, $total_results, 'global');

        $time_ms = round((microtime(true) - $start_time) * 1000, 2);

        Response::json([
            'success' => true,
            'message' => empty($products) && empty($categories) ? 'No exact match found, showing related results' : 'Search results found',
            'query' => $raw_q,
            'normalized_query' => $q,
            'did_you_mean' => $did_you_mean,
            'data' => [
                'products' => $products,
                'categories' => $categories,
                'suggestions' => [],
                'related' => $related
            ],
            'meta' => [
                'total_products' => count($products),
                'total_categories' => count($categories),
                'search_time_ms' => $time_ms,
                'page' => $filters['page'],
                'limit' => $filters['limit']
            ]
        ]);
    }

    public function productsSearch() {
        $raw_q = Request::getQuery('q', '');
        $filters = $this->getFilters();
        $offset = ($filters['page'] - 1) * $filters['limit'];
        $products = $this->searchModel->globalSearch($raw_q, $filters['limit'], $offset, $filters, $filters['sort']);
        Response::json(['success' => true, 'data' => ['products' => $products]]);
    }

    public function categoriesSearch() {
        $raw_q = Request::getQuery('q', '');
        $categories = $this->searchModel->searchCategories($raw_q, 20);
        Response::json(['success' => true, 'data' => ['categories' => $categories]]);
    }

    public function suggestions() {
        $raw_q = Request::getQuery('q', '');
        $q = SearchHelper::normalize($raw_q);
        if (strlen($q) < 2) {
            Response::json(['success' => true, 'data' => ['products' => [], 'categories' => [], 'keywords' => []]]);
        }

        $products = $this->searchModel->globalSearch($raw_q, 5, 0, [], 'relevance');
        $categories = $this->searchModel->searchCategories($raw_q, 3);
        
        $p_names = array_column($products, 'name');
        $c_names = array_column($categories, 'name');
        
        $keywords = [];
        if (!empty($p_names)) {
            $keywords[] = strtolower($p_names[0]);
        }

        Response::json([
            'success' => true,
            'data' => [
                'products' => $p_names,
                'categories' => $c_names,
                'keywords' => $keywords
            ]
        ]);
    }

    public function related() {
        $related = $this->searchModel->getRelatedProductsByCategoryFallback(10);
        Response::json(['success' => true, 'data' => ['related' => $related]]);
    }

    public function popular() {
        $popular = $this->searchModel->getPopularSearches();
        Response::json(['success' => true, 'data' => ['popular_keywords' => $popular]]);
    }

    public function voiceSearch() {
        $json = Request::getJson();
        $query = $json['query'] ?? '';
        
        if (empty($query)) {
            Response::error('Voice query is empty', 400);
        }

        $_GET['q'] = $query;
        $this->globalSearch(); 
    }
}
