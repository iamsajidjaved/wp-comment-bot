<?php
namespace App\Console\Commands;

use App\Models\CommentSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishWpComment extends Command
{
    protected $signature   = 'comment:publish';
    protected $description = 'Publish WordPress comment and approve it immediately via REST API';

    public function handle()
    {
        $posts    = file(base_path('comments/posts.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $comments = file(base_path('comments/comments.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $emails   = file(base_path('comments/emails.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $authors  = file(base_path('comments/authors.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (count($emails) !== count($authors)) {
            $this->error("Emails and authors count mismatch.");
            return 1;
        }

        $postUrl     = $posts[array_rand($posts)];
        $commentText = $comments[array_rand($comments)];

        $index  = rand(0, count($emails) - 1);
        $email  = $emails[$index];
        $author = $authors[$index];

        $payload = [
            'postUrl' => $postUrl,
            'comment' => $commentText,
            'email'   => $email,
            'author'  => $author,
        ];

        Log::info($payload);

        $tempFile = storage_path('app/temp_comment_payload_' . uniqid() . '.json');
        File::put($tempFile, json_encode($payload));

        $scriptPath = base_path('puppeteer-scripts/comment-publish.js');
        $command    = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempFile);

        $output = shell_exec($command);
        File::delete($tempFile);

        $this->line("Raw Puppeteer Output: $output");

        $result = json_decode($output, true);

        if (isset($result['commentId'])) {
            $commentSubmission = CommentSubmission::create([
                'post_url'   => $postUrl,
                'comment_id' => $result['commentId'],
                'author'     => $author,
                'email'      => $email,
                'comment'    => $commentText,
                'status'     => 'pending',
            ]);

            $site   = parse_url($postUrl, PHP_URL_HOST);
            $apiUrl = "https://$site/wp-json/wp/v2/comments/{$result['commentId']}";

            $response = Http::withBasicAuth(
                env('WP_USERNAME'),
                env('WP_APP_PASSWORD')
            )->post($apiUrl, ['status' => 'approve']);

            if ($response->successful()) {
                $commentSubmission->status = 'approved';
                $commentSubmission->save();

                $this->info("✅ Comment published and approved. Comment ID: {$result['commentId']}");
            } else {
                $commentSubmission->status = 'pending'; // or 'failed' if you want
                $commentSubmission->save();

                $this->warn("⚠️ Comment published but approval failed. Comment ID: {$result['commentId']}");
            }
        } else {
            $this->error("❌ Failed to publish comment. Error: " . ($result['error'] ?? 'Unknown error'));
        }

        return 0;
    }
}
