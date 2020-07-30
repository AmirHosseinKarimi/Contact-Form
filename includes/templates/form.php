<div id="contact-form" class="cf">
  <h3><?php echo esc_html($attrs['title']) ?></h3>
  <form action="<?php echo esc_url(get_rest_url(null, 'contact-form/message')) ?>" method="POST">
    <div class="cf-row">
      <label for="cf-name">Name</label>
      <input type="text" id="cf-name" name="name" required placeholder="John Doe">
    </div>
    <div class="cf-row">
      <label for="cf-email">Email</label>
      <input type="email" id="cf-email" name="email" required placeholder="johndoe@gmail.com">
    </div>
    <div class="cf-row">
      <label for="cf-message">Message</label>
      <textarea id="cf-message" name="message" required placeholder="Hey..."></textarea>
    </div>
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('cf-submit-form') ?>">
    <div class="cf-row submit-row">
      <button id="cf-submit" type="submit"><?php echo esc_html($attrs['submit_text']) ?></button>
    </div>
  </form>
</div>
