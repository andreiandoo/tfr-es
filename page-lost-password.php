<?php
/* Template Name: Lost Password Page */

// if (is_user_logged_in()) {
//     wp_redirect(home_url());
//     exit;
// }

get_header(); ?>

<div class="flex items-center justify-center min-h-screen px-4 py-12 bg-gradient-to-br from-pink-500 to-yellow-500 sm:px-6 lg:px-8">
  <div class="w-full max-w-md p-10 space-y-8 bg-white shadow-xl rounded-2xl">
    <div>
      <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">Recuperează parola</h2>
      <p class="mt-2 text-sm text-center text-gray-600">
        Introdu adresa ta de email și îți vom trimite un link de resetare.
      </p>
    </div>

    <form class="mt-8 space-y-6" method="post" action="<?php echo esc_url(site_url('wp-login.php?action=lostpassword', 'login_post')); ?>">
      <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>" />

      <div class="-space-y-px rounded-md shadow-sm">
        <div>
          <label for="user_login" class="sr-only">Email</label>
          <input id="user_login" name="user_login" type="email" required
                 class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-md appearance-none focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm"
                 placeholder="Email">
        </div>
      </div>

      <div>
        <button type="submit" name="wp-submit"
                class="relative flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-pink-600 border border-transparent rounded-md group hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
          Trimite link-ul de resetare
        </button>
      </div>
    </form>

    <div class="text-sm text-center text-gray-600">
      Ți-ai amintit parola?
      <a href="<?php echo wp_login_url(); ?>" class="font-medium text-pink-600 hover:text-pink-500">Revenire la login</a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
