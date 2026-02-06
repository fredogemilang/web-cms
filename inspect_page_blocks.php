<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$page = App\Models\Page::find(2);

if ($page) {
    $result = [
        'title' => $page->title,
        'blocks' => []
    ];
    $blocks = $page->blocks()->get();
    foreach ($blocks as $block) {
        $result['blocks'][] = [
            'name' => $block->name,
            'type' => $block->type,
            'value' => $block->value
        ];
    }
    file_put_contents('blocks.json', json_encode($result, JSON_PRETTY_PRINT));
}
