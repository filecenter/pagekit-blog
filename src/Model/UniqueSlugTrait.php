<?php

namespace Pagekit\Blog\Model;

use Pagekit\Database\ORM\Annotation\Saving;
use Pagekit\Database\ORM\ModelTrait;
use Pagekit\Event\Event;

/**
 * Trait UniqueSlugTrait
 * @package Pagekit\Blog\Model
 */
trait UniqueSlugTrait
{

    /**
     * @Saving()
     *
     * @param Event $event
     * @param object $model
     */
    public static function uniqueSlugEvent(Event $event, $model)
    {
        if (is_subclass_of($model, ModelTrait::class)) {
            throw new \InvalidArgumentException('Argument 2 must be Pagekit model');
        }

        $i = 2;
        $id = $model->id;

        while (self::where('slug = ?', [$model->slug])
            ->where(function ($query) use ($id) {
                if ($id) {
                    $query->where('id <> ?', [$id]);
                }
            })
            ->first()) {
            $model->slug = preg_replace('/-\d+$/', '', $model->slug) . '-' . $i++;
        }
    }

}