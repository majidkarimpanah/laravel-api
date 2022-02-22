<?php

namespace App\Http\Controllers;

use App\ListModel;
use App\User;
use Auth;
use Common\Comments\Comment;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Str;

class UserProfileController extends BaseController
{
    public function show(User $user)
    {
        $this->authorize('show', $user);

        return $this->success(['user' => $user]);
    }

    public function loadLists(User $user)
    {
        $this->authorize('show', $user);

        $builder = $user->lists();

        if (Auth::id() !== $user->id) {
            $builder->where('public', true);
        }

        $datasource = new MysqlDataSource($builder, request()->all());

        $pagination = $datasource->paginate();

        $pagination->transform(function (ListModel $list) {
            $list->description = Str::limit($list->description, 80);
            return $list;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function loadRatings(User $user)
    {
        $this->authorize('show', $user);

        $datasource = new MysqlDataSource(
            $user
                ->reviews()
                ->whereNull('body')
                ->with(['reviewable']),
            request()->all(),
        );

        $pagination = $datasource->paginate();
        return $this->success(['pagination' => $pagination]);
    }

    public function loadReviews(User $user)
    {
        $this->authorize('show', $user);

        $datasource = new MysqlDataSource(
            $user
                ->reviews()
                ->whereNotNull('body')
                ->with(['reviewable']),
            request()->all(),
        );

        $pagination = $datasource->paginate();
        return $this->success(['pagination' => $pagination]);
    }

    public function loadComments(User $user)
    {
        $this->authorize('show', $user);

        $datasource = new MysqlDataSource(
            $user
                ->comments()
                ->with(['commentable', 'user'])
                ->where('deleted', false),
            request()->all(),
        );

        $pagination = $datasource->paginate();

        $pagination->transform(function (Comment $comment) {
            $comment->relative_created_at = $comment->created_at->diffForHumans();
            unset($comment->created_at);
            unset($comment->updated_at);
            if ($comment->relationLoaded('commentable')) {
                $normalized = $comment->commentable->toNormalizedArray();
                $comment->unsetRelation('commentable');
                $comment->setAttribute('commentable', $normalized);
            }
            return $comment;
        });

        return $this->success(['pagination' => $pagination]);
    }
}
