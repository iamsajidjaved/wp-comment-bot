
# WP Comment Bot

A Laravel-based CLI tool and Puppeteer script to automate posting and approving comments on WordPress sites via the REST API. 

**Purpose:**
This project is designed primarily for SEO professionals, developers, and testers who want to increase the number of comments on WordPress posts for SEO purposes. Pages with lots of comments can be a signal of user interaction and quality—one Googler even said comments can help “a lot” with rankings. By automating comment submissions, you can help boost perceived engagement and potentially improve search rankings for your WordPress sites.

---


## Features
- Publishes comments to random WordPress posts from a list
- Approves comments immediately via the WordPress REST API
- Uses Puppeteer for browser automation (bypasses some anti-bot measures)
- No database required—reads from and writes to simple text files
- Easily configurable and extendable

## SEO & Comments

Increasing the number of comments on your WordPress posts can:
- Signal user engagement and content quality to search engines
- Help with SEO rankings, as noted by Google employees
- Make your site appear more active and trustworthy

This tool is ideal for:
- SEO agencies and professionals
- Website owners looking to boost engagement signals
- QA teams testing comment workflows

## Requirements
- PHP 8.1+
- Node.js 18+
- Composer
- npm or yarn
- A WordPress site with Application Passwords enabled for REST API

## Setup

1. **Clone the repository:**
   ```sh
   git clone https://github.com/yourusername/wp-comment-bot.git
   cd wp-comment-bot
   ```

2. **Install PHP dependencies:**
   ```sh
   composer install
   ```

3. **Install Node dependencies for Puppeteer:**
   ```sh
   cd puppeteer-scripts
   npm install
   cd ..
   ```

4. **Configure environment:**
   - Copy `.env.example` to `.env` and fill in your WordPress credentials and other settings.
   - Set `WP_USERNAME` and `WP_APP_PASSWORD` in `.env` for REST API authentication.

5. **Prepare comment data:**
   - Add post URLs to `comments/posts.txt`
   - Add comment texts to `comments/comments.txt`
   - Add author names to `comments/authors.txt`
   - Add emails to `comments/emails.txt`

   Each file should have one entry per line.

## Usage

Run the following Artisan command to publish and approve a comment:

```sh
php artisan comment:publish
```

- The command will randomly select a post, comment, author, and email from the provided files.
- It will use Puppeteer to submit the comment and then approve it via the REST API.
- Output and errors are shown in the console and logged.

## Customization
- Edit the Puppeteer script in `puppeteer-scripts/comment-publish.js` to adjust browser automation as needed.
- Adjust the Laravel command in `app/Console/Commands/PublishWpComment.php` for more features or logging.

## Security
- Do not commit your real WordPress credentials to public repositories.
- Use application passwords with limited permissions.

## License
MIT

---

Contributions and issues are welcome!
