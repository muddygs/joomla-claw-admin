#!/usr/bin/env bash
#rsync -arv --delete /var/www/j4_dev/public_html/administrator/components/com_claw/ component/admin/
rsync -arv /var/www/j4_dev/public_html/administrator/components/com_claw/ component/admin/
rsync -arv /var/www/j4_dev/public_html/components/com_claw/ component/site/
rsync -arv /var/www/j4_dev/public_html/layouts/claw/ layouts/claw/
mv component/admin/claw.xml component/
mv component/admin/script-admin.php component/
mv component/admin/script.php package/
#rsync -arv --delete /var/www/j4_dev/public_html/libraries/claw/ library/
rsync -arv /var/www/j4_dev/public_html/libraries/claw/ library/
rsync -arv /var/www/j4_dev/public_html/plugins/task/clawcorp/ plugins/task/clawcorp/
rsync -arv /var/www/j4_dev/public_html/media/com_claw/ media_raw/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_sponsors modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_cart modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_vendors modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_regbuttons modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_tabferret modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_schedule modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_spaschedule modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_skillslist modules/
