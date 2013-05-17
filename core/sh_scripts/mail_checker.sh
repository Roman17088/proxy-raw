for mail in $(find /var/www/vhosts/hectorvalverde.com/mail/new/*); do
	cat $mail | perl /var/www/vhosts/hectorvalverde.com/proxy-raw.com/core/perl_scripts/mail_attachment.pl
	rm $mail
done 2> /dev/null
