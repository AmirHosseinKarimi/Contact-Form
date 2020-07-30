<div class="wrap">
  <h1><?php _e('Contact Form - View Message', 'contact_form') ?></h1>
  <hr />
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row"><label for="blogname"><?php _e('Name', 'contact_form') ?></label></th>
        <td>
          <input
            type="text"
            class="regular-text"
            value="<?php echo esc_attr($name) ?>"
            readonly
          />
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="blogname"><?php _e('Email', 'contact_form') ?></label></th>
        <td>
          <input
            type="email"
            class="regular-text"
            value="<?php echo esc_attr($email) ?>"
            readonly
          />
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="blogname"><?php _e('Message', 'contact_form') ?></label></th>
        <td>
        <textarea
          rows="10"
          cols="50"
          readonly
          class="large-text code"><?php echo esc_textarea($post->post_content) ?></textarea>
        </td>
      </tr>
    </tbody>
  </table>
</div>
