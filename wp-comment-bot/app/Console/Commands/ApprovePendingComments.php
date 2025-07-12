<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommentSubmission;
use App\Jobs\ApproveWpComment;

class ApprovePendingComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:approve-pending-comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue all pending WordPress comments for approval via the REST API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $comments = CommentSubmission::where('is_approved', false)->get();

        foreach ($comments as $comment) {
            ApproveWpComment::dispatch($comment);
        }

        $this->info("Queued " . $comments->count() . " comments for approval.");
    }
}
