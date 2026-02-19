<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$numar_articole = (int) get_sub_field('numar_articole'); // number of posts to show
$numar_articole = max(0, $numar_articole); // guard

$model_key = acf_select_key('model'); // 'grid' or 'lista'
$is_list   = ($model_key === 'lista');

// --- Fields
$anchor            = get_sub_field('anchor');
$class             = get_sub_field('class');
$culoare_fundal    = get_sub_field('culoare_fundal');
$culoare_text      = get_sub_field('culoare_text') ?: '#ffffff';

$are_cta           = (bool) get_sub_field('are_cta');
$fundal_cta        = get_sub_field('fundal_cta');
$culoare_cta       = get_sub_field('culoare_cta');
$cta_text          = get_sub_field('cta_text');
$cta_url           = get_sub_field('cta_url');

$titlu_sectiune    = get_sub_field('titlu_sectiune');
$culoare_titlu     = get_sub_field('culoare_titlu');
$subtitlu_sectiune = get_sub_field('subtitlu_sectiune');
$culoare_subtitlu  = get_sub_field('culoare_subtitlu');

$continut          = get_sub_field('continut'); // rich text (optional)

// Compute responsive grid columns based on count (1→1col, 2→2col, 3→3col, 4+→4col)
function grid_cols_by_count(int $n): string {
  if ($n <= 1) return 'grid-cols-1';
  if ($n === 2) return 'grid-cols-1 sm:grid-cols-2';
  if ($n === 3) return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
  return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
}

// Query posts only if we have a positive number requested
$articles = null;
if ($numar_articole > 0) {
  $articles = new WP_Query([
    'post_type'           => 'post',
    'posts_per_page'      => $numar_articole,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => 1,
  ]);
}
?>

<div
  <?= $anchor ? 'id="' . esc_attr($anchor) . '"' : '' ?>
  class="<?= esc_attr(trim(($class ?? '') . ' container-full mx-auto')) ?>"
  style="<?= esc_attr(
    ($culoare_fundal ? "background-color: {$culoare_fundal}; " : '') .
    "color: {$culoare_text};"
  ) ?>"
>
  <div class="flex flex-col mx-auto container-lg">

    <?php if ($titlu_sectiune || $subtitlu_sectiune): ?>
      <div class="flex flex-col items-center mb-8 text-center">
        <?php if ($titlu_sectiune): ?>
          <h3 class="text-4xl font-bold" <?= $culoare_titlu ? 'style="color:' . esc_attr($culoare_titlu) . '"':''; ?>>
            <?= esc_html($titlu_sectiune) ?>
          </h3>
        <?php endif; ?>
        <?php if ($subtitlu_sectiune): ?>
          <p class="mt-2 opacity-80" <?= $culoare_subtitlu ? 'style="color:' . esc_attr($culoare_subtitlu) . '"':''; ?>>
            <?= esc_html($subtitlu_sectiune) ?>
          </p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($articles && $articles->have_posts()): ?>
      <section aria-label="Articole">
        <?php if ($is_list): ?>
          <div class="flex flex-col gap-6">
            <?php while ($articles->have_posts()): $articles->the_post(); ?>
              <article class="flex items-start gap-4 p-4 transition group rounded-xl bg-white/5 backdrop-blur-sm hover:bg-white/10">
                <a href="<?php the_permalink(); ?>" class="block w-40 overflow-hidden rounded-lg shrink-0">
                  <?php if (has_post_thumbnail()): ?>
                    <?php the_post_thumbnail('medium', ['class' => 'h-24 w-40 object-cover transition group-hover:scale-[1.03]']); ?>
                  <?php else: ?>
                    <div class="w-40 h-24 bg-slate-200"></div>
                  <?php endif; ?>
                </a>
                <div class="min-w-0">
                  <h4 class="text-lg font-semibold text-slate-800 line-clamp-2">
                    <a href="<?php the_permalink(); ?>" class="hover:text-es-orange"><?php the_title(); ?></a>
                  </h4>
                  <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <?php
                      $cats = get_the_category();
                      if (!empty($cats)) {
                        echo '<span>•</span>';
                        foreach ($cats as $c) {
                          echo '<span class="rounded bg-es-orange/90 px-2 py-0.5 font-semibold uppercase text-white">'.$c->name.'</span>';
                        }
                      }
                    ?>
                  </div>
                  <p class="mt-2 text-sm line-clamp-2 text-slate-700/90"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
                </div>
              </article>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        <?php else: /* GRID */ ?>
          <?php $gridCols = grid_cols_by_count($numar_articole); ?>
          <div class="grid <?= esc_attr($gridCols) ?> gap-6">
            <?php while ($articles->have_posts()): $articles->the_post(); ?>
              <article class="overflow-hidden transition bg-white shadow-sm group rounded-xl hover:shadow-md">
                <a href="<?php the_permalink(); ?>" class="block">
                  <div class="aspect-[16/9] w-full overflow-hidden relative">
                    <?php if (has_post_thumbnail()): ?>
                      <?php the_post_thumbnail('medium_large', ['class' => 'h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]']); ?>
                    <?php else: ?>
                      <div class="w-full h-full bg-slate-200"></div>
                    <?php endif; ?>
                    <div class="absolute inset-0 z-10 hidden transition-all duration-300 ease-in-out bg-gradient-to-t from-slate-900/80 via-slate-900/50 to-transparent group-hover:block"></div>
                    <div class="absolute z-20 flex flex-wrap gap-2 mb-2 left-4 bottom-4">
                        <?php
                        $cats = get_the_category();
                        if (!empty($cats)) {
                            foreach ($cats as $c) {
                            echo '<span class="rounded bg-es-orange px-2 py-0.5 text-xs font-semibold uppercase text-white">'.$c->name.'</span>';
                            }
                        }
                        ?>
                    </div>
                  </div>
                </a>
                <div class="p-4">
                  <h4 class="mb-2 text-lg font-semibold leading-6 text-slate-800 line-clamp-2">
                    <a href="<?php the_permalink(); ?>" class="hover:text-es-orange"><?php the_title(); ?></a>
                  </h4>
                </div>
              </article>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
        <?php endif; ?>
      </section>
    <?php elseif ($numar_articole > 0): ?>
      <p class="text-center text-slate-500">Nu s-au găsit articole.</p>
    <?php endif; ?>

    <?php if ($subtitlu_sectiune || $are_cta): ?>
      <div class="flex flex-col items-center mt-12 text-center gap-y-4">
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
