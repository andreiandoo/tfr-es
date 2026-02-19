<?php
/* Template Name: Students List */
include get_template_directory() . '/partials/logged-styles.php';
?>

<div <?php if ($vp === 'mobile') : ?>x-data="{ sidebarOpen: false }"<?php else : ?>x-data="{ sidebarOpen: true }"<?php endif; ?> class="<?php echo $main_classes; ?>">
  <?php include get_template_directory() . '/partials/dashboard-sidebar.php'; ?>

  <!-- Conținut principal -->
 <main <?php if ($vp === 'mobile') : ?>:class="sidebarOpen ? 'w-full' : 'w-full'"<?php else : ?>:class="sidebarOpen ? 'w-full' : 'w-[calc(100%-4rem)]'"<?php endif; ?> class="<?php echo $sidebar_classes; ?>">
    <?php include get_template_directory() . '/partials/dashboard-topbar.php'; ?>  

    <div class="<?php echo $panel_classes; ?>">
      <?php
        include get_template_directory() . '/partials/panou-elevi.php';
      ?>

      <?php get_footer('blank'); ?>