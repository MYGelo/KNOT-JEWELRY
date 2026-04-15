<?php

$title = get_field('faq_title');
$items = get_field('faq_items');

$block_anchor = $block['anchor'] ?? '';
$block_classes = 'faq';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	id="<?= esc_attr($block_anchor) ?>"
>
    <div class="container">
        <?php if ($title): ?>
            <h2 class="text-title"><?=$title ?></h2>
        <?php endif; ?>

        <?php if (!empty($items)):
            $half = floor(count($items) / 2);
            $left_part =  array_slice($items, 0, $half);
            $right_part =  array_slice($items, $half, count($items) - $half);
            ?>
            <div class="faq-new__container">
                <ul>
                    <?php foreach ($left_part as $item): ?>
                        <li class="faq-new__item">
                            <div class="faq-new__question-header">
                                <span class="text-normal-bold"><?=$item['title'] ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 10L12 15L17 10" stroke="#111111" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="faq-new__question-content">
                                <p class="text-normal"><?=$item['description'] ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <ul>
                    <?php foreach ($right_part as $item): ?>
                        <li class="faq-new__item">
                            <div class="faq-new__question-header">
                                <span class="text-normal-bold"><?=$item['title'] ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M7 10L12 15L17 10" stroke="#111111" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="faq-new__question-content">
                                <p class="text-normal"><?=$item['description'] ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$faq = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => []
];

if (is_array($items)) {
    foreach ($items as $item) {
        $faq['mainEntity'][] = [
            "@type" => "Question",
            "name" => $item['title'],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $item['description']
            ]
        ];
    }
}
?>
<script type="application/ld+json">
<?= json_encode($faq, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>