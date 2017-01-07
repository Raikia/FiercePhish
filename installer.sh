#!/usr/bin/env bash


### Colors for output ###
RESTORE='\033[0m'

RED='\033[00;31m'
GREEN='\033[00;32m'
YELLOW='\033[00;33m'
BLUE='\033[00;34m'
PURPLE='\033[00;35m'
CYAN='\033[00;36m'
LIGHTGRAY='\033[00;37m'

LRED='\033[01;31m'
LGREEN='\033[01;32m'
LYELLOW='\033[01;33m'
LBLUE='\033[01;34m'
LPURPLE='\033[01;35m'
LCYAN='\033[01;36m'
WHITE='\033[01;37m'

OS=""
OS_VERSION=""
VERBOSE=false
FIERCEPHISH_MYSQL_PASSWD=
SERVER_IP=$(curl -s icanhazip.com)

FP_INSTRUCTIONS=()
MAIL_INSTRUCTIONS=()
DNS_INSTRUCTIONS=()

### Functions ###

random_str()
{
	cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1
}

get_input()
{
	local default=$1
	if [[ $0 = "bash" ]]
		then
		echo $default
	else
		local input_answer=""
		read input_answer
		echo $input_answer
	fi
}

prompt()
{
	local prompt=$1
	if [[ $0 != "bash" ]]
		then
		echo -ne "   ${LYELLOW}[>] ${prompt} > ${RESTORE}"
	fi
}

sys_cmd()
{
	com=$1
	if [[ $VERBOSE = true ]]
		then
		notice "Running ${com}..."
		eval "${com}"
	else
		#notice "Running ${com} > /dev/null 2>&1"
		eval "${com} > /dev/null 2>&1"
	fi
}

info()
{
	local prompt=$1
	echo -e "   ${YELLOW}[~] ${GREEN}${prompt}${RESTORE}"
}


error()
{
	local prompt=$1
	echo -e "   ${LRED}[!] ${prompt}${RESTORE}"
}

notice()
{
	local prompt=$1
	echo -e "   ${YELLOW}[~] ${WHITE}${prompt}${RESTORE}"
}


detect_os()
{
	info "Detecting OS distribution..."
	if [[ -f /etc/lsb-release ]]
		then
		. /etc/lsb-release
		OS=${DISTRIB_ID}
		OS_VERSION=${DISTRIB_RELEASE}
		if [[ $OS = 'Ubuntu' ]]
			then
			if [[ $OS_VERSION = "14.04" || $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
				then
				notice "Found that you are running ${LYELLOW}${OS} ${OS_VERSION}${WHITE}! This is a supported operating system!"
			else
				error "You are running ${LYELLOW}${OS} ${OS_VERSION}${LRED}. This is not supported by this installer. There's really no reason to not update Ubuntu"
				exit 1
			fi
		else
			error "You are running ${LYELLOW}${OS} ${OS_VERSION}${RESTORE}. This isn't supported by the installer yet"
			exit 1;
		fi
	else
		error "Could not identify what OS is running! This installer works on Ubuntu only right now"
		exit 1
	fi
}

menu()
{
	echo -e ""
	echo -e "${CYAN}---------------- ${LYELLOW}FiercePhish Installer${CYAN} ---------------"
	echo -e "|                                                    |"
	echo -e "|  This installer automatically install FiercePhish  |"
	echo -e "|  and all the other services needed. It is designed |"
	echo -e "|  to work with Ubuntu, but it will attempt to       |"
	echo -e "|  detect what distro you are running.               |"
	echo -e "|                                                    |"
	echo -e "------------------------------------------------------${RESTORE}"
	echo -e ""
	if [[ $0 != "bash" ]]
		then
		echo -e "    ${LYELLOW}Options:${RESTORE} "
		echo -e "        1. ${WHITE}Install FiercePhish + SMTP + IMAP (${LRED}recommended${WHITE})${RESTORE}"
		echo -e "        2. ${WHITE}Install FiercePhish only${RESTORE}"
		echo -e "        3. ${WHITE}Install SMTP + IMAP only${RESTORE}"
	fi
	echo -e ""
	selection=""
	while [ true ]
	do
		prompt "Selection [1-3]"
		selection=$(get_input "1")
		if [ "$selection" -eq "$selection" ] 2> /dev/null
			then
			if [[ $selection -lt 4 && $selection -gt 0 ]]
				then
				break
			fi
		fi
		error "Unknown selection!  Choose again!"
	done
	echo -e ""
}

main()
{
	menu
	if [[ $0 = "bash" ]]
		then
		echo -e "${LRED}  !!! This is the quick install method and will install FiercePhish + SMTP + IMAP !!!"
		echo -e "  !!!       You have 10 seconds to CTRL+C if you do not want this to happen     !!!${RESTORE}"
		sleep 10
	fi
	detect_os
	if [[ $selection -eq 1 ]]
		then
		install_fiercephish
		install_smtp_imap
	elif [[ $selection -eq 2 ]]
		then
		install_fiercephish
	elif [[ $selection -eq 3 ]]
		then
		install_smtp_imap
	else
		error "Unknown action selected"
		exit 1
	fi
	notice "Installation is complete"
	info "Perform the following actions to finish up:"
	echo -e ""
	if [[ ${#FP_INSTRUCTIONS[@]} -ne 0 ]]
		then
		echo -e "   FiercePhish Follow Up Items:"
		for i in "${!FP_INSTRUCTIONS[@]}"
		do 
			echo -e "     $((i+1)). ${FP_INSTRUCTIONS[$i]}"
		done
		echo -e ""
	fi
	if [[ ${#MAIL_INSTRUCTIONS[@]} -ne 0 ]]
		then
		echo -e "   SMTP/IMAP Follow Up Items:"
		for i in "${!MAIL_INSTRUCTIONS[@]}"
		do 
			echo -e "     $((i+1)). ${MAIL_INSTRUCTIONS[$i]}"
		done
		echo -e ""
	fi
	if [[ ${#DNS_INSTRUCTIONS[@]} -ne 0 ]]
		then
		echo -e "   DNS Changes:"
		for i in "${!DNS_INSTRUCTIONS[@]}"
		do 
			echo -e "     $((i+1)). ${DNS_INSTRUCTIONS[$i]}"
		done
		echo -e ""
	fi
}

install_fiercephish()
{
	notice "Installing FiercePhish!"


	info "Updating package repositories"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "apt-get update"
	fi
	

	info "Installing the required packages (this may take a few minutes)"
	if [ -z $MYSQL_ROOT_PASSWD ]
		then
		prompt "Set MySQL root passsword"
		MYSQL_ROOT_PASSWD=$(get_input "root")
		info "Downloading..."
	fi
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWD'"
		sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWD'"
		if [[ $OS_VERSION = "14.04" ]]
			then
			sys_cmd "DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php5 php5-cli mysql-server php5-mysql libapache2-mod-php5 php5-mcrypt php5-imap phpunit npm unzip git curl supervisor"
		elif [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php php-cli mysql-server php-mysql libapache2-mod-php php-mcrypt php-mbstring php-imap phpunit npm unzip git curl supervisor"
		fi
	fi


	info "Installing Composer"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "14.04" || $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer"
		fi
	fi


	info "Installing Bower"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "14.04" || $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "npm install -g bower"
			sys_cmd "ln -s /usr/bin/nodejs /usr/bin/node"
		fi
	fi


	info "Pulling the latest FiercePhish from GitHub to /var/www/fiercephish"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "git clone https://github.com/Raikia/FiercePhish.git /var/www/fiercephish"
		sys_cmd "chown -R www-data:www-data /var/www/fiercephish"
	fi


	info "Installing FiercePhish into Apache (this can take a few minutes)"
	if [ -z $WEBSITE_DOMAIN ]
		then
		prompt "What is the domain name of the website (ie: example.com) (IP address is ok)"
		WEBSITE_DOMAIN=$(get_input "localhost")
		if [[ $WEBSITE_DOMAIN = "" ]]
			then
			WEBSITE_DOMAIN="localhost"
		fi
		info "Installing..."
	fi
	if [[ $OS = "Ubuntu" ]]
		then
		cat > /etc/apache2/sites-available/fiercephish.conf <<- EOM
<VirtualHost *:80>
    ServerName $WEBSITE_DOMAIN
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/fiercephish/public
    <Directory /var/www/fiercephish>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOM
		sys_cmd "a2ensite fiercephish"
		sys_cmd "a2enmod rewrite"
		sys_cmd "a2dissite 000-default"
		sys_cmd "service apache2 restart"
		sys_cmd "pushd /var/www/fiercephish"
		sys_cmd "composer install"
		sys_cmd "bower install --allow-root"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'create database fiercephish'"
		FIERCEPHISH_MYSQL_PASSWD=$(random_str)
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e \$'create user fiercephish@localhost identified by \'${FIERCEPHISH_MYSQL_PASSWD}\''"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e \$'SET PASSWORD FOR fiercephish@localhost = PASSWORD(\'${FIERCEPHISH_MYSQL_PASSWD}\');'"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'grant all privileges on fiercephish.* to fiercephish@localhost'"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'flush privileges'"
		sys_cmd "popd"
	fi


	info "Configuring FiercePhish"
	sys_cmd "pushd /var/www/fiercephish"
	sys_cmd "cp .env.example .env"
	sys_cmd "touch storage/logs/laravel.log"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "chown -R www-data:www-data ."
	fi
	sys_cmd "sed -i 's/APP_DEBUG=.*$/APP_DEBUG=false/' .env"
	sys_cmd "sed -i 's/APP_URL=.*$/APP_URL=http:\/\/${WEBSITE_DOMAIN}/' .env"
	sys_cmd "sed -i 's/DB_USERNAME=.*$/DB_USERNAME=fiercephish/' .env"
	sys_cmd "sed -i 's/DB_PASSWORD=.*$/DB_PASSWORD=${FIERCEPHISH_MYSQL_PASSWD}/' .env"

	info "Generating database"
	sys_cmd "php artisan key:generate"
	sys_cmd "php artisan migrate"

	info "Creating User"
	if [[ -z ${ADMIN_USERNAME} ]]
		then
		/usr/bin/php artisan fp:createuser -c
	else
		sys_cmd "php artisan fp:createuser -c ${ADMIN_USERNAME} ${ADMIN_EMAIL} ${ADMIN_PASSWORD}"
	fi
	sys_cmd "php artisan config:cache"
	sleep 5
	sys_cmd "popd"

	info "Configuring Supervisor to process jobs"
	if [[ $OS = "Ubuntu" ]]
		then
		# For some reason if the supervisor runs more than 1 process, emails dont get sent...
		cat > /etc/supervisor/conf.d/fiercephish.conf <<- EOM
[program:fiercephish]
command=/usr/bin/php /var/www/fiercephish/artisan queue:listen --queue=high,medium,low,default
process_name = %(program_name)s-80%(process_num)02d
stdout_logfile = /var/log/fiercephish-80%(process_num)02d.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=10
numprocs=1
directory=/var/www/fiercephish
stopwaitsecs=600
user=www-data
EOM
		sys_cmd "service supervisor start"
		sleep 5
		sys_cmd "supervisorctl reload"
		sleep 10
		sys_cmd "service supervisor restart"
		sleep 5
	fi
	
	if [[ -z ${SSL_ENABLE} ]]
		then
		prompt "Do you want to enable HTTPS for FiercePhish using LetsEncrypt (you must have a valid domain name)? [y/n]"
		ssl=$(get_input "n")
		SSL_ENABLE=false
		if [[ ${ssl} =~ [yY] ]]
			then
			SSL_ENABLE=true
		fi
	fi
	if [[ $SSL_ENABLE = true ]]
		then
		error "You want to enable HTTPS but this isn't implemented yet.  You can do this yourself though until its implemented :-("
	fi
	FP_INSTRUCTIONS+=("Go to http://${SERVER_IP}/ to use FiercePhish! (or http://${WEBSITE_DOMAIN}/ if you used a domain name)")
	DNS_INSTRUCTIONS+=("A record for '@' point to '${SERVER_IP}'")
	DNS_INSTRUCTIONS+=("A record for 'www' point to '${SERVER_IP}'")
	notice "Done installing FiercePhish!"
}

install_smtp_imap()
{
	notice "Installing SMTP (Postfix) and IMAP (dovecot)"
	
	info "Updating package repositories"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "apt-get update"
	fi
	

	info "Installing the required packages (this may take a few minutes)"
	if [ -z $EMAIL_DOMAIN ]
		then
		prompt "What is the domain name you will be sending email from (ie: example.com) (if none, use localhost)"
		EMAIL_DOMAIN=$(get_input "localhost")
		if [[ $EMAIL_DOMAIN = "" ]]
			then
			EMAIL_DOMAIN="localhost"
		fi
		info "Installing..."
	fi
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "debconf-set-selections <<< \$'postfix postfix/mailname string \'${EMAIL_DOMAIN}\''"
		sys_cmd "debconf-set-selections <<< \$'postfix postfix/main_mailer_type string \'Internet Site\''"
		sys_cmd "DEBIAN_FRONTEND=noninteractive apt-get -y install postfix curl dovecot-imapd opendkim opendkim-tools"
	fi
	
	info "Creating local user fiercephish for email retrieval"
	IMAP_PASSWORD=$(random_str)
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "adduser --disabled-password --gecos '' fiercephish"
		sys_cmd "echo 'fiercephish:${IMAP_PASSWORD}' | chpasswd"
	fi
	
	
	info "Configuring Postfix"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "sed -i 's/myhostname = .*$/myhostname = ${EMAIL_DOMAIN}/' /etc/postfix/main.cf"
		grep -q -F 'resolve_numeric_domain' /etc/postfix/main.cf || echo 'resolve_numeric_domain = yes' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/resolve_numeric_domain = .*$/resolve_numeric_domain = yes/' /etc/postfix/main.cf"
		grep -q -F 'smtp_tls_security_level' /etc/postfix/main.cf || echo 'smtp_tls_security_level = may' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/smtp_tls_security_level = .*$/smtp_tls_security_level = may/' /etc/postfix/main.cf"
		grep -q -F 'smtp_tls_loglevel' /etc/postfix/main.cf || echo 'smtp_tls_loglevel = 1' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/smtp_tls_loglevel = .*$/smtp_tls_loglevel = 1/' /etc/postfix/main.cf"
		grep -q -F 'luser_relay' /etc/postfix/main.cf || echo 'luser_relay = fiercephish' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/luser_relay = .*$/luser_relay = fiercephish/' /etc/postfix/main.cf"
		grep -q -F 'local_recipient_maps' /etc/postfix/main.cf || echo 'local_recipient_maps = ' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/local_recipient_maps = .*$/local_recipient_maps = /' /etc/postfix/main.cf"
		echo ${EMAIL_DOMAIN} > /etc/mailname
		sys_cmd "service postfix restart"
	fi
	
	
	info "Configuring DKIM"
	if [[ $OS = "Ubuntu" ]]
		then
		grep -q -F 'AutoRestart' /etc/opendkim.conf || echo 'AutoRestart            Yes' >> /etc/opendkim.conf
		grep -q -F 'AutoRestartRate' /etc/opendkim.conf || echo 'AutoRestartRate            10/1h' >> /etc/opendkim.conf
		grep -q -F 'UMask' /etc/opendkim.conf || echo 'UMask            002' >> /etc/opendkim.conf
		grep -q -F 'Syslog' /etc/opendkim.conf || echo 'Syslog            yes' >> /etc/opendkim.conf
		grep -q -F 'SyslogSuccess' /etc/opendkim.conf || echo 'SyslogSuccess            Yes' >> /etc/opendkim.conf
		grep -q -F 'LogWhy' /etc/opendkim.conf || echo 'LogWhy            Yes' >> /etc/opendkim.conf
		grep -q -F 'Canonicalization' /etc/opendkim.conf || echo 'Canonicalization            relaxed/simple' >> /etc/opendkim.conf
		grep -q -F 'ExternalIgnoreList' /etc/opendkim.conf || echo 'ExternalIgnoreList            refile:/etc/opendkim/TrustedHosts' >> /etc/opendkim.conf
		grep -q -F 'InternalHosts' /etc/opendkim.conf || echo 'InternalHosts            refile:/etc/opendkim/TrustedHosts' >> /etc/opendkim.conf
		grep -q -F 'KeyTable' /etc/opendkim.conf || echo 'KeyTable            refile:/etc/opendkim/KeyTable' >> /etc/opendkim.conf
		grep -q -F 'SigningTable' /etc/opendkim.conf || echo 'SigningTable            refile:/etc/opendkim/SigningTable' >> /etc/opendkim.conf
		grep -q -F 'Mode' /etc/opendkim.conf || echo 'Mode            sv' >> /etc/opendkim.conf
		grep -q -F 'PidFile' /etc/opendkim.conf || echo 'PidFile            /var/run/opendkim/opendkim.pid' >> /etc/opendkim.conf
		grep -q -F 'SignatureAlgorithm' /etc/opendkim.conf || echo 'SignatureAlgorithm            rsa-sha256' >> /etc/opendkim.conf
		grep -q -F 'UserID' /etc/opendkim.conf || echo 'UserID            opendkim:opendkim' >> /etc/opendkim.conf
		grep -q -F 'Socket' /etc/opendkim.conf || echo 'Socket            inet:12301@localhost' >> /etc/opendkim.conf
		sys_cmd "sed -i 's/AutoRestart .*$/AutoRestart           Yes/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/AutoRestartRate .*$/AutoRestartRate           10/1h/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/UMask .*$/UMask           002/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/Syslog .*$/Syslog           yes/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/SyslogSuccess .*$/SyslogSuccess           Yes/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/LogWhy .*$/LogWhy           Yes/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/Canonicalization .*$/Canonicalization           relaxed\/simple/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/ExternalIgnoreList .*$/ExternalIgnoreList           refile:\/etc\/opendkim\/TrustedHosts/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/InternalHosts .*$/InternalHosts           refile:\/etc\/opendkim\/TrustedHosts/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/KeyTable .*$/KeyTable           refile:\/etc\/opendkim\/KeyTable/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/SigningTable .*$/SigningTable           refile:\/etc\/opendkim\/SigningTable/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/Mode .*$/Mode           sv/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/PidFile .*$/PidFile           \/var\/run\/opendkim\/opendkim.pid/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/SignatureAlgorithm .*$/SignatureAlgorithm           rsa-sha256/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/UserID .*$/UserID           opendkim:opendkim/' /etc/opendkim.conf"
		sys_cmd "sed -i 's/Socket .*$/Socket           inet:12301@localhost/' /etc/opendkim.conf"
		echo 'SOCKET="inet:12301@localhost"' > /etc/default/opendkim
		grep -q -F 'milter_protocol' /etc/postfix/main.cf || echo 'milter_protocol = 2' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/^.*milter_protocol = .*$/milter_protocol = 2/' /etc/postfix/main.cf"
		grep -q -F 'milter_default_action' /etc/postfix/main.cf || echo 'milter_default_action = accept' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/^.*milter_default_action = .*$/milter_default_action = accept/' /etc/postfix/main.cf"
		grep -q -F 'smtpd_milters' /etc/postfix/main.cf || echo 'smtpd_milters = inet:localhost:12301' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/^.*smtpd_milters = .*$/smtpd_milters = inet:localhost:12301/' /etc/postfix/main.cf"
		grep -q -F 'non_smtpd_milters' /etc/postfix/main.cf || echo 'non_smtpd_milters = inet:localhost:12301' >> /etc/postfix/main.cf
		sys_cmd "sed -i 's/^.*non_smtpd_milters = .*$/non_smtpd_milters = inet:localhost:12301/' /etc/postfix/main.cf"
		sys_cmd "mkdir -p /etc/opendkim/keys"
		cat > /etc/opendkim/TrustedHosts <<- EOM
127.0.0.1
localhost
192.168.0.1/24

${EMAIL_DOMAIN}
*.${EMAIL_DOMAIN}
EOM
		echo "mail._domainkey.${EMAIL_DOMAIN} ${EMAIL_DOMAIN}:mail:/etc/opendkim/keys/${EMAIL_DOMAIN}/mail.private" > /etc/opendkim/KeyTable
		echo "*@${EMAIL_DOMAIN} mail._domainkey.${EMAIL_DOMAIN}" > /etc/opendkim/SigningTable
		sys_cmd "pushd /etc/opendkim/keys"
		sys_cmd "mkdir ${EMAIL_DOMAIN}"
		sys_cmd "pushd ${EMAIL_DOMAIN}"
		sys_cmd "opendkim-genkey -s mail -d ${EMAIL_DOMAIN}"
		sys_cmd "chown opendkim:opendkim mail.private"
		DKIM_KEY=$(cat mail.txt | xargs | sed 's/.*(\s\(.*\)\s).*/\1/')
		DNS_INSTRUCTIONS+=("TXT record for 'mail._domainkey' with text: ${DKIM_KEY}")
	fi
	
	
	info "Configuring Dovecot (for future use)"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "sed -i 's/^.*disable_plaintext_auth = .*$/disable_plaintext_auth = no/' /etc/dovecot/conf.d/10-auth.conf"
		sys_cmd "sed -i 's/^.*auth_mechanisms = .*$/auth_mechanisms = plain login/' /etc/dovecot/conf.d/10-auth.conf"
	fi
	
	info "Restarting mail services"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "service postfix restart"
		sys_cmd "service dovecot restart"
		sys_cmd "service opendkim restart"
	fi
	
	info "Updating FiercePhish configuration file"
	if [[ -f /var/www/fiercephish/.env ]]
		then
		sys_cmd "sed -i 's/MAIL_DRIVER=.*$/MAIL_DRIVER=smtp/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/MAIL_HOST=.*$/MAIL_HOST=127.0.0.1/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/MAIL_PORT=.*$/MAIL_PORT=25/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/MAIL_USERNAME=.*$/MAIL_USERNAME=null/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/MAIL_PASSWORD=.*$/MAIL_PASSWORD=null/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/MAIL_ENCRYPTION=.*$/MAIL_ENCRYPTION=null/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/IMAP_USERNAME=.*$/IMAP_USERNAME=fiercephish/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/IMAP_PASSWORD=.*$/IMAP_PASSWORD=${IMAP_PASSWORD}/' /var/www/fiercephish/.env"
		sys_cmd "pushd /var/www/fiercephish"
		sys_cmd "php artisan config:cache"
		sys_cmd "popd"
	else
		error "Unable to find the .env file for FiercePhish.  You'll have to update it manually at the end"
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_DRIVER=smtp')
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_HOST=127.0.0.1')
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_PORT=25')
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_USERNAME=null')
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_PASSWORD=null')
		FP_INSTRUCTIONS+=('Edit the following in .env:  MAIL_ENCRYPTION=null')
		FP_INSTRUCTIONS+=('Edit the following in .env:  IMAP_USERNAME=fiercephish')
		FP_INSTRUCTIONS+=("Edit the following in .env:  IMAP_PASSWORD=${IMAP_PASSWORD}")
		FP_INSTRUCTIONS+=("Run: php artisan config:cache")
		sleep 5
	fi
	
	DNS_INSTRUCTIONS+=("A record for 'mail' point to '${SERVER_IP}'")
	DNS_INSTRUCTIONS+=("MX record point to 'mail' subdomain (or MXE record pointing to ${SERVER_IP})")
	DNS_INSTRUCTIONS+=("TXT record for '@' with text: v=spf1 a mx a:mail.${EMAIL_DOMAIN} a:${EMAIL_DOMAIN} ip4:${SERVER_IP} ~all")
	DNS_INSTRUCTIONS+=("TXT record for '_dmarc' with text: v=DMARC1; p=none");
	notice "Done installing SMTP and IMAP!"
}

if [[ $1 = '-v' ]]
	then VERBOSE=true
fi
if [ $EUID != 0 ]; then
	error "You must run the installer script as root"
	exit 1
fi

if [[ $0 = "bash" && ! -f ~/fiercephish.config ]]
	then
	error "Because you are running this as a remote pipe execution, you need to create a configuration file for all the information that is required."
	notice "Please edit ~/fiercephish.config with the necessary information and rerun this command"
	cat > ~/fiercephish.config <<- EOM
#### FiercePhish Installation Configuration File ####

# Set this to true once you are done configuring everything
CONFIGURED=false

# Set this to true if you want to see all output of all installation actions
VERBOSE=false

# Do you want HTTPS to be configured (using LetsEncrypt) for SMTP and Web?
# YOU MUST HAVE A VALID DOMAIN NAME FOR THIS TO WORK!
SSL_ENABLE=false

# Set this to what you want the mysql root password to be
MYSQL_ROOT_PASSWD=root_passwd

# Set this to what the website domain is (ie: example.com). No "http://"
# If you don't have a domain, use the publicly facing IP address (or 127.0.0.1)
# This will be what you use to browse to FiercePhish in your browser
WEBSITE_DOMAIN=127.0.0.1

# Set this to the domain that you will be sending email from
# If you don't have a domain, use "localhost"
EMAIL_DOMAIN=localhost


# Firet FiercePhish user's Username
ADMIN_USERNAME=admin

# First FiercePhish user's Email
ADMIN_EMAIL=root@localhost

# First FiercePhish user's Password
ADMIN_PASSWORD=test

EOM
	exit 1
elif [[ $0 = "bash" && -f ~/fiercephish.config ]]
	then
	source ~/fiercephish.config
	if [[ $CONFIGURED = true ]]
		then
		if [[ -z $CONFIGURED || -z $VERBOSE || -z $SSL_ENABLE || -z $MYSQL_ROOT_PASSWD || -z $WEBSITE_DOMAIN || -z $EMAIL_DOMAIN || -z $ADMIN_USERNAME || -z $ADMIN_EMAIL || -z $ADMIN_PASSWORD ]]
			then
			error "Found the configuration file, but it is missing some variables!"
			exit 1
		fi
		info "Found the FiercePhish configuration file and continuing with installation"
	else
		error "Edit ~/fiercephish.config with the proper settings. Once done, make sure CONFIGURED=true at the top"
		exit 1
	fi
fi

main

if [[ $0 = "bash" && -f ~/fiercephish.config ]]
	then
	rm ~/fiercephish.config
fi