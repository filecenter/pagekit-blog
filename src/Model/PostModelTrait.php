<?php

namespace Pagekit\Blog\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\ModelTrait;

trait PostModelTrait
{
    use ModelTrait, UniqueSlugTrait;

    /**
     * Updates the comments info on post.
     *
     * @param int $id
     */
    public static function updateCommentInfo($id)
    {
        $query = Comment::where(['post_id' => $id, 'status' => Comment::STATUS_APPROVED]);

        self::where(compact('id'))->update(['comment_count' => $query->count()]);
    }

    /**
     * Get all users who have written an article
     */
    public static function getAuthors()
    {
        return self::query()->select('user_id', 'name', 'username')->groupBy('user_id', 'name', 'username')->join('@system_user', 'user_id = @system_user.id')->execute()->fetchAll();
    }

    /**
     * @Saving
     */
    public static function saving($event, Post $post)
    {
        $post->modified = new \DateTime();
    }

    /**
     * @Deleting
     */
    public static function deleting($event, Post $post)
    {
        self::getConnection()->delete('@blog_comment', ['post_id' => $post->id]);
    }
}
