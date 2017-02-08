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
VERBOSE=false
BACKUP_LOCATION="/tmp/fiercephish_db_backup.sql"
GITHUB_BRANCH="master"

## Main function
main()
{
    if [ $EUID != 0 ]
    	then
    	error "You must run the installation script as root"
    	exit 1
    fi
    if [[ ! $1 = '-r' ]]
    then
        show_header
        validate_vars_general
        check_install
        check_new_version
        self_update
        exit 0
    else
        check_os
        run_update
        exit 0
    fi
}

## Action functions
show_header()
{
    echo -e ""
    echo -e "${CYAN}#################################################################"
    echo -e "####################   ${LYELLOW}FiercePhish Updater${CYAN}   ####################"
    echo -e "####################      By ${GREEN}Chris King${CYAN}      ####################"
    echo -e "####################       ${GREEN}@raikiasec${CYAN}        ####################"
    echo -e "#################################################################${RESTORE}"
    echo -e ""
    echo -e ""
    notice "This updater automatically updates your FiercePhish instance to the latest version."
    notice "It is designed to work with Ubuntu and currently only works for Ubuntu 16.04 and Ubuntu 16.10"
    notice "This updater expects you to have installed FiercePhish using the automated installer or following"
    notice "the manual install method"
    echo -e ""
}

check_new_version()
{
    info "Checking for new FiercePhish version"
    local current_version=$(cat VERSION)
    local latest_version=$(curl -s https://raw.githubusercontent.com/Raikia/FiercePhish/${GITHUB_BRANCH}/VERSION?${RANDOM})
    
    if [[ $current_version == $latest_version ]]
        then
        info "You are already running the latest version of FiercePhish (v${current_version})!"
        exit 0
    fi
    notice "Update available!"
    notice "You are running v${LYELLOW}${current_version}${WHITE} and the latest version is v${LYELLOW}${latest_version}${WHITE}!"
    prompt "Do you want to update? [y/N]"
    INPUT=$(get_input "n")
    if [[ $INPUT = "" ]]
        then
        INPUT="n"
    fi
    if [[ ! $INPUT =~ ^[y|Y]$ ]]
        then
        error "Exiting updater"
        exit 1
    fi 
    
}

check_install()
{
    info "Looking for FiercePhish instance..."
    if [[ ! -f /etc/apache2/sites-enabled/fiercephish.conf || ! -d /var/www/fiercephish/ ]]
        then
        error "Unable to find a FiercePhish instance.  Did you install it with the automated installer or with the correct settings?"
        exit 1
    fi
}

self_update()
{
    info "Pulling latest version from GitHub"
    sys_cmd "pushd /var/www/fiercephish/"
    sys_cmd "wget --no-cache -O update.sh https://raw.githubusercontent.com/Raikia/FiercePhish/${GITHUB_BRANCH}/update.sh?${RANDOM}"
    info "Successfully pulled the latest updater!"
    info "Beginning update process"
    /usr/bin/env bash ./update.sh -r
}

validate_vars_general()
{
	mem=$(free -m | awk '/^Mem:/{print $2}')
	swap=$(free -m | awk '/^Swap:/{print $2}')
	total=$(($mem+$swap))
	CREATE_SWAPSPACE="false"
	if [[ $total -lt 600 ]]
		then
		error "System memory + swap is less than 600 MB (it has ${total} MB)!"
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
			else
				error "You are running ${LYELLOW}${OS} ${OS_VERSION}${LRED}. This is not supported by this update. There's really no reason to not update Ubuntu"
				exit 1
			fi
		else
			error "You are running ${LYELLOW}${OS} ${OS_VERSION}${RESTORE}. This isn't supported by the updater yet"
			exit 1;
		fi
	else
		error "Could not identify what OS is running! This updater works on Ubuntu only right now"
		exit 1
	fi
	echo -e ""
}

run_update()
{
	info "Ensuring dependencies are properly installed"
	sys_cmd "apt-get update"
	sys_cmd "apt-get install -y git curl unzip"
	sys_cmd "pushd /var/www/fiercephish/"
	info "Updating Bower"
	sys_cmd "/usr/bin/env npm cache clean"
	sys_cmd "/usr/bin/env npm update -g bower"
	info "Updating Composer"
	sys_cmd "composer self-update"
	info "Putting FiercePhish into maintenance mode"
	sys_cmd "/usr/bin/env php artisan down"
	backup_database
	info "Pulling the latest version of FiercePhish"
	sys_cmd "git fetch --all"
	sys_cmd "git reset --hard origin/${GITHUB_BRANCH}"
	info "Updating Composer"
	sys_cmd "composer install"
	info "Updating Bower"
	sys_cmd "bower install --allow-root"
	info "Running migrations"
	sys_cmd "/usr/bin/env php artisan migrate"
	update_env
	info "Updating cron job"
	cron_command="/usr/bin/env php /var/www/fiercephish/artisan schedule:run >> /dev/null 2>&1"
	cron_job="* * * * * $cron_command"
	cat <(grep -i -F -v "$cron_command" <(crontab -u www-data -l 2>/dev/null)) <(echo "$cron_job") | crontab -u www-data -
	info "Updating Supervisor for job processing"
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
		sys_cmd "service supervisor restart"
		sleep 5
		sys_cmd "supervisorctl reload"
		sleep 10
		sys_cmd "service supervisor restart"
		sleep 5
	fi
	info "Restarting queue workers"
	sys_cmd "/usr/bin/env php artisan queue:restart"
	info "Clearing cache"
	sys_cmd "/usr/bin/env php artisan cache:clear"
	sys_cmd "/usr/bin/env php artisan clear-compiled"
	info "Setting proper permissions"
	if [[ $OS = "Ubuntu" ]]
		then
		sys_cmd "chown -R www-data:www-data /var/www/fiercephish/"
	fi
	info "Turning off maintenance mode"
	sys_cmd "/usr/bin/env php artisan up"
	notice "Update complete!"
	cleanup_backup
	notice "Process complete! Enjoy the new FiercePhish"
}

update_env()
{
	info "Creating new .env file"
	local envVars=("APP_ENV" "APP_DEBUG" "APP_LOG_LEVEL" "APP_TIMEZONE" "APP_KEY" "APP_URL" "APP_NAME" "PROXY_URL" "PROXY_SCHEMA" "DB_CONNECTION" "DB_HOST" "DB_PORT" "DB_USERNAME" "DB_PASSWORD" "DB_DATABASE" "CACHE_DRIVER" "SESSION_DRIVER" "BROADCAST_DRIVER" "QUEUE_DRIVER" "REDIS_HOST" "REDIS_PASSWORD" "REDIS_PORT" "PUSHER_APP_ID" "PUSHER_APP_KEY" "PUSHER_APP_SECRET" "MAIL_DRIVER" "MAIL_HOST" "MAIL_PORT" "MAIL_USERNAME" "MAIL_PASSWORD" "MAIL_ENCRYPTION" "MAILGUN_DOMAIN" "MAILGUN_SECRET" "URI_PREFIX" "TEST_EMAIL_JOB" "IMAP_HOST" "IMAP_PORT" "IMAP_USERNAME" "IMAP_PASSWORD" "MAIL_BCC_ALL")
	sys_cmd "mv .env .env_old"
	sys_cmd "cp .env.example .env"
	source .env_old
	for i in "${!envVars[@]}"
		do 
		eval tempVar=\$${envVars[$i]}
		tempVar=${tempVar//\//\\/}
		sys_cmd "sed -i 's/${envVars[$i]}=.*$/${envVars[$i]}=${tempVar}/' .env"
	done
	sys_cmd "rm .env_old"
	info "Caching new configuration"
	sys_cmd "/usr/bin/env php artisan config:cache"
}


backup_database()
{
	source .env
	info "Backing up the FiercePhish database"
	mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME"  --password="$DB_PASSWORD" "$DB_DATABASE" > $BACKUP_LOCATION 2> /dev/null
	info "Done backing up database"
}

cleanup_backup()
{
	notice "Do you want to clean up the database backup file (${BACKUP_LOCATION})?"
	prompt "Delete backups? [y/N]"
	INPUT=$(get_input "n")
	if [[ $INPUT =~ ^[y|Y]$ ]]
		then
		info "Removing backups"
		sys_cmd "rm ${BACKUP_LOCATION}"
		info "Backups removed"
	else
		info "Keeping backup database dump, located: ${LYELLOW}${BACKUP_LOCATION}${RESET}"
	fi
}

## Helper functions

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

main "$@"

