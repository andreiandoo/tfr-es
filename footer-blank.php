
<?php
	$default_logo = get_field('default_logo','options');
	//$mobile_logo = get_field('mobile_logo','options');
	$dark_logo = get_field('dark_logo','options');
	$copyrights = get_field('copyrights','options');
	$telefon = get_field('telefon','options');
	$email = get_field('email','options');


	$facebook = get_field('facebook','options');
	$instagram = get_field('instagram','options');
	$whatsapp = get_field('whatsapp','options');
	$tiktok = get_field('tiktok','options');
	$youtube = get_field('youtube','options');
	$linkedin = get_field('linkedin','options');

    $vp = isset($_COOKIE['vp']) ? sanitize_text_field($_COOKIE['vp']) : '';
?>

<?php do_action( 'tailpress_content_end' ); ?>

<?php do_action( 'tailpress_content_after' ); ?>


        </div>

        <footer id="colophon" class="sticky bottom-0 flex items-center justify-between py-4 pl-2 pr-6 text-white bg-transparent gap-x-4 site-footer" role="contentinfo">
            <?php do_action( 'tailpress_footer' ); ?>

            <?php 
            if ($vp != 'mobile') { ?>
            <div class="w-full ">
                <?php
                if (has_nav_menu('footer_logged_menu')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer_logged_menu',
                        'container' => 'nav',
                        'container_class' => 'footer-nav',
                        'container_id' => 'fm-1-id',
                        'menu_class' => 'flex gap-x-1 items-center justify-start text-sm',
                        'menu_id' => '',
                        'depth' => 1,
                    ));
                }?>
            </div>
            <?php } ?>

            <div class="container flex items-center justify-end mx-auto text-right gap-x-4">
                <?php 
                if ($vp != 'mobile') { ?>
                    <div class="flex items-center justify-center mr-4 gap-x-4">
                        <?php if($facebook) : ?>
                            <a href="<?php echo $facebook;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 50 50" class="size-5">
                                    <path d="M25,3C12.85,3,3,12.85,3,25c0,11.03,8.125,20.137,18.712,21.728V30.831h-5.443v-5.783h5.443v-3.848 c0-6.371,3.104-9.168,8.399-9.168c2.536,0,3.877,0.188,4.512,0.274v5.048h-3.612c-2.248,0-3.033,2.131-3.033,4.533v3.161h6.588 l-0.894,5.783h-5.694v15.944C38.716,45.318,47,36.137,47,25C47,12.85,37.15,3,25,3z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                        <?php if($instagram) : ?>
                            <a href="<?php echo $instagram;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 64 64" class="size-5">
                                <path d="M 21.580078 7 C 13.541078 7 7 13.544938 7 21.585938 L 7 42.417969 C 7 50.457969 13.544938 57 21.585938 57 L 42.417969 57 C 50.457969 57 57 50.455062 57 42.414062 L 57 21.580078 C 57 13.541078 50.455062 7 42.414062 7 L 21.580078 7 z M 47 15 C 48.104 15 49 15.896 49 17 C 49 18.104 48.104 19 47 19 C 45.896 19 45 18.104 45 17 C 45 15.896 45.896 15 47 15 z M 32 19 C 39.17 19 45 24.83 45 32 C 45 39.17 39.169 45 32 45 C 24.83 45 19 39.169 19 32 C 19 24.831 24.83 19 32 19 z M 32 23 C 27.029 23 23 27.029 23 32 C 23 36.971 27.029 41 32 41 C 36.971 41 41 36.971 41 32 C 41 27.029 36.971 23 32 23 z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                        <?php if($whatsapp) : ?>
                            <a href="<?php echo $whatsapp;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 64 64" class="size-5">
                                <path d="M 32 10 C 19.85 10 10 19.85 10 32 C 10 36.065 11.10725 39.869719 13.03125 43.136719 L 10.214844 53.683594 L 21.277344 51.208984 C 24.450344 52.983984 28.106 54 32 54 C 44.15 54 54 44.15 54 32 C 54 19.85 44.15 10 32 10 z M 32 14 C 41.941 14 50 22.059 50 32 C 50 41.941 41.941 50 32 50 C 28.269 50 24.803687 48.864875 21.929688 46.921875 L 15.791016 48.294922 L 17.353516 42.439453 C 15.250516 39.493453 14 35.896 14 32 C 14 22.059 22.059 14 32 14 z M 24.472656 21.736328 C 24.105656 21.736328 23.515672 21.871969 23.013672 22.417969 C 22.520672 22.964969 21.113281 24.278844 21.113281 26.964844 C 21.113281 29.640844 23.057078 32.23675 23.330078 32.59375 C 23.603078 32.96075 27.100531 38.639266 32.644531 40.822266 C 37.240531 42.632266 38.179547 42.273688 39.185547 42.179688 C 40.183547 42.093688 42.408328 40.866703 42.861328 39.595703 C 43.313328 38.323703 43.312875 37.232906 43.171875 37.003906 C 43.034875 36.781906 42.676859 36.644094 42.130859 36.371094 C 41.584859 36.097094 38.906297 34.777656 38.404297 34.597656 C 37.909297 34.417656 37.542547 34.323141 37.185547 34.869141 C 36.818547 35.415141 35.778125 36.643953 35.453125 37.001953 C 35.138125 37.368953 34.823344 37.411672 34.277344 37.138672 C 33.731344 36.865672 31.975531 36.292594 29.894531 34.433594 C 28.275531 32.992594 27.182188 31.208063 26.867188 30.664062 C 26.551188 30.119062 26.832469 29.821828 27.105469 29.548828 C 27.353469 29.310828 27.652781 28.916563 27.925781 28.601562 C 28.189781 28.277563 28.282891 28.056453 28.462891 27.689453 C 28.651891 27.332453 28.555922 27.007375 28.419922 26.734375 C 28.284922 26.460375 27.226234 23.765406 26.740234 22.691406 C 26.332234 21.787406 25.905672 21.760953 25.513672 21.751953 C 25.196672 21.735953 24.829656 21.736328 24.472656 21.736328 z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                        <?php if($tiktok) : ?>
                            <a href="<?php echo $tiktok;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 64 64" class="size-5">
                                <path d="M48,8H16c-4.418,0-8,3.582-8,8v32c0,4.418,3.582,8,8,8h32c4.418,0,8-3.582,8-8V16C56,11.582,52.418,8,48,8z M50,27 c-3.964,0-6.885-1.09-9-2.695V38.5C41,44.841,35.841,50,29.5,50S18,44.841,18,38.5S23.159,27,29.5,27h2v5h-2 c-3.584,0-6.5,2.916-6.5,6.5s2.916,6.5,6.5,6.5s6.5-2.916,6.5-6.5V14h5c0.018,1.323,0.533,8,9,8V27z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                        <?php if($youtube) : ?>
                            <a href="<?php echo $youtube;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 64 64" class="size-5">
                                <path d="M56.456,17.442c-0.339-1.44-1.421-2.595-2.866-3.053C49.761,13.174,41.454,12,32,12s-17.761,1.174-21.591,2.389 c-1.445,0.458-2.527,1.613-2.866,3.053C6.903,20.161,6,25.203,6,32c0,6.797,0.903,11.839,1.544,14.558 c0.339,1.44,1.421,2.595,2.866,3.053C14.239,50.826,22.546,52,32,52s17.761-1.174,21.591-2.389 c1.445-0.458,2.527-1.613,2.866-3.053C57.097,43.839,58,38.797,58,32C58,25.203,57.097,20.161,56.456,17.442z M27,40V24l14.857,8 L27,40z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                        <?php if($linkedin) : ?>
                            <a href="<?php echo $linkedin;?>" target="_blank" class="transition-all duration-150 ease-in-out text-white/90 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 64 64" class="size-5">
                                <path d="M32,6C17.641,6,6,17.641,6,32c0,14.359,11.641,26,26,26s26-11.641,26-26C58,17.641,46.359,6,32,6z M25,44h-5V26h5V44z M22.485,24h-0.028C20.965,24,20,22.888,20,21.499C20,20.08,20.995,19,22.514,19c1.521,0,2.458,1.08,2.486,2.499 C25,22.887,24.035,24,22.485,24z M44,44h-5v-9c0-3-1.446-4-3-4c-1.445,0-3,1-3,4v9h-5V26h5v3c0.343-0.981,1.984-3,5-3c4,0,6,3,6,8 V44z"></path>
                                </svg>
                            </a>
                        <?php endif;?>
                    </div>
                <?php } ?>
                <div class="flex items-center text-sm gap-x-1">
                    <span class="">Un proiect</span>
                    <a href="https://teachforromania.org" target="_blank" class="font-bold"><?php echo $copyrights;?></a> &copy; <?php echo date_i18n( 'Y' );?>
                </div>
            </div>
        </footer>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    (function(){
    const vp = window.matchMedia('(width < 768px)').matches ? 'mobile' : 'desktop';
    document.cookie = `vp=${vp}; path=/; max-age=604800; SameSite=Lax`;
    })();
</script>
<?php wp_footer(); ?>

</body>
</html>
