<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Events\CommentApproved;
use WebDevEtc\BlogEtc\Events\CommentWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

/**
 * Class BlogEtcCommentsAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCommentsAdminController extends Controller
{


    /**
     * BlogEtcCommentsAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
    }

    /**
     * Show all comments (and show buttons with approve/delete)
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        return view("blogetc_admin::comments.index")->withComments(BlogEtcComment::orderBy("created_at", "desc")->with("post")->paginate(100));
    }


    /**
     * Approve a comment
     *
     * @param $blogCommentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($blogCommentId)
    {
        $comment = BlogEtcComment::findOrFail($blogCommentId);
        $comment->approved = true;
        $comment->save();

        Helpers::flash_message("Approved!");

        event(new CommentApproved($comment));

        return back();
    }

    /**
     * Delete a submitted comment
     *
     * @param $blogCommentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($blogCommentId)
    {
        $comment = BlogEtcComment::findOrFail($blogCommentId);
        event(new CommentWillBeDeleted($comment));
        $comment->delete();
        Helpers::flash_message("Deleted!");
        return back();
    }


}