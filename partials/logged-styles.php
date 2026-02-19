<?php
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header('blank');
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;

$vp = isset($_COOKIE['vp']) ? sanitize_text_field($_COOKIE['vp']) : '';
if ($vp === 'mobile') {
    $main_classes = 'flex w-screen h-screen overflow-hidden font-sans transition-all duration-300 ease-in-out bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600';
    $sidebar_classes = 'h-full overflow-hidden transition-all duration-300 bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600 transition-all duration-300 ease-in-out';
    $panel_classes = 'grid h-[calc(100vh-8rem)] grid-cols-1 overflow-y-scroll bg-slate-100 rounded-2xl  rounded-tr-none rounded-br-none content-start main-content transition-content scrollbar-none scrollbar-thumb-rounded scrollbar-thumb-gray-300 scrollbar-track-sky-800 mobile:h-[calc(100vh-3rem)] mobile:pb-10';
} else {
    $main_classes = 'flex w-screen h-screen overflow-hidden font-sans transition-all duration-300 ease-in-out';
    $sidebar_classes = 'h-full overflow-hidden transition-all duration-300 bg-gradient-to-l from-sky-800 via-sky-700 to-sky-600 transition-all duration-300 ease-in-out';
    $panel_classes = 'grid h-[calc(100vh-8rem)] grid-cols-1 overflow-y-scroll bg-slate-100 mr-6 content-start main-content transition-content scrollbar-none scrollbar-thumb-rounded scrollbar-thumb-gray-300 scrollbar-track-sky-800 rounded-2xl shadow-xl mobile:h-[calc(100vh-3rem)] mobile:pb-10';
}
?>

<style>
  html, body {
    height: 100%;
    overflow: hidden;
  }
</style>