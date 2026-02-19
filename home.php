<?php
get_header();
?>
<style>
    .funnel-anime{
  transform-origin: top center;      /* hinge from the top */
  backface-visibility: hidden;
  will-change: transform, opacity;
}

/* Accessibility: disable motion if the user prefers reduced motion */
@media (prefers-reduced-motion: reduce){
  .funnel-anime{ transform: none !important; opacity: 1 !important; }
}

/* Force fullwidth on mobile - prevent scrollbar from stealing layout space */
@media (max-width: 768px) {
  html, body {
    scrollbar-width: none;           /* Firefox */
    -ms-overflow-style: none;        /* IE/Edge */
  }
  html::-webkit-scrollbar,
  body::-webkit-scrollbar {
    display: none;                   /* Chrome/Safari */
  }
}
</style>



<div class="py-16 origin-top funnel-anime">
    <div class="container px-4 mx-auto">
        <div class="flex flex-col items-center justify-center gap-4 py-8 text-white">
            <h4 class="text-lg text-center">
                👋 Bine ai venit pe blogul platformei Edu-Start ✨
            </h4>
            <h1 class="font-bold text-center text-7xl max-w-[75%] mx-auto">
                Împărtășim opinii și experiențe din educație.
            </h1>
            <p class="text-lg text-center text-white/90">
                Aducem împreună oameni și idei pentru a avea un impact mai bun în sălile de clasă.
            </p>
        </div>
    </div>
</div>

<?php

$paged = ( get_query_var('paged') ) ? get_query_var('paged') : ( ( get_query_var('page') ) ? get_query_var('page') : 1 );

$posts_per_page = 6;

$args = [
    'post_type' => 'post',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
];

$query = new WP_Query($args);
$total_posts = $query->found_posts;
$total_pages = $query->max_num_pages;
?>

<div class="container py-6 mx-auto bg-white rounded-2xl px-14">
    <?php if ($query->have_posts()) : ?>
    <div class="grid grid-cols-4 gap-8 mobile:grid-cols-1 tablet:grid-cols-2">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <article class="flex flex-col p-4 group">
                <?php if (has_post_thumbnail()) : ?>
                    <a href="<?php the_permalink(); ?>" class="transition-transform duration-300 group-hover:-translate-y-2">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-60 object-cover rounded-lg mb-4']); ?>
                    </a>
                <?php endif; ?>

                <div class="mb-2 text-sm text-gray-500">
                    <span><?php echo get_the_date(); ?></span>
                    &middot;
                    <span><?php echo estimate_reading_time(get_the_content()); ?> min. citire</span>
                </div>

                <h2 class="mb-2 text-lg font-semibold leading-5 text-center">
                    <a href="<?php the_permalink(); ?>" class="transition-colors duration-300 group-hover:text-es-orange hover:text-es-orange">
                        <?php the_title(); ?>
                    </a>
                </h2>

                <div class="flex-grow px-6 text-sm text-center text-slate-500">
                    <?php
                    $subtitlu = get_field('subtitlu');
                    if ($subtitlu) {
                        echo mb_strimwidth($subtitlu, 0, 250, '...');
                    } else {
                        echo wp_trim_words(get_the_excerpt(), 250);
                    }
                    ?>
                </div>

                <div class="flex items-center mt-4">
                    <?php echo get_avatar(get_the_author_meta('ID'), 40, '', '', ['class' => 'rounded-full mr-3']); ?>
                    <div class="text-sm font-medium text-gray-800">
                        <?php echo esc_html(get_the_author_meta('first_name')) . ' ' . esc_html(get_the_author_meta('last_name')); ?>
                    </div>
                </div>
            </article>
            <?php endwhile; ?>
        </div>

        <?php
            $prev_link = get_previous_posts_link('Pagina anterioară');
            $next_link = get_next_posts_link('Pagina următoare', $query->max_num_pages);
        ?>
        <div class="mt-10 text-sm text-center text-gray-600">
            <p class="mb-4">
                Pagina <?php echo $paged; ?> din <?php echo $total_pages; ?>.
                În total <?php echo $total_posts; ?> articole au fost publicate.
            </p>
            <div class="flex justify-center gap-4">
                <?php if ($prev_link) : ?>
                    <div class="px-6 py-2 bg-gray-200 rounded-sm hover:bg-gray-300"><?php echo $prev_link; ?></div>
                <?php endif; ?>
                <?php if ($next_link) : ?>
                    <div class="px-6 py-2 bg-gray-200 rounded-sm hover:bg-gray-300"><?php echo $next_link; ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php else : ?>
            <p class="text-center text-gray-500">Momentan nu există articole de afișat.</p>
        <?php endif; ?>
    </div>
</div>
<?php wp_reset_postdata(); ?>

<script>
(function ($) {
  const $win = $(window);
  const $targets = $('.funnel-anime');

  // Tweak to taste
  const MAX_ANGLE_DEG = 65;   // how much to tilt away
  const MAX_TRANSLATE_Z = -280; // px (negative = move away from viewer)
  const RANGE_PX = 300;       // pixels of scroll after the top hits viewport top to reach full effect

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Preserve any pre-existing transforms
  $targets.each(function(){
    const $el = $(this);
    const base = $el.css('transform');
    $el.data('baseTransform', base && base !== 'none' ? base : '');
    $el.css({ transformOrigin: 'top center', backfaceVisibility: 'hidden', willChange: 'transform, opacity' });
  });

  const clamp = (n, a, b) => Math.max(a, Math.min(n, b));
  const ease  = (t) => t * t * (3 - 2 * t); // smoothstep
  let ticking = false;

  function onScroll(){
    if (!ticking){
      requestAnimationFrame(updateAll);
      ticking = true;
    }
  }

  function updateAll(){
    const vh = window.innerHeight || $win.height();

    $targets.each(function () {
      const el = this, $el = $(el);
      if (prefersReduced){ $el.css({ transform: $el.data('baseTransform'), opacity: '' }); return; }

      const rect = el.getBoundingClientRect();

      // Start when the TOP of the element hits the TOP of the viewport (the “scroll touches the div” moment)
      const overscroll = -rect.top;                 // <= 0 before the top reaches viewport top; > 0 after
      let p = clamp(overscroll / RANGE_PX, 0, 1);   // 0..1 progress
      p = ease(p);

      const angle = MAX_ANGLE_DEG * p;              // deg
      const tz    = MAX_TRANSLATE_Z * p;            // px (further away as we scroll)
      const opacity = 1 - p;

      const base = $el.data('baseTransform');
      const transform =
        (base ? base + ' ' : '') +
        'perspective(1000px) rotateX(' + angle.toFixed(2) + 'deg) translateZ(' + tz.toFixed(1) + 'px)';

      $el.css({
        transform,
        opacity: opacity.toFixed(3),
        pointerEvents: opacity < 0.05 ? 'none' : ''
      });
    });

    ticking = false;
  }

  // If you prefer to start when the element ENTERS the viewport (bottom touches it),
  // replace the overscroll line above with:
  // const overscroll = (vh - rect.top);

  $win.on('scroll resize', onScroll);
  updateAll();
})(jQuery);


</script>

<?php get_footer(); ?>