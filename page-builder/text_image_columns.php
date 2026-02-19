<?php
// Helpers
$length = get_sub_field('lungime');          // select
$type = get_sub_field('tip_bloc');              // select
$anchor = get_sub_field('anchor');          // text
$class = get_sub_field('class');           // text
$bg_color = get_sub_field('culoare_fundal');  // color picker
if(!$bg_color) {
    $bg_color = '#ffffff;';
}
$text_color = get_sub_field('culoare_text');    // color picker
if(!$text_color) {
    $text_color = '#111827;';
}
$has_cta = get_sub_field('are_cta');       // boolean
if($has_cta) {
    $bg_cta = get_sub_field('fundal_cta');    // color picker
    $color_cta = get_sub_field('culoare_cta'); // color picker
    $text_cta = get_sub_field('cta_text');      // text
    $link_cta = get_sub_field('cta_url');      // url
}

$title = get_sub_field('titlu_sectiune');              // text
$subtitle = get_sub_field('subtitlu_sectiune');    // textarea
$subtitle_color = get_sub_field('culoare_subtitlu'); // color picker

if($type == 'one_column') {
    $columns = 1;
} elseif($type == 'two_col') {
    $columns = 2;
} elseif($type == 'three_col') {
    $columns = 3;
} elseif($type == 'four_col') {
    $columns = 4;
} else {
    $columns = 1; // default
}

$i=1;
// from 1 to 4 columns
while($i <= $columns) {
    $column_img = get_sub_field('imagine_coloana_' . $i); // image url
    $column_title = get_sub_field('titlu_coloana_' . $i); // text
    $column_text = get_sub_field('text_coloana_' . $i);   // textarea
    $column_data[] = array(
        'img' => $column_img,
        'title' => $column_title,
        'text' => $column_text,
    );
    $i++;
}

// Inline style for custom colors (keeps Tailwind utility freedom + dynamic colors)
$style_bits = [];
if (!empty($bg_color))   { $style_bits[] = 'background-color: ' . $bg_color; }
if (!empty($text_color)) { $style_bits[] = 'color: ' . $text_color; }

$style_attr = $style_bits ? ' style="' . esc_attr(implode('; ', $style_bits)) . '"' : '';

// Helper: safe ID for anchor (optional)
$section_id = $anchor ? sanitize_title($anchor) : '';

?>

<section
  <?php if ($section_id) : ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
  class="py-16 px-6 md:px-12 lg:px-24 <?php echo esc_attr($class); ?>"
  <?php echo $style_attr; ?>>
    <div class="mx-auto container-lg">
        <?php if (!empty($title)) : ?>
            <h2 class="mb-4 text-3xl font-bold text-center md:text-4xl">
                <?php echo esc_html($title); ?>
            </h2>
        <?php endif; ?>

        <?php if (!empty($subtitle)) : ?>
            <p class="mb-12 text-center md:text-lg" style="<?php if(!empty($subtitle_color)) { echo 'color:' . esc_attr($subtitle_color) . ';'; } ?>">
                <?php echo esc_html($subtitle); ?>
            </p>
        <?php endif; ?>

        <div class="grid gap-8 <?php if($columns == 1) { echo 'grid-cols-1'; } elseif($columns == 2) { echo 'grid-cols-1 md:grid-cols-2'; } elseif($columns == 3) { echo 'grid-cols-1 md:grid-cols-3'; } elseif($columns == 4) { echo 'grid-cols-1 md:grid-cols-4'; } ?>">

            <?php foreach($column_data as $col) : ?>
                <div class="flex flex-col items-center text-center">
                    <?php if(!empty($col['img'])) : ?>
                        <div class="mb-4">
                            <img src="<?php echo esc_url($col['img']['url']); ?>" alt="<?php echo esc_attr($col['img']['alt']); ?>" class="h-auto max-w-full">
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($col['title'])) : ?>
                        <h3 class="mb-2 text-xl font-semibold">
                            <?php echo esc_html($col['title']); ?>
                        </h3>
                    <?php endif; ?>

                    <?php if(!empty($col['text'])) : ?>
                        <div class="text-base leading-relaxed">
                            <?php echo wp_kses_post(wpautop($col['text'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>