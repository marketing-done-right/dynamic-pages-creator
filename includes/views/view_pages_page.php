<?php
 $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
 ?>
 <div class="wrap">
     <h1>Created Pages</h1>
     <table class="widefat">
         <thead>
             <tr>
                 <th>Page Title</th>
                 <th>Slug</th>
                 <th>Date Created</th>
             </tr>
         </thead>
         <tbody>
             <?php if (empty($existing_pages_ids)): ?>
                 <tr>
                     <td colspan="3">No pages have been created yet.</td>
                 </tr>
             <?php else: ?>
                 <?php foreach ($existing_pages_ids as $id => $info): ?>
                     <tr>
                         <td><?php echo esc_html(get_the_title($id)); ?></td>
                         <td><?php echo esc_html(get_post_field('post_name', $id)); ?></td>
                         <td><?php echo esc_html($info['date']); ?></td>
                     </tr>
                 <?php endforeach; ?>
             <?php endif; ?>
         </tbody>
     </table>
 </div>
