for mail in $(find /home/hvalverde/mail/new/*); do
	cat $mail | perl /home/hvalverde/proxy-raw/core/perl_scripts/mail_attachment.pl
	rm $mail
done 2> /dev/null
