#!/usr/bin/env bash


### Colors for output
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

### Global variables

OS=""
OS_VERSION=""
GITHUB_BRANCH="master"
VERBOSE=false
FIERCEPHISH_MYSQL_PASSWD=
SERVER_IP=$(curl -s icanhazip.com)

FP_INSTRUCTIONS=()
MAIL_INSTRUCTIONS=()
DNS_INSTRUCTIONS=()

read -d '' CONFIG_FILE_CONTENTS << EOF
#################################
### FiercePhish Configuration ###
###      By Chris King        ###
###        @raikiasec         ###
#################################


## Set this to true once you are done configuring everything
##    Default: false
##    Recommended: true
CONFIGURED=false



############ General Settings ############

## Set this to true if you want to see all output of all installation actions
##    Default: false
##    Recommended: false
VERBOSE=false



############ Web Settings ############

## Specify the port you'd like Apache to run on
##    Default: 80
##    Recommended: 80
APACHE_PORT=80

## Set this to what the website domain is (ie: example.com). No "http://"
## If you don't have a domain, use the publicly facing IP address (or 127.0.0.1)
## This will be what you use to browse to FiercePhish in your browser
##     Default: 127.0.0.1
##     Recommended: <domain or 127.0.0.1>
WEBSITE_DOMAIN="127.0.0.1"



############ SMTP Settings ############

## Set this to the domain that you will be sending email from. If you don't
## have a domain, use "localhost". Otherwise, use the domain (ie: example.com) 
## without "http://"
##     Default: localhost
##     Recommended: <domain or localhost>
EMAIL_DOMAIN="localhost"



############ Account Settings ############ 

## Set this to what you want the MySQL root password to be. If MySQL is already
## installed, make sure this is the valid root password for it.
##     Default: mysqlPasswd123
##     Recommended: <something else>
MYSQL_ROOT_PASSWD="mysqlPasswd123"

## First FiercePhish user's username
##     Default: admin
##     Recommended: admin
ADMIN_USERNAME="admin"

## First FiercePhish user's email
##     Default: root@localhost
##     Recommended: <your email address>
ADMIN_EMAIL="root@localhost"

## First FiercePhish user's password
##     Default: defaultpass
##     Recommended: <something else>
ADMIN_PASSWORD="defaultpass"



############ Advanced Settings ############

## If you have limited RAM (less than 600 MB) and no swap space, set this to true
## to automatically create 2GB swap space.  This is useful if you are running the
## installer on a brand new VPS that has little RAM.
##     Default: false
##     Recommended: false
CREATE_SWAPSPACE=false



EOF

### Main function

main()
{
    show_header
    if [ $EUID != 0 ]
    	then
    	error "You must run the installation script as root"
    	exit 1
    fi
    check_os
    get_config_vars "$@"
    prompt_choice
    if [[ $INPUT_SELECTION = 1 ]]
    	then
    	validate_vars_general
    	validate_vars_http
    	validate_vars_smtp
    elif [[ $INPUT_SELECTION = 2 ]]
    	then
    	validate_vars_general
    	validate_vars_http
    elif [[ $INPUT_SELECTION = 3 ]]
    	then
    	validate_vars_general
    	validate_vars_smtp
    elif [[ $INPUT_SELECTION = 4 ]]
    	then
    	validate_vars_general
    	validate_vars_ssl
    	install_ssl
    	exit 1
    elif [[ $INPUT_SELECTION = 5 ]]
    	then
    	validate_vars_general
    	uninstall_ssl
    	exit 1
    else
    	error "Unknown selection!"
    	exit 1
    fi
    review_vars
    if [[ $INPUT_SELECTION = 1 ]]
    	then
    	install_fiercephish
    	install_smtp_imap
    elif [[ $INPUT_SELECTION = 2 ]]
    	then
    	install_fiercephish
    elif [[ $INPUT_SELECTION = 3 ]]
    	then
    	install_smtp_imap
    fi
    notice "Installation is complete"
    if [[ ${#FP_INSTRUCTIONS[@]} -ne 0 || ${#MAIL_INSTRUCTIONS[@]} -ne 0 || ${#DNS_INSTRUCTIONS[@]} -ne 0 ]]
    	then
		info "Perform the following actions to finish up:"
		echo -e ""
	fi
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
	if [[ $0 = "bash" ]]
		then
		rm ~/fiercephish.config
	fi
}




### Action functions

show_header()
{
    echo -e ""
    echo -e "${CYAN}#################################################################"
    echo -e "####################  ${LYELLOW}FiercePhish Installer${CYAN}  ####################"
    echo -e "####################      By ${GREEN}Chris King${CYAN}      ####################"
    echo -e "####################        ${GREEN}@raikiasec${CYAN}       ####################"
    echo -e "#################################################################${RESTORE}"
    echo -e ""
    echo -e ""
    notice "This installer automatically installs FiercePhish and all the other services needed."
    notice "It is designed to work with Ubuntu and currently only works for Ubuntu 16.04 and Ubuntu 16.10"
    echo -e ""
}

check_os()
{
	info "Detecting OS distribution..."
	if [[ -f /etc/lsb-release ]]
		then
		. /etc/lsb-release
		OS=${DISTRIB_ID}
		OS_VERSION=${DISTRIB_RELEASE}
		if [[ $OS = 'Ubuntu' ]]
			then
			if [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
				then
				notice "Found that you are running ${LYELLOW}${OS} ${OS_VERSION}${WHITE}! This is a supported operating system!"
			elif [[ $OS_VERSION = "14.04" ]]
				then
				error "You are running ${LYELLOW}${OS} ${OS_VERSION}${LRED}. As of FiercePhish v1.2.0, this OS version is not officially supported."
				error "You can read how to get FiercePhish working with ${LYELLOW}${OS} ${OS_VERSION}${LRED} here: "
				error "     https://github.com/Raikia/FiercePhish/wiki/Ubuntu-14.04-Installation-Guide"
				exit 1
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
	echo -e ""
}

get_config_vars()
{
	if [[ $0 = "bash" ]]
		then
		if [[ ! -f ~/fiercephish.config ]]
			then
			error "Because you are running this as a remote pipe execution, you need to create a configuration file for all the information that is required."
			notice "Please edit ~/fiercephish.config with the necessary information and rerun this command"
			echo "$CONFIG_FILE_CONTENTS" > ~/fiercephish.config
			exit 1
		else
			source ~/fiercephish.config
			info "Found the fiercephish.config configuration file!"
			if [[ -z $CONFIGURED || ! $CONFIGURED = "true" ]]
				then
				error "You must set CONFIGURED=true in the configuration file to proceed"
				exit 1
			fi
		fi
	fi
	if [[ $1 = '-v' ]]
		then
		VERBOSE=true
		notice "Verbose mode is set to ${LYELLOW}ON${RESTORE}"
	fi
}

prompt_choice()
{
	if [[ $0 != "bash" ]]
		then
		echo -e "    ${LYELLOW}Options:${RESTORE} "
		echo -e "        1. ${WHITE}Install FiercePhish + SMTP + IMAP (${LRED}recommended${WHITE})${RESTORE}"
		echo -e "        2. ${WHITE}Install FiercePhish only${RESTORE}"
		echo -e "        3. ${WHITE}Install SMTP + IMAP only${RESTORE}"
		echo -e "        4. ${WHITE}Setup SSL using LetsEncrypt${RESTORE}"
		echo -e "        5. ${WHITE}Disable SSL${RESTORE}"
	fi
	echo -e ""
	while [ true ]
		do
		prompt "Selection [1-5]"
		INPUT_SELECTION=$(get_input "1")
		if [ "$INPUT_SELECTION" -eq "$INPUT_SELECTION" ] 2> /dev/null
			then
			if [[ $INPUT_SELECTION -lt 6 && $INPUT_SELECTION -gt 0 ]]
				then
				break
			fi
		fi 
		error "Unknown selection! Choose again!"
	done 
	if [[ $0 = "bash" ]]
		then
		echo -e "${LRED}  !!! This is the quick install method and will install FiercePhish + SMTP + IMAP !!!"
		echo -e "  !!!       You have 10 seconds to CTRL+C if you do not want this to happen       !!!${RESTORE}"
		sleep 10
	fi
	echo -e ""
}

validate_vars_general()
{
	if [[ -z $CONFIGURED || $CONFIGURED = false ]]
		then
		if [[ $0 = "bash" ]]
			then
			error "CONFIGURED variable is not set or is not \"true\". Set the variable to \"true\" to proceed"
			exit 1
		fi
	fi
	
	if [[ -z $VERBOSE ]]
		then
		error "VERBOSE variable is not set.  Make sure it is \"true\" or \"false\""
		exit 1
	fi
	
	
	mem=$(free -m | awk '/^Mem:/{print $2}')
	swap=$(free -m | awk '/^Swap:/{print $2}')
	total=$(($mem+$swap))
	if [[ $total -lt 600 ]]
		then
		error "System memory + swap is less than 600 MB (it has ${total} MB)!"
		if [[ ! $0 = "bash" ]]
			then
			echo -e ""
			notice "FiercePhish requires at least 600 MB of RAM. Creating swap space can fix a low RAM issue."
			prompt "Do you want to create a 2GB swap space? [Y/n]"
			CREATE_SWAPSPACE_INPUT=$(get_input "n")
			if [[ $CREATE_SWAPSPACE_INPUT =~ ^[n|N]$ ]]
				then
				CREATE_SWAPSPACE="false"
			else
				CREATE_SWAPSPACE="true"
			fi
		else
			if [[ -z $CREATE_SWAPSPACE ]]
				then
				error "CREATE_SWAPSPACE variable is not set or is not \"true\". Set the variable to \"true\" to proceed"
				exit 1
			fi
		fi
	fi
	
	if [[ $CREATE_SWAPSPACE = "true" ]]
		then
		notice "Creating a 2GB swapspace at /swapfile"
		sys_cmd "fallocate -l 2G /swapfile"
		sys_cmd "chmod 600 /swapfile"
		sys_cmd "mkswap /swapfile"
		sys_cmd "swapon /swapfile"
		echo "/swapfile none swap sw 0 0" >> /etc/fstab
		grep -q -F 'swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
		notice "Done creating swapspace. Swap enabled"
	fi
}

validate_vars_http()
{
	while [[ -z $APACHE_PORT ]]
		do
		if [[ $0 = "bash" ]]
			then
			error "APACHE_PORT is not set or is not a number.  It must be a number between 1 and 65535."
			exit 1
		else
			echo -e ""
			prompt "Enter the port you want Apache to run on [80]"
			APACHE_PORT=$(get_input "80")
			if [[ $APACHE_PORT = "" ]]
				then
				APACHE_PORT=80
			fi
			if [[ ! $APACHE_PORT =~ ^[0-9]+$ || $APACHE_PORT -lt 1 || $APACHE_PORT -gt 65535 ]]
			then
				error "Invalid port!  It must be a number between 1 and 65535"
				unset APACHE_PORT
			fi
		fi 
	done
	
	if [[ -z $WEBSITE_DOMAIN ]]
		then
		if [[ $0 = "bash" ]]
			then
			error "WEBSITE_DOMAIN is not set. It must be a domain name, the public IP, or \"127.0.0.1\""
			exit 1
		else
			echo -e ""
			notice "If you have purchased a real domain to use with FiercePhish, enter it below. If you don't"
			notice "have a domain, you can use the public IP address or just \"127.0.0.1\""
			prompt "Enter the domain name for the website [127.0.0.1]"
			WEBSITE_DOMAIN=$(get_input "127.0.0.1")
			if [[ $WEBSITE_DOMAIN = "" ]]
				then
				WEBSITE_DOMAIN="127.0.0.1"
			fi
		fi
	fi
	
	
	if [[ -z $MYSQL_ROOT_PASSWD ]]
		then
		if [[ $0 = "bash" ]]
			then
			error "MYSQL_ROOT_PASSWD is not set. If MySQL is installed already, this should be the current root MySQL password."
			error "Otherwise, it should be whatever you want the new password to be"
			exit 1
		else
			echo -e ""
			if [[ $(type mysql) ]] >/dev/null 2>&1
				then
				notice "MySQL installation detected!"
				prompt "Enter the current root MySQL password"
				MYSQL_ROOT_PASSWD=$(get_input "pass")
			else
				prompt "Enter a password for the MySQL root user"
				MYSQL_ROOT_PASSWD=$(get_input "pass")
			fi
		fi
	fi
	if [[ $(type mysql) ]] >/dev/null 2>&1
		then
		while [[ ! $(mysql -u root --password="${MYSQL_ROOT_PASSWD}" -e "show databases" 2> /dev/null) ]]
			do
			error "Invalid root MySQL password!"
			if [[ $0 = "bash" ]]
				then
				exit 1
			fi
			prompt "Enter the current root MySQL password"
			MYSQL_ROOT_PASSWD=$(get_input "pass")
		done
	fi
	
	if [[ -z $ADMIN_USERNAME ]]
		then
		if [[ $0 = "bash" ]]
			then
			error "ADMIN_USERNAME is not set."
			exit 1
		else
			echo -e ""
			notice "Enter the account information for the first FiercePhish user account:"
			prompt "Enter a username [admin]"
			ADMIN_USERNAME=$(get_input "admin")
			if [[ $ADMIN_USERNAME = "" ]]
				then
				ADMIN_USERNAME="admin"
			fi
		fi
	fi
	
	while [[ -z $ADMIN_EMAIL ]]
		do
		if [[ $0 = "bash" ]]
			then
			error "ADMIN_EMAIL is not set."
			exit 1
		else
			prompt "Enter an email [root@localhost]"
			ADMIN_EMAIL=$(get_input "root@localhost")
			if [[ $ADMIN_EMAIL = "" ]]
				then
				ADMIN_EMAIL="root@localhost"
			fi
		fi
	done
	
	while [[ -z $ADMIN_PASSWORD ]]
		do
		if [[ $0 = "bash" ]]
			then
			error "ADMIN_PASSWORD is not set."
			exit 1
		else
			prompt "Enter a password"
			ADMIN_PASSWORD=$(get_input "")
			if [[ $ADMIN_PASSWORD = "" ]]
				then
				unset ADMIN_PASSWORD
			fi
		fi
	done
	
}

validate_vars_smtp()
{
	while [[ -z $EMAIL_DOMAIN ]]
		do
		if [[ $0 = "bash" ]]
			then
			error "EMAIL_DOMAIN is not set. It must be a domain name, or \"localhost\""
			exit 1
		else
			DEFAULT_EMAIL_DOMAIN="localhost"
			if [[ ! -z $WEBSITE_DOMAIN && $WEBSITE_DOMAIN != "127.0.0.1" ]]
				then
				DEFAULT_EMAIL_DOMAIN=$WEBSITE_DOMAIN
			fi
			echo -e ""
			notice "If you have purchased a real domain to send email from, enter it below (ie: example.com). If you"
			notice "plan to spoof another domain that you do not own, enter \"localhost\". ${LRED}Do not enter an IP address${WHITE}"
			prompt "Enter the domain that will send email [${DEFAULT_EMAIL_DOMAIN}]"
			EMAIL_DOMAIN=$(get_input "localhost")
			if [[ $EMAIL_DOMAIN = "" ]]
				then
				EMAIL_DOMAIN=$DEFAULT_EMAIL_DOMAIN
			fi
			
			if valid_ip "$EMAIL_DOMAIN"
				then
				error "This must be a domain, not an IP address. If you don't have a domain, enter \"localhost\"!"
				unset EMAIL_DOMAIN
			fi
		fi
	done
}

validate_vars_ssl()
{
	while [[ -z $SSL_DOMAIN ]]
		do
		echo -e ""
		notice "Enter the domain you would like configured for SSL below (${LRED}This domain's A records must be set properly!${LYELLOW})"
		notice "Note: this will auto-redirect all HTTP requests to HTTPS"
		prompt "Enter the domain you want configured for SSL"
		SSL_DOMAIN=$(get_input "")
		if [[ $SSL_DOMAIN = "" ]]
			then
			unset SSL_DOMAIN
		fi
	done
	
	while [[ -z $SSL_EMAIL ]]
		do
		echo -e ""
		notice "LetsEncrypt requires a valid email address for you. Enter it below"
		prompt "Enter your email"
		SSL_EMAIL=$(get_input "")
		if [[ $SSL_EMAIL = "" ]]
			then
			unset SSL_EMAIL
		fi
	done
}


review_vars()
{
	if [[ ! $0 = "bash" ]]
		then
		info "Review the configurations below: "
		echo -e "
     VERBOSE           = ${VERBOSE}
     APACHE_PORT       = ${APACHE_PORT}
     WEBSITE_DOMAIN    = ${WEBSITE_DOMAIN}
     MYSQL_ROOT_PASSWD = ${MYSQL_ROOT_PASSWD}
     ADMIN_USERNAME    = ${ADMIN_USERNAME}
     ADMIN_EMAIL       = ${ADMIN_EMAIL}
     ADMIN_PASSWORD    = ${ADMIN_PASSWORD}
     EMAIL_DOMAIN      = ${EMAIL_DOMAIN}
     "
    	prompt "Continue with install? [Y/n]"
    	CONTINUE=$(get_input "n")
    	if [[ $CONTINUE =~ ^[n|N]$ ]]
    		then
    		error "Exiting install!"
    		exit 1
    	else
    		info "Continuing installation..."
    	fi
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
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWD'"
		sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWD'"
		if [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php php-cli mysql-server php-mysql libapache2-mod-php php-mcrypt php-mbstring php-imap php-gd php-zip phpunit npm unzip git curl supervisor"
		fi
		sys_cmd "service mysql restart"
	fi

	

	info "Installing Composer"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer"
		fi
	fi


	info "Installing Bower"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "npm install -g bower"
			sys_cmd "ln -s /usr/bin/nodejs /usr/bin/node"
		fi
	fi


	info "Pulling the latest FiercePhish from GitHub to /var/www/fiercephish"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "git clone https://github.com/Raikia/FiercePhish.git /var/www/fiercephish"
		sys_cmd "pushd /var/www/fiercephish"
		sys_cmd "git checkout ${GITHUB_BRANCH}"
		sys_cmd "popd"
		sys_cmd "chown -R www-data:www-data /var/www/fiercephish"
	fi


	info "Installing FiercePhish into Apache (this can take a few minutes)"
	if [[ $OS = "Ubuntu" ]]
		then
		cat > /etc/apache2/sites-available/fiercephish.conf <<- EOM
<VirtualHost *:${APACHE_PORT}>
    ServerName ${WEBSITE_DOMAIN}
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/fiercephish/public
    <Directory /var/www/fiercephish>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error_fiercephish.log
    CustomLog \${APACHE_LOG_DIR}/access_fiercephish.log combined
</VirtualHost>
EOM
		if [[ $APACHE_PORT != "80" ]]
			then
			grep -q -F 'FiercePhish Listener' /etc/apache2/ports.conf || echo -e "\n# FiercePhish Listener\nListen ${APACHE_PORT}" >> /etc/apache2/ports.conf
			sys_cmd "cat /etc/apache2/ports.conf | tr '\n' '\f' | sed -e 's/FiercePhish Listener\fListen .*$/FiercePhish Listener\fListen ${APACHE_PORT}\f/'  | tr '\f' '\n' > /etc/apache2/ports.conf.new; mv /etc/apache2/ports.conf.new /etc/apache2/ports.conf"
		fi
		
		sys_cmd "a2ensite fiercephish"
		sys_cmd "a2enmod rewrite"
		sys_cmd "a2dissite 000-default"
		if [[ $OS_VERSION = "16.04" || $OS_VERSION = "16.10" ]]
			then
			sys_cmd "phpenmod imap"
		fi
		sys_cmd "service apache2 restart"
		sys_cmd "pushd /var/www/fiercephish"
		sys_cmd "composer install"
		sys_cmd "bower install --allow-root"
		sys_cmd "mysql -u root --password='${MYSQL_ROOT_PASSWD}' -e 'create database fiercephish'"
		FIERCEPHISH_MYSQL_PASSWD=$(random_str)
		sys_cmd "mysql -u root --password='${MYSQL_ROOT_PASSWD}' -e \$'create user fiercephish@localhost identified by \'${FIERCEPHISH_MYSQL_PASSWD}\''"
		sys_cmd "mysql -u root --password='${MYSQL_ROOT_PASSWD}' -e \$'SET PASSWORD FOR fiercephish@localhost = PASSWORD(\'${FIERCEPHISH_MYSQL_PASSWD}\');'"
		sys_cmd "mysql -u root --password='${MYSQL_ROOT_PASSWD}' -e 'grant all privileges on fiercephish.* to fiercephish@localhost'"
		sys_cmd "mysql -u root --password='${MYSQL_ROOT_PASSWD}' -e 'flush privileges'"
		sys_cmd "popd"
	fi


	info "Configuring FiercePhish"
	sys_cmd "pushd /var/www/fiercephish"
	sys_cmd "cp .env.example .env"
	sys_cmd "touch storage/logs/laravel.log"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "chown -R www-data:www-data /var/www/fiercephish"
		sys_cmd "touch /var/log/apache2/error_fiercephish.log"
		sys_cmd "touch /var/log/apache2/access_fiercephish.log"
		sys_cmd "chown root:www-data /var/log/apache2/"
		sys_cmd "chown root:www-data /var/log/apache2/error_fiercephish.log"
		sys_cmd "chown root:www-data /var/log/apache2/access_fiercephish.log"
	fi
	sys_cmd "sed -i 's/APP_DEBUG=.*$/APP_DEBUG=false/' .env"
	sys_cmd "sed -i 's/APP_URL=.*$/APP_URL=http:\/\/${WEBSITE_DOMAIN}:${APACHE_PORT}/' .env"
	sys_cmd "sed -i 's/DB_USERNAME=.*$/DB_USERNAME=fiercephish/' .env"
	sys_cmd "sed -i 's/DB_PASSWORD=.*$/DB_PASSWORD=${FIERCEPHISH_MYSQL_PASSWD}/' .env"

	info "Generating database"
	sys_cmd "php artisan key:generate"
	sys_cmd "php artisan config:cache"
	sleep 10
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

	info "Installing cron job"
	cron_command="/usr/bin/env php /var/www/fiercephish/artisan schedule:run >> /dev/null 2>&1"
	cron_job="* * * * * $cron_command"
	cat <(grep -i -F -v "$cron_command" <(crontab -u www-data -l 2>/dev/null)) <(echo "$cron_job") | crontab -u www-data -


	info "Configuring Supervisor to process jobs"
	if [[ $OS = "Ubuntu" ]]
		then
		cat > /etc/supervisor/conf.d/fiercephish.conf <<- EOM
[program:fiercephish]
command=/usr/bin/php /var/www/fiercephish/artisan queue:work --queue=operation,email,campaign_email,default --tries 1 --timeout=86100
process_name = %(program_name)s-80%(process_num)02d
stdout_logfile = /var/log/fiercephish-80%(process_num)02d.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=10
autostart=true
autorestart=true
numprocs=10
directory=/var/www/fiercephish
user=www-data
redirect_stderr=true
EOM
		sys_cmd "supervisorctl reread"
		sleep 2
		sys_cmd "supervisorctl update"
		sleep 2
		sys_cmd "service supervisor start"
		sleep 5
		sys_cmd "supervisorctl reload"
		sleep 10
		sys_cmd "service supervisor restart"
		sleep 5
	fi
	
	ADDON_INSTRUCTION=""
	if [[ $WEBSITE_DOMAIN != "localhost" ]]
	then
		if ! valid_ip $WEBSITE_DOMAIN
			then
			ADDON_INSTRUCTION=" (or ${LYELLOW}http://${WEBSITE_DOMAIN}:${APACHE_PORT}/${RESTORE})"
			DNS_INSTRUCTIONS+=("${LCYAN}A${RESTORE} record for '${LGREEN}@${RESTORE}' point to '${LYELLOW}${SERVER_IP}${RESTORE}'")
			DNS_INSTRUCTIONS+=("${LCYAN}A${RESTORE} record for '${LGREEN}www${RESTORE}' point to '${LYELLOW}${SERVER_IP}${RESTORE}'")
		fi
	fi
	FP_INSTRUCTIONS+=("Go to ${LYELLOW}http://${SERVER_IP}:${APACHE_PORT}/${RESTORE} to use FiercePhish!${ADDON_INSTRUCTION}")
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
		sys_cmd "usermod -a -G mail fiercephish"
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
		sys_cmd "touch /var/log/mail.log"
		sys_cmd "chown root:www-data /var/log/mail.log"
		echo ${EMAIL_DOMAIN} > /etc/mailname
		sys_cmd "postfix stop"
		sys_cmd "postfix start"
	fi
	
	if ! valid_ip $EMAIL_DOMAIN && [[ $EMAIL_DOMAIN != 'localhost' ]]
		then
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
			sys_cmd "sed -i 's/AutoRestartRate .*$/AutoRestartRate           10\/1h/' /etc/opendkim.conf"
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
			grep -q -P '^SOCKET=' /etc/default/opendkim || echo 'SOCKET="inet:12301@localhost"' >> /etc/default/opendkim
			sys_cmd "sed -i 's/^SOCKET=.*$/SOCKET=\"inet:12301@localhost\"/' /etc/default/opendkim"
			grep -q -F 'milter_protocol' /etc/postfix/main.cf || echo 'milter_protocol = 2' >> /etc/postfix/main.cf
			sys_cmd "sed -i 's/^.*milter_protocol = .*$/milter_protocol = 2/' /etc/postfix/main.cf"
			grep -q -F 'milter_default_action' /etc/postfix/main.cf || echo 'milter_default_action = accept' >> /etc/postfix/main.cf
			sys_cmd "sed -i 's/^.*milter_default_action = .*$/milter_default_action = accept/' /etc/postfix/main.cf"
			grep -q -P '^smtpd_milters' /etc/postfix/main.cf || echo 'smtpd_milters = inet:localhost:12301' >> /etc/postfix/main.cf
			sys_cmd "sed -i 's/^smtpd_milters = .*$/smtpd_milters = inet:localhost:12301/' /etc/postfix/main.cf"
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
			DNS_INSTRUCTIONS+=("${LCYAN}TXT${RESTORE} record for '${LGREEN}mail._domainkey${RESTORE}' with text: \n            ${LYELLOW}${DKIM_KEY}${RESTORE}")
			sys_cmd "popd"
			sys_cmd "popd"
		fi
	
	
		info "Configuring Dovecot"
		if [[ $OS = "Ubuntu" ]]
			then
			sys_cmd "sed -i 's/^.*disable_plaintext_auth = .*$/disable_plaintext_auth = no/' /etc/dovecot/conf.d/10-auth.conf"
			sys_cmd "sed -i 's/^.*auth_mechanisms = .*$/auth_mechanisms = plain login/' /etc/dovecot/conf.d/10-auth.conf"
			sys_cmd "sed -i 's/^#\?log_path =.*$/log_path = \/var\/log\/dovecot.log/' /etc/dovecot/conf.d/10-logging.conf"
			sys_cmd "touch /var/log/dovecot.log"
			sys_cmd "chown root:www-data /var/log/dovecot.log"
		fi
	else
		notice "Skipping OpenDKIM and Dovecot installs because you aren't sending email from your own domain"
	fi
	
	info "Restarting mail services"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "postfix stop"
		sys_cmd "postfix start"
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
		sys_cmd "sed -i 's/IMAP_HOST=.*$/IMAP_HOST=127.0.0.1/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/IMAP_PORT=.*$/IMAP_PORT=143/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/IMAP_USERNAME=.*$/IMAP_USERNAME=fiercephish/' /var/www/fiercephish/.env"
		sys_cmd "sed -i 's/IMAP_PASSWORD=.*$/IMAP_PASSWORD=${IMAP_PASSWORD}/' /var/www/fiercephish/.env"
		sys_cmd "pushd /var/www/fiercephish"
		sys_cmd "php artisan config:cache"
		sys_cmd "popd"
	else
		error "Unable to find the .env file for FiercePhish.  You'll have to update it manually at the end"
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_DRIVER=smtp${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_HOST=127.0.0.1${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_PORT=25${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_USERNAME=null${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_PASSWORD=null${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}MAIL_ENCRYPTION=null${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}IMAP_HOST=127.0.0.1${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}IMAP_PORT=143${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}IMAP_USERNAME=fiercephish${RESTORE}")
		FP_INSTRUCTIONS+=("Edit the following in ${LGREEN}.env${RESTORE}:  ${LYELLOW}IMAP_PASSWORD=${IMAP_PASSWORD}${RESTORE}")
		FP_INSTRUCTIONS+=("Run: ${LYELLOW}php artisan config:cache${RESTORE}")
		sleep 5
	fi
	
	if ! valid_ip $EMAIL_DOMAIN && [[ $EMAIL_DOMAIN != 'localhost' ]]
		then
		DNS_INSTRUCTIONS+=("${LCYAN}A${RESTORE} record for '${LGREEN}mail${RESTORE}' point to '${LYELLOW}${SERVER_IP}${RESTORE}'")
		DNS_INSTRUCTIONS+=("${LCYAN}MX${RESTORE} record point to '${LGREEN}mail${RESTORE}' subdomain (or MXE record pointing to ${LGREEN}${SERVER_IP}${RESTORE})")
		DNS_INSTRUCTIONS+=("${LCYAN}TXT${RESTORE} record for '${LGREEN}@${RESTORE}' with text: ${LYELLOW}v=spf1 a mx a:mail.${EMAIL_DOMAIN} a:${EMAIL_DOMAIN} ip4:${SERVER_IP} ~all${RESTORE}")
		DNS_INSTRUCTIONS+=("${LCYAN}TXT${RESTORE} record for '${LGREEN}_dmarc${RESTORE}' with text: ${LYELLOW}v=DMARC1; p=none${RESTORE}");
	fi
	notice "Done installing SMTP and IMAP!"
}


install_ssl()
{
	if [[ -f /etc/apache2/sites-available/fiercephish.conf ]]
		then
		info "Downloading LetsEncrypt"
		sys_cmd "pushd /usr/local/sbin/"
		sys_cmd "wget https://dl.eff.org/certbot-auto -O certbot-auto"
		sys_cmd "chmod a+x /usr/local/sbin/certbot-auto"
		info "Installing and configuring LetsEncrypt...this can take a few minutes"
		if [[ $VERBOSE = "true" ]]
			then
			resp=$(certbot-auto -n -d ${SSL_DOMAIN} --agree-tos --email ${SSL_EMAIL} --redirect --hsts --apache 2>&1 | tee /dev/tty)
		else
			resp=$(certbot-auto -n -d ${SSL_DOMAIN} --agree-tos --email ${SSL_EMAIL} --redirect --hsts --apache 2>&1)
		fi
		if [[ $resp =~ Failed ]]
			then
			error "Error creating SSL certificate!  Check to make sure the A record of your domain \"${SSL_DOMAIN}\" is properly set."
			error "Full error log is at ~/letsencrypt_error.log"
			echo "${resp}" > ~/letsencrypt_error.log
			exit 1;
		elif [[ $resp =~ Congratulations ]]
			then
			info "Success! Your FiercePhish instance is now SSL encrypted!"
		elif [[ $resp =~ existing ]]
			then
			error "You already have an SSL certificate set up for this domain"
		else
			error "Unknown response: ${resp}"
		fi
		info "Setting up SSL certificate auto renewal"
		cron_command="/usr/local/sbin/certbot-auto renew >> /var/log/le-renew.log 2>&1"
		cron_job="30 2 * * 1 $cron_command"
		cat <(grep -i -F -v "$cron_command" <(crontab -u root -l 2>/dev/null)) <(echo "$cron_job") | crontab -u root -
		info "Done setting up the SSL certificate"
		info "Installation complete"
	else
		error "FiercePhish is not installed!  Can't set up SSL encryption if FiercePhish isn't installed"
	fi
	sys_cmd "popd"
}


uninstall_ssl()
{
	if [[ -f /etc/apache2/sites-enabled/fiercephish.conf ]]
		then
		if [[ -f /etc/apache2/sites-enabled/fiercephish-le-ssl.conf ]]
			then
			info "Disabling FiercePhish's SSL configuration"
			sys_cmd "a2dissite fiercephish-le-ssl"
			info "Removing HTTP to HTTPS redirect"
			sys_cmd "sed -i 's/^RewriteEngine .*$//' /etc/apache2/sites-available/fiercephish.conf"
			sys_cmd "sed -i 's/^RewriteCond .*$//' /etc/apache2/sites-available/fiercephish.conf"
			sys_cmd "sed -i 's/^RewriteRule .*$//' /etc/apache2/sites-available/fiercephish.conf"
			info "Rebooting Apache"
			sys_cmd "service apache2 restart"
			info "Done!  SSL disabled"
		else
			error "SSL is not enabled for FiercePhish"
		fi
	else
		error "FiercePhish is not installed!"
	fi
}



### Helper functions

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
		read -e input_answer
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


valid_ip()
{
    local  ip=$1
    local  stat=1

    if [[ $ip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        OIFS=$IFS
        IFS='.'
        ip=($ip)
        IFS=$OIFS
        [[ ${ip[0]} -le 255 && ${ip[1]} -le 255 \
            && ${ip[2]} -le 255 && ${ip[3]} -le 255 ]]
        stat=$?
    fi
    return $stat
}

## Execute main function

main "$@"

