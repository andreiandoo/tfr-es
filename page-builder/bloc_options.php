<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$lungime = get_sub_field('lungime');
if ($lungime == 'full') {
    $lungime = '100%';
} else if ($lungime == 'normal') {
    $lungime = '1200px';
} else if ($lungime == 'scurt') {
    $lungime = '800px';
}
$tip_bloc = get_sub_field('tip_bloc');
if($tip_bloc == 'one_col') {
    $tip_bloc = 'grid grid-cols-1';
} else if($tip_bloc == 'two_col') {
    $tip_bloc = 'grid grid-cols-2 gap-x-8';
} else if($tip_bloc == 'three_col') {
    $tip_bloc = 'grid grid-cols-3 gap-x-8';
} else if($tip_bloc == 'four_col') {
    $tip_bloc = 'grid grid-cols-4 gap-x-8';
}
$anchor = get_sub_field('anchor');
$class = get_sub_field('class');
$culoare_fundal = get_sub_field('culoare_fundal');
$culoare_text = get_sub_field('culoare_text');
$are_cta = get_sub_field('are_cta');
$fundal_cta = get_sub_field('fundal_cta');
$culoare_cta = get_sub_field('culoare_cta');
$cta_text = get_sub_field('cta_text');
$cta_url = get_sub_field('cta_url');
$titlu_sectiune = get_sub_field('titlu_sectiune');
$culoare_titlu = get_sub_field('culoare_titlu');
$subtitlu_sectiune = get_sub_field('subtitlu_sectiune');
$culoare_subtitlu = get_sub_field('culoare_subtitlu');
?>

<div <?php if($anchor) : echo 'id="' . esc_attr( $anchor ) . '"'; endif;?> 
    class="<?php if($class) : echo esc_attr( $class ); endif;?>" style="background-color: <?php echo esc_attr( $culoare_fundal ); ?>; color: <?php echo esc_attr( $culoare_text ); ?>;">
    <div class="mx-auto" <?php if($lungime) : echo 'style="max-width:' . esc_attr( $lungime ) . ';"'; endif; ?>>
        <div class="<?php echo esc_attr( $tip_bloc ); ?>">
            <h1 class="" <?php if($culoare_titlu) : echo 'style="color:' . esc_attr( $culoare_titlu ) . '"'; endif; ?>><?php echo esc_html( $titlu_sectiune ); ?></h1>
            <h3 <?php if($culoare_subtitlu) : echo 'style="color:' . esc_attr( $culoare_subtitlu ) . '"'; endif; ?>><?php echo esc_html( $subtitlu_sectiune ); ?></h3>

            <?php if($are_cta) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="btn" 
                style="background-color: <?php echo esc_attr( $fundal_cta ); ?>; color: <?php echo esc_attr( $culoare_cta ); ?>;">
                    <?php echo esc_html( $cta_text ); ?>
                </a>
            <?php endif; ?>
        </div>

        
        <div class=""></div>
        <div class=""></div>
        <div class=""></div>
        <div class=""></div>
        <div class=""></div>
        
    </div>
</div>