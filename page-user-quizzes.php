<?php
/* Template Name: User Quizzes */
include get_template_directory() . '/partials/logged-styles.php';
?>

<div x-data="{ sidebarOpen: true }" class="<?php echo $main_classes; ?>">
  <?php include get_template_directory() . '/partials/dashboard-sidebar.php'; ?>

  <!-- Conținut principal -->
  <main :class="sidebarOpen ? 'w-[calc(100%-16rem)]' : 'w-[calc(100%-4rem)]'" class="<?php echo $sidebar_classes; ?>">
    <?php include get_template_directory() . '/partials/dashboard-topbar.php'; ?>  

    <div class="<?php echo $panel_classes; ?>">
      <?php
        // Încarcă conținutul subpaginii (ex: /panou, /panou/setari, etc.)
        $uri = $_SERVER['REQUEST_URI'];
        $slug = trim(parse_url($uri, PHP_URL_PATH), '/');
        $slug_parts = explode('/', $slug);
        $last_segment = end($slug_parts);

        $allowed_pages = ['panou', 'setari', 'profil', 'clase', 'scoli', 'chestionare'];
        if (in_array($last_segment, $allowed_pages)) {
            $partial_path = get_template_directory() . '/partials/panou-' . $last_segment . '.php';
            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<p class="text-gray-600">Această pagină nu există încă.</p>';
            }
        } else {
            echo '<p class="text-gray-600">Pagină necunoscută.</p>';
        }
      ?>

      <?php get_footer('blank'); ?>