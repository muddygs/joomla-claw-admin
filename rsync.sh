#!/bin/bash
#rsync -arv --delete /var/www/j4_dev/public_html/administrator/components/com_claw/ component/admin/
rsync -arv /var/www/j4_dev/public_html/administrator/components/com_claw/ component/admin/
rsync -arv /var/www/j4_dev/public_html/components/com_claw/ component/site/
mv component/admin/claw.xml component/
#rsync -arv --delete /var/www/j4_dev/public_html/libraries/claw/ library/
rsync -arv /var/www/j4_dev/public_html/libraries/claw/ library/
#rsync -arv /var/www/j4_dev/public_html/plugins/system/claw/ plugin/
rsync -arv /var/www/j4_dev/public_html/media/com_claw/ media_raw/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_sponsors modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_cart modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_vendors modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_regbuttons modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_tabferret modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_schedule modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_spaschedule modules/
rsync -arv /var/www/j4_dev/public_html/modules/mod_claw_skillslist modules/
