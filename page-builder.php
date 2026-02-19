<?php
/* Template Name: Builder */

get_header();
$directory = get_template_directory();

// helper to get a scalar key from an ACF select (value/label/array)
function acf_select_key($field) {
  $raw = get_sub_field($field);
  if (is_array($raw)) return $raw['value'] ?? $raw['label'] ?? '';
  return (string) $raw;
}
?>

<div class="flex-grow">
    <div class="relative">
        <?php if ( have_rows( 'page_builder' ) ):
            while ( have_rows( 'page_builder' ) ) : the_row();
                if ( get_row_layout() == 'top_page' ) :
                    include( $directory . '/page-builder/top_page.php' );
                endif;
            endwhile;
            endif;
        ?>
    </div>
</div>

<div class="flex-grow">
    <div class="relative h-full bg-transparent">

    <?php if ( have_rows( 'page_builder' ) ):
        while ( have_rows( 'page_builder' ) ) : the_row();
            if ( get_row_layout() == 'mixed_text_columns' ) :
                include( $directory . '/page-builder/mixed_text_columns.php');
                
            elseif ( get_row_layout() == 'text_image_columns' ) :
                include( $directory . '/page-builder/text_image_columns.php');

            elseif ( get_row_layout() == 'breaker' ) :
                include( $directory . '/page-builder/breaker.php');

            elseif ( get_row_layout() == 'bloc_options' ) :
                include( $directory . '/page-builder/bloc_options.php');

            elseif ( get_row_layout() == 'articole_blog' ) :
                include( $directory . '/page-builder/articole_blog.php');
            
            endif;
        endwhile;
    else:
        //
    endif; ?>
    
    </div>
</div>

<?php
get_footer();
?>