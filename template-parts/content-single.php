<!-- FIXED READER LAYOUT (desktop: split 2-col, mobile: single column) -->
<div class="container inset-x-0 top-24 bottom-24 z-10 mx-auto h-[calc(100vh-12rem)] rounded-2xl bg-white overflow-hidden mobile:h-auto mobile:rounded-none mobile:overflow-visible">
  <div id="post-<?php the_ID(); ?>" class="h-full mobile:h-auto"><?php /* post_class(); */ ?>
    <div class="grid h-full grid-cols-2 overflow-hidden rounded-2xl mobile:grid-cols-1 mobile:h-auto mobile:overflow-visible mobile:rounded-none">

      <!-- LEFT column: pinned image (top on mobile) -->
      <div class="sticky top-0 z-10 h-full mobile:static mobile:h-auto">
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="h-full post-thumbnail mobile:h-auto">
            <?php the_post_thumbnail( 'large', array( 'class' => 'h-full w-full object-cover mobile:h-64 mobile:max-h-80' ) ); ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT column: scrollable content (flows naturally on mobile) -->
      <div class="h-full min-h-0 article mobile:h-auto">
        <div class="relative h-full min-h-0 px-10 py-6 overflow-y-auto prose main-content scrollbar-thumb-rounded scrollbar-thumb-gray-300 scrollbar-track-sky-800 mobile:h-auto mobile:overflow-visible mobile:px-4 mobile:py-4">

          <?php
            $categories = get_the_category();
            if ( ! empty( $categories ) ) {
              echo '<div class="mt-8 mb-4 text-sm text-center text-gray-500 mobile:mt-4">';
              foreach ( $categories as $category ) {
                echo '<span class="inline-block px-2 py-1 mb-2 mr-2 text-sm font-semibold text-white uppercase rounded bg-es-orange">' . esc_html( $category->name ) . '</span>';
              }
              echo '</div>';
            }
          ?>

          <h1 class="mb-2 text-3xl font-bold text-center text-es-orange mobile:text-2xl"><?php the_title(); ?></h1>

          <div class="flex items-center justify-center gap-x-8 mobile:flex-col mobile:gap-y-2 mobile:gap-x-0">
            <div class="flex items-center gap-x-2">
              <?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', '', array( 'class' => 'mr-3 rounded-full' ) ); ?>
              <div class="text-sm font-medium text-gray-800">
                <?php echo esc_html( get_the_author_meta( 'first_name' ) ) . ' ' . esc_html( get_the_author_meta( 'last_name' ) ); ?>
              </div>
            </div>
            <div class="text-sm text-gray-500">
              <span><?php echo get_the_date(); ?></span>
              <span class="mx-2">|</span>
              <span><?php echo estimate_reading_time( get_the_content() ); ?> min. citire</span>
            </div>
          </div>

          <div class="flex flex-col py-4 mb-8 text-justify subtitlu text-slate-800">
            <?php $subtitlu = get_field( 'subtitlu' ); ?>
            <?php if ( $subtitlu ) : ?>
              <p class="mb-4 text-lg text-slate-800"><?php echo $subtitlu; ?></p>
            <?php else : ?>
              <p class="mb-4 text-lg text-slate-800"><?php echo wp_trim_words( get_the_excerpt(), 50, '...' ); ?></p>
            <?php endif; ?>
          </div>

          <div class="text-slate-800/90">
            <?php the_content(); ?>
          </div>

          <footer class="mt-4 entry-footer">
            <?php the_tags( '<div class="tags"><span class="tags-title">' . __( 'Tags:', 'tailpress' ) . '</span>', ', ', '</div>' ); ?>
          </footer>

          <div class="entry-content">
            <?php
              wp_link_pages(
                array(
                  'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'tailpress' ) . '</span>',
                  'after'       => '</div>',
                  'link_before' => '<span>',
                  'link_after'  => '</span>',
                  'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'tailpress' ) . ' </span>%',
                  'separator'   => '<span class="screen-reader-text">, </span>',
                )
              );
            ?>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- RELATED ARTICLES (based on categories) -->
<?php
  $current_id = get_the_ID();
  $cat_ids    = wp_get_post_categories( $current_id, array( 'fields' => 'ids' ) );

  if ( ! empty( $cat_ids ) ) :
    $rel_query = new WP_Query( array(
      'posts_per_page'      => 4,
      'post__not_in'        => array( $current_id ),
      'ignore_sticky_posts' => 1,
      'category__in'        => $cat_ids,
    ) );
?>

  <div class="py-16 mx-auto container-lg">
    <h2 class="mb-8 text-2xl font-bold text-center text-white">Articole similare</h2>

    <?php if ( $rel_query->have_posts() ) : ?>
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php while ( $rel_query->have_posts() ) : $rel_query->the_post(); ?>
          <article class="overflow-hidden transition bg-white shadow-sm group rounded-xl hover:shadow-md">
            <a href="<?php echo esc_url( get_permalink() ); ?>" class="block">
              <?php if ( has_post_thumbnail() ) : ?>
                <div class="aspect-[16/9] w-full overflow-hidden">
                  <?php the_post_thumbnail( 'medium_large', array(
                    'class' => 'h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]',
                    'alt'   => esc_attr( get_the_title() ),
                  ) ); ?>
                </div>
              <?php else : ?>
                <div class="aspect-[16/9] w-full bg-slate-100"></div>
              <?php endif; ?>
            </a>

            <div class="p-4">
              <?php
                $rel_cats = get_the_category();
                if ( ! empty( $rel_cats ) ) :
              ?>
                <div class="mb-2 text-xs">
                  <?php foreach ( $rel_cats as $rc ) : ?>
                    <span class="mr-2 inline-block rounded bg-es-orange px-2 py-0.5 font-semibold uppercase text-white"><?php echo esc_html( $rc->name ); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <h3 class="mb-2 text-base font-semibold leading-5 line-clamp-2 text-slate-900">
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="hover:text-es-orange"><?php the_title(); ?></a>
              </h3>
            </div>
          </article>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    <?php else : ?>
      <p class="text-center text-slate-500">Momentan nu există articole similare.</p>
    <?php endif; ?>
  </div>

<?php endif; ?>
