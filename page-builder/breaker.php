<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// --- Options & mappings
$lungime_map = ['full' => '100%', 'normal' => '1200px', 'short' => '800px'];
$pozitionare_map = [
  'left'   => 'justify-start text-left',
  'center' => 'justify-center text-center',
  'right'  => 'justify-end text-right',
  'full'   => 'justify-between text-justify',
];

$lungime_key   = acf_select_key('lungime');
$pozitionare_key = acf_select_key('pozitionare_bloc');

$lungime     = $lungime_map[$lungime_key]       ?? '1200px';
$pozitionare = $pozitionare_map[$pozitionare_key] ?? 'justify-center text-center';

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

$continut = get_sub_field('continut'); // rich text

?>

<div
  <?= $anchor ? 'id="' . esc_attr($anchor) . '"' : '' ?>
  class="<?= esc_attr(trim(($class ?? '') . ' container-full mx-auto')) ?>"
  style="<?= esc_attr(
    ($culoare_fundal ? "background-color: {$culoare_fundal}; " : '') .
    "color: {$culoare_text};"
  ) ?>"
>
  <div class="container-sm mx-auto flex flex-col <?php echo $pozitionare; ?> <?php echo !$culoare_fundal ? 'border-t border-slate-200 py-8' : ''; ?>">

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

    <?php if ( ! empty( $continut ) ) : ?>
      <div class="mb-12 prose prose-invert max-w-none">
        <?= wp_kses_post( wpautop( $continut ) ) ?>
      </div>
    <?php endif; ?>

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

