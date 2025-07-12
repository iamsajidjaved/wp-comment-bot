<?php
namespace App\Jobs;

use App\Models\CommentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class ApproveWpComment implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct(CommentSubmission $comment)
    {
        $this->comment = $comment;
    }

    public function handle(): void
    {
        $site   = parse_url($this->comment->post_url, PHP_URL_HOST);
        $apiUrl = "https://$site/wp-json/wp/v2/comments/{$this->comment->comment_id}";

        $response = Http::withBasicAuth(
            env('WP_USERNAME'),
            env('WP_APP_PASSWORD')
        )->post($apiUrl, [
            'status' => 'approve',
        ]);

        if ($response->successful()) {
            $this->comment->is_approved = true;
            $this->comment->save();
        }
    }
}
