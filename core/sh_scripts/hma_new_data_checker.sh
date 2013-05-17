if [ -f /home/hvalverde/mail/attachment/proxylist*.zip ]; then
	for zip_file in $(find /home/hvalverde/mail/attachment/proxylist*.zip); do
		zip_basename=$(basename $zip_file)
		mv $zip_file /home/hvalverde/proxy-raw/core/hma_compressed_files/.
		php /home/hvalverde/proxy-raw/core/feeders/HMA.feeder.php $zip_basename
	done
fi

rm /home/hvalverde/mail/attachment/*.html 2> /dev/null
