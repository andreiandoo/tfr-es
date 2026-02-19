<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-white text-gray-900 antialiased w-full max-w-full scrollbar-thin' ); ?>>

<?php do_action( 'tailpress_site_before' ); ?>

<div id="page" class="flex flex-col min-h-screen">

	<?php do_action( 'tailpress_header' ); ?>

	<header class="sticky top-0 z-50 w-full text-white transition-all duration-300 bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600 site-header backdrop-blur-md">
		<div class="container px-6 mx-auto mobile:px-4">
			<div class="py-4 flex justify-between items-center">
				<div class="flex items-center justify-between">
					<div class="applogo">
						<a href="<?php echo get_site_url();?>" class="flex items-center mb-1 text-3xl leading-5 text-white gap-x-1 group"><span class="font-bold">edu</span><span class="mb-1 text-2xl italic transition-all duration-150 ease-in-out text-sky-300 group-hover:scale-110">start</span></a>
					</div>
				</div>

				<div class="hidden mobile:flex items-center justify-end login-menu gap-x-4">
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo home_url(); ?>/panou" class="text-white hover:text-sky-200">
							Buna <?php echo esc_html( wp_get_current_user()->first_name ); ?>
						</a>
						<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="ml-4 text-white/20">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo home_url(); ?>/login" class="px-6 py-2 text-white border hover:text-sky-200 border-white/10 rounded-xl">
							<?php esc_html_e( 'Login', 'tailpress' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="px-6 py-2 text-white border hover:text-sky-200 border-white/10 rounded-xl">
							<?php esc_html_e( 'Register', 'tailpress' ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php
				wp_nav_menu(
					array(
						'container_id'    => 'primary-menu',
						'container_class' => 'hidden bg-gray-100 mt-4 p-4 lg:mt-0 lg:p-0 lg:bg-transparent lg:block',
						'menu_class'      => 'lg:flex lg:-mx-4',
						'theme_location'  => 'primary',
						'li_class'        => 'lg:mx-4',
						'fallback_cb'     => false,
					)
				);
				?>

				<div class="flex items-center justify-end login-menu gap-x-4 mobile:hidden">
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo home_url(); ?>/panou" class="text-white hover:text-sky-200">
							Buna <?php echo esc_html( wp_get_current_user()->first_name ); ?>
						</a>
						<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="ml-4 text-white/20">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo home_url(); ?>/login" class="px-6 py-2 text-white border hover:text-sky-200 border-white/10 rounded-xl">
							<?php esc_html_e( 'Login', 'tailpress' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="px-6 py-2 text-white border hover:text-sky-200 border-white/10 rounded-xl">
							<?php esc_html_e( 'Register', 'tailpress' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<div id="content" class="z-20 flex-grow site-content bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600">

		<?php do_action( 'tailpress_content_start' ); ?>

		<main class="">
