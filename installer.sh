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
FIREPHISH_MYSQL_PASSWD=

### Functions ###
confirm()
{
	local prompt=$1
	while [ true ]
	do
		echo -e -n "${LYELLOW}[?]  ${prompt} [y/n]: ${RESTORE}"
		answer=$(get_input "y")
		case $answer in
			[yY])
				echo "Yes!"
				break
				;;
			[nN])
				echo "No!"
				break
				;;
			*)
				echo "What?"
				;;
		esac
	done
}

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
	echo -e "   ${LYELLOW}[>] ${prompt} > ${RESTORE}"
}

sys_cmd()
{
	com=$1
	if [[ $VERBOSE = true ]]
		then
		notice "Running ${com}..."
		eval "${com}"
	else
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
	echo -e "${CYAN}----------------- ${LYELLOW}FirePhish Installer${CYAN} ----------------"
	echo -e "|                                                    |"
	echo -e "|  This installer automatically install FirePhish    |"
	echo -e "|  and all the other services needed. It is designed |"
	echo -e "|  designed to work with Ubuntu, but it will attempt |"
	echo -e "|  to detect what distro you are running.            |"
	echo -e "|                                                    |"
	echo -e "------------------------------------------------------${RESTORE}"
	echo -e ""
	echo -e "    ${LYELLOW}Options:${RESTORE} "
	echo -e "        1. ${WHITE}Install FirePhish + SMTP + IMAP (${LRED}recommended${WHITE})${RESTORE}"
	echo -e "        2. ${WHITE}Install FirePhish only${RESTORE}"
	echo -e "        3. ${WHITE}Install SMTP + IMAP only${RESTORE}"
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
		echo -e "${LRED}  !!! This is the quick install method and will install FirePhish + SMTP + IMAP !!!"
		echo -e "  !!!       You have 10 seconds to CTRL+C if you do not want this to happen     !!!${RESTORE}"
		sleep 10
	fi
	detect_os
	if [[ $selection -eq 1 ]]
		then
		install_firephish
		install_smtp_imap
	elif [[ $selection -eq 2 ]]
		then
		install_firephish
	elif [[ $selection -eq 3 ]]
		then
		install_smtp_imap
	else
		error "Unknown action selected"
		exit 1
	fi
}

install_firephish()
{
	info "Installing FirePhish!"


	info "Updating package repositories"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "apt-get update"
	fi
	exit 1

	info "Installing the required packages"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "14.04" ]]
			then
			if [ -z $MYSQL_ROOT_PASSWD ]
				then
				prompt "Set MySQL root passsword"
				MYSQL_ROOT_PASSWD=$(get_input "root")
			fi
			sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWD'"
			sys_cmd "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWD'"
			sys_cmd "DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 php5 php5-cli mysql-server php5-mysql libapache2-mod-php5 php5-mcrypt phpunit npm unzip git curl"
		fi
	fi


	info "Installing Composer"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "14.04" ]]
			then
			sys_cmd "curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer"
		fi
	fi


	info "Installing Bower"
	if [[ $OS = "Ubuntu" ]]
		then
		if [[ $OS_VERSION = "14.04" ]]
			then
			sys_cmd "npm install -g bower"
			sys_cmd "ln -s /usr/bin/nodejs /usr/bin/node"
		fi
	fi


	info "Pulling the latest FirePhish from GitHub to /var/www/firephish"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "git clone https://github.com/Raikia/FirePhish.git /var/www/firephish"
		sys_cmd "chown -R www-data:www-data /var/www/firephish"
	fi


	info "Installing FirePhish into Apache (this can take a few minutes)"
	if [ -z $WEBSITE_DOMAIN ]
		then
		prompt "What is the domain name of the website (ie: example.com) (IP address is ok)"
		WEBSITE_DOMAIN=$(get_input "localhost")
	fi
	if [[ $OS = "Ubuntu" ]]
		then
		cat > /etc/apache2/sites-available/firephish.conf <<- EOM
<VirtualHost *:80>
	ServerName $WEBSITE_DOMAIN
	ServerAdmin webmaster@localhost
    DocumentRoot /var/www/firephish/public
    <Directory /var/www/firephish>
    	Options FollowSymLinks
    	AllowOverride All
    	Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOM
		sys_cmd "a2ensite firephish"
		sys_cmd "a2enmod rewrite"
		sys_cmd "a2dissite 000-default"
		sys_cmd "service apache2 restart"
		sys_cmd "pushd /var/www/firephish"
		sys_cmd "composer install"
		sys_cmd "bower install --allow-root"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'create database firephish'"
		FIREPHISH_MYSQL_PASSWD=$(random_str)
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e \$'create user firephish@localhost identified by \'${FIREPHISH_MYSQL_PASSWD}\''"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'grant all privileges on firephish.* to firephish@localhost'"
		sys_cmd "mysql -u root -p${MYSQL_ROOT_PASSWD} -e 'flush privileges'"
		sys_cmd "popd"
	fi


	info "Configuring FirePhish"
	sys_cmd "pushd /var/www/firephish"
	sys_cmd "cp .env.example .env"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "chown www-data:www-data .env"
	fi
	sys_cmd "sed -i 's/APP_DEBUG=.*$/APP_DEBUG=false/' .env"
	sys_cmd "sed -i 's/APP_URL=.*$/APP_URL=http:\/\/${WEBSITE_DOMAIN}/' .env"
	sys_cmd "sed -i 's/DB_USERNAME=.*$/DB_USERNAME=firephish/' .env"
	sys_cmd "sed -i 's/DB_PASSWORD=.*$/DB_PASSWORD=${FIREPHISH_MYSQL_PASSWD}/' .env"

	info "Generating database"
	sys_cmd "php artisan key:generate"
	sys_cmd "php artisan migrate"

	info "Creating User"
	if [[ -z ${ADMIN_USERNAME} ]]
		then
		sys_cmd "php artisan fp:createuser -c"
	else
		sys_cmd "php artisan fp:createuser -c ${ADMIN_USERNAME} ${ADMIN_EMAIL} ${ADMIN_PASSWORD}"
	fi

	notice "Done installing FirePhish!"
}

install_smtp_imap()
{
	echo ""
}

if [[ $1 = '-v' ]]
	then VERBOSE=true
fi
if [ $EUID != 0 ]; then
	error "You must run the installer script as root"
	exit 1
fi

if [[ $0 = "bash" && ! -f ~/firephish.config ]]
	then
	error "Because you are running this as a remote pipe execution, you need to create a configuration file for all the information that is required."
	notice "Please edit ~/firephish.config with the necessary information and rerun this command"
	cat > ~/firephish.config <<- EOM
#### FirePhish Installation Configuration File ####
CONFIGURED=false            # Set this to true once you are done configuring everything
MYSQL_ROOT_PASSWD=          # Set this to what you want the mysql root password to be
WEBSITE_DOMAIN=             # Set this to what the website domain is that will be sending email (ie: example.com)
ADMIN_USERNAME=admin        # Set username for FirePhish here
ADMIN_EMAIL=root@localhost  # Set email for FirePhish user
ADMIN_PASSWORD=test         # Set password for FirePhish here
EOM
	exit 1
elif [[ $0 = "bash" && -f ~/firephish.config ]]
	then
	source ~/firephish.config
	if [[ $CONFIGURED = true ]]
		then
		if [[ -z $CONFIGURED || -z $MYSQL_ROOT_PASSWD || -z $WEBSITE_DOMAIN || -z $ADMIN_USERNAME || -z $ADMIN_EMAIL || -z $ADMIN_PASSWORD ]]
			then
			error "Found the configuration file, but it is missing some variables!"
			exit 1
		fi
		info "Found the FirePhish configuration file and am continuing with installation"
	else
		error "Edit ~/firephish.config with the proper settings. Once done, make sure CONFIGURED=true at the top"
		exit 1
	fi
fi

main
