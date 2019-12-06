<?php

namespace Pagekit\Blog\Model;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Pagekit\Application;
use Pagekit\Database\ORM\ModelTrait;
use Pagekit\Event\Event;

/**
 * Class Category
 * @package Pagekit\Blog\Model
 * @Entity(tableClass="@blog_categories")
 */
class Category implements \JsonSerializable
{
    use ModelTrait, UniqueSlugTrait;

    /**
     * @var int
     * @Column(type="integer")
     * @Id()
     */
    public $id;

    /**
     * @var string
     * @Column(type="string")
     */
    public $slug;

    /**
     * @var string
     * @Column(type="string")
     */
    public $title;

    /**
     * @var string
     * @Column(type="string")
     */
    public $description;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $created_at;

    /**
     * @var Post[]
     * @ManyToMany(targetEntity="Post", tableThrough="@blog_categories_post", keyThroughFrom="category_id", keyThroughTo="post_id")
     */
    public $posts;

    /**
     * Before creating.
     *
     * @Init()
     * @Creating()
     *
     * @param Event $event
     * @param Category $category
     */
    public static function onModelInit(Event $event, Category $category)
    {
        if ($category->created_at instanceof \DateTime) {
            return;
        }

        try {
            $category->created_at = new \DateTime();
        } catch (\Exception $exception) {
        }
    }
    /**
     * Before saving.
     *
     * @Saving()
     *
     * @param Event    $event
     * @param Category $category
     */
    public static function onBeforeSaving(Event $event, Category $category)
    {
        // Cut all script tags
        $category->description = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $category->description);
    }

    /**
     * After deleted.
     *
     * @Deleted()
     *
     * @param Event $event
     * @param Category $category
     * @throws InvalidArgumentException
     */
    public static function onDeleted(Event $event, Category $category)
    {
        self::getConnection()->delete('@blog_categories_post', ['category_id' => $category->id]);
    }

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->onModelInit(new Event('model.category.init'), $this);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            'url' => Application::url('@blog/category', ['slug' => $this->slug ?: null], 'base')
        ];

        return $this->toArray($data);
    }

}