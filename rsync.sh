#/bin/bash
#rsync -arv --delete /var/www/eb/public_html/administrator/components/com_claw/ component/admin/
rsync -arv /var/www/eb/public_html/administrator/components/com_claw/ component/admin/
mv component/admin/claw.xml component/
mv component/admin/script.php component/
#rsync -arv --delete /var/www/eb/public_html/libraries/claw/ library/
rsync -arv /var/www/eb/public_html/libraries/claw/ library/
#rsync -arv /var/www/eb/public_html/plugins/system/claw/ plugin/
rsync -arv /var/www/eb/public_html/media/com_claw/ media/
