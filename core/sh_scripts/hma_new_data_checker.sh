if [ -f /var/www/vhosts/hectorvalverde.com/mail/attachment/proxylist*.zip ]; then
	for zip_file in $(find /var/www/vhosts/hectorvalverde.com/mail/attachment/proxylist*.zip); do
		zip_basename=$(basename $zip_file)
		mv $zip_file /var/www/vhosts/hectorvalverde.com/proxy-raw.com/core/hma_compressed_files/.
		/var/www/vhosts/hectorvalverde.com/proxy-raw.com/core/feeders/HMA.feeder.php $zip_basename
	done
fi

rm /var/www/vhosts/hectorvalverde.com/mail/attachment/*.html 2> /dev/null
