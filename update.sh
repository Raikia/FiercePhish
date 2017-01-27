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
#GITHUB_URL="https://raw.githubusercontent.com/Raikia/FiercePhish/master"
GITHUB_URL="https://raw.githubusercontent.com/Raikia/FiercePhish/updater"

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
        #check_install
        check_new_version
        self_update
    else
    	echo -e "UPDATE LOLOLOLOL"
    	exit 1
        check_os
        run_update
    fi
}

## Action functions
show_header()
{
    echo -e ""
    echo -e "${CYAN}################################################################"
    echo -e "####################  ${LYELLOW}FiercePhish Updater${CYAN}  ####################"
    echo -e "####################     By ${GREEN}Chris King${CYAN}      ####################"
    echo -e "####################       ${GREEN}@raikiasec${CYAN}       ####################"
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
    local latest_version=$(curl -s https://raw.githubusercontent.com/Raikia/FiercePhish/updater/VERSION)
    
    if [[ $current_version == $latest_version ]]
        then
        info "You are already running the latest version of FiercePhish (v${current_version})!"
        exit 0
    fi
    notice "Update available!"
    notice "You are running v${current_version} and the latest version is v${latest_version}!"
    prompt "Do you want to update? [y/N]"
    INPUT=$(get_input "n")
    if [[ $INPUT = "" ]]
        then
        INPUT="n"
    fi
    if [[ ! $INPUT =~ ^[y|Y]$ ]]
        then
        error "Exiting updater"
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
    sys_cmd "wget -O update.sh ${GITHUB_URL}/update.sh"
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

