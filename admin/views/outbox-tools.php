<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
  <h1>Outbox Tools — EduStart ↔ Cube</h1>
  <p>Folosește REST endpoints pentru a pune în coadă evenimente de upsert, apoi cronul <code>edustart_cube_outbox_process</code> le livrează către Cube.</p>
  <h2>REST Enqueue</h2>
  <ul>
    <li><code>POST /wp-json/edu/v1/enqueue/teacher</code> body: <code>{"user_id":5}</code></li>
    <li><code>POST /wp-json/edu/v1/enqueue/generation</code> body: <code>{"generation_id":7}</code></li>
    <li><code>POST /wp-json/edu/v1/enqueue/student</code> body: <code>{"student_id":11}</code></li>
  </ul>
  <p>Header necesar: <code>X-Api-Key</code> (din pagina de setări).</p>

  <h2>Cron</h2>
  <p>Job: <code>edustart_cube_outbox_process</code> rulează la 2 minute. Poți rula manual cu un plugin de cron management sau WP-CLI.</p>

  <p><a href="<?php echo esc_url($base); ?>" class="button">Înapoi la Setări</a></p>
</div>
