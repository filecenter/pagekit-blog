<?php

namespace Pagekit\Blog\Controller;

use Pagekit\Application as App;
use Pagekit\Blog\Model\Category;
use Pagekit\Blog\Model\Post;
use Pagekit\Module\Module;
use Pagekit\Routing\Annotation\Route;

/**
 * Class CategoryController
 *
 * @package Pagekit\Blog\Controller
 * @Route("category", name="category")
 */
class CategoryController
{

    /**
     * @var Module
     */
    protected $blog;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->blog = App::module('blog');
    }

    /**
     * The view category action.
     *
     * @param string $slug
     * @param int $page
     *
     * @Route("/{slug}")
     * @Route("/{slug}/page/{page}", name="page", requirements={"page"="\d+"})
     *
     * @return array
     * @throws \Exception
     */
    public function categoryAction($slug, $page = 1)
    {
        /** @var Category $category */
        $category = Category::where(['slug' => trim($slug)])->first();

        if (!$category) {
            App::abort(404, 'Category not found!');
        }

        /** @var \Pagekit\Database\Query\QueryBuilder $query */
        $query = Post::where(['status = ?', 'date < ?'])
            ->where(function ($query) {
                return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
            })
            ->related('user', 'categories')
            ->innerJoin('@blog_categories_post', 'post_id = id AND category_id = ?')
            ->params([$category->id, Post::STATUS_PUBLISHED, new \DateTime]);

        if (!$limit = $this->blog->config('posts.posts_per_page')) {
            $limit = 10;
        }

        $count = $query->count('id');
        $total = ceil($count / $limit);
        $page = max(1, min($total, $page));
        $query->offset(($page - 1) * $limit)->limit($limit)->orderBy('date', 'DESC');

        foreach ($posts = $query->get() as $post) {
            $post->excerpt = App::content()->applyPlugins($post->excerpt, ['post' => $post, 'markdown' => $post->get('markdown')]);
            $post->content = App::content()->applyPlugins($post->content, ['post' => $post, 'markdown' => $post->get('markdown'), 'readmore' => true]);
        }

        return [
            '$view' => [
                'title' => __($category->title),
                'name' => 'blog/category.php',
                'link:feed' => [
                    'rel' => 'alternate',
                    'href' => App::url('@blog/feed'),
                    'title' => App::module('system/site')->config('title'),
                    'type' => App::feed()->create($this->blog->config('feed.type'))->getMIMEType()
                ]
            ],
            'blog' => $this->blog,
            'category' => $category,
            'posts' => $posts,
            'total' => $total,
            'page' => $page
        ];
    }

}