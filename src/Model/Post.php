<?php

namespace Pagekit\Blog\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\Annotation\BelongsTo;
use Pagekit\Database\ORM\Annotation\Column;
use Pagekit\Database\ORM\Annotation\Entity;
use Pagekit\Database\ORM\Annotation\HasMany;
use Pagekit\Database\ORM\Annotation\Id;
use Pagekit\Database\ORM\Annotation\ManyToMany;
use Pagekit\Database\ORM\Annotation\OrderBy;
use Pagekit\System\Model\DataModelTrait;
use Pagekit\User\Model\AccessModelTrait;
use Pagekit\User\Model\User;

/**
 * Class Post
 *
 * @package Pagekit\Blog\Model
 * @Entity(tableClass="@blog_post")
 */
class Post implements \JsonSerializable
{
    use AccessModelTrait, DataModelTrait, PostModelTrait;

    /* Post draft status. */
    const STATUS_DRAFT = 0;

    /* Post pending review status. */
    const STATUS_PENDING_REVIEW = 1;

    /* Post published. */
    const STATUS_PUBLISHED = 2;

    /* Post unpublished. */
    const STATUS_UNPUBLISHED = 3;

    /**
     * @var integer
     * @Column(type="integer")
     * @Id()
     */
    public $id;

    /**
     * @var string
     * @Column(type="string")
     */
    public $title;

    /**
     * @var string
     * @Column(type="string")
     */
    public $slug;

    /**
     * @var integer
     * @Column(type="integer")
     */
    public $user_id;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $date;

    /**
     * @var string
     * @Column(type="text")
     */
    public $content = '';

    /**
     * @var string
     * @Column(type="text")
     */
    public $excerpt = '';

    /**
     * @var integer
     * @Column(type="smallint")
     */
    public $status;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    public $modified;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    public $comment_status;

    /**
     * @var integer
     * @Column(type="integer")
     */
    public $comment_count = 0;

    /**
     * @var int Post views counter
     * @Column(type="integer")
     */
    public $views = 0;

    /**
     * @var User
     * @BelongsTo(targetEntity="Pagekit\User\Model\User", keyFrom="user_id")
     */
    public $user;

    /**
     * @var Comment[]
     * @HasMany(targetEntity="Comment", keyFrom="id", keyTo="post_id")
     * @OrderBy({"created" = "DESC"})
     */
    public $comments;

    /**
     * @var Category[]
     * @ManyToMany(targetEntity="Category", tableThrough="@blog_categories_post", keyThroughFrom="post_id", keyThroughTo="category_id")
     */
    public $categories = [];

    /**
     * @var array
     */
    protected static $properties = [
        'author' => 'getAuthor',
        'published' => 'isPublished',
        'accessible' => 'isAccessible'
    ];

    public static function getStatuses()
    {
        return [
            self::STATUS_PUBLISHED => __('Published'),
            self::STATUS_UNPUBLISHED => __('Unpublished'),
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PENDING_REVIEW => __('Pending Review')
        ];
    }

    public function getStatusText()
    {
        $statuses = self::getStatuses();

        return isset($statuses[$this->status]) ? $statuses[$this->status] : __('Unknown');
    }

    public function isCommentable()
    {
        $blog = App::module('blog');
        $autoclose = $blog->config('comments.autoclose') ? $blog->config('comments.autoclose_days') : 0;

        return $this->comment_status && (!$autoclose or $this->date >= new \DateTime("-{$autoclose} day"));
    }

    public function getAuthor()
    {
        return $this->user ? $this->user->username : null;
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED && $this->date < new \DateTime;
    }

    public function isAccessible(User $user = null)
    {
        return $this->isPublished() && $this->hasAccess($user ?: App::user());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = [
            'url' => App::url('@blog/id', ['id' => $this->id ?: 0], 'base')
        ];

        if ($this->comments) {
            $data['comments_pending'] = count(array_filter($this->comments, function ($comment) {
                return $comment->status == Comment::STATUS_PENDING;
            }));
        }

        if ($this->categories) {
            $data['categories'] = array_column($this->categories, 'id');
        }

        return $this->toArray($data);
    }
}
