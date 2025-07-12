<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateVietnameseNamesCommand extends Command
{
    protected $signature = 'generate:vietnamese-names';
    protected $description = 'Generate 1000 unique Vietnamese names with diacritics and realistic emails, store in text files';

    public function handle()
    {
        $lastNames = [
            'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng',
            'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý', 'Trịnh', 'Đinh', 'Mai', 'Cao',
            'Lương', 'Lưu', 'Trương', 'Lâm', 'Vĩnh', 'Tô', 'Hà', 'Đào', 'Thái', 'Quách',
            'Kiều', 'Châu', 'Nông', 'Tôn', 'Lã', 'Lại', 'Lục', 'Bành', 'Đoàn', 'Tăng',
            'Hứa', 'Tạ', 'Hồng', 'Phùng', 'Tống', 'Đàm', 'Hàn', 'Ân', 'Vương', 'Khúc',
            'Nghiêm', 'Sơn', 'Điền', 'Mạc', 'Bế', 'Hầu'
        ];

        $middleNames = [
            'Văn', 'Thị', 'Ngọc', 'Minh', 'Quốc', 'Anh', 'Hồng', 'Đức', 'Thanh', 'Bảo',
            'Tuấn', 'Lan', 'Hương', 'Khoa', 'Phương', 'Nam', 'Hiếu', 'Tâm', 'Mai', '',
            'Công', 'Thủy', 'Linh', 'Xuân', 'Sơn', 'Hải', 'Huyền', 'Tùng', 'Kim', 'Trung',
            'Bắc', 'Diệp', 'Nhật', 'Phúc', 'Quỳnh', 'Vỹ', 'Kiệt', 'Tiên', 'Nhi', 'Duy',
            'Hòa', 'Thu', 'Bình', 'Nguyệt', 'Oanh', 'Phong', 'Yến', 'Cẩm', 'Đại', 'Hạnh',
            'Ngân', 'Việt', 'Khánh', 'Đăng', 'Thắng', 'Hoài', 'Thảo'
        ];

        $firstNames = [
            'An', 'Bình', 'Châu', 'Duy', 'Giang', 'Hạnh', 'Hiếu', 'Khang', 'Linh', 'Minh',
            'Nam', 'Nga', 'Phúc', 'Quân', 'Sơn', 'Tâm', 'Thảo', 'Tiên', 'Tuấn', 'Vỹ',
            'Hào', 'Khánh', 'Mai', 'Nhi', 'Phong', 'Thủy', 'Trung', 'Yến', 'Bắc', 'Cẩm',
            'Đại', 'Diệp', 'Hân', 'Khoa', 'Lan', 'Nguyệt', 'Oanh', 'Phương', 'Quỳnh', 'Sang',
            'Hòa', 'Thu', 'Bích', 'Đăng', 'Thắng', 'Việt', 'Hùng', 'Ngân', 'Trâm', 'Khôi',
            'Hiền', 'Hương', 'Thịnh', 'Vinh', 'Trí', 'Đạt', 'Hoài', 'Tuyết'
        ];

        $emailProviders = [
            'gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'proton.me', 'icloud.com'
        ];

        $names = [];
        $emails = [];
        $usedNames = [];

        while (count($names) < 1000) {
            $lastName = $lastNames[array_rand($lastNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $firstName = $firstNames[array_rand($firstNames)];

            $fullName = trim("$lastName $middleName $firstName");

            if (in_array($fullName, $usedNames)) {
                continue;
            }

            $nameParts = explode(' ', $fullName);
            $emailBase = strtolower(implode('.', $nameParts));

            if (extension_loaded('intl')) {
                $emailBase = normalizer_normalize($emailBase, \Normalizer::FORM_C);
            }

            try {
                $emailBase = preg_replace('/[^a-z0-9.]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $emailBase));
            } catch (\Exception $e) {
                $emailBase = preg_replace('/[^a-z0-9.]/', '', $emailBase);
            }

            $variation = rand(0, 3);
            if ($variation === 0) {
                $emailBase .= rand(10, 999);
            } elseif ($variation === 1 && count($nameParts) > 1) {
                $firstPart = $nameParts[0];
                $lastPart = end($nameParts);
                try {
                    $firstPart = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $firstPart);
                    $lastPart = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $lastPart);
                } catch (\Exception $e) {
                    $firstPart = preg_replace('/[^a-z0-9]/', '', $firstPart);
                    $lastPart = preg_replace('/[^a-z0-9]/', '', $lastPart);
                }
                $emailBase = strtolower($firstPart[0] . '.' . $lastPart);
            } elseif ($variation === 2) {
                $emailBase .= '.' . rand(90, 05);
            }

            $email = $emailBase . '@' . $emailProviders[array_rand($emailProviders)];

            if (in_array($email, $emails)) {
                continue;
            }

            $usedNames[] = $fullName;
            $names[] = $fullName;
            $emails[] = $email;
        }

        $this->info("Generated " . count($names) . " names and " . count($emails) . " emails.");

        $authorsPath = 'comments-data/authors.txt';
        $emailsPath = 'comments-data/emails.txt';
        $fullAuthorsPath = storage_path("app/{$authorsPath}");
        $fullEmailsPath = storage_path("app/{$emailsPath}");

        // Ensure directory exists
        Storage::makeDirectory('comments-data');

        $authorsContent = implode("\n", $names);
        $emailsContent = implode("\n", $emails);

        try {
            Storage::put($authorsPath, $authorsContent);
            Storage::put($emailsPath, $emailsContent);
        } catch (\Exception $e) {
            $this->warn("Storage::put failed: " . $e->getMessage());

            if (!is_dir(dirname($fullAuthorsPath))) {
                mkdir(dirname($fullAuthorsPath), 0777, true);
            }

            if (file_put_contents($fullAuthorsPath, $authorsContent, LOCK_EX) === false) {
                $this->error("Failed to write to {$fullAuthorsPath}");
                return 1;
            }

            if (file_put_contents($fullEmailsPath, $emailsContent, LOCK_EX) === false) {
                $this->error("Failed to write to {$fullEmailsPath}");
                return 1;
            }
        }

        if (filesize($fullAuthorsPath) === 0 || filesize($fullEmailsPath) === 0) {
            $this->error("One or both files are empty after writing.");
            return 1;
        }

        $this->info("Successfully generated and saved 1000 names and emails.");
        $this->info("Files saved at:\n- {$fullAuthorsPath}\n- {$fullEmailsPath}");

        return 0;
    }
}
