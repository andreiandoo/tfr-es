<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
  <h1>EduStart ↔ Cube — Setări</h1>

  <h2 class="title">Chei & Endpoint-uri</h2>
  <table class="form-table" role="presentation">
    <tr>
      <th scope="row">X-Api-Key (test endpoints)</th>
      <td><code><?php echo esc_html(get_option('edustart_api_key')); ?></code></td>
    </tr>
    <tr>
      <th scope="row">Webhook Secret (HMAC)</th>
      <td><code><?php echo esc_html(get_option('edustart_webhook_secret')); ?></code></td>
    </tr>
    <tr>
      <th scope="row">Health (GET)</th>
      <td><code><?php echo esc_html(get_site_url().'/wp-json/edu/v1/health'); ?></code></td>
    </tr>
    <tr>
      <th scope="row">Echo (POST)</th>
      <td><code><?php echo esc_html(get_site_url().'/wp-json/edu/v1/test/echo'); ?></code></td>
    </tr>
    <tr>
      <th scope="row">Webhook Numeracy Result (POST)</th>
      <td><code><?php echo esc_html(get_site_url().'/wp-json/edu/v1/webhooks/numeracy/result'); ?></code></td>
    </tr>
  </table>

  <form method="post" style="margin-top:16px;">
    <?php wp_nonce_field('ec_keys'); ?>
    <p>
      <button class="button button-secondary" name="ec_regen_api" value="1">Regenerează X-Api-Key</button>
      <button class="button button-secondary" name="ec_regen_webhook" value="1">Regenerează Webhook Secret</button>
    </p>
  </form>

  <hr>

  <?php
    // handle save partner settings
    if (isset($_POST['ec_save_partner']) && check_admin_referer('ec_partner')) {
        EduStart_Cube_Client::save_settings($_POST);
        echo '<div class="updated"><p>Setări Cube salvate.</p></div>';
    }
    $s = EduStart_Cube_Client::get_settings();
  ?>

  <h2 class="title">Cube (OAuth2 Client Credentials)</h2>
  <form method="post">
    <?php wp_nonce_field('ec_partner'); ?>
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label>Base URL</label></th>
        <td><input type="url" name="base_url" value="<?php echo esc_attr($s['base_url']); ?>" class="regular-text" placeholder="https://cube.example.com"></td>
      </tr>
      <tr>
        <th scope="row"><label>Client ID</label></th>
        <td><input type="text" name="client_id" value="<?php echo esc_attr($s['client_id']); ?>" class="regular-text"></td>
      </tr>
      <tr>
        <th scope="row"><label>Client Secret</label></th>
        <td><input type="text" name="client_secret" value="<?php echo esc_attr($s['client_secret']); ?>" class="regular-text"></td>
      </tr>
    </table>
    <p><button class="button button-primary" name="ec_save_partner" value="1">Salvează</button>
       <a class="button" href="<?php echo esc_url( admin_url('options-general.php?page=edustart-cube-settings') ); ?>">Refresh</a>
       <a class="button button-secondary" href="<?php echo esc_url( admin_url('admin.php?page=edustart-cube-outbox-tools') ); ?>">Outbox Tools</a>
    </p>
  </form>

  <hr>

  <h2 class="title">Backfill chei securizate (ULID)</h2>
  <p>Rulează endpoint-ul <code>POST /wp-json/edu/v1/identity/backfill</code> cu header <code>X-Api-Key</code> pentru a genera chei pentru toți profesorii (rol: <em>profesor</em>), generațiile și elevii existenți.</p>
  <p>Debug: <code>GET /wp-json/edu/v1/identity/resolve?entity_type=student&secure_key=...</code></p>
</div>
