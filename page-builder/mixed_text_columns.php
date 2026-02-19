<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// --- Options & mappings
$lungime_map = ['full' => '100%', 'normal' => '1200px', 'short' => '800px'];
$lungime     = $lungime_map[ get_sub_field('lungime') ] ?? '1200px';

$grid_map = [
  'one_col'   => ['cls' => 'grid grid-cols-1',             'cols' => 1],
  'two_col'   => ['cls' => 'grid grid-cols-2 gap-x-8',     'cols' => 2],
  'three_col' => ['cls' => 'grid grid-cols-3 gap-x-8',     'cols' => 3],
  'four_col'  => ['cls' => 'grid grid-cols-4 gap-x-8',     'cols' => 4],
];
$tip_bloc = get_sub_field('tip_bloc');
$grid     = $grid_map[$tip_bloc] ?? $grid_map['one_col'];

// --- Fields
$anchor             = get_sub_field('anchor');
$class              = get_sub_field('class');
$culoare_fundal     = get_sub_field('culoare_fundal');
$culoare_text       = get_sub_field('culoare_text') ?: '#ffffff';

$are_cta            = (bool) get_sub_field('are_cta');
$fundal_cta         = get_sub_field('fundal_cta');
$culoare_cta        = get_sub_field('culoare_cta');
$cta_text           = get_sub_field('cta_text');
$cta_url            = get_sub_field('cta_url');

$titlu_sectiune     = get_sub_field('titlu_sectiune');
$culoare_titlu      = get_sub_field('culoare_titlu');
$subtitlu_sectiune  = get_sub_field('subtitlu_sectiune');
$culoare_subtitlu   = get_sub_field('culoare_subtitlu');

// --- Build columns (1..4) dynamically
$cols = [];
for ($i = 1; $i <= 4; $i++) {
  $cols[$i] = [
    'img'   => get_sub_field("imagine_coloana_$i"),
    'title' => get_sub_field("titlu_coloana_$i"),
    'text'  => get_sub_field("text_coloana_$i"),
  ];
}

// --- Renderer for a column card
$render_card = static function (array $data, bool $is_first = false) {
  $has_img   = ! empty($data['img']);
  $wrap_cl   = $has_img ? '' : 'border rounded-2xl border-white/10';
  // Keep the “lifted” content block look when image exists (as in original col 1)
  $text_wrap = $has_img ? 'p-6 pt-16 -mt-16 mx-4 border rounded-2xl bg-white/5 border-white/20' : 'p-4';
  ?>
  <div class="flex flex-col gap-3 <?= esc_attr($wrap_cl) ?>">
    <?php if ($has_img): ?>
      <div class="relative overflow-hidden rounded-2xl">
        <img src="<?= esc_url($data['img']) ?>" alt="<?= esc_attr($data['title'] ?? '') ?>">
      </div>
    <?php endif; ?>

    <?php if (! empty($data['title']) || ! empty($data['text'])): ?>
      <div class="overflow-hidden <?= esc_attr($text_wrap) ?>">
        <?php if (! empty($data['title'])): ?>
          <h4 class="mb-4 text-xl font-semibold"><?= esc_html($data['title']) ?></h4>
        <?php endif; ?>
        <?php if (! empty($data['text'])): ?>
          <div class="flex flex-col gap-y-2"><?= wp_kses_post($data['text']) ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php
};
?>

<div
  <?= $anchor ? 'id="' . esc_attr($anchor) . '"' : '' ?>
  class="<?= esc_attr(trim(($class ?? '') . ' container-lg mx-auto px-4 py-16')) ?>"
  style="<?= esc_attr(
    ($culoare_fundal ? "background-color: {$culoare_fundal}; " : '') .
    "color: {$culoare_text};"
  ) ?>"
>
  <div class="mx-auto">

    <?php if ($titlu_sectiune || $subtitlu_sectiune): ?>
      <div class="flex flex-col items-center mb-8 text-center">
        <?php if ($titlu_sectiune): ?>
          <h3 class="text-4xl font-bold"
              <?= $culoare_titlu ? 'style="color:' . esc_attr($culoare_titlu) . '"':''; ?>>
            <?= esc_html($titlu_sectiune) ?>
          </h3>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="<?= esc_attr($grid['cls']) ?>">
      <?php for ($i = 1; $i <= $grid['cols']; $i++) { $render_card($cols[$i], $i === 1); } ?>
    </div>

    <?php if ($subtitlu_sectiune || $are_cta): ?>
      <div class="flex flex-col items-center mt-8 text-center gap-y-4">
        <?php if ($subtitlu_sectiune): ?>
          <h4 <?= $culoare_subtitlu ? 'style="color:' . esc_attr($culoare_subtitlu) . '"':''; ?>>
            <?= esc_html($subtitlu_sectiune) ?>
          </h4>
        <?php endif; ?>

        <?php if ($are_cta && $cta_url && $cta_text): ?>
          <a
            href="<?= esc_url($cta_url) ?>"
            class="btn <?= ($fundal_cta || $culoare_cta) ? '' : 'btn-white' ?>"
            style="<?= esc_attr(
              ($fundal_cta ? "background-color: {$fundal_cta}; " : '') .
              ($culoare_cta ? "color: {$culoare_cta};" : '')
            ) ?>"
          >
            <?= esc_html($cta_text) ?>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

