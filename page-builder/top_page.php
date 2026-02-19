<?php
// Helpers
$anchor       = get_sub_field('anchor');          // text
$class        = get_sub_field('class');           // text
$bg_color     = get_sub_field('culoare_fundal');  // color picker
if(!$bg_color) {
    $bg_color = '#ffffff;';
}
$text_color   = get_sub_field('culoare_text');    // color picker
if(!$text_color) {
    $text_color = '#111827;';
}
$pretitle     = get_sub_field('pretitlu');        // text
$title        = get_sub_field('titlu_pagina');    // text
$subtitle     = get_sub_field('subtitlu_pagina'); // text
$subtext      = get_sub_field('subtext');         // textarea

$has_gradient = get_sub_field('are_gradient');    // boolean
$has_image    = get_sub_field('are_imagine');     // boolean

if($has_image) {
    $top_image    = get_sub_field('imagine');  // image url
}

$has_bg_image = get_sub_field('are_imagine_fundal');  // boolean (per your helper notes)
if($has_bg_image) {
    $bg_image     = get_sub_field('imagine_fundal');  // image url
}

$has_bg_video = get_sub_field('are_video_fundal');  // boolean
if($has_bg_video == 1) {
    $top_video    = get_sub_field('videoclip');   // video url
    $src = sprintf(
    'https://www.youtube-nocookie.com/embed/%1$s?autoplay=1&mute=1&loop=1&playsinline=1&controls=0&rel=0&modestbranding=1&iv_load_policy=3&fs=0&disablekb=1&playlist=%1$s',
    urlencode($top_video)
    );
}

        // image url
       // video embed url

// Inline style for custom colors (keeps Tailwind utility freedom + dynamic colors)
$style_bits = [];
if ($has_bg_image && !empty($bg_image) || $has_bg_video && !empty($top_video)) {
    $style_bits[] = 'background-size: cover';
    $style_bits[] = 'background-position: center center';
    $style_bits[] = 'background-repeat: no-repeat';
    $style_bits[] = 'color:#ffffff';
} else {
    if (!empty($bg_color))   { $style_bits[] = 'background-color: ' . $bg_color; }
    if (!empty($text_color)) { $style_bits[] = 'color: ' . $text_color; }
}
$style_attr = $style_bits ? ' style="' . esc_attr(implode('; ', $style_bits)) . '"' : '';

// Helper: safe ID for anchor (optional)
$section_id = $anchor ? sanitize_title($anchor) : '';
?>

<section
  <?php if ($section_id) : ?>id="<?php echo esc_attr($section_id); ?>"<?php endif; ?>
  class="relative isolate overflow-hidden <?php echo esc_attr($class); ?>">
  <!-- Background layers -->
  <div class="container absolute mx-auto overflow-hidden -z-10 rounded-2xl">
    

    
  </div>

  <!-- Content container -->
  <div class="container inset-x-0 z-10 mx-auto overflow-hidden rounded-2xl" <?php echo $style_attr; ?>>
    <?php
      if ($has_bg_video && !empty($top_video)) : ?>
        <div class="relative overflow-hidden w-full min-h-[60vh]">
            <div class="absolute inset-0 -z-10" aria-hidden="true">
                <iframe
                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[100vw] h-[56.25vw] min-w-[177.7778vh] min-h-[100vh] pointer-events-none"
                src="<?php echo esc_url( $src ); ?>"
                title="Background video"
                frameborder="0"
                allow="autoplay; fullscreen; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin">
                </iframe>
            </div>

            <div class="absolute inset-0 w-full  min-h-[60vh] bg-black/10 -z-0"></div>

            <div class="relative z-10 flex flex-col justify-center min-h-[60vh] px-6 py-16 mx-auto">
                <div class="absolute inset-0 top-0 left-0 w-full min-h-[60vh] bg-gradient-to-r from-slate-900 via-slate-900/70 to-slate-900/30"></div>
                <div class="relative z-10 max-w-2xl pl-12">
                    <?php if (!empty($pretitle)) : ?>
                        <p class="mb-2 text-sm font-semibold tracking-wide uppercase opacity-80">
                            <?php echo esc_html($pretitle); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($title)) : ?>
                        <h1 class="mb-3 text-3xl font-bold leading-tight md:text-6xl">
                            <?php echo esc_html($title); ?>
                        </h1>
                    <?php endif; ?>

                    <?php if (!empty($subtitle)) : ?>
                        <p class="mb-4 text-lg font-medium md:text-2xl opacity-90">
                            <?php echo esc_html($subtitle); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($subtext)) : ?>
                        <div class="prose max-w-none opacity-90">
                            <?php echo wp_kses_post(wpautop($subtext)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php
      // Full-bleed background IMAGE
      elseif ($has_bg_image && !empty($bg_image)) : ?>
        <img src="<?php echo esc_url($bg_image); ?>"
           alt=""
           class="object-cover w-full h-full" loading="lazy" decoding="async" />
    <?php endif; ?>
    <?php if ($has_gradient) : ?>
      <!-- Optional gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-tr from-black/50 via-black/20 to-transparent mix-blend-multiply"></div>
    <?php endif; ?>
    <div class="grid items-center gap-10 md:grid-cols-2">

      

      <!-- Media column (image OR video, optional) -->
      <?php if ($has_image && !empty($top_image)) : ?>
        <div class="relative">
          <div class="overflow-hidden shadow-lg rounded-2xl ring-1 ring-black/5">
            <img src="<?php echo esc_url($top_image); ?>"
                 alt=""
                 class="object-cover w-full h-auto" loading="lazy" decoding="async" />
          </div>
        </div>
      <?php elseif (!empty($top_video) && !$has_bg_video) : ?>
        <div class="relative overflow-hidden shadow-lg rounded-2xl ring-1 ring-black/5">
          <div class="relative aspect-video">
            <?php
              $embed_side = wp_oembed_get($top_video);
              if ($embed_side) {
                $embed_side = preg_replace('/<iframe\b/', '<iframe class="absolute inset-0 w-full h-full"', $embed_side);
                echo $embed_side;
              }
            ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>
