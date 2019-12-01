<?php

namespace Pagekit\Blog\Controller;

use Pagekit\Application;
use Pagekit\Blog\Model\Category;

/**
 * Class CategoryApiController
 * @package Pagekit\Blog\Controller
 * @Route("category", name="category")
 */
class CategoryApiController
{

    /**
     * Returns the list of categories.
     *
     * @param array $filter
     * @param int $page
     *
     * @Route("/", methods="GET")
     * @Request({"filter": "array", "page":"int"})
     * @Access("blog: manage all posts")
     *
     * @return array
     */
    public function indexAction(array $filter = [], $page = 0)
    {
        $limit = (isset($filter['limit']) && $filter['limit'] > 0 && $filter['limit'] < 100) ? (int)$filter['limit'] : 25; // default items per page

        if (!isset($filter['order']) || trim($filter['order']) == '' || !preg_match('/^(title|date)\s(asc|desc)$/i', $filter['order'], $order)) {
            $order = [1 => 'title', 2 => 'asc'];
        } else {
            $order[1] = ($order[1] == 'date') ? 'created_at' : $order[1];
        }

        $queryBuilder = Category::query();

        if (!empty($filter['search'])) {
            $search_terms = trim($filter['search']);
            $queryBuilder->where(function ($query) use ($search_terms) {
                $query->orWhere(['title LIKE :query', 'slug LIKE :query', 'description LIKE :query'], ['query' => "%{$search_terms}%"]);
            });
        }

        $count = $queryBuilder->count();
        $pages = ceil($count / $limit);

        return [
            'count' => $count,
            'pages' => $pages,
            'categories' => array_values($queryBuilder->offset(max(0, min($pages - 1, $page)) * $limit)
                ->limit($limit)
                ->orderBy($order[1], $order[2])
                ->get())
        ];
    }

    /**
     * The save category action.
     *
     * @param array $category_data
     * @param int $id
     *
     * @Route("/", methods="POST")
     * @Route("/{id}", methods="POST", requirements={"id"="\d+"})
     * @Request({"category": "array", "id": "int"}, csrf=true)
     * @Access("blog: manage all posts")
     *
     * @return array
     */
    public function saveAction($category_data, $id = 0)
    {
        if (!$id || !$category = Category::find($id)) {
            if ($id) {
                Application::abort(404, 'Category not found');
            }

            $category = new Category();
        }

        $category_data = array_intersect_key($category_data, ['title' => null, 'description' => null, 'slug' => null, 'data' => null]);

        if (empty($category_data['title']) || trim($category_data['title']) === '') {
            Application::abort(400, 'Invalid the title of category');
        }

        if (!$category_data['slug'] = Application::filter($category_data['slug'] ?: $category_data['title'], 'slugify')) {
            Application::abort(400, 'Invalid the slug of category');
        }

        $category_data['title'] = htmlspecialchars(trim($category_data['title']), ENT_QUOTES, 'UTF-8');

        if (isset($category_data['description'])) {
            $category_data['description'] = trim($category_data['description']);
        }

        try {
            $category->save($category_data);
        } catch (\Exception $exception) {
            Application::abort(400, $exception->getMessage());
        }

        return [
            'message' => 'success',
            'category' => $category
        ];
    }

    /**
     * The delete category action.
     *
     * @param int $id
     *
     * @Route("/{id}", methods="DELETE", requirements={"id"="\d+"})
     * @Request({"id": "int"}, csrf=true)
     * @Access("blog: manage all posts")
     *
     * @return array
     */
    public function deleteAction($id)
    {
        if ($category = Category::find($id)) {
            $category->delete();
        }

        return ['message' => 'success'];
    }

    /**
     * The bulk delete action.
     *
     * @param array $ids
     *
     * @Route("/bulk", methods="DELETE")
     * @Request({"ids": "array"}, csrf=true)
     * @Access("blog: manage all posts")
     *
     * @return array
     */
    public function bulkDeleteAction(array $ids = [])
    {
        foreach (array_filter($ids) as $id) {
            $this->deleteAction($id);
        }

        return ['message' => 'success'];
    }

}